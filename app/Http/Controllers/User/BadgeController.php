<?php

namespace App\Http\Controllers\User;

use App\Models\Badge;
use App\Helpers\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BadgeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'color' => ['required', 'unique:badges,color'],
            'text' => ['required', 'unique:badges,text']
        ]);

        Badge::create(
            $request->merge(['user_id' => auth()->id()])->only([
                'user_id',
                'text',
                'color'
            ])
        );

        Toastr::success('Created Successfully');

        return back();
    }
}
