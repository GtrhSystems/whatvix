<?php

namespace App\Http\Controllers\Admin;

use App\Actions\OptionUpdate;
use App\Models\Option;
use App\Traits\Uploader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OptionController extends Controller
{
    use Uploader;
    public function __construct()
    {
        $this->middleware('permission:page');
    }


    /**
     * Update the option value.
     *
     * @param Request $request
     * @param string  $key
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $key, OptionUpdate $optionUpdate)
    {
        $option = Option::firstOrNew(['key' => $key]);

        if ($key === 'tax') {
            $option->value = $request->tax;
            $option->save();
        } else {
            $optionUpdate->update($key);
        }

        return back()->with('success', 'Updated Successfully');
    }
}
