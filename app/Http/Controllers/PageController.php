<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home');
    }

    public function dashboard()
    {
        $projects = Auth()->user()->projects()->with('databases')->get();
        return view('pages.dashboard', compact('projects'));
    }
}
