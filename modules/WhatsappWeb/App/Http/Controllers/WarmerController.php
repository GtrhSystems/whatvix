<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\WhatsappWeb\App\Models\Warmer;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class WarmerController extends Controller
{
    public function index()
    {
        $query = Warmer::where('user_id', activeWorkspaceOwnerId());
        PageHeader::set('Warmer')
            ->addModal('Add New', 'warmerCreate')
            ->addOverview('Total Warmers Dataset', $query->clone()->count(), 'bx:list-ul')
            ->addOverview('Created In Last 7 days', $query->clone()->whereDate('created_at', '>=', now()->subDays(7))->count(), 'bx:calendar')
            ->addOverview('Created In Last 30 days', $query->clone()->whereDate('created_at', '>=', now()->subDays(30))->count(), 'bx:calendar-alt');

        $warmers = $query->latest()->paginate();
        return Inertia::render('Warmer/Index', [
            'warmers' => $warmers
        ]);
    }
    public function show($id)
    {
        PageHeader::set('Warmer')
            ->addModal('Configure Automation', 'automationConfig', 'bx:cog')
            ->addModal('Configure Q&A Flow', 'qaConfig', 'material-symbols-light:flowchart-outline-sharp');
        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();
        $platforms = $user->platforms()
            ->where('status', 'connected')
            ->whereJsonContains('meta->verified', true)
            ->whatsappWeb()
            ->get();
        $warmer = Warmer::query()
            ->where('user_id', $user->id)
            ->findOrFail($id);
        return Inertia::render('Warmer/Show', [
            'platforms' => $platforms,
            'warmer' => $warmer
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $warmer = Warmer::create([
            'user_id' => activeWorkspaceOwnerId(),
            'title' => $request->title
        ]);
        return to_route('user.whatsapp-web.warmer.show', $warmer->id)->with('success', 'Warmer created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'dataset' => 'nullable|array',
            'dataset.*.question' => 'required|string',
            'dataset.*.answer' => 'required|string',
        ], [
            'dataset.*.question.required' => 'question field is required.',
            'dataset.*.answer.required' => 'answer field is required.',
        ]);

        $warmer = Warmer::findOrFail($id);

        $dataset = $request->filled('dataset') ? $request->dataset : $warmer->dataset;
        $warmer->update([
            'title' => $request->title,
            'dataset' => $dataset,
        ]);
        return back()->with('success', 'Warmer updated successfully.');
    }

    public function sendMessage(Request $request, WhatsAppWebService $whatsAppWebService)
    {

        $request->validate([
            'device_id' => 'required',
            'sender_id' => 'required', // receiver_id
            'message' => 'required|string',
            'delay' => 'required|numeric|between:0,100',
        ]);
        $receiver = Platform::where('id', $request->get('device_id'))
            ->where('owner_id', activeWorkspaceOwnerId())
            ->where('module', 'whatsapp-web')
            ->firstOrFail();
        $sender = Platform::where('id', $request->get('sender_id'))
            ->where('owner_id', activeWorkspaceOwnerId())
            ->where('module', 'whatsapp-web')
            ->firstOrFail();

        $jid = $whatsAppWebService->setJid($receiver->meta['phone_number']);
        $message = [
            'text' => $request->message
        ];
        sleep($request->get('delay'));
        $newMessage = $whatsAppWebService
            ->sendMessage($sender->uuid, $jid, $message, 'text');
        if ($newMessage->successful()) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function destroy($id)
    {
        $warmer = Warmer::where('user_id', activeWorkspaceOwnerId())->findOrFail($id);
        $warmer->delete();
        return back()->with('danger', 'Warmer deleted successfully.');
    }
}
