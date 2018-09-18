<?php

namespace Tests\Feature;

use GoogleMaps\GoogleMaps;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Tests\CreatesApplication;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed');
    }

    /**
     * A test for showing all orders.
     *
     * @return void
     */
    public function testAll()
    {
        // Testing without limit.
        $response = $this->json('GET', 'api/orders');
        $response
        ->assertStatus(200)
        ->assertJsonCount(10);

        // Testing with limit.
        $response = $this->json('GET', 'api/orders?limit=12');
        $response
        ->assertStatus(200)
        ->assertJsonCount(12);

        // Testing with page number.
        $response = $this->json('GET', 'api/orders?page=2&limit=12');
        $response
        ->assertStatus(200)
        ->assertJsonCount(8);
    }

    /**
     * A test for placing an order.
     *
     * @return void
     */
    public function testPlace()
    {
        // Testing for success.
        $response = $this->json(
            'POST',
            'api/order',
            [
                'origin' => ["25.1742347", "75.8732978"],
                'destination' => ["25.1580145", "75.8250617"]
            ]
        );
        $distance = new GoogleMaps();
        $distance = $distance->load('distancematrix')
        ->setParam([
            'origins' => ["25.1742347,75.8732978"],
            'destinations' => ["25.1580145,75.8250617"],
        ])->get();
        $distance = json_decode($distance)->rows[0]->elements[0]->distance->text;
        $response
        ->assertStatus(200)
        ->assertJson([
            'id' => 21,
            'distance' => $distance,
            'status' => 'UNASSIGN'
        ]);

        // Testing for fail.
        $response = $this->json(
            'POST',
            'api/order',
            [
                'origin' => ["test", "75.8732978"],
                'destination' => ["25.1580145", "75.8250617"]
            ]
        );
        $response
            ->assertStatus(500)
            ->assertJson([
            'error' => 'Distance cannot be calculated between origin and destination.'
            ]);
    }

    public function testTake()
    {
        // Placing an order.
        $response = $this->json(
            'POST',
            'api/order',
            [
                'origin' => ["25.1742347", "75.8732978"],
                'destination' => ["25.1580145", "75.8250617"]
            ]
        );

        $orderId = json_decode($response->getContent())->id;

        // Testing to take a placed order.
        $response = $this->json(
            'PUT',
            'api/order/' . $orderId,
            [
                'status' => 'taken',
            ]
        );

        $response
        ->assertStatus(200)
        ->assertJson([
            'status' => 'SUCCESS'
        ]);

        // Testing with wrong status keyword.
        $response = $this->json(
            'PUT',
            'api/order/' . $orderId,
            [
                'status' => 'take',
            ]
        );

        $response
        ->assertStatus(409)
        ->assertJson([
            'error' => 'Status keyword is wrong, please provide correct status keyword.'
        ]);

        // Testing to take already taken order.
        $response = $this->json(
            'PUT',
            'api/order/' . $orderId,
            [
                'status' => 'taken',
            ]
        );

        $response
        ->assertStatus(409)
        ->assertJson([
            'error' => 'ORDER_ALREADY_BEEN_TAKEN'
        ]);
    }
}
