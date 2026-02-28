<?php

namespace Modules\WhatsappWeb\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\WhatsappWeb\App\Models\BulkSendLog;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class BulkSendController extends Controller
{
    public function contact_list(Request $request)
    {
        $user = $request->user();
        $contacts = $user->groups()
            ->whereIn("id", $request->group_ids)
            ->whatsappWeb()->with('customers')->get();

        $contacts = $contacts->flatMap(function ($group) {
            return $group->customers->map(function ($customer) use ($group) {
                return [
                    'id' => $customer->id,
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'name' => $customer->name,
                    'phone' => $customer->uuid,
                    'status' => 'pending',
                    'has_whatsapp' => 'pending',
                ];
            });
        })->values();
        return response()->json($contacts);
    }
    public function replaceShortCodes(string $text, array $replaceCodes): string
    {
        return str_replace(array_keys($replaceCodes), array_values($replaceCodes), $text);
    }
    public function send_message(Request $request, WhatsAppWebService $whatsAppWebService)
    {
        $contact = $request->contact;
        $form = $request->form;
        $platform = Platform::where('id', $form['platform_id'])
            ->where('owner_id', activeWorkspaceOwnerId())
            ->where('module', 'whatsapp-web')
            ->firstOrFail();
        $messageType = 'text';
        if ($form['message_type'] == 'text') {
            $message = ['text' => $form['message']];
        }
        if ($form['message_type'] == 'template') {
            $template = Template::whatsappWeb()
                ->where('id', $form['template_id'])
                ->firstOrFail();
            $message = $template->meta;
            $messageType = $template->type;
            $shortcodes = [
                '{name}' => $contact['name'] ?? '',
            ];
            if (isset($message['text'])) {
                $message['text'] = $this->replaceShortCodes($message['text'], $shortcodes);
            }

            if (isset($message['caption'])) {
                $message['caption'] = $this->replaceShortCodes($message['caption'], $shortcodes);
            }
        }
        $jid = $whatsAppWebService->setJid($contact['phone']);
        $res = $whatsAppWebService
            ->sendMessage($platform->uuid, $jid, $message, $messageType);
        $bulkSendLog = BulkSendLog::create([
            'platform_id' => $platform->id,
            'user_id' => activeWorkspaceOwnerId(),
            'template_id' => $form['template_id'] ?? null,
            'recipient_number' => $contact['phone'],
            'message_type' => $messageType,
            'meta' => $message,
        ]);
        if ($res->successful()) {
            $bulkSendLog->update(['status' => 'sent']);
            return response()->json(['success' => true, 'data' => $res->json()]);
        } else {
            $bulkSendLog->update(['status' => 'failed']);
            return response()->json(['success' => false, 'data' => $res->json()], 400);
        }
    }
}
