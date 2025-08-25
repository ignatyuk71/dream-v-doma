<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderDelivery;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 🧹 Нормалізація: якщо фронт прислав старе поле id — мапимо в product_variant_id
        $payload = $request->all();
        if (!empty($payload['cartItems']) && is_array($payload['cartItems'])) {
            foreach ($payload['cartItems'] as $k => $row) {
                if (!isset($payload['cartItems'][$k]['product_variant_id']) && isset($row['id'])) {
                    $payload['cartItems'][$k]['product_variant_id'] = $row['id'];
                }
                // узгодження image/image_url
                if (!isset($payload['cartItems'][$k]['image_url']) && isset($row['image'])) {
                    $payload['cartItems'][$k]['image_url'] = $row['image'];
                }
            }
        }
        $request->replace($payload);

        $data = $request->validate([
            // Клієнт
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|max:255',

            // Локаль
            'locale'     => 'nullable|string|in:uk,ru,en',

            // Доставка
            'type'                => 'required|string|in:branch,postomat,courier',
            'city'                => 'required|array',
            'city.DeliveryCity'   => 'required|string',
            'warehouse'           => 'nullable|string',
            'courier_address'     => 'nullable|string|max:500',

            // Оплата
            'payment_type'        => 'required|string|in:card,cod,invoice',

            // Товари
            'cartItems'                           => 'required|array|min:1',
            'cartItems.*.product_variant_id'      => 'required|integer|exists:product_variants,id',
            'cartItems.*.product_id'              => 'nullable|integer|exists:products,id',
            'cartItems.*.quantity'                => 'required|integer|min:1',
            'cartItems.*.price'                   => 'nullable|numeric|min:0', // приймаємо, але перерахуємо
            'cartItems.*.name'                    => 'nullable|string|max:255',
            'cartItems.*.size'                    => 'nullable|string|max:50',
            'cartItems.*.color'                   => 'nullable|string|max:50',
            'cartItems.*.variant_sku'             => 'nullable|string|max:100',
            'cartItems.*.image_url'               => 'nullable|string|max:512',
            'cartItems.*.attributes_json'         => 'nullable', // може бути масив або json

            // Підсумки
            'delivery_cost'       => 'required|numeric|min:0',
            'total'               => 'required|numeric|min:0',
            'promo'               => 'nullable|string',
            'bonuses'             => 'nullable|numeric|min:0',
            'np_description'      => 'nullable|string|max:255',
            'np_address'          => 'nullable|string|max:255',
            'notes'               => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // 👤 Клієнт
            $customer = Customer::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name'  => $data['first_name'].' '.$data['last_name'],
                    'email' => $data['email'] ?? null,
                ]
            );

            $orderNumber = 'ORD-'.time().rand(1000, 9999);

            $order = Order::create([
                'customer_id'  => $customer->id,
                'order_number' => $orderNumber,
                'total_price'  => 0, // перерахуємо нижче
                'currency'     => 'UAH',
                'status'       => 'pending',
                'notes'        => $data['notes'] ?? '',
            ]);

            $locale = $data['locale'] ?? app()->getLocale() ?: 'uk';
            $itemsSubtotal = 0;

            foreach ($data['cartItems'] as $item) {
                $variant = ProductVariant::with('product')->find($item['product_variant_id']);
                $product = $variant?->product;

                // product_id може прийти з фронта або беремо з варіанта
                $productId = $item['product_id'] ?? $product?->id;

                // Назва (пріоритет: переклад -> з фронта -> fallback)
                if ($product) {
                    $tr = ProductTranslation::where('product_id', $product->id)
                        ->where('locale', $locale)
                        ->first();
                    $productName = $tr?->name
                        ?? ($item['name'] ?? ($product->translations->first()->name ?? '—'));
                } else {
                    $productName = $item['name'] ?? '—';
                }

                // Ціна за одиницю — достовірна
                $unitPrice =
                    ($variant && $variant->price_override !== null)
                        ? (float)$variant->price_override
                        : (float)($product->price ?? ($item['price'] ?? 0));

                $qty   = (int)$item['quantity'];
                $lineTotal = $unitPrice * $qty;
                $itemsSubtotal += $lineTotal;

                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $productId,
                    'product_variant_id' => $variant?->id,
                    'product_name'       => $productName,
                    'variant_sku'        => $item['variant_sku'] ?? ($variant->name ?? null),
                    'size'               => $item['size'] ?? ($variant->size ?? null),
                    'color'              => $item['color'] ?? ($variant->color ?? null),
                    'image_url'          => $item['image_url'] ?? null,
                    'attributes_json'    => isset($item['attributes_json'])
                        ? (is_array($item['attributes_json']) ? json_encode($item['attributes_json']) : $item['attributes_json'])
                        : null,
                    'quantity'           => $qty,
                    'price'              => $unitPrice,
                    'total'              => $lineTotal,
                ]);
            }

            // 📦 Доставка
            OrderDelivery::create([
                'order_id'        => $order->id,
                'delivery_type'   => $data['type'],
                'np_ref'          => $data['warehouse'] ?? null,
                'np_description'  => $data['np_description'] ?? null,
                'np_address'      => $data['np_address'] ?? null,
                'courier_address' => $data['courier_address'] ?? null,
            ]);

            // 💸 Перерахунок підсумку на бекенді
            $deliveryCost = (float)($data['delivery_cost'] ?? 0);
            $bonuses      = (float)($data['bonuses'] ?? 0);
            $codFee       = ($data['payment_type'] ?? '') === 'cod' ? 26 : 0;

            $orderTotal = max(0, $itemsSubtotal + $deliveryCost + $codFee - $bonuses);

            $order->update(['total_price' => $orderTotal]);

            // Платіж
            Payment::create([
                'order_id'       => $order->id,
                'payment_method' => $data['payment_type'],
                'amount'         => $orderTotal,
                'currency'       => 'UAH',
                'status'         => 'pending',
                'transaction_id' => null,
            ]);

            DB::commit();

            return response()->json([
                'message'      => 'Замовлення успішно створено',
                'order_id'     => $order->id,
                'order_number' => $orderNumber,
                'total'        => $orderTotal,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Помилка збереження замовлення: '.$e->getMessage(), [
                'trace'        => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Помилка збереження замовлення',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $orderNumber)
    {
        $order = Order::with([
            'customer.address',
            'items.product.translations',
            'items.product.images',
            'delivery',
        ])->where('order_number', $orderNumber)->firstOrFail();

        $delivery = $order->delivery;

        $deliveryInfo = [
            'name'    => $delivery->np_description ?? '—',
            'address' => $delivery->np_address ?? ($delivery->courier_address ?? '—'),
        ];

        return response()->json([
            'order_number' => $order->order_number,
            'customer' => [
                'name'  => $order->customer->name,
                'phone' => $order->customer->phone,
            ],
            'address'  => $order->customer->address?->formatted ?? '—',
            'delivery' => $deliveryInfo,
            'items'    => $order->items->map(function ($item) {
                return [
                    'id'            => $item->id,
                    'product_name'  => $item->product_name,    // snapshot
                    'image_url'     => $item->image_url,       // snapshot
                    'size'          => $item->size,            // snapshot
                    'color'         => $item->color,           // snapshot
                    'quantity'      => $item->quantity,
                    'price'         => number_format($item->price, 2),
                    'total'         => number_format($item->total, 2),
                ];
            }),
            'total_price' => number_format($order->total_price, 2),
        ]);
    }
}
