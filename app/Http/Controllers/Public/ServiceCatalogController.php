<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Support\HomeContent;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'serviceFlows' => HomeContent::serviceCatalogFlows(),
        ]);
    }

    public function show(ServiceType $service): View
    {
        abort_unless($service->is_active, 404);

        return view('public.services.show', compact('service'));
    }
}
