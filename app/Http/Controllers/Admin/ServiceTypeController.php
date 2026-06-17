<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceTypeController extends Controller
{
    public function index(): View
    {
        $services = ServiceType::query()
            ->withCount('applications')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', compact('services'));
    }

    public function edit(ServiceType $serviceType): View
    {
        $serviceType->loadCount('applications');

        return view('admin.services.edit', [
            'service' => $serviceType,
        ]);
    }

    public function update(Request $request, ServiceType $serviceType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $serviceType->update($validated);

        return redirect()->route('admin.services.index')->with('success', 'Katalog layanan berhasil diperbarui.');
    }

    public function destroy(ServiceType $serviceType): RedirectResponse
    {
        if ($serviceType->applications()->exists()) {
            return redirect()
                ->route('admin.services.edit', $serviceType)
                ->withErrors(['delete' => 'Layanan tidak dapat dihapus karena sudah memiliki permohonan. Nonaktifkan saja di form edit.']);
        }

        $serviceType->delete();

        return redirect()->route('admin.services.index')->with('success', 'Layanan berhasil dihapus dari katalog.');
    }
}
