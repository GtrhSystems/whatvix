<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Inertia\Inertia;
use App\Models\Option;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:language');
    }

    public function index()
    {
        PageHeader::set(__('Languages'))->addModal(__('Add New'), 'createModal', 'bx:plus');
        $languages = get_option('languages');
        $countries = base_path('lang/langlist.json');
        $countries = json_decode(file_get_contents($countries), true);

        return Inertia::render('Admin/Language/Index', [
            'languages' => $languages,
            'countries' => $countries
        ]);
    }


    public function store(Request $request)
    {
        $file = base_path('lang/default.json');
        $file = file_get_contents($file);
        File::put(base_path('lang/' . $request->language_code . '.json'), $file);
        $languages = get_option('languages');

        $arr = [];
        if (!empty($languages)) {
            foreach ($languages as $key => $value) {
                $arr[$key] = $value;
            }
        }

        $arr[$request->language_code] = $request->name;

        $langlist = Option::where('key', 'languages')->first();
        if (empty($langlist)) {
            $langlist = new Option;
            $langlist->key = 'languages';
        }
        $langlist->value = $arr;
        $langlist->save();
        Cache::forget('languages');

        return back();
    }

    public function show($id)
    {

        PageHeader::set(__('Language :id', ['id' => $id]))
            ->addModal(__('Add New'), 'createModal', 'bx:plus')
            ->addBackLink(route('admin.language.index'));


        $file = base_path('lang/' . $id . '.json');
        $posts = file_get_contents($file);
        $posts = json_decode($posts);

        return Inertia::render('Admin/Language/Show', [
            'posts' => $posts,
            'id' => $id
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
        $data = [];
        foreach ($request->values as $key => $row) {
            $data[$key] = $row;
        }

        $file = json_encode($data, JSON_PRETTY_PRINT);
        File::put(base_path('lang/' . $id . '.json'), $file);

        return Inertia::location(back());
    }

    public function addKey(Request $request)
    {

        $request->validate([
            'key' => 'required',
            'value' => 'required',
        ]);

        $file = base_path('lang/' . $request->id . '.json');
        $posts = file_get_contents($file);
        $posts = json_decode($posts);
        foreach ($posts as $key => $row) {
            $data[$key] = $row;
        }
        $data[$request->key] = $request->value;

        File::put(base_path('lang/' . $request->id . '.json'), json_encode($data, JSON_PRETTY_PRINT));

        return back();
    }

    public function destroy($id)
    {
        $posts = Option::where('key', 'languages')->first();
        $languages = $posts->value;

        $data = [];
        foreach ($languages as $key => $row) {
            if ($id != $key) {
                $data[$key] = $row;
            }
        }

        $posts->value = $data;
        $posts->save();

        if (file_exists(base_path('lang/' . $id . '.json'))) {
            unlink(base_path('lang/' . $id . '.json'));
        }

        Cache::forget('languages');

        return back();
    }
}
