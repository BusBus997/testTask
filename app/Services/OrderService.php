<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderService
{
    public function createOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity)
    {
        $barcode = $this->generateUniqueBarcode();
        $total_price = ($ticket_adult_price * $ticket_adult_quantity) + ($ticket_kid_price * $ticket_kid_quantity);

        $bookingResponse = $this->sendBookingRequest($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode);

        while ($bookingResponse['error'] ?? null === 'barcode already exists') {
            $barcode = $this->generateUniqueBarcode();
            $bookingResponse = $this->sendBookingRequest($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode);
        }

        if ($bookingResponse['message'] === 'order successfully booked') {
            $approvalResponse = $this->sendApprovalRequest($barcode);

            if ($approvalResponse['message'] === 'order successfully approved') {
                return $this->saveOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $total_price);
            }

            return ['error' => 'Order not approved', 'details' => $approvalResponse];
        }

        return ['error' => 'Booking failed', 'details' => $bookingResponse];
    }

    private function generateUniqueBarcode()
    {
        $barcode = Str::random(10);
        while (Order::where('barcode', $barcode)->exists()) {
            $barcode = Str::random(10);
        }
        return $barcode;
    }

    private function sendBookingRequest($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode)
    {
        $response = Http::post('https://api.site.com/book', [
            'event_id' => $event_id,
            'event_date' => $event_date,
            'ticket_adult_price' => $ticket_adult_price,
            'ticket_adult_quantity' => $ticket_adult_quantity,
            'ticket_kid_price' => $ticket_kid_price,
            'ticket_kid_quantity' => $ticket_kid_quantity,
            'barcode' => $barcode,
        ]);

        return $response->json();
    }

    private function sendApprovalRequest($barcode)
    {
        $response = Http::post('https://api.site.com/approve', [
            'barcode' => $barcode,
        ]);

        return $response->json();
    }

    private function saveOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $total_price)
    {
        return Order::create([
            'event_id' => $event_id,
            'event_date' => $event_date,
            'ticket_adult_price' => $ticket_adult_price,
            'ticket_adult_quantity' => $ticket_adult_quantity,
            'ticket_kid_price' => $ticket_kid_price,
            'ticket_kid_quantity' => $ticket_kid_quantity,
            'barcode' => $barcode,
            'total_price' => $total_price,
        ]);
    }
}
