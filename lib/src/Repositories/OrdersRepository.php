<?php

namespace App\Repositories;

use Bitrix\Main\DB\Exception;
use CIBlockElement;
use CFile;

class OrdersRepository extends AbstractRepository
{
    private const START_ORDER_NUMBER = 31000;

    public function __construct()
    {
        parent::__construct();
        $this->iblockId = ORDERS_IBLOCK_ID;
        $this->personalsRepository = new PersonalsRepository();
    }

    public function getLastId()
    {
        $arFilter = [
            'IBLOCK_ID' => ORDERS_IBLOCK_ID,
        ];

        $res = CIBlockElement::GetList(
            ['PROPERTY_id' => 'DESC'],
            $arFilter,
            false,
            ['nPageSize' => 1]
        );

        if ($item = $res->GetNextElement()) {

            $properties = $item->getProperties();

            return $properties['id']['VALUE'] + 1;

        }

        return $this::START_ORDER_NUMBER;
    }

    /**
     * @param $params
     * @param null $files
     * @return array
     * @throws Exception
     */
    public function create($params, $files = null)
    {
        $el = new CIBlockElement;
        $attachment = false;
        global $currentClinic;

        if (isset($files['attachment'])) {

            $attachment = CFile::SaveFile($files['attachment'], "attachment");
        }

        $dateTimeHash = md5(sprintf('%s %s %s', $params['date'], $params['time'], microtime()));


        $properties = [
            'personals' => $params['personals'] ?? $params['person_id'] ?? null,
            'receipt_date' => $params['date'],
            'date_time_hash' => $dateTimeHash,
            'name' => $params['name'],
            'email' => $params['email'],
            'comment' => $params['comment'],
            'time' => $params['time'],
            'phone' => $params['phone'],
            'attachment' => $attachment,
            'admin' => $currentClinic['admin'],
            'id' => $this->getLastId(),
        ];

        if($params['in_1c']){
            $properties['IN_1C'] = true;
        }

        $arLoadProductArray = [
            "MODIFIED_BY" => 1,
            "CREATED_BY" => 1,
            "IBLOCK_ID" => ORDERS_IBLOCK_ID,
            "PROPERTY_VALUES" => $properties,
            "NAME" => $dateTimeHash,
            "ACTIVE" => "Y",
        ];

        if ($id = $el->Add($arLoadProductArray)) {
            return $this->getById($id);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }


    /**
     * @param $date
     * @param $time
     * @return array|null
     */
    public function getByDateAndTime($date, $time, $personId = null)
    {
        $out = null;

        $arFilter = [
            'IBLOCK_ID' => ORDERS_IBLOCK_ID,
            'PROPERTY_receipt_date' => ConvertDateTime($date, "YYYY-MM-DD"),
            'PROPERTY_time' => $time,
            'ACTIVE' => 'Y',
        ];

        if($personId){
            $arFilter['PROPERTY_personals'] = $personId;
        }

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
                'time' => $properties['time']['VALUE'],
                'date' => $properties['receipt_date']['VALUE'],
                'name' => $properties['name']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
                'email' => $properties['email']['VALUE'],
                'personal_id' => $properties['personals']['VALUE'],
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

        global $currentClinic;

        $arFilter = [
            'IBLOCK_ID' => ORDERS_IBLOCK_ID,
            'PROPERTY_admin' => $currentClinic['admin'],
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

            $personalIds = $this->personalsRepository->getIdsBy1cIds($properties['personals']['VALUE']);

            $out = [
                'id' => $fields['ID'],
                'external_id' => $properties['id']['VALUE'],
                'time' => $properties['time']['VALUE'],
                'date' => $properties['receipt_date']['VALUE'],
                'name' => $properties['name']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
                'email' => $properties['email']['VALUE'],
                'patient' => $properties['patient']['VALUE'],
                'personal_id' => $properties['personals']['VALUE'],
                'personal_external_id' => array_shift($personalIds),
                'date_create' => $fields['DATE_CREATE'],
                'date_create_unix' => $fields['DATE_CREATE_UNIX'],
                'date_time_hash' => $properties['date_time_hash']['VALUE'],
            ];
        }

        return $out;
    }

    /**
     * @param $dateTimeHash
     * @return array
     */
    public function getByHash($dateTimeHash)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => ORDERS_IBLOCK_ID,
            'PROPERTY_date_time_hash' => $dateTimeHash,
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
                'time' => $properties['time']['VALUE'],
                'date' => $properties['receipt_date']['VALUE'],
                'name' => $properties['name']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
                'email' => $properties['email']['VALUE'],
                'personal_id' => $properties['personals']['VALUE'],
            ];
        }

        return $out;
    }

    /**
     * @param $personalId
     * @param $date
     * @return array
     */
    public function getForPersonByDate($personalId, $date)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => ORDERS_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'PROPERTY_personals' => $personalId,
            'PROPERTY_receipt_date' => ConvertDateTime($date, "YYYY-MM-DD"),
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
                'time' => $properties['time']['VALUE'],
                'date' => $properties['receipt_date']['VALUE'],
                'personal_id' => $properties['personals']['VALUE'],
            ];
        }

        return $out;
    }

    /**
     * @param $params
     * @return array
     */
    public function showList($params)
    {
        $out = [];

        list($dateCreated, $time) = explode('T', $params['date_created']);

        $arFilter = [
            'IBLOCK_ID' => ORDERS_IBLOCK_ID,
            'ACTIVE' => 'Y',
            '!PROPERTY_IN_1C' => '1'
            // 'DATE_CREATE' => $dateCreated,
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

            $timeExploded = explode(':', $properties['time']['VALUE']);
            $time = $properties['time']['VALUE'];

            if (strlen($timeExploded[0]) == 1) {
                $time = '0'.$time;
            }
            CIBlockElement::SetPropertyValues($fields['ID'], ORDERS_IBLOCK_ID, 1, 'IN_1C');
            $out[] = [
                'id' => $fields['ID'],
                'time' => $time,
                'date' => $properties['receipt_date']['VALUE'],
                'name' => $properties['name']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
                'email' => $properties['email']['VALUE'],
                'external_id' => $properties['id']['VALUE'],
                'personal_id' => $properties['personals']['VALUE'],
                'created_at' => $fields['DATE_CREATE'],
            ];
        }

        return $out;
    }


    /**
     * @param $params
     * @return array
     * @throws Exception
     */
    public function update($params)
    {
        global $currentClinic;

        $el = new CIBlockElement;
        $dateTimeHash = md5(sprintf('%s %s', $params['date'], $params['time']));
        $properties = [
            'personals' => $params['person_id'],
            'receipt_date' => $params['date'],
            'date_time_hash' => $dateTimeHash,
            'name' => $params['name'],
            'email' => $params['email'],
            'comment' => $params['comment'],
            'time' => $params['time'],
            'phone' => $params['phone'],
            'admin' => $currentClinic['admin'],
        ];

        $arLoadProductArray = [
            "PROPERTY_VALUES" => $properties,
            "NAME" => $dateTimeHash,
        ];

        $item = $this->getById($params['id']);

        if (empty($item)) {
            $item = $this->create($params);
        }

        if ($el->Update($item['id'], $arLoadProductArray)) {
            return $this->getById($params['id']);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }

    /**
     * @param $data
     * @return array
     */
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
