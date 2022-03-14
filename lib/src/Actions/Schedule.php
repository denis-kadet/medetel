<?php

namespace App\Actions;

use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Schedule extends AbstractAction {

    /**
     * @var ScheduleService
     */
    private $scheduleService;

    public function __construct()
    {
        $this->scheduleService = new ScheduleService();
    }

    public function getDisabledDaysByPersonForDate($request)
    {
        $response = new JsonResponse($this->scheduleService->getDisabledDays(
            $request->personals_id,
            $request->date
        ));
        $response->headers->set('Content-Type', 'application/json');
        $response->sendHeaders();
        return $response->getContent();
    }

    public function getBypersonForDate($request)
    {

        $response = new JsonResponse($this->scheduleService->getDaySchedule(
            $request->personals_id,
            $request->date
        ));
  

        $response->headers->set('Content-Type', 'application/json');
        $response->sendHeaders();
        return $response->getContent();
    }


}
