<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    use Uploader;

    public function __construct()
    {
        $this->middleware('permission:team');
    }

    public function index()
    {


        $postQuery = Post::query()->where('type', 'team');

        $posts = $postQuery->newQuery()->with('preview')->latest()->paginate();
        $totalMembers = $postQuery->newQuery()->count();
        $totalActiveMembers = $postQuery->newQuery()->where('status', 1)->count();
        $totalInActiveMembers = $postQuery->newQuery()->where('status', 0)->count();

        PageHeader::set(__('Teams'))->addLink(
            __('Create a team'),
            route('admin.team.create'),
        )
            ->addOverview(__('Total Team Members'), $totalMembers, 'bx:group')
            ->addOverview(__('Active Team Members'), $totalActiveMembers, 'bx:check-circle')
            ->addOverview(__('Inactive Team Members'), $totalInActiveMembers, 'bx:x-circle');

        return Inertia::render('Admin/Team/Index', [
            'posts' => $posts,
        ]);
    }

    public function create()
    {
        PageHeader::set(__('Create Team'))->addBackLink(route('admin.team.index'));
        return Inertia::render('Admin/Team/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'member_name' => 'required|max:150',
            'member_position' => 'required|max:100',
            'profile_picture' => 'required|image|max:2000',
            'about' => 'required|max:1000',
        ]);

        DB::transaction(function () use ($request) {
            $post = new Post;
            $post->title = $request->member_name;
            $post->slug = $request->member_position;
            $post->status = $request->status ? 1 : 0;
            $post->type = 'team';
            $post->save();

            $post->excerpt()->create([
                'post_id' => $post->id,
                'key' => 'excerpt',
                'value' => json_encode($request->socials),
            ]);


            $post->info()->create([
                'post_id' => $post->id,
                'key' => 'info',
                'value' => json_encode($request->input('information')),
            ]);

            $post->description()->create([
                'post_id' => $post->id,
                'key' => 'description',
                'value' => $request->about,
            ]);

            $preview = $this->uploadFile('profile_picture');

            $post->preview()->create([
                'post_id' => $post->id,
                'key' => 'preview',
                'value' => $preview,
            ]);
        });

        Toastr::success(__('Team created successfully'));
        return redirect()->route('admin.team.index');
    }

    public function edit($id)
    {
        PageHeader::set(__('Edit Team'))->addBackLink(route('admin.team.index'));

        $info = Post::with('description', 'preview', 'excerpt', 'info')->where('type', 'team')->findOrFail($id);
        $info->info()->firstOrCreate(['key' => 'info'], [
            'value' => json_encode([
                "location" => "",
                "email" => "",
                "age" => "",
                "qualification" => "",
                "gender" => "male",
            ])
        ]);
        $socials = json_decode($info->excerpt->value ?? '', true);

        $information = (array) json_decode($info->info()->value('value'), true);

        return Inertia::render('Admin/Team/Edit', [
            'info' => $info,
            'information' => $information,
            'socials' => $socials,
        ]);
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

        $data = $request->validate([
            'team.title' => 'required|max:150',
            'team.slug' => 'required|max:100',
            'team.profile_picture' => 'nullable|image|max:2000',
            'team.description.value' => 'required|max:1000',
        ]);

        DB::transaction(function () use ($data, $request, $id) {
            $post = Post::with('preview')->findOrFail($id);

            $post->title = $data['team']['title'];
            $post->slug = $data['team']['slug'];
            $post->type = 'team';
            $post->status = $request['team']['status'] ? 1 : 0;
            $post->save();

            $post->excerpt()->update([
                'value' => json_encode($request->socials),
            ]);

            $infoData = json_encode($request->input('information'));

            $post->info()->update(
                ['value' => $infoData]
            );

            $post->description()->update([
                'value' => $data['team']['description']['value'],
            ]);

            if ($request->hasFile('team.preview.value')) {
                $preview = $this->uploadFile('team.preview.value');
                $post->preview()->update([
                    'value' => $preview,
                ]);
            }
        });

        Toastr::success(__('Team updated successfully'));
        return redirect()->route('admin.team.index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::where('type', 'team')->with('preview')->findOrFail($id);

        if (!empty($post->preview)) {
            $this->removeFile($post->preview->value);
        }
        $post->delete();
        Toastr::success(__('Team deleted successfully'));
        return redirect()->back();
    }
}
