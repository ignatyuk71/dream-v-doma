<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Список замовлень + фільтри + легка статистика.
     */
    public function index(Request $request)
    {
        $statuses = OrderStatus::labels();

        // Легка статистика по сторінці (за наявним фільтром) або глобальна — за бажанням
        $raw = Order::selectRaw('status, COUNT(*) cnt')->groupBy('status')->pluck('cnt', 'status');
        $stats = [
            'pending'   => (int)($raw['pending']   ?? 0),
            'delivered' => (int)($raw['delivered'] ?? 0),
            'refunded'  => (int)($raw['refunded']  ?? 0),
            'cancelled' => (int)($raw['cancelled'] ?? 0),
        ];

        $orders = Order::with(['items', 'delivery', 'customer'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->string('q'));
                $q->where(function ($qq) use ($term) {
                    $qq->where('order_number', 'like', "%{$term}%")
                       ->orWhereHas('customer', function ($qc) use ($term) {
                           $qc->where('name',  'like', "%{$term}%")
                              ->orWhere('phone','like', "%{$term}%")
                              ->orWhere('email','like', "%{$term}%");
                       });
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('from'),   fn($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'),     fn($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'statuses', 'stats'));
    }

    /**
     * Деталі замовлення.
     */
    public function show(Order $order)
    {
        $order->load(['items', 'delivery', 'customer']);
        $statuses = OrderStatus::labels();

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    /**
     * Оновлення (через звичайну HTML-форму).
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'notes'  => ['nullable', 'string'],
        ]);

        $order->update([
            'status' => OrderStatus::from($validated['status']),
            'notes'  => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Статус замовлення оновлено');
    }

    /**
     * PATCH /admin/orders/{order}/status (AJAX)
     * Оновлення статусу без перезавантаження.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $order->status = OrderStatus::from($data['status']);
        $order->save();

        return response()->json([
            'ok'     => true,
            'status' => $order->status->value,
            'label'  => OrderStatus::labels()[$order->status->value] ?? $order->status->value,
        ]);
    }

    /**
     * DELETE /admin/orders/{order}
     * Видалення замовлення з дочірніми записами (items, delivery) в транзакції.
     */
    public function destroy(Request $request, Order $order)
    {
        try {
            DB::transaction(function () use ($order) {
                // дочірні
                $order->items()->delete();
                $order->delivery()->delete();
                // якщо є інші звʼязки — додай тут:
                // $order->payments()->delete();
                // $order->history()->delete();
                // ...

                // саме замовлення
                $order->delete();
            });

            if ($request->expectsJson()) {
                return response()->json(['ok' => true]);
            }

            return back()->with('success', 'Замовлення видалено');
        } catch (\Throwable $e) {
            Log::error('Order delete failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Помилка видалення'], 500);
            }

            return back()->with('error', 'Не вдалося видалити замовлення');
        }
    }
}
