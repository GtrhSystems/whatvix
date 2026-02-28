<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\Template;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;

class TemplateController extends Controller
{
    public function index()
    {

        $query = Template::query();

        PageHeader::set()
            ->title('Templates')
            ->overviews([
                [
                    'icon' => "bx:checkbox-checked",
                    'title' => 'Active Templates',
                    'value' => $query->clone()->whereNot('status', 'APPROVED')->count(),
                ],
                [
                    'icon' => "bx:x-circle",
                    'title' => 'Pending Templates',
                    'value' => $query->clone()->where('status', 'APPROVED')->count(), 
                ],
            ]);

        $templates = Template::with(['platform:id,name', 'owner:id,name'])
            ->when(request()->filled('search'), function ($query) {
                $query->whereLike('name', '%' . request()->search . '%');
            })
            ->latest()
            ->paginate();

        return Inertia::render('Admin/Logs/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return back()->with('success', 'Template Deleted Successfully');
    }
}
