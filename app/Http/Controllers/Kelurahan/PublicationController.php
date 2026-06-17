<?php

namespace App\Http\Controllers\Kelurahan;

use App\Enums\RtPublicationType;
use App\Http\Controllers\Controller;
use App\Models\RtProfile;
use App\Models\RtPublication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function indexKegiatan(Request $request): View
    {
        return $this->index($request, RtPublicationType::Kegiatan);
    }

    public function indexPengumuman(Request $request): View
    {
        return $this->index($request, RtPublicationType::Pengumuman);
    }

    protected function index(Request $request, RtPublicationType $type): View
    {
        $rtProfiles = RtProfile::forPublicSelect()->get();

        $query = RtPublication::query()
            ->with('rtProfile')
            ->where('type', $type)
            ->latest('published_at')
            ->latest('id');

        if ($request->filled('rt_profile_id')) {
            $rt = RtProfile::find((int) $request->rt_profile_id);
            if ($rt) {
                $query->where('rt_profile_id', $rt->id);
            }
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where('judul', 'like', $term);
        }

        $publications = $query->paginate(15)->withQueryString();

        return view('kelurahan.publications.index', compact('type', 'publications', 'rtProfiles'));
    }
}
