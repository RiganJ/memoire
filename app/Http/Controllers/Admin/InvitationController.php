<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvitationRequest;
use App\Models\Invitation;
use App\Models\Template;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;
        $invitations = Invitation::query()
            ->with('template')
            ->withCount('guests')
            ->when($search, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.invitations.index', compact('invitations'));
    }

    public function create(): View
    {
        return view('admin.invitations.create', ['templates' => $this->publishedTemplates()]);
    }

    public function store(InvitationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $invitation = DB::transaction(function () use ($validated): Invitation {
            $invitation = Invitation::create(Arr::except($validated, 'guests'));

            foreach ($validated['guests'] ?? [] as $guest) {
                $invitation->guests()->create([
                    ...$guest,
                    'slug' => $invitation->uniqueGuestSlug($guest['name']),
                    'token' => (string) Str::uuid(),
                ]);
            }

            return $invitation;
        });

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Undangan berhasil ditambahkan.');
    }

    public function show(Invitation $invitation): View
    {
        $invitation->load('template')->loadCount('guests');
        $guests = $invitation->guests()->latest()->paginate(20);

        return view('admin.invitations.show', compact('invitation', 'guests'));
    }

    public function edit(Invitation $invitation): View
    {
        return view('admin.invitations.edit', [
            'invitation' => $invitation,
            'templates' => $this->publishedTemplates($invitation->template_id),
        ]);
    }

    public function update(InvitationRequest $request, Invitation $invitation): RedirectResponse
    {
        $invitation->update(Arr::except($request->validated(), 'guests'));

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Undangan berhasil diperbarui.');
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return redirect()->route('admin.invitations.index')->with('success', 'Undangan dan seluruh data tamunya berhasil dihapus.');
    }

    public function regenerateCustomerAccessCode(Invitation $invitation): RedirectResponse
    {
        $invitation->update([
            'customer_access_code' => Invitation::generateUniqueCustomerAccessCode(),
        ]);

        return redirect()->route('admin.invitations.show', $invitation)->with('success', 'Kode akses customer berhasil dibuat ulang. Kode lama sudah tidak berlaku.');
    }

    /** @return Collection<int, Template> */
    private function publishedTemplates(?int $currentTemplateId = null): Collection
    {
        return Template::query()
            ->where(function (Builder $query) use ($currentTemplateId): void {
                $query->where('status', 'published');
                if ($currentTemplateId !== null) {
                    $query->orWhereKey($currentTemplateId);
                }
            })
            ->orderBy('name')
            ->get();
    }
}
