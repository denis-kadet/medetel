<?php

namespace App\Repositories;

use Bitrix\Main\DB\Exception;
use CIBlockElement;

class SchedulesRepository extends AbstractRepository
{
    public $startOrderNumber = 31000;

    public function __construct()
    {
        parent::__construct();
        $this->iblockId = SCHEDULES_IBLOCK_ID;
    }

    /**
     * @param $personalId
     *
     * @return array
     */
    public function getByPersonalId($personalId)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => SCHEDULES_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'PROPERTY_personals' => $personalId,
        ];

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            []
        );

        while ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();

            $out[] = [
                'id' => $fields['ID'],
                'schedule' => json_decode($properties['schedules']['~VALUE'], true),
                'date_start' => $properties['date_start']['VALUE'],
                'date_end' => $properties['date_end']['VALUE'],
                'client_time' => $properties['client_time']['VALUE'],
            ];
        }

        return $out;
    }


    /**
     * @param $personalId
     * @param $date
     *
     * @return array
     */
    public function getByPersonAndDate2($personalId, $rangeDateStart, $rangeDateTo)
    {
        $arFilter = [
            'IBLOCK_ID' => SCHEDULES_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'PROPERTY_personals' => $personalId,
            '>=PROPERTY_date_start' => $rangeDateStart->format('Y-m-d'),
            //'>=PROPERTY_date_end' => $rangeDateTo->format('d.m.Y'),
        ];

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            []
        );
        $out = [];
        while ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();
            $out[] = [
                'id' => $fields['ID'],
                'external_id' => $properties['id']['VALUE'],
                'schedule' => json_decode($properties['schedules']['~VALUE'], true),
                'date_start' => $properties['date_start']['VALUE'],
                'date_end' => $properties['date_end']['VALUE'],
                'client_time' => $properties['client_time']['VALUE'],
            ];
        }

        if($out['date_start']){

            $dateStart = \DateTime::createFromFormat('d.m.Y', $out['date_start']);

            /** @var \DateTime $rangeDateTo */
            if($rangeDateStart->format('m') != $dateStart->format('m')){

                $out = null;
            }
        }


        return $out;
    }


    /**
     * @param $personalId
     * @param $date
     *
     * @return array
     */
    public function getByPersonAndDate($personalId, $date)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => SCHEDULES_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'PROPERTY_personals' => $personalId,
            '<=PROPERTY_date_start' => ConvertDateTime($date, "YYYY-MM-DD"),
            '>=PROPERTY_date_end' => ConvertDateTime($date, "YYYY-MM-DD"),
        ];

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            []
        );

        if ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();
            $out = [
                'id' => $fields['ID'],
                'external_id' => $properties['id']['VALUE'],
                'schedule' => json_decode($properties['schedules']['~VALUE'], true),
                'date_start' => $properties['date_start']['VALUE'],
                'date_end' => $properties['date_end']['VALUE'],
                'client_time' => $properties['client_time']['VALUE'],
            ];
        }

        return $out;
    }


    /**
     * @param $id
     * @return array
     */
    public function getById($id)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => SCHEDULES_IBLOCK_ID,
            [
                'LOGIC' => 'OR',
                'ID' => $id,
                'PROPERTY_id' => $id,
            ],
        ];

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            []
        );

        if ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();
            $out = [
                'id' => $fields['ID'],
                'schedule' => json_decode($properties['schedules']['~VALUE'], true),
                'date_start' => $properties['date_start']['VALUE'],
                'date_end' => $properties['date_end']['VALUE'],
                'external_id' => $properties['id']['VALUE'],
                'client_time' => $properties['client_time']['VALUE'],
            ];
        }

        return $out;
    }

    public function create($data)
    {
        $el = new CIBlockElement;

        global $currentClinic;

        $properties = $data;

        $properties['admin'] = $currentClinic['admin'];
        $properties['schedules'] = json_encode($data['schedules']);

        if(!$data['id']){
            $properties['id'] = $this->getLastId();
        }

        $arLoadProductArray = [
            "MODIFIED_BY" => 1,
            "CREATED_BY" => 1,
            "IBLOCK_ID" => SCHEDULES_IBLOCK_ID,
            "PROPERTY_VALUES" => $properties,
            "NAME" => 'График приема',
            "ACTIVE" => "Y",
        ];

        if ($id = $el->Add($arLoadProductArray)) {
            return $this->getById($id);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }

    public function update($data)
    {
        global $currentClinic;

        $el = new CIBlockElement;

        $properties = $data;

        $properties['admin'] = $currentClinic['admin'];
        $properties['schedules'] = json_encode($data['schedules']);

        $active = 'Y';

        if(empty($data['schedules'])){
            $active = 'N';
        }

        $arLoadProductArray = [
            "MODIFIED_BY" => 1,
            "CREATED_BY" => 1,
            "IBLOCK_ID" => SCHEDULES_IBLOCK_ID,
            "PROPERTY_VALUES" => $properties,
            "NAME" => 'График приема',
            "ACTIVE" => $active,
        ];

        $item = $this->getById($data['id']);

        if (empty($item)) {
            $item = $this->create($data);
        }

        if($el->Update($item['id'], $arLoadProductArray)){
            return $this->getById($data['id']);
        }else{
            throw new Exception($el->LAST_ERROR);
        }
    }

    public function delete($data)
    {
        $el = new CIBlockElement;

        $arLoadProductArray = [
            "ACTIVE" => "N",
        ];

        $ids = $this->getIdsBy1cIds($data['id']);

        if ($el->Update($ids[0], $arLoadProductArray)) {
            return $this->getById($ids[0]);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }
}
