<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderFormTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderFormTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.order-forms.index', ['templates' => OrderFormTemplate::query()->latest()->get()]);
    }

    public function create(): View
    {
        return view('admin.order-forms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $template = OrderFormTemplate::create($this->payload($request));

        return redirect()->route('admin.order-forms.edit', $template)->with('success', 'Form kategori berhasil dibuat.');
    }

    public function edit(OrderFormTemplate $orderForm): View
    {
        return view('admin.order-forms.edit', compact('orderForm'));
    }

    public function update(Request $request, OrderFormTemplate $orderForm): RedirectResponse
    {
        $orderForm->update($this->payload($request, $orderForm));

        return back()->with('success', 'Form kategori berhasil diperbarui.');
    }

    public function destroy(OrderFormTemplate $orderForm): RedirectResponse
    {
        $orderForm->delete();

        return redirect()->route('admin.order-forms.index')->with('success', 'Form kategori dihapus.');
    }

    private function payload(Request $request, ?OrderFormTemplate $template = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100', Rule::unique('order_form_templates', 'category')->ignore($template)],
            'description' => ['nullable', 'string', 'max:500'],
            'fields_definition' => ['required', 'string', 'max:10000'],
        ]);

        $fields = collect(preg_split('/\r\n|\r|\n/', trim($data['fields_definition'])))
            ->filter()
            ->map(function (string $line): array {
                [$key, $label, $type, $required, $options] = array_pad(array_map('trim', explode('|', $line, 5)), 5, null);
                abort_unless($key && $label && in_array($type, ['text', 'textarea', 'date', 'select'], true), 422, 'Format field tidak valid.');

                return [
                    'key' => str($key)->slug('_')->toString(),
                    'label' => $label,
                    'type' => $type,
                    'required' => $required === 'required',
                    'options' => $type === 'select' ? array_values(array_filter(array_map('trim', explode(',', (string) $options)))) : [],
                ];
            })->values()->all();

        if ($fields === []) {
            abort(422, 'Tambahkan minimal satu field.');
        }

        return [...$data, 'fields' => $fields];
    }
}
