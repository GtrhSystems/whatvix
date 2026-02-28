<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Category;
use App\Helpers\PageHeader;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:blog');
    }

    public function index()
    {
        PageHeader::set(__('Tags'))
            ->addOverview(__('Total Tags'), Category::whereType('tags')->count(), 'bx:grid-alt')
            ->addOverview(__('Active Tags'), Category::whereType('tags')->where('status', 1)->count(), 'bx:check-circle')
            ->addOverview(__('Inactive Tags'), Category::whereType('tags')->where('status', 0)->count(), 'bx:x-circle')
            ->addModal(__('Add New Tag'), 'createModal');

        $tags = Category::whereType('tags')->withCount('postCategories')->latest()->paginate(10);
        $totalTags = Category::whereType('tags')->count();
        $activeTags = Category::whereType('tags')->where('status', 1)->count();
        $inActiveTags = Category::whereType('tags')->where('status', 0)->count();
        $languages = get_option('languages');

        return Inertia::render('Admin/Blog/Tag/Index', [
            'tags' => $tags,
            'totalTags' => $totalTags,
            'activeTags' => $activeTags,
            'inActiveTags' => $inActiveTags,
            'languages' => $languages,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     *
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'min:2', 'max:100'],
            'language' => ['required']
        ]);

        Category::create([
            'title' => $request->title,
            'type' => 'tags',
            'slug' => Str::slug($request->title),
            'status' => $request->status,
            'lang' => $request->language,
        ]);

        return back()->with('success', 'Tag created successfully.');
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'min:2', 'max:100'],
            'lang' => ['required']
        ]);

        $tag = Category::findOrFail($id);

        $tag->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'status' => $request->status,
            'lang' => $request->lang,
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tag = Category::findOrFail($id);
        $tag->delete();

        return back()->with('success', 'Tag deleted successfully.');
    }
}
