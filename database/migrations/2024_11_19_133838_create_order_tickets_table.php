<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



Schema::create('order_tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('ticket_type_id')->constrained()->onDelete('cascade');
    $table->integer('quantity');
    $table->timestamps();
});
