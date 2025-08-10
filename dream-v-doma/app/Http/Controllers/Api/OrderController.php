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

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            // Дані клієнта
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            // Локаль для перекладів
            'locale' => 'nullable|string|in:uk,ru,en',
            // Дані доставки
            'type' => 'required|string|in:branch,postomat,courier',
            'city' => 'required|array',
            'city.DeliveryCity' => 'required|string',
            'warehouse' => 'nullable|string', // Ref відділення чи поштомату
            'courier_address' => 'nullable|string|max:500',
            // Дані оплати
            'payment_type' => 'required|string|in:card,cod,invoice',
            // Товари
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer|exists:products,id',
            'cartItems.*.quantity' => 'required|integer|min:1',
            'cartItems.*.price' => 'required|numeric|min:0',
            'delivery_cost' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'promo' => 'nullable|string',
            'bonuses' => 'nullable|numeric|min:0',
            'np_description' => 'nullable|string|max:255',
            'np_address' => 'nullable|string|max:255',
            'courier_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            
        ]);

        DB::beginTransaction();

        try {
            $customer = Customer::firstOrCreate(
                ['phone' => $data['phone']],
                [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'] ?? null,
                ]
            );

            $orderNumber = 'ORD-' . time() . rand(1000, 9999); // щоб уникнути дублікатів

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'total_price' => $data['total'],
                'currency' => 'UAH',
                'status' => 'pending',
                'notes' => $data['notes'] ?? '',
            ]);

            $locale = $data['locale'] ?? app()->getLocale() ?: 'uk';
            foreach ($data['cartItems'] as $item) {
                $product = Product::find($item['id']);
                $translation = null;

                if ($product) {
                    $translation = ProductTranslation::where('product_id', $product->id)
                                                    ->where('locale', $locale)
                                                    ->first();
                    \Log::info('Переклад продукту:', ['translation' => $translation ? $translation->toArray() : null]);
                }

                $productName = $translation && !empty($translation->name) ? $translation->name : 'Без назви';

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $productName,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? 0,
                    'total' => ($item['price'] ?? 0) * $item['quantity'],
                ]);
            }

            OrderDelivery::create([
                'order_id' => $order->id,
                'delivery_type' => $data['type'],
                'np_ref' => $data['warehouse'] ?? null,
                'np_description' => $data['np_description'] ?? null,
                'np_address' => $data['np_address'] ?? null,
                'courier_address' => $data['courier_address'] ?? null,
            ]);

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_type'],
                'amount' => $data['total'],
                'currency' => 'UAH',
                'status' => 'pending',
                'transaction_id' => null,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Замовлення успішно створено',
                'order_id' => $order->id,
                'order_number' => $orderNumber,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Помилка збереження замовлення: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Помилка збереження замовлення',
                'details' => $e->getMessage()
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
            'name' => $delivery->np_description ?? '—',
            'address' => $delivery->np_address ?? ($delivery->courier_address ?? '—'),
        ];
        
        return response()->json([
            'order_number' => $order->order_number,
            'customer' => [
                'name' => $order->customer->name,
                'phone' => $order->customer->phone,
            ],
            'address' => $order->customer->address?->formatted ?? '—',
            'delivery' => $deliveryInfo,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->translations->first()?->name ?? '—',
                    'product_image' => $item->product->images->first()?->getFullUrlAttribute() ?? null,
                    'quantity' => $item->quantity,
                    'price' => number_format($item->price, 2),
                ];
            }),
        ]);
    }




    

}
