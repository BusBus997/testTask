<?php

namespace Tests\Unit;

use App\Services\OrderService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    public function test_create_order_with_mock_api()
    {
        // Замокать API
        Http::fake([
            'https://api.site.com/book' => Http::response(['message' => 'order successfully booked'], 200),
            'https://api.site.com/approve' => Http::response(['message' => 'order successfully approved'], 200),
        ]);

        // Инициализировать OrderService
        $orderService = new OrderService();

        // Данные для теста
        $event_id = 123;
        $event_date = '2024-11-25 15:00:00';
        $ticket_adult_price = 500;
        $ticket_adult_quantity = 2;
        $ticket_kid_price = 300;
        $ticket_kid_quantity = 1;

        // Вызов метода
        $response = $orderService->createOrder(
            $event_id,
            $event_date,
            $ticket_adult_price,
            $ticket_adult_quantity,
            $ticket_kid_price,
            $ticket_kid_quantity
        );

        // Проверка, что Order был успешно создан
        $this->assertNotEmpty($response);
        $this->assertEquals($response->event_id, $event_id);
        $this->assertEquals($response->total_price, 1300); // (500*2 + 300*1)

        // Проверка, что запрос на создание заказа был отправлен
        Http::assertSent(function ($request) use ($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity) {
            return $request->url() == 'https://api.site.com/book' &&
                $request->method() == 'POST' &&
                $request->data() == [
                    'event_id' => $event_id,
                    'event_date' => $event_date,
                    'ticket_adult_price' => $ticket_adult_price,
                    'ticket_adult_quantity' => $ticket_adult_quantity,
                    'ticket_kid_price' => $ticket_kid_price,
                    'ticket_kid_quantity' => $ticket_kid_quantity
                ];
        });

        // Проверка, что запрос на подтверждение был отправлен
        Http::assertSent(function ($request) {
            return $request->url() == 'https://api.site.com/approve' &&
                $request->method() == 'POST';
        });
    }
}
