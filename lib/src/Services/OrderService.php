<?php

namespace App\Services;

use App\Repositories\OrdersRepository;
use App\Repositories\PatientsRepository;
use App\Repositories\PersonalsRepository;
use App\Repositories\SpecializationsRepository;
use Bitrix\Main\DB\Exception;
use Bitrix\Main\Mail\Event;

class OrderService
{
    public function __construct()
    {
        $this->orderRepositiry = new OrdersRepository();
        $this->personalsRepository = new PersonalsRepository();
        $this->patientRepository = new PatientsRepository();
        $this->specializationsRepository = new SpecializationsRepository();
    }

    /**
     * @param $params
     * @param $files
     * @return array
     * @throws Exception
     */
    public function createOrder($params, $files)
    {
        $patient = $this->patientRepository->getByEmail($params['email']);
        if (empty($patient)) {
            $patient = $this->patientRepository->create($params);
        }
        $params['patient'] = $patient['id'];

        if (!$this->dateIsBusy($params['date'], $params['time'], $params['person_id'])) {

            $result =  $this->orderRepositiry->create($params, $files);

            $doctor = $this->personalsRepository->getPersonalById($result['personal_id']);
            $specialization = $this->specializationsRepository->getById($doctor['specializations'][0]);
            $arEventFields = array(
                "EMAIL_FROM"  => $result['email'],
                "NAME_PATIENT"  => $result['name'],
                "PHONE_PATIENT"  => $result['phone'],
                "DATA_EVENT"  => $result['date'],
                "TIME_EVENT"  => $result['time'],
                "NAME_DOCTOR"  => $doctor['surname'].' '.$doctor['name'].' '.$doctor['patronymic'],
                "SPECIALIZATION"  => $specialization['name'],
            );

            Event::SendImmediate([
                "EVENT_NAME" => "NEW_EVENT",
                "LID" => "s1",
                "C_FIELDS" => $arEventFields,
            ]);

            return  $result;
        }

        return [];
    }

    /**
     * @param $dateTimeHash
     * @return array
     */
    public function getByHash($dateTimeHash)
    {
        return $this->orderRepositiry->getByHash($dateTimeHash);
    }

    /**
     * @param $date
     * @param $time
     * @param null $personId
     * @return bool
     */
    public function dateIsBusy($date, $time, $personId = null)
    {

        if ($this->orderRepositiry->getByDateAndTime($date, $time, $personId)) {
            return true;
        }

        return false;
    }

    /**
     * @param $postData
     * @return array
     */
    public function getPreparedDataFromPost($postData)
    {
        return [
            'person_id' => $postData['person_id'],
            'date' => $postData['date'],
            'time' => $postData['time'],
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public function showList($params)
    {
        $result = $this->orderRepositiry->showList($params);

        foreach ($result as $key => $item){
            $personalIds = $this->personalsRepository->get1CIdsByBItrixIds($item['personal_id']);
            $result[$key]['personal_id'] = array_shift($personalIds);
        }

        //print_r($result);
     //   $this->personalsRepository->get1CIdsByBItrixIds();


        return $result;

    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public function create($data)
    {
        $personalsIds  = $this->personalsRepository->getIdsBy1cIds($data['personal_id']);

        $data['personals'] = array_shift($personalsIds);

        return $this->orderRepositiry->create($data);
    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public function update($data)
    {
        return $this->orderRepositiry->update($data);
    }

    /**
     * @param $data
     * @return array
     */
    public function delete($data)
    {
        return $this->orderRepositiry->delete($data);
    }

}
