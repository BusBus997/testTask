<?php





use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketTypesSeeder extends Seeder
{
    public function run()
    {
        DB::table('ticket_types')->insert([
            ['name' => 'Взрослый', 'price' => 500],
            ['name' => 'Детский', 'price' => 300],
            ['name' => 'Льготный', 'price' => 400],
            ['name' => 'Групповой', 'price' => 350],
        ]);
    }
}
