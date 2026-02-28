<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:blog');
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        PageHeader::set(__('Blog Category'))
            ->addOverview(__('Total Categories'), Category::whereType('blog_category')->count(), 'bx:grid-alt')
            ->addOverview(__('Active Categories'), Category::whereType('blog_category')->where('status', 1)->count(), 'bx:check-circle')
            ->addOverview(__('Inactive Categories'), Category::whereType('blog_category')->where('status', 0)->count(), 'bx:x-circle')
            ->addModal('Add New', 'createModal', 'bx:plus');

        $categories = Category::whereType('blog_category')->latest()->paginate(10);
        $totalCategories = Category::whereType('blog_category')->count();
        $activeCategories = Category::whereType('blog_category')->where('status', 1)->count();
        $inActiveCategories = Category::whereType('blog_category')->where('status', 0)->count();
        $languages = get_option('languages');

        return Inertia::render('Admin/Blog/Category/Index', [
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'activeCategories' => $activeCategories,
            'inActiveCategories' => $inActiveCategories,
            'languages' => $languages,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'min:2', 'max:100'],
            'language' => ['required'],
        ]);

        Category::create([
            'title' => $request->title,
            'status' => $request->status,
            'lang' => $request->language,
            'type' => $request->type,
            'slug' => Str::slug($request->title),
        ]);

        return redirect()->back();
    }



    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => ['required', 'min:2', 'max:100'],
            'lang' => ['required'],
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'title' => $request->title,
            'status' => $request->status,
            'slug' => Str::slug($request->title),
            'lang' => $request->lang,
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->back();
    }
}
