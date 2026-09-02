<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function landing(): View
    {
        return view('pages.landing');
    }

    public function product(): View
    {
        return view('pages.product');
    }

    public function pricing(): View
    {
        return view('pages.pricing');
    }

    public function enterprise(): View
    {
        return view('pages.enterprise');
    }

    public function thankYou(): View
    {
        return view('pages.contact-thank-you');
    }
}
