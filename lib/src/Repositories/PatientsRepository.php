<?php

namespace App\Repositories;

use Bitrix\Main\DB\Exception;
use CIBlockElement;

class PatientsRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct();
        $this->iblockId = PATIENT_IBLOCK_ID;
    }

    /**
     * @param $personalId
     *
     * @return array
     */
    public function getAll()
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => PATIENT_IBLOCK_ID,
            'ACTIVE' => 'Y',
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
                'name' => $fields['NAME'],
                'email' => $properties['email']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
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
            'IBLOCK_ID' => PATIENT_IBLOCK_ID,
            'ID' => $id,
            'PROPERTY_admin' => $currentClinic['admin'],
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
                'name' => $fields['NAME'],
                'external_id' => $properties['id']['VALUE'],
                'email' => $properties['email']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
            ];
        }

        return $out;
    }

    /**
     * @param $id
     * @return array
     */
    public function getByEmail($email)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => PATIENT_IBLOCK_ID,
            'PROPERTY_EMAIL' => $email,
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
                'name' => $fields['NAME'],
                'email' => $properties['email']['VALUE'],
                'phone' => $properties['phone']['VALUE'],
            ];
        }

        return $out;
    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public function create($data)
    {
        global $currentClinic;
        $el = new CIBlockElement;

        $properties = $data;

        $properties['admin'] = $currentClinic['admin'];

        $arLoadProductArray = [
            "IBLOCK_ID" => PATIENT_IBLOCK_ID,
            "NAME" => $data['name'],
            "ACTIVE" => "Y",
            "PROPERTY_VALUES" => $properties,
        ];

        if ($id = $el->Add($arLoadProductArray)) {
            return $this->getById($id);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }

    /**
     * @param $data
     * @return array
     * @throws Exception
     */
    public function update($data)
    {
        global $currentClinic;

        $el = new CIBlockElement;
        $properties = $data;
        $properties['admin'] = $currentClinic['admin'];

        $arLoadProductArray = array(
            "NAME" => $data['name'],
        );

        if($el->Update($data['id'], $arLoadProductArray)){
            return $this->getById($data['id']);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }
}
