<?php

namespace App\Http\Controllers;

use App\Order;
use GoogleMaps\GoogleMaps;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function place(Request $request)
    {
        $distance = $this->getDistance($request->only(['origin', 'destination']));
        if (!$distance['success']) {
            return response(json_encode([
                'error' => 'Distance cannot be calculated between origin and destination.'
            ]), 500);
        }

        $order = new Order;
        $order->distance = $distance['data'];
        $order->save();
        return response(json_encode([
            'id' => $order->id,
            'distance' => $distance['data'],
            'status' => 'UNASSIGN'
        ]), 200);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function all(Request $request)
    {
        $limit = $request->query('limit') ? : 10;
        $order = Order::paginate($limit);
        return response(json_encode(
            $order->items()
        ), 200);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param $id
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function take(Request $request, $id)
    {
        if (strtoupper($request->input('status')) !== "TAKEN") {
            return response(json_encode([
                'error' => 'Status keyword is wrong, please provide correct status keyword.'
            ]), 409);
        }

        $order = Order::where('id', $id)->where('status', 'UNASSIGN');

        if ($order->count() && $order->update(['status' => 'TAKEN'])) {
            return response(json_encode([
                'status' => 'SUCCESS'
            ]), 200);
        }

        return response(json_encode([
            'error' => 'ORDER_ALREADY_BEEN_TAKEN'
        ]), 409);
    }

    /**
     * @param $location
     *
     * @return mixed
     */
    protected function getDistance($location)
    {
        $distance = new GoogleMaps();
        $distance = $distance->load('distancematrix')
            ->setParam([
                'origins' => implode($location['origin'], ','),
                'destinations' => implode($location['destination'], ','),
            ])->get();
        if (json_decode($distance)->rows[0]->elements[0]->status == "NOT_FOUND") {
            return [
                'success' => false,
            ];
        }
        $distance = json_decode($distance)->rows[0]->elements[0]->distance->text;

        return [
            'success' => true,
            'data' => $distance
        ];
    }
}
