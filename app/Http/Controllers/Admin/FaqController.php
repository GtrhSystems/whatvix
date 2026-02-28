<?php

namespace App\Http\Controllers\Admin;

use Str;
use Auth;
use App\Models\Post;
use Inertia\Inertia;
use App\Models\Category;
use App\Models\Postmeta;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:faq');
    }
    public function index()
    {
        PageHeader::set()
            ->title(__('Faq'))
            ->addOverview('Total Faq', Post::where('type', 'faq')->count(), 'bx:grid-alt')
            ->addOverview('Active Faq', Post::where('type', 'faq')->where('status', 1)->count(), 'bx:check-circle')
            ->addOverview('Inactive Faq', Post::where('type', 'faq')->where('status', 0)->count(), 'bx:x-circle')
            ->addModal('Create Faq', 'createModal', 'bx-plus');

        $faqs = Post::where('type', 'faq')->with(['excerpt', 'faq_categories:id,title'])
            ->latest()->paginate(20);

        $languages = get_option('languages');

        $categories = Category::whereType('faq_category')
            ->where('status', 1)
            ->select('id', 'title', 'status')
            ->get();

        return Inertia::render('Admin/Faq/Index', [
            'faqs' => $faqs,
            'languages' => $languages,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|max:150',
            'answer' => 'required|max:500',
        ]);


        DB::beginTransaction();
        try {

            $post = new Post;
            $post->title = $request->question;
            $post->slug = str()->slug($request->question);
            $post->type = 'faq';
            $post->lang = $request->language ?? 'en';
            $post->save();
            $post->faq_categories()->sync($request->categories);
            $post->excerpt()->create([
                'post_id' => $post->id,
                'key' => 'excerpt',
                'value' => $request->answer,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return redirect()->back()->with('success', 'Faq created successfully');
    }


    public function update(Request $request, $id)
    {

        $request->validate([
            'question' => 'required|max:150',
            'answer' => 'required|max:500',
        ]);


        DB::beginTransaction();
        try {

            $post = Post::findorFail($id);
            $post->title = $request->question;
            $post->slug = str()->slug($request->question);
            $post->type = 'faq';
            $post->lang = $request->language ?? 'en';
            $post->save();
            $post->faq_categories()->sync($request->categories ?? []);
            $post->excerpt()->update([
                'post_id' => $post->id,
                'key' => 'excerpt',
                'value' => $request->answer,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
        return redirect()->back()->with('success', 'Faq updated successfully');
    }

    public function destroy($id)
    {
        $post = Post::where('type', 'faq')->findOrFail($id);
        $post->delete();

        return back();
    }
}
