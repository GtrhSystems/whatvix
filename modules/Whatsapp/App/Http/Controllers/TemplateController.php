<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Models\Template;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Whatsapp\App\Requests\TemplateRequest;

class TemplateController extends Controller
{
    public function index()
    {
        if (request('sync_templates') == 1) {
            validateWorkspacePlan('custom_template');
            try {
                $this->syncTemplates();
                Toastr::success(__('Template Synced Successfully'));
            } catch (\Throwable $th) {
                Toastr::danger($th->getMessage());
            }

            return to_route('user.whatsapp.templates.index');
        }

        $query = activeWorkspaceOwner()->templates()->whatsapp();
        PageHeader::set()
            ->title('Device Template')
            ->overviews([
                [
                    'icon' => "bx:checkbox-checked",
                    'title' => 'Total Templates',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:color",
                    'title' => 'Custom Template',
                    'value' => $query->clone()->whereNot('type', 'template')->count(),
                ],
                [
                    'icon' => "bx-file",
                    'title' => 'Interactive Template',
                    'value' => $query->clone()->where('type', 'interactive')->count(),
                ],

            ])
            ->addLink('Sync Template', route('user.whatsapp.templates.index', ['sync_templates' => true]), 'bx:sync')
            ->addLink('Add New', route('user.whatsapp.templates.create'), 'bx:plus');
        ;

        $templates = Template::with('platform:id,name')
            ->where('owner_id', activeWorkspaceOwnerId())
            ->whatsapp()
            ->filterOn(['name', 'status'])
            ->latest()
            ->paginate();

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
       

        PageHeader::set()
            ->title('Create Template')
            ->buttons([
                [
                    'text' => 'Back',
                    'url' => route('user.whatsapp.templates.index')
                ]
            ]);

        return Inertia::render('Templates/Create');
    }

    public function store(TemplateRequest $request)
    {
        Template::create([
            'module' => 'whatsapp',
            'owner_id' => activeWorkspaceOwnerId(),
            'name' => $request->name,
            'type' => $request->message_type,
            'meta' => $request->meta,
        ]);

        return to_route('user.whatsapp.templates.index')->with('success', 'Template Saved Successfully');
    }

    public function show(Template $template)
    {
        PageHeader::set()->title('Device Template Show')->buttons([
            [
                'text' => 'Back to List',
                'url' => route('user.whatsapp.templates.index'),
            ],
        ]);

        return Inertia::render('Templates/Show', [
            'template' => $template,
        ]);
    }
    public function edit(Template $template)
    {

        PageHeader::set()->title('Edit Template')
            ->buttons([
                [
                    'text' => 'Back to List',
                    'url' => route('user.whatsapp.templates.index'),
                ],
            ]);

        return Inertia::render('Templates/Create', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, Template $template)
    {
        $template->update([
            'name' => $request->name,
            'meta' => $request->meta,
            'status' => 'active',
        ]);

        return to_route('user.whatsapp.templates.index')
            ->with('success', 'Template Updated Successfully');
    }

    public function destroy(Template $template)
    {
        $template->validateOwnership();
        $template->delete();
        return back()->with('success', 'Template Deleted Successfully');
    }

    /**
     * Sync all whatsapp devices templates with the current user's templates
     *
     * @return void
     */
    protected function syncTemplates()
    {
        $devices = activeWorkspaceOwner()->platforms()->whatsapp()->get();
        foreach ($devices as $device) {
            $device->syncTemplates();
        }
    }

    public function getDeviceTemplateList(Request $request)
    {
        $user = activeWorkspaceOwner();
        $templates = $user
            ->templates()
            ->whatsapp()
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->input('type'));
                if (request('type') == 'template') {
                    $q->orWhereNull('type');
                }
                return $q;
            })
            ->when(
                $request->filled('platform_id') && $request->get('type'),
                function ($q) use ($request) {
                    if (request('type') == 'interactive')
                        return $q;

                    $q->where('platform_id', $request->input('platform_id'));
                }
            )

            ->latest()
            ->get();

        return $templates;
    }

    public function copy(Template $template)
    {
        $template->validateOwnership();
        $copyTemplate = $template->replicate();
        $copyTemplate->name .= ' - Copy';
        $copyTemplate->status = 'draft';
        $copyTemplate->save();

        return back()->with('success', 'Template Copied Successfully');
    }
}
