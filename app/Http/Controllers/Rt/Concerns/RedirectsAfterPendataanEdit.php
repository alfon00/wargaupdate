<?php

namespace App\Http\Controllers\Rt\Concerns;

use App\Models\Resident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RedirectsAfterPendataanEdit
{
    protected function redirectAfterPendataanRelatedUpdate(Request $request, string $successMessage): RedirectResponse
    {
        if ($request->input('return') === 'pendataan' && $request->filled('pendataan_head')) {
            $head = Resident::query()->find($request->input('pendataan_head'));
            if ($head?->is_head_of_family) {
                return redirect()
                    ->route('rt.pendataan.show', $head)
                    ->with('success', $successMessage);
            }
        }

        return redirect()
            ->route('rt.data-warga.index', array_filter(['household' => $request->input('household_id')]))
            ->with('success', $successMessage);
    }
}
