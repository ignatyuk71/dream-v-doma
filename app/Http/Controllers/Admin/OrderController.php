<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\OrderStatus;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Статуси для селекторів/бейджів
        $statuses = OrderStatus::labels();

        // Статистика (для верхніх віджетів, якщо захочеш показати)
        $raw = Order::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c','status');
        $stats = [
            'pending'   => (int) ($raw['pending']   ?? 0),
            'completed' => (int) ($raw['delivered'] ?? 0), // Completed == delivered
            'refunded'  => (int) ($raw['refunded']  ?? 0),
            'failed'    => (int) ($raw['cancelled'] ?? 0), // Failed == cancelled
        ];

        // Список замовлень (розгортання потребує items+delivery+customer)
        $orders = Order::with(['items', 'delivery', 'customer'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->get('q'));
                $q->where('order_number', 'like', "%{$term}%")
                ->orWhereHas('customer', function ($qq) use ($term) {
                    $qq->where('name','like',"%{$term}%")
                        ->orWhere('phone','like',"%{$term}%")
                        ->orWhere('email','like',"%{$term}%");
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->get('status')))
            ->when($request->filled('from'), fn($q) => $q->whereDate('created_at', '>=', request('from')))
            ->when($request->filled('to'),   fn($q) => $q->whereDate('created_at', '<=', request('to')))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();
    

        return view('admin.orders.index', compact('orders', 'statuses', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'delivery', 'customer']);
        $statuses = OrderStatus::labels();

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_column(OrderStatus::cases(), 'value'))],
            'notes'  => ['nullable', 'string'],
        ]);

        $order->update([
            'status' => $request->string('status'),
            'notes'  => $request->string('notes'),
        ]);

        return back()->with('success', 'Статус замовлення оновлено');
    }

    
}
