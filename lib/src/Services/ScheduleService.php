<?php

namespace App\Services;

use App\Repositories\OrdersRepository;
use App\Repositories\PersonalsRepository;
use App\Repositories\SchedulesRepository;
use App\Repositories\SpecializationsRepository;
use Bitrix\Main\DB\Exception;
use DateTime;
use Bitrix\Main\Diag\Debug;
class ScheduleService
{
    /**
     * @var SchedulesRepository
     */
    private $scheduleRepository;
    /**
     * @var OrdersRepository
     */
    private $ordersRepository;
    /**
     * @var SpecializationsRepository
     */
    private $specializationsRepository;
    /**
     * @var PersonalsRepository
     */
    private $personalsRepository;

    public function __construct()
    {
        $this->scheduleRepository = new SchedulesRepository();
        $this->ordersRepository = new OrdersRepository();
        $this->specializationsRepository = new SpecializationsRepository();
        $this->personalsRepository = new PersonalsRepository();
    }

    /**
     * @param $personId
     * @param $date
     * @return array
     */
    public function getDisabledDays($personId, $date)
    {
        $out['week_days'] = [0, 1, 2, 3, 4, 5, 6];
        $out['enabled_days'] = [];
        $dateExploded = explode('.', $date);
        $rangeDateStart = DateTime::createFromFormat(
            'd.m.Y',
            sprintf('01.%s.%s', $dateExploded[1], $dateExploded[2])
        );

        $monthEnd = preg_replace('/0/', '', $dateExploded[1] + 1);
        if ($monthEnd > 12) {
            $monthEnd = '01';
        } elseif ($monthEnd < 10) {
            $monthEnd = '0'.$monthEnd;
        }
        $rangeDateTo = DateTime::createFromFormat(
            'd.m.Y',
            sprintf('01.%s.%s', $monthEnd, $dateExploded[2])
        );

        $schedules = $this->scheduleRepository->getByPersonAndDate2($personId, $rangeDateStart, $rangeDateTo);

        foreach ($schedules as $schedule) {
            $exploded = explode('.', $schedule['date_start']);
            $out['enabled_days'][] = (int)$exploded[0];
        }

        return $out;
    }

    /**
     * @param $personId
     * @param $date
     * @return array|array[]
     * @throws Exception
     */
    public function getDaySchedule($personId, $date)
    {
        $out = [
            'morning' => [],
            'day' => [],
            'evening' => [],
        ];

        $schedule = $this->scheduleRepository->getByPersonAndDate($personId, $date);
//        Debug::writeToFile($schedule['date_start']  , 'schedule', './debug/debug.txt');
        $orders = $this->ordersRepository->getForPersonByDate($personId, $date);
//        Debug::writeToFile($orders  , 'order', './debug/debug.txt');
        $ordersMapped = [];

        foreach ($orders as $order) {
            $ordersMapped[$order['time']] = $order;
        }
         
        if (empty($schedule)) {
            return $out;
        }


        $dateTime = DateTime::createFromFormat('d.m.Y', $date);
        //        Debug::writeToFile($dateTime , 'dateTime', './debug/debug.txt');
        $weekDay = $dateTime->format('w');

        $weekDayJs = $weekDay - 1;
        if($weekDayJs < 0){
            $weekDayJs = 6;
        }
        if (!$schedule['schedule'][$weekDayJs]) {
            return $out;
        }

        $scheduleDay = $schedule['schedule'][$weekDayJs];

        $dataComp = $this->dataСomparison($schedule['date_start']);
        $dateNow = new DateTime("28.04.2022");
        $dateFix = new DateTime($dataComp);



    if(floor($scheduleDay['hourFrom']) > 24){
        $workMinutesStart = $scheduleDay['hourFrom'] / 60;
        $workMinutesEnd = $scheduleDay['hourTo'] / 60;
        Debug::writeToFile($workMinutesEnd  , 'workMinutesEnd', './debug/debug.txt');
        Debug::writeToFile($workMinutesStart  , 'workMinutesStart', './debug/debug.txt');
        Debug::writeToFile($scheduleDay['hourFrom'] , 'scheduleDay111', './debug/debug.txt');
    } else{
        $workMinutesStart = $scheduleDay['hourFrom'] * 60;
        $workMinutesEnd = $scheduleDay['hourTo'] * 60;
        Debug::writeToFile($workMinutesEnd  , 'стрый end', './debug/debug.txt');
        Debug::writeToFile($workMinutesStart  , 'старый старт', './debug/debug.txt');
        Debug::writeToFile($scheduleDay , 'scheduleDay222', './debug/debug.txt');
    }


            for ($i = $workMinutesStart; $i < $workMinutesEnd; $i += $schedule['client_time']) {
                $time = $this->convertMinutesToTime($i);
                if ($i < 780) {
                    $out['morning'][] = [
                        'busy' => isset($ordersMapped[$time]),
                        'time' => $time,
                    ];
                } elseif ($i >= 780 && $i < 960) {
                    $out['day'][] = [
                        'busy' => isset($ordersMapped[$time]),
                        'time' => $time,
                    ];
                } elseif ($i >= 960) {
                    $out['evening'][] = [
                        'busy' => isset($ordersMapped[$time]),
                        'time' => $time,
                    ];
                }
            }

        return $out;
    }


    private function dataСomparison($dataСomparison)
        {
            $part = explode('.', $dataСomparison);
            $a = $part[2] . '-' . $part[1] . '-' . $part[0];
            return $a;
        }


    /**
     * @param $minutes
     * @return string
     */
    private function convertMinutesToTime($minutes)
    {

        $h = (int)($minutes / 60);
        $m = $minutes % 60;

        return $h.':'.(strlen($m) == 1 ? $m.'0' : $m);
    }

    /**
     * @param $personId
     * @param $date
     * @throws Exception
     */
    public function getTimeTable($personId, $date)
    {
        $this->ordersRepository->getForPersonByDate($personId, $date);
    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public function create($data)
    {
        $personalsIds = $this->personalsRepository->getIdsBy1cIds($data['personals']);
        $data['personals'] = array_shift($personalsIds);

        return $this->scheduleRepository->create($data);
    }

    /**
     * @param $data
     * @return array
     */
    public function update($data)
    {
        $personalsIds = $this->personalsRepository->getIdsBy1cIds($data['personals']);
        $data['personals'] = array_shift($personalsIds);

        return $this->scheduleRepository->update($data);
    }

    /**
     * @param $data
     * @return array
     */
    public function delete($data)
    {
        return $this->scheduleRepository->delete($data);
    }

    /**
     * @param $time
     * @return float|int
     */
    public function convertTime($time)
    {
        $exploded = explode(':', $time);
        //0.001 added to fix uncorrect time when $time = **:20
        $secTime=$exploded[0]*3600+$exploded[1]*60+$exploded[2];
//        return (float)$exploded[0] + ($exploded[1] / 60 + 0.001);
        return $secTime;
    }

    /**
     * @param $time
     * @return float|int
     */
    public function convertTimeForSoapResponse($time = null)
    {
        $hour = '00';
        $min = '00';
        if ($time) {
            $hour = (int)$time;
            $min = ($time - $hour) * 60 ?: '00';
        }

        return sprintf('2000-01-01 %s:%s:00 UTC', $hour, $min);
    }

    /**
     * @param $date
     * @return string
     */
    public function convertDate($date)
    {

        $exploded = explode('T', $date);

        return DateTime::createFromFormat('Y-m-d', $exploded[0])->format('d.m.Y');
    }
}
