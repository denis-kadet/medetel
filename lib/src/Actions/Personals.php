<?php

namespace App\Actions;

use App\Services\PersonalsService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Personals extends AbstractAction {

    /**
     * @var PersonalsService
     */
    private $personalService;

    public function __construct()
    {
        $this->personalService = new PersonalsService();
    }

    public function index()
    {
        $response = new JsonResponse($this->personalService->getPersonalsList());
        $response->headers->set('Content-Type', 'application/json');
        $response->sendHeaders();
        return $response->getContent();
    }

    public function personalsItem(Request $request)
    {
        $response = new JsonResponse($this->personalService->getPersonalsItem());
        $response->headers->set('Content-Type', 'application/json');
        $response->sendHeaders();
        return $response->getContent();
    }


}
