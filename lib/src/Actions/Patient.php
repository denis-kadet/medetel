<?php

namespace App\Actions;

use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Patient extends AbstractAction {

    /**
     * @var PatientService
     */
    private $personalService;

    public function __construct()
    {
        $this->personalService = new PatientService();
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
