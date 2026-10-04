<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicOrderRequest;
use App\Http\Requests\UpdatePublicOrderRequest;
use App\Models\Catalog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFormTemplate;
use App\Models\PaymentMethod;
use App\Models\ServicePackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicOrderController extends Controller
{
    public function paymentMethods(): JsonResponse
    {
        return response()->json(PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (PaymentMethod $method): array => [
                'id' => $method->id,
                'code' => $method->code,
                'name' => $method->name,
                'account_name' => $method->account_name,
                'account_number' => $method->account_number,
                'instructions' => $method->instructions,
                'is_qris' => $method->code === 'dana',
            ]));
    }

    public function catalogs(): JsonResponse
    {
        $packages = ServicePackage::query()->where('is_active', true)->get()->keyBy('name');

        return response()->json(Catalog::query()->where('status', 'active')->orderBy('name')->get()->map(function (Catalog $catalog) use ($packages): array {
            $package = $packages->get($catalog->package);

            return [
                'id' => $catalog->id,
                'name' => $catalog->name,
                'category' => $catalog->category,
                'package' => $catalog->package,
                'package_id' => $package?->id,
                'price' => $package?->price,
                'color' => $catalog->color,
                'link' => $catalog->link,
                'image' => $catalog->image_path ? asset('storage/'.$catalog->image_path) : null,
            ];
        }));
    }

    public function form(Catalog $catalog): JsonResponse
    {
        abort_unless($catalog->status === 'active', 404);
        $template = OrderFormTemplate::active();

        return response()->json(['template' => $template ? [
            'id' => $template->id,
            'name' => $template->name,
            'category' => $template->category,
            'description' => $template->description,
            'fields' => $template->fields,
        ] : null]);
    }

    public function store(StorePublicOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        [$catalog, $package, $template] = $this->resolveSelection($data);
        $this->validateAnswers($data['answers'], $template);

        $order = DB::transaction(function () use ($data, $catalog, $package, $template): Order {
            $customer = Customer::query()->firstOrNew(['email' => $data['email']]);
            $customer->fill([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'segment' => 'active',
                'last_active_at' => now(),
            ]);
            $customer->total_orders = (int) $customer->total_orders + 1;
            $customer->save();

            return Order::create($this->orderAttributes($data, $catalog, $package, $template, $customer) + [
                'uuid' => (string) Str::uuid(),
                'order_number' => $this->nextOrderNumber(),
                'status' => 'waiting',
                'payment_status' => 'unpaid',
            ]);
        });

        return response()->json($this->orderResponse($order), 201);
    }

    public function update(UpdatePublicOrderRequest $request, Order $order): JsonResponse
    {
        if ($order->payment_status === 'paid' || $order->payments()->whereIn('status', ['pending', 'paid'])->exists()) {
            return response()->json(['message' => 'Data transaksi tidak dapat diubah setelah QRIS dibuat.'], 409);
        }

        $data = $request->validated();
        [$catalog, $package, $template] = $this->resolveSelection($data);
        $this->validateAnswers($data['answers'], $template);

        DB::transaction(function () use ($data, $catalog, $package, $template, $order): void {
            $customer = $order->customer ?? Customer::query()->firstOrNew(['email' => $data['email']]);
            $customer->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'last_active_at' => now()]);
            $customer->save();
            $order->update($this->orderAttributes($data, $catalog, $package, $template, $customer));
        });

        return response()->json($this->orderResponse($order->refresh()));
    }

    /** @param array<string, mixed> $data
     * @return array{Catalog, ServicePackage, OrderFormTemplate}
     */
    private function resolveSelection(array $data): array
    {
        $catalog = Catalog::query()->where('status', 'active')->findOrFail($data['catalog_id']);
        $package = ServicePackage::query()->where('is_active', true)->findOrFail($data['package_id']);

        if ($catalog->package !== $package->name) {
            throw ValidationException::withMessages(['package_id' => 'Paket tidak sesuai dengan desain yang dipilih.']);
        }

        $template = OrderFormTemplate::active();

        return [$catalog, $package, $template];
    }

    /** @param array<string, mixed> $answers */
    private function validateAnswers(array $answers, OrderFormTemplate $template): void
    {
        $rules = [];
        foreach ($template->fields as $field) {
            $rules[$field['key']] = array_filter([$field['required'] ? 'required' : 'nullable', 'string', 'max:2000']);
            if ($field['type'] === 'select') {
                $rules[$field['key']][] = 'in:'.implode(',', $field['options']);
            }
        }

        Validator::make($answers, $rules)->validate();
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function orderAttributes(array $data, Catalog $catalog, ServicePackage $package, OrderFormTemplate $template, Customer $customer): array
    {
        return [
            'customer_id' => $customer->id,
            'catalog_id' => $catalog->id,
            'service_package_id' => $package->id,
            'customer_name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'package' => $package->name,
            'event_type' => $catalog->category,
            'form_category' => $catalog->category,
            'form_data' => $data['answers'],
            'total' => $package->price,
        ];
    }

    /** @return array<string, mixed> */
    private function orderResponse(Order $order): array
    {
        return [
            'message' => 'Data pemesan berhasil disimpan.',
            'order' => [
                'uuid' => $order->uuid,
                'order_number' => $order->order_number,
                'name' => $order->customer_name,
                'phone' => $order->phone,
                'email' => $order->email,
                'package' => $order->package,
                'total' => (int) $order->total,
            ],
        ];
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'MEM-'.Str::upper(Str::random(10));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
