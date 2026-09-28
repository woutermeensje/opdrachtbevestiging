<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OpstellenController extends Controller
{
    public function show(string $type): View
    {
        return view('opstellen.'.$type, [
            'type' => $type,
            'typeName' => config('opstellen.types')[$type],
        ]);
    }
}
