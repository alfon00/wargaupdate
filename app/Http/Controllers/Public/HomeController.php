<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\HomeContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'heroTagline' => HomeContent::heroTagline(),
            'platformIntroLead' => HomeContent::platformIntroLead(),
            'platformAdvantages' => HomeContent::platformAdvantages(),
            'homeFaq' => HomeContent::faq(),
        ]);
    }
}
