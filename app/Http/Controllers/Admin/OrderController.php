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
        // Мапа статусів для селекторів/бейджів
        $statuses = OrderStatus::labels();
    
        // ---- фільтри ----
        $term = trim((string) $request->input('q', ''));
        // дозволені значення статусів
        $allowedStatuses = array_map(fn($c) => $c->value, OrderStatus::cases());
        // приймаємо і рядок, і масив; нормалізуємо в масив унікальних валідних значень
        $statusFilter = $request->has('status')
            ? (array) $request->input('status')
            : [];
        $statusFilter = array_values(array_unique(array_filter(
            array_map('strval', $statusFilter),
            fn($s) => in_array($s, $allowedStatuses, true)
        )));
    
        // дати (без падіння на кривих значеннях)
        $from = $request->input('from');
        $to   = $request->input('to');
    
        // ---- статистика (глобальна; можна перевести на фільтровану за потреби) ----
        $raw = Order::selectRaw('status, COUNT(*) cnt')->groupBy('status')->pluck('cnt', 'status');
        $stats = [
            'pending'   => (int) ($raw['pending']   ?? 0),
            'delivered' => (int) ($raw['delivered'] ?? 0),
            'refunded'  => (int) ($raw['refunded']  ?? 0),
            'cancelled' => (int) ($raw['cancelled'] ?? 0),
        ];
    
        // ---- запит ----
        $orders = Order::with(['items', 'delivery', 'customer'])
            // Пошук
            ->when($term !== '', function ($q) use ($term) {
                // нормалізуємо телефон для пошуку по цифрах
                $digits = preg_replace('/\D+/', '', $term);
    
                $q->where(function ($qq) use ($term, $digits) {
                    $qq->where('order_number', 'like', "%{$term}%")
                       // покупець
                       ->orWhereHas('customer', function ($qc) use ($term, $digits) {
                           $qc->where('name',  'like', "%{$term}%")
                              ->orWhere('email','like', "%{$term}%");
    
                           if ($digits !== '') {
                               // шукаємо телефон як підрядок цифр
                               $qc->orWhereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') LIKE ?", ["%{$digits}%"]);
                           }
                       })
                       // артикул з позицій
                       ->orWhereHas('items', function ($qi) use ($term) {
                           $qi->where('variant_sku', 'like', "%{$term}%")
                              ->orWhere('product_name', 'like', "%{$term}%");
                       });
                });
            })
            // Мульти-статус
            ->when(!empty($statusFilter), fn($q) => $q->whereIn('status', $statusFilter))
            // Дата від
            ->when($from, function ($q) use ($from) {
                $q->whereDate('created_at', '>=', $from);
            })
            // Дата до
            ->when($to, function ($q) use ($to) {
                $q->whereDate('created_at', '<=', $to);
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();
    
        return view('admin.orders.index', [
            'orders'        => $orders,
            'statuses'      => $statuses,
            'stats'         => $stats,
            // для зручності — віддати назад застосовані фільтри (якщо потрібно у в’ю)
            'statusFilter'  => $statusFilter,
            'q'             => $term,
            'from'          => $from,
            'to'            => $to,
        ]);
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
