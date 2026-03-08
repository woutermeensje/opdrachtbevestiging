<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index');
    }

    public function create(): View
    {
        return view('dashboard.create');
    }

    public function confirmations(): View
    {
        return view('dashboard.confirmations');
    }

    public function profile(): View
    {
        return view('dashboard.profile');
    }
}
