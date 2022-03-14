<?php

namespace App\Actions;

use App\Services\SpecializationsService;
use Illuminate\Http\JsonResponse;

class Specializations extends AbstractAction {

    /**
     * @var SpecializationsService
     */
    private $personalService;

    public function __construct()
    {
        $this->personalService = new SpecializationsService();
    }

    public function index()
    {
        $response = new JsonResponse($this->personalService->getSpecializationList());
        $response->headers->set('Content-Type', 'application/json');
        $response->sendHeaders();
        return $response->getContent();
    }

}
