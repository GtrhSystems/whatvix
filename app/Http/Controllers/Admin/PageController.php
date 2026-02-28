<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Post;
use Inertia\Inertia;
use App\Actions\Page;
use App\Helpers\Toastr;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Requests\PageRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:page');
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {

        $pages = Post::where('type', 'page')->orderBy('id', 'desc')->paginate(20);
        $totalActivePosts = Post::where('type', 'page')->where('status', 1)->count();
        $totalInActivePosts = Post::where('type', 'page')->where('status', 0)->count();
        $totalPosts = Post::where('type', 'page')->count();
        PageHeader::set('Pages')->addLink(__('Add New'), route('admin.page.create'), 'bx:plus')
            ->addOverview(__('Total Static Pages'), $totalPosts, 'bx:grid-alt')
            ->addOverview(__('Active Static Pages'), $totalActivePosts, 'bx:check-circle')
            ->addOverview(__('Inactive Static Pages'), $totalInActivePosts, 'bx:x-circle')
        ;

        return Inertia::render('Admin/CustomPage/Index', [
            'pages' => $pages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        PageHeader::set('Create Page')->addBackLink(route('admin.page.index'));
        return Inertia::render('Admin/CustomPage/Create', [
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PageRequest $request, Page $page)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {

            $page = $page->create($request);

            DB::commit();
        } catch (Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }

        return to_route('admin.page.index');
    }



    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit($id)
    {
        PageHeader::set('Edit Page')->addBackLink(route('admin.page.index'));

        $info = Post::with('description', 'seo')->findOrFail($id);
        $seo = json_decode($info->seo->value ?? '');


        return Inertia::render('Admin/CustomPage/Edit', [
            'info' => $info,
            'seo' => $seo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, Page $page)
    {
        $request->validate([
            'title' => 'required|max: 150',
            'description.value' => 'required|max:5000',
        ]);


        DB::beginTransaction();
        try {

            $page = $page->update($request, $id);

            DB::commit();

            return to_route('admin.page.index');
        } catch (Throwable $th) {
            DB::rollback();

            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy($id)
    {
        $page = Post::findOrFail($id);
        $page->delete();
        Cache::forget('page_' . $page->slug);
        Toastr::danger(__('Deleted Successfully'));
        return back();
    }
}
