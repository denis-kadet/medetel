<?php

namespace App\Actions;

use App\Services\OrderService;
use Bitrix\Main\DB\Exception;
use Illuminate\Http\JsonResponse;
use Klein\Request;

class Orders extends AbstractAction
{

    private $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * @param Request $request
     * @return false|string
     * @throws Exception
     */
    public function createOrder(Request $request)
    {
        $newOrder = $this->orderService->createOrder($request->paramsPost()->all(), $request->files()->all());

        $newOrder['id'] = $newOrder['date_time_hash'];

        $response = new JsonResponse(
            $newOrder
        );

        $response->headers->set('Content-Type', 'application/json');

        $response->sendHeaders();

        return $response->getContent();
    }
}
