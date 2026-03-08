<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function howItWorks(): View
    {
        return view('pages.how-it-works');
    }

    public function pricing(): View
    {
        return view('pages.pricing');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }
}
