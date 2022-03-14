<?php

namespace App\Services;

use Bitrix\Main\DB\Exception;
use DateTime;
use SimpleXMLElement;

class WsdlService
{

    const ACTION_TYPE_UPDATE = 'редактирование';
    const ACTION_TYPE_CREATE = 'добавление';
    const ACTION_TYPE_DELETE = 'удаление';

    /**
     * @var SimpleXMLElement
     */
    private $xmlBody;
    /**
     * @var ScheduleService
     */
    private $scheduleService;
    /**
     * @var PersonalsService
     */
    private $personalService;
    /**
     * @var OrderService
     */
    private $orderService;

    public function __construct()
    {
        $this->specializationService = new SpecializationsService();
        $this->personalService = new PersonalsService();
        $this->scheduleService = new ScheduleService();
        $this->orderService = new OrderService();
    }

    /**
     * @var SimpleXMLElement
     */
    private $xmlDocument;

    public function resolve($headers, $body)
    {
        $this->startXml();
        $this->{preg_replace('/\"/', '', $headers->Soapaction)}($body);

        $xml = preg_replace('/xmlns\:tns\=\"tns\"/', '', $this->xmlDocument->asXML());
        $xml = preg_replace('/xmlns\:soap\=\"soap\"/', '', $xml);

        return $xml;
    }

    /**
     * @throws \Exception
     */
    public function startXml()
    {
        $xmlstr = '<?xml version="1.0" encoding="UTF-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:tns="urn:Medetel"></soap:Envelope>';
        $this->xmlDocument = new SimpleXMLElement($xmlstr);
        $this->xmlBody = $this->xmlDocument->addChild('soap:Body', null, 'soap');
        $this->xmlBody->registerXPathNamespace('soap', 'soap');
    }

    public function specialization($body)
    {
        $requestXml = $this->parseXmlRequest($body);
        $action = (string)$requestXml->Body->specialization_request->action;
        $data = [
            'name' => (string)$requestXml->Body->specialization_request->specialization->name,
            'id' => (string)$requestXml->Body->specialization_request->specialization->id,
        ];

        switch ($action) {
            case self::ACTION_TYPE_CREATE:
                $result = $this->specializationService->create($data);
                break;
            case self::ACTION_TYPE_UPDATE:
                $result = $this->specializationService->update($data);
                break;
            case self::ACTION_TYPE_DELETE:
                $result = $this->specializationService->delete($data);
                break;
        }

        $nodeSpecializationResponse = $this->xmlBody->addChild('tns:specializations_response', '', 'tns');

        $nodeSpecializationResult = $nodeSpecializationResponse->addChild('specialization_result', '', '');
        $nodeSpecialization = $nodeSpecializationResult->addChild('specialization', '', '');
        $nodeSpecialization->addChild('id', $result['id'], '');
        $nodeSpecialization->addChild('name', $result['name'], '');
    }

    /**
     * @param $body
     */
    public function personal($body)
    {
        $requestXml = $this->parseXmlRequest($body);
        $action = (string)$requestXml->Body->personal_request->action;
        $data = [
            'name' => (string)$requestXml->Body->personal_request->personal->name,
            'id' => (string)$requestXml->Body->personal_request->personal->id,
            'surname' => (string)$requestXml->Body->personal_request->personal->surname,
            'patronymic' => (string)$requestXml->Body->personal_request->personal->patronymic,
            'email' => (string)$requestXml->Body->personal_request->personal->email,
            'active' => (string)$requestXml->Body->personal_request->personal->approved == 'true' ? true : false,
            'specializations' => explode(
                ',',
                (string)$requestXml->Body->personal_request->personal->specialization_ids
            ),
        ];

        switch ($action) {
            case self::ACTION_TYPE_CREATE:
                $result = $this->personalService->create($data);
                break;
            case self::ACTION_TYPE_UPDATE:
                $result = $this->personalService->update($data);
                break;
            case self::ACTION_TYPE_DELETE:
                $result = $this->personalService->delete($data);
                break;
        }

        $nodePersonalResponse = $this->xmlBody->addChild('tns:personal_response', '', 'tns');
        $nodePersonalResult = $nodePersonalResponse->addChild('personal_result', '', '');
        $nodePersonal = $nodePersonalResult->addChild('personal', '', '');
        $nodePersonal->addChild('id', $result['external_id'], '');
        $nodePersonal->addChild('name', $result['name'], '');
        $nodePersonal->addChild('surname', $result['surname'], '');
        $nodePersonal->addChild('patronymic', $result['patronymic'], '');
        $nodePersonal->addChild('email', $result['email'], '');
        $nodePersonal->addChild('specialization_ids', implode(',', $result['specialization_1c_ids']), '');
        $nodePersonal->addChild('approved', false, '');
    }

    /**
     * @param $body
     */
    public function personals_array($body)
    {
        $requestXml = $this->parseXmlRequest($body);
        $action = (string)$requestXml->Body->personal_request->action;
        $data = [
            'name' => (string)$requestXml->Body->personal_request->personal->name,
            'id' => (string)$requestXml->Body->personal_request->personal->id,
            'surname' => (string)$requestXml->Body->personal_request->personal->surname,
            'patronymic' => (string)$requestXml->Body->personal_request->personal->patronymic,
            'email' => (string)$requestXml->Body->personal_request->personal->email,
            'specializations' => explode(
                ',',
                (string)$requestXml->Body->personal_request->personal->specialization_ids
            ),
        ];

        switch ($action) {
            case self::ACTION_TYPE_CREATE:
                $result = $this->personalService->create($data);
                break;
            case self::ACTION_TYPE_UPDATE:
                $result = $this->personalService->update($data);
                break;
            case self::ACTION_TYPE_DELETE:
                $result = $this->personalService->delete($data);
                break;
        }

        $nodePersonalResponse = $this->xmlBody->addChild('tns:personal_response', '', 'tns');
        $nodePersonalResult = $nodePersonalResponse->addChild('personal_result', '', '');
        $nodePersonal = $nodePersonalResult->addChild('personal', '', '');
        $nodePersonal->addChild('id', $result['external_id'], '');
        $nodePersonal->addChild('name', $result['name'], '');
        $nodePersonal->addChild('surname', $result['surname'], '');
        $nodePersonal->addChild('patronymic', $result['patronymic'], '');
        $nodePersonal->addChild('email', $result['email'], '');
        $nodePersonal->addChild('specialization_ids', implode(',', $result['specializations']), '');
        $nodePersonal->addChild('approved', '', '');

    }

    /**
     * @param $body
     * @throws Exception
     */
    public function schedule($body)
    {
        $requestXml = $this->parseXmlRequest($body);
        $action = (string)$requestXml->Body->schedule_request->action;

        $weekDays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];

        foreach ($weekDays as $key => $dayName) {
            $fieldNameStart = sprintf('%s_start', $dayName);
            $fieldNameEnd = sprintf('%s_end', $dayName);
            if (preg_match('/\s/', $requestXml->Body->schedule_request->schedule->{$fieldNameStart})) {
                $schedules[$key] = [
                    'day' => $key,
                    'hourFrom' => $this->scheduleService->convertTime(
                        explode(' ', $requestXml->Body->schedule_request->schedule->{$fieldNameStart})[1]
                    ),
                    'hourTo' => $this->scheduleService->convertTime(
                        explode(' ', $requestXml->Body->schedule_request->schedule->{$fieldNameEnd})[1]
                    ),
                ];
            }
        }

        $data = [
            'personals' => (string)$requestXml->Body->schedule_request->personal_id,
            'id' => (string)$requestXml->Body->schedule_request->schedule->id,
            'date_start' => $this->scheduleService->convertDate(
                (string)$requestXml->Body->schedule_request->schedule->start_date
            ),
            'date_end' => $this->scheduleService->convertDate(
                (string)$requestXml->Body->schedule_request->schedule->end_date
            ),
            'schedules' => $schedules,
            'client_time' => (string)$requestXml->Body->schedule_request->schedule->reception_duration,
        ];

        switch ($action) {
            case self::ACTION_TYPE_CREATE:
                $result = $this->scheduleService->create($data);
                break;
            case self::ACTION_TYPE_UPDATE:
                $result = $this->scheduleService->update($data);
                break;
            case self::ACTION_TYPE_DELETE:
                $result = $this->scheduleService->delete($data);
                break;
        }

        $nodeScheduleResponse = $this->xmlBody->addChild('tns:schedule_response', '', 'tns');
        $nodeScheduleResult = $nodeScheduleResponse->addChild('schedule_result', '', '');
        $nodeSchedule = $nodeScheduleResult->addChild('schedule', '', '');
        $nodeSchedule->addChild('id', $result['external_id'], '');
        $nodeSchedule->addChild('reception_duration', $result['client_time'], '');
        $nodeSchedule->addChild('start_date', DateTime::createFromFormat('d.m.Y', $result['date_start'])
            ->format('Y-m-d'), '');
        $nodeSchedule->addChild('end_date', DateTime::createFromFormat('d.m.Y', $result['date_end'])
            ->format('Y-m-d'), '');
        foreach ($weekDays as $key => $dayName) {
            $nodeSchedule->addChild(
                sprintf('%s_start', $dayName),
                $this->scheduleService->convertTimeForSoapResponse($result['schedule'][$key]['hourFrom'] ?? null),
                ''
            );
            $nodeSchedule->addChild(
                sprintf('%s_end', $dayName),
                $this->scheduleService->convertTimeForSoapResponse($result['schedule'][$key]['hourTo'] ?? null),
                ''
            );
        }
    }

    function make_seed()
    {
        list($usec, $sec) = explode(' ', microtime());

        return $sec + $usec * 1000000;
    }


    /**
     * @param $body
     * @throws Exception
     */
    public function event($body)
    {
        mt_srand(
            function () {
                list($usec, $sec) = explode(' ', microtime());

                return $sec + $usec * 1000;
            }
        );
        $randval = mt_rand();
        $requestXml = $this->parseXmlRequest($body);
        $action = (string)$requestXml->Body->event_request->action;
        list($date, $time) = explode('T', (string)$requestXml->Body->event_request->event->scheduled_at);
        $timeExploded = explode(':', $time);
        $data = [
            'name' => (string)$requestXml->Body->event_request->patient->name,
            'id' => (string)$requestXml->Body->event_request->event->id ?: $randval,
            'personal_id' => (string)$requestXml->Body->event_request->personal_id,
            'date' => (DateTime::createFromFormat('Y-m-d', $date))->format('d.m.Y'),
            'time' => sprintf('%s:%s',$timeExploded[0]+3,$timeExploded[1]),
            'email' => (string)$requestXml->Body->event_request->patient->email,
            'comment' => '',
            'phone' => (string)$requestXml->Body->event_request->patient->phone,
            'in_1c' => true,
        ];

        switch ($action) {
            case self::ACTION_TYPE_CREATE:
                $result = $this->orderService->create($data);
                break;
            case self::ACTION_TYPE_UPDATE:
                $result = $this->orderService->update($data);
                break;
            case self::ACTION_TYPE_DELETE:
                $result = $this->orderService->delete($data);
                break;
        }

        $nodeEventResponse = $this->xmlBody->addChild('tns:event_response', '', 'tns');
        $nodeEventResult = $nodeEventResponse->addChild('event_result', '', '');
        $nodeEvent = $nodeEventResult->addChild('event', '', '');
        $nodeEvent->addChild('id', $result['external_id'], '');
        $nodeEvent->addChild('scheduled_at', sprintf('%s %s:00 +0300', $result['date'], $result['time']), '');
        $nodeEvent->addChild('personal', $result['personal_external_id'], '');
        $nodeEvent->addChild('patient', $result['patient'], '');
        $nodeEvent->addChild('created_at', sprintf('%s +0300', $result['date_create']), '');
    }

    /**
     *
     */
    public function personals()
    {
        $personalsResponse = $this->xmlBody->addChild('tns:personals_response', null, 'tns');
        foreach ($this->personalService->getPersonalsList() as $perosnal) {
            $nodeSpecialization = $personalsResponse->addChild('personal', '', '');
            $nodeSpecialization->addChild('id', $perosnal['external_id'], '');
            $nodeSpecialization->addChild('name', $perosnal['name'], '');
            $nodeSpecialization->addChild('email', $perosnal['email'], '');
            $nodeSpecialization->addChild('surname', $perosnal['surname'], '');
            $nodeSpecialization->addChild('patronymic', $perosnal['patronymic'], '');
            $nodeSpecialization->addChild('specialization_ids', implode(',', $perosnal['specializations']), '');
            $nodeSpecialization->addChild('approved', '', '');
        }
    }

    /**
     * @param $body
     */
    public function events($body)
    {
        $requestXml = $this->parseXmlRequest($body);
        $since = (string)$requestXml->Body->events_request->since;

        $nodeEventsResponse = $this->xmlBody->addChild('tns:events_response', null, 'tns');
        $nodeEventsResult = $nodeEventsResponse->addChild('events_result', null, '');

        $events = $this->orderService->showList(['date_created' => $since]);

        foreach ($events as $item) {
            $nodeEvent = $nodeEventsResult->addChild('event');
            $nodeEvent->addChild('id', $item['external_id'], '');
            $item['date'] = DateTime::createFromFormat('d.m.Y', $item['date'])->format('Y-m-d');
            $nodeEvent->addChild('scheduled_at', sprintf('%s %s:00 +0300', $item['date'], $item['time']), '');
            $nodeEvent->addChild('personal', $item['personal_id'], '');
            $item['created_at']
                = DateTime::createFromFormat('d.m.Y H:i:s', $item['created_at'])->format('Y-m-d H:i:s');
            $nodeEvent->addChild('created_at', $item['created_at'].' +0300', '');
            $patient = $nodeEvent->addChild('patient', '', '');
            $patient->addAttribute('id', $item['patient'], '');
            $patient->addAttribute('name', $item['name'], '');
            $patient->addAttribute('phone', $item['phone'], '');
            $patient->addAttribute('email', $item['email'], '');
        }
    }

    /**
     * @param $body
     */
    public function specializations($body)
    {
        $nodeSpecializationsResponse = $this->xmlBody->addChild('tns:specializations_response', null, 'tns');
        foreach ($this->specializationService->getSpecializationList() as $specialization) {
            $nodeSpecialization = $nodeSpecializationsResponse->addChild('specialization', '', '');
            $nodeSpecialization->addChild('id', $specialization['id'], '');
            $nodeSpecialization->addChild('name', $specialization['name'], '');
        }
    }

    public function getChema()
    {
        global $currentClinic;

        $contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/lib/resources/wsdl_chema.xml');
        $contents = preg_replace('/#LOCATION_ADDRESS#/',$currentClinic['site'].'api/medetel_soap/action', $contents);
        return  $contents;
    }

    /**
     * @param $body
     * @return false|SimpleXMLElement|string|null
     */
    private function parseXmlRequest($body)
    {
        return simplexml_load_string(str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $body));
    }
}
