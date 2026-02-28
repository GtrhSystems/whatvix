<?php

namespace App\Http\Controllers\User;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\AiTemplate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AiToolsController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $templates = AiTemplate::query()
            ->filterOn(['title', 'description'])
            ->withCount([
                'users as isBookmarked' => function ($query) {
                    $query->where('user_id', Auth::id());
                }
            ])
            ->latest()
            ->orderBy('isBookmarked', 'DESC')
            ->get();
        $bookmarked = $user->aiTemplates()->select('ai_templates.id')->get();
        PageHeader::set(title: 'AI Tools', overviews: [
            [
                'icon' => "bx:list-ul",
                'title' => 'AI Tools',
                'value' => $templates->count(),
            ],
            [
                'icon' => "bx:checkbox-checked",
                'title' => 'Bookmarked',
                'value' => $bookmarked->count(),
            ],
            [
                'icon' => "bx:credit-card",
                'title' => 'Credits',
                'value' => $user->credits,
            ],
        ]);
        return Inertia::render('User/AiTools/Index', [
            'templates' => $templates,
            'credits' => $user->credits,
            'bookmarked' => $bookmarked
        ]);
    }
    public function show($uuid)
    {
        $template = AiTemplate::where('uuid', $uuid)->firstOrFail();

        PageHeader::set(title: $template->title);
        $languages = json_decode(file_get_contents(base_path('database/json/languages.json')), true);
        $languages = array_values(array_map(function ($language) {
            return [
                'id' => $language['id'],
                'name' => $language['name'],
            ];
        }, $languages));

        return Inertia::render('User/AiTools/Show', [
            'template' => $template,
            'credits' => Auth::user()->credits,
            'languages' => $languages
        ]);
    }

    public function bookmark()
    {
        /** @var \App\Models\User */
        $user = Auth::user();
        $user->aiTemplates()->toggle(request('ai_template_id'));
        return back();
    }
}
