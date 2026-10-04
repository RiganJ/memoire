<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CatalogRequest;
use App\Models\Catalog;
use App\Models\OrderFormTemplate;
use App\Models\ServicePackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'in:active,draft,archived'],
        ]);

        $catalogs = Catalog::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'all' => Catalog::count(),
            'active' => Catalog::where('status', 'active')->count(),
            'draft' => Catalog::where('status', 'draft')->count(),
            'archived' => Catalog::where('status', 'archived')->count(),
        ];
        $categories = Catalog::query()->select('category')->distinct()->orderBy('category')->pluck('category');

        return view('admin.catalog', compact('catalogs', 'categories', 'summary'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.catalog.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CatalogRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('catalogs', 'public');
        }

        $catalog = Catalog::create($data);
        $catalog->orderFormTemplate()->create([
            'name' => 'Form Pesanan '.$catalog->name,
            'category' => $catalog->category,
            'description' => 'Lengkapi detail dasar untuk desain '.$catalog->name.'.',
            'fields' => OrderFormTemplate::BASIC_FIELDS,
        ]);

        return redirect()->route('admin.catalog.index')->with('success', 'Desain berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Catalog $catalog): View
    {
        return view('admin.catalog.edit', [
            'catalog' => $catalog,
            ...$this->formOptions($catalog),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('catalogs', 'public');

            if ($catalog->image_path) {
                Storage::disk('public')->delete($catalog->image_path);
            }
        }

        $catalog->update($data);
        $catalog->orderFormTemplate?->update(['category' => $catalog->category]);

        return redirect()->route('admin.catalog.index')->with('success', 'Desain berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Catalog $catalog): RedirectResponse
    {
        if ($catalog->image_path) {
            Storage::disk('public')->delete($catalog->image_path);
        }

        $catalog->delete();

        return redirect()->route('admin.catalog.index')->with('success', 'Desain berhasil dihapus.');
    }

    /**
     * @return array{categoryOptions: Collection<int, string>, packageOptions: Collection<int, string>}
     */
    private function formOptions(?Catalog $catalog = null): array
    {
        $categoryOptions = Catalog::query()
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->merge(OrderFormTemplate::query()->select('category')->distinct()->pluck('category'))
            ->when($catalog, fn ($categories) => $categories->push($catalog->category))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $packageOptions = ServicePackage::query()
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');

        return compact('categoryOptions', 'packageOptions');
    }
}
