<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServicePackageController extends Controller
{
    public function index(): View
    {
        return view('admin.packages.index', ['packages' => ServicePackage::query()->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('admin.packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ServicePackage::create($this->data($request));

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(ServicePackage $package): View
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, ServicePackage $package): RedirectResponse
    {
        $package->update($this->data($request));

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(ServicePackage $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dihapus.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'features_text' => ['nullable', 'string', 'max:4000'],
            'badge' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            ...$data,
            'features' => collect(preg_split('/\r\n|\r|\n/', trim((string) $data['features_text'])))->filter()->values()->all(),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
