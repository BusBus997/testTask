<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer',
            'event_date' => 'required|date',
            'ticket_adult_price' => 'required|numeric',
            'ticket_adult_quantity' => 'required|integer',
            'ticket_kid_price' => 'required|numeric',
            'ticket_kid_quantity' => 'required|integer'
        ]);

        $order = $this->orderService->createOrder(
            $validated['event_id'],
            $validated['event_date'],
            $validated['ticket_adult_price'],
            $validated['ticket_adult_quantity'],
            $validated['ticket_kid_price'],
            $validated['ticket_kid_quantity']
        );

        return response()->json($order);
    }

    public function addTicketsToOrder($orderId, $ticketTypeId, $quantity)
    {
        $order = Order::findOrFail($orderId);

        $orderTicket = $order->orderTickets()->create([
            'ticket_type_id' => $ticketTypeId,
            'quantity' => $quantity,
        ]);

        for ($i = 0; $i < $quantity; $i++) {
            $orderTicket->tickets()->create([
                'barcode' => 'barcode_' . uniqid(),
            ]);
        }

        return response()->json(['message' => 'Tickets added successfully!']);
    }
}
