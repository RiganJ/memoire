<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderFormTemplate;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderFormTemplateController extends Controller
{
    public function index(): View
    {
        $forms = OrderFormTemplate::query()
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.order-forms.index', [
            'forms' => $forms,
            'activeForm' => $forms->firstWhere('is_active', true),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new OrderFormTemplate([
            'category' => OrderFormTemplate::SHARED_CATEGORY,
            'fields' => OrderFormTemplate::BASIC_FIELDS,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $orderForm = new OrderFormTemplate($this->payload($request));
        $orderForm->is_active = false;
        $orderForm->save();

        return redirect()
            ->route('admin.order-forms.show', $orderForm)
            ->with('success', 'Form baru berhasil dibuat. Aktifkan form ini agar digunakan pada checkout.');
    }

    public function show(OrderFormTemplate $orderForm): View
    {
        return view('admin.order-forms.show', ['orderForm' => $orderForm]);
    }

    public function edit(OrderFormTemplate $orderForm): View
    {
        return $this->formView($orderForm);
    }

    public function update(Request $request, OrderFormTemplate $orderForm): RedirectResponse
    {
        $orderForm->update($this->payload($request, $orderForm));

        return redirect()
            ->route('admin.order-forms.show', $orderForm)
            ->with('success', 'Form pesanan berhasil diperbarui.');
    }

    public function destroy(OrderFormTemplate $orderForm): RedirectResponse
    {
        if ($orderForm->is_active) {
            return back()->with('error', 'Form yang sedang aktif tidak dapat dihapus. Aktifkan form lain terlebih dahulu.');
        }

        $orderForm->delete();

        return redirect()
            ->route('admin.order-forms.index')
            ->with('success', 'Form pesanan berhasil dihapus.');
    }

    public function activate(OrderFormTemplate $orderForm): RedirectResponse
    {
        DB::transaction(function () use ($orderForm): void {
            OrderFormTemplate::query()
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            OrderFormTemplate::query()->where('is_active', true)->update(['is_active' => false]);
            $orderForm->is_active = true;
            $orderForm->save();
        });

        return back()->with('success', 'Form ini sekarang digunakan untuk seluruh checkout.');
    }

    private function formView(OrderFormTemplate $orderForm): View
    {
        return view('admin.order-forms.edit', [
            'orderForm' => $orderForm,
            'paymentMethods' => PaymentMethod::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    /**
     * @return array{name: string, category: string, description: ?string, fields: array<int, array{key: string, label: string, type: string, required: bool, options: array<int, string>}>}
     */
    private function payload(Request $request, ?OrderFormTemplate $template = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('order_form_templates', 'name')->ignore($template?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'fields' => ['required', 'array', 'min:1', 'max:30'],
            'fields.*.key' => ['nullable', 'string', 'max:80'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.type' => ['required', Rule::in(['text', 'textarea', 'date', 'select'])],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.options' => ['nullable', 'string', 'max:2000'],
        ]);

        $usedKeys = [];
        $existingKeys = collect($template?->fields ?? [])->pluck('key')->all();
        $fields = collect($data['fields'])
            ->map(function (array $field, int $index) use (&$usedKeys, $existingKeys): array {
                $options = $field['type'] === 'select'
                    ? collect(preg_split('/\r\n|\r|\n|,/', (string) ($field['options'] ?? '')))->map(fn (string $option): string => trim($option))->filter()->unique()->values()->all()
                    : [];

                if ($field['type'] === 'select' && $options === []) {
                    abort(422, 'Field pilihan wajib memiliki minimal satu opsi.');
                }

                $submittedKey = Str::slug((string) ($field['key'] ?? ''), '_');
                $baseKey = in_array($submittedKey, $existingKeys, true)
                    ? $submittedKey
                    : Str::slug($field['label'], '_');
                $baseKey = $baseKey !== '' ? $baseKey : 'field_'.($index + 1);
                $key = $baseKey;
                $suffix = 2;

                while (in_array($key, $usedKeys, true)) {
                    $key = $baseKey.'_'.$suffix;
                    $suffix++;
                }

                $usedKeys[] = $key;

                return [
                    'key' => $key,
                    'label' => trim($field['label']),
                    'type' => $field['type'],
                    'required' => (bool) ($field['required'] ?? false),
                    'options' => $options,
                ];
            })
            ->values()
            ->all();

        return [
            'name' => trim($data['name']),
            'category' => OrderFormTemplate::SHARED_CATEGORY,
            'description' => filled($data['description'] ?? null) ? trim($data['description']) : null,
            'fields' => $fields,
        ];
    }
}
