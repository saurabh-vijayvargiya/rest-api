<?php

use App\Order;
use Faker\Factory;
use Illuminate\Database\Seeder;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        Order::truncate();
        $faker = Factory::create();
        $status = ['UNASSIGN', 'TAKEN'];
        // And now, let's create a few orders in our database:
        for ($i = 0; $i < 20; $i++) {
            Order::create([
                'distance' => rand(1, 99) . ' km',
                'status' => array_random($status),
            ]);
        }
    }
}
