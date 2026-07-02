<?php

namespace App\Prospect\Http\Controllers\Project;

use App\Prospect\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // dd(auth()->user($this->guard));
        echo "Hello " . auth()->user($this->guard)->name . "!";
        exit;
        // return view('client.index');
    }
}
