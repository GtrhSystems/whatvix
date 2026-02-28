<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Template;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Services\AssetService;
use App\Http\Controllers\Controller;

class TemplateController extends Controller
{
    use Uploader;
    public function index()
    {
        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();
        $query = $user
            ->templates()
            ->whatsappWeb();


        PageHeader::set()
            ->title('Templates')
            ->overviews([
                [
                    'icon' => "bx:grid-alt",
                    'title' => 'Total Templates',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:check-circle",
                    'title' => 'Active Templates',
                    'value' => $query->clone()->where('status', "active")->count(),
                ],
                [
                    'icon' => "bx:x-circle",
                    'title' => 'Inactive Templates',
                    'value' => $query->clone()->where('status','!=', "active")->count(),
                ]
            ])
            ->addLink('Add New', route('user.whatsapp-web.templates.create'), 'bx:plus');

        $templates = $query->filterOn(['name', 'status'])->latest()->paginate();

        return Inertia::render('Templates/Index', [
            'templates' => $templates
        ]);
    }

    public function create()
    {
        validateWorkspacePlan('custom_template');
        PageHeader::set()
            ->title('Create Template')
            ->buttons([
                [
                    'text' => 'Back',
                    'url' => route('user.whatsapp-web.templates.index')
                ]
            ]);

        return Inertia::render('Templates/Create');
    }

    public function store(Request $request)
    {
        validateWorkspacePlan('custom_template');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,image,video,audio,location,poll,document',
            'meta' => 'required|array',
        ]);

        $metaValidation = $this->getMetaValidationRules($request->type);
        $request->validate($metaValidation);

        $meta = $this->saveFiles($request);

        activeWorkspaceOwner()->templates()->create([
            'platform' => 'whatsapp-web',
            'module' => 'whatsapp-web',
            'name' => $validated['name'],
            'type' => $validated['type'],
            'meta' => $meta ?? $validated['meta'],
        ]);

        return to_route('user.whatsapp-web.templates.index')->with('success', 'Template Saved Successfully');
    }


    public function show(Template $template)
    {
        PageHeader::set()->title('Device Template Show')->buttons([
            [
                'text' => 'Back to List',
                'url' => route('user.whatsapp-web.templates.index'),
            ],
        ]);

        return Inertia::render('Templates/Show', [
            'template' => $template,
        ]);
    }

    public function edit($id)
    {
        $template = activeWorkspaceOwner()->templates()->whatsappWeb()->findOrFail($id);

        PageHeader::set()->title('Edit Template')
            ->buttons([
                [
                    'text' => 'Back to List',
                    'url' => route('user.whatsapp-web.templates.index'),
                ],
            ]);

        return Inertia::render('Templates/Edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,image,video,audio,location,poll,document',
            'meta' => 'required|array',
        ]);

        $metaValidation = $this->getMetaValidationRules($request->type);
        $request->validate($metaValidation);
        $meta = $this->saveFiles($request);


        $template->update([
            'name' => $validated['name'],
            'meta' => $meta,
        ]);

        return to_route('user.whatsapp-web.templates.index')->with('success', 'Template Updated Successfully');
    }

    public function destroy(Template $template)
    {
        $template->delete();

        return back()->with('success', 'Template Deleted Successfully');
    }

    public function saveFiles($request)
    {
        $meta = $request->meta;
        $allowedTypes = ['image', 'video', 'audio', 'document'];
        $type = $request->type;
        if (in_array($type, $allowedTypes) && $request->hasFile("meta.{$type}")) {
            $meta[$type] = (new AssetService())->upload("meta.{$type}")?->path;
            unset($meta["{$type}Url"], $meta["{$type}_name"]);
        }

        return $meta;
    }
    public function getMetaValidationRules($type)
    {
        return match ($type) {
            'text' => ['meta.text' => 'required|string'],
            'image' => [
                'meta.image' => 'required|file|image|max:5120', // 5MB max
                'meta.caption' => 'nullable|string|max:1024',
            ],
            'video' => [
                'meta.video' => 'required|file|mimetypes:video/mp4,video/quicktime|max:20480', // 20MB max
                'meta.caption' => 'nullable|string|max:1024',
                'meta.gifPlayback' => 'boolean',
            ],
            'audio' => [
                'meta.audio' => 'required|file|mimetypes:audio/mpeg,audio/mp3,audio/wav|max:10240', // 10MB max
            ],
            'location' => [
                'meta.latitude' => 'required|numeric',
                'meta.longitude' => 'required|numeric',
            ],
            'poll' => [
                'meta.name' => 'required|string',
                'meta.values' => 'required|array',
                'meta.values.*' => 'required|string',
            ],
            'document' => [
                'meta.document' => 'required|file|max:10480',
                'meta.caption' => 'nullable|string|max:1024',
            ],
            default => [],
        };
    }
}
