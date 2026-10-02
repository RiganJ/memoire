<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderFormTemplate;
use App\Models\ServicePackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublicOrderController extends Controller
{
    public function catalogs(): JsonResponse
    {
        return response()->json(Catalog::query()->where('status', 'active')->orderBy('name')->get()->map(fn (Catalog $catalog) => [
            'id' => $catalog->id, 'name' => $catalog->name, 'category' => $catalog->category,
            'package' => $catalog->package, 'color' => $catalog->color,
            'link' => $catalog->link,
            'image' => $catalog->image_path ? asset('storage/'.$catalog->image_path) : null,
        ]));
    }

    public function form(Catalog $catalog): JsonResponse
    {
        abort_unless($catalog->status === 'active', 404);
        $template = OrderFormTemplate::query()->where('category', $catalog->category)->first();

        return response()->json(['template' => $template ? [
            'id' => $template->id, 'name' => $template->name, 'category' => $template->category,
            'description' => $template->description, 'fields' => $template->fields,
        ] : null]);
    }

    public function store(Request $request): JsonResponse
    {
        $base = $request->validate([
            'catalog_id' => ['required', 'integer', 'exists:catalogs,id'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'answers' => ['required', 'array'],
        ]);
        $catalog = Catalog::query()->where('status', 'active')->findOrFail($base['catalog_id']);
        $template = OrderFormTemplate::query()->where('category', $catalog->category)->firstOrFail();
        $rules = [];
        foreach ($template->fields as $field) {
            $rules['answers.'.$field['key']] = array_filter([$field['required'] ? 'required' : 'nullable', 'string', 'max:2000']);
            if ($field['type'] === 'select') {
                $rules['answers.'.$field['key']][] = 'in:'.implode(',', $field['options']);
            }
        }
        Validator::make($request->all(), $rules)->validate();
        $packagePrice = (int) (ServicePackage::query()
            ->where('name', $catalog->package)
            ->where('is_active', true)
            ->value('price') ?? 0);

        $order = DB::transaction(function () use ($base, $catalog, $packagePrice, $template): Order {
            $customer = Customer::query()->firstOrNew(['email' => $base['email']]);
            $customer->fill([
                'name' => $base['name'], 'phone' => $base['phone'] ?? $customer->phone,
                'segment' => 'active', 'last_active_at' => now(),
            ]);
            $customer->total_orders = (int) $customer->total_orders + 1;
            $customer->total_spent = (float) $customer->total_spent + $packagePrice;
            $customer->save();

            return Order::create([
                'customer_id' => $customer->id, 'catalog_id' => $catalog->id,
                'order_number' => $this->nextOrderNumber(), 'customer_name' => $customer->name,
                'email' => $customer->email, 'phone' => $customer->phone, 'package' => $catalog->package,
                'event_type' => $catalog->category, 'form_category' => $template->category,
                'form_data' => $base['answers'], 'total' => $packagePrice,
                'status' => 'waiting', 'payment_status' => 'unpaid',
            ]);
        });

        return response()->json(['message' => 'Pesanan Anda sudah kami terima.', 'order_number' => $order->order_number], 201);
    }

    private function nextOrderNumber(): string
    {
        $next = (int) Order::max('id') + 1;
        do {
            $number = 'INV-'.str_pad((string) $next++, 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
