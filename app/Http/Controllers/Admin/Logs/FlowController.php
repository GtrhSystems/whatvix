<?php


namespace App\Http\Controllers\Admin\Logs;

use App\Models\Flow;
use Inertia\Inertia;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FlowController extends Controller
{
    public function index(Request $request)
    {
        PageHeader::set()->title('Flows')->overviews([
            [
                'title' => 'Total Flows',
                'value' => Flow::count(),
                'icon' => 'bx:bar-chart'
            ],
            [
                'icon' => 'bx:check-circle',
                'title' => 'Active Chat Flows',
                'value' => Flow::where('status', 'active')->count(),
            ],
            [
                'icon' => 'bx:x-circle',
                'title' => 'Inactive Chat Flows',
                'value' => Flow::where('status', 'inactive')->count(),
            ],
        ]);

        $flows = Flow::with('device:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('keyword', 'like', "%{$request->search}%")
                    ->orWhereRelation('template', 'name', 'like', "%{$request->search}%");
            })
            ->latest()->paginate();

        return Inertia::render('Admin/Logs/Flows/Index', [
            'flows' => $flows
        ]);
    }

    public function destroy($id)
    {
        $chat_bot = Flow::findOrFail($id);
        $chat_bot->delete();
        return back()->with('success', 'Flow deleted successfully');
    }
}
