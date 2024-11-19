<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'event_id',
        'event_date',
        'ticket_adult_price',
        'ticket_adult_quantity',
        'ticket_kid_price',
        'ticket_kid_quantity',
        'barcode',
        'equal_price',
    ];


    public $timestamps = true;
    protected static function booted()
    {
        static::creating(function ($order) {

            $order->barcode = uniqid('barcode_');
        });
    }

    public function orderTickets()
    {
        return $this->hasMany(OrderTicket::class);
    }



}
