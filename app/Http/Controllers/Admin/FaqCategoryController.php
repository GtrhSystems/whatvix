<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:faq');
    }

    public function index()
    {
        PageHeader::set(__('Faq Categories'))
            ->addOverview(__('Total Categories'), Category::whereType('faq_category')->count(), 'bx:grid-alt')
            ->addOverview(__('Active Categories'), Category::whereType('faq_category')->where('status', 1)->count(), 'bx:check-circle')
            ->addOverview(__('Inactive Categories'), Category::whereType('faq_category')->where('status', 0)->count(), 'bx:x-circle')
            ->addModal('Add New', 'createModal', 'bx-plus');
        $categories = Category::whereType('faq_category')->latest()->paginate(10);
        $totalCategories = Category::whereType('faq_category')->count();
        $activeCategories = Category::whereType('faq_category')->where('status', 1)->count();
        $inActiveCategories = Category::whereType('faq_category')->where('status', 0)->count();
        $languages = get_option('languages');

        return Inertia::render('Admin/Faq/Category/Index', [
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'activeCategories' => $activeCategories,
            'inActiveCategories' => $inActiveCategories,
            'languages' => $languages,
        ]);
    }

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
            'type' => 'faq_category',
            'slug' => str()->slug($request->title),
        ]);

        return redirect()->back();
    }

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
            'slug' => str()->slug($request->title),
            'lang' => $request->lang,
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->back();
    }
}
