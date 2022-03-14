<?php

namespace App\Repositories;

use Bitrix\Main\DB\Exception;
use CIBlockElement;

class SpecializationsRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct();
        $this->iblockId = SPECIALIZATIONS_IBLOCK_ID;
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
            'IBLOCK_ID' => SPECIALIZATIONS_IBLOCK_ID,
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
            $out[] = [
                'id' => $fields['ID'],
                'name' => $fields['NAME'],
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
    public function getById($id)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => SPECIALIZATIONS_IBLOCK_ID,
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
                'external_id' => $properties['id']['VALUE'],
                'name' => $fields['NAME'],
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
            "IBLOCK_ID" => SPECIALIZATIONS_IBLOCK_ID,
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
        $el = new CIBlockElement;

        $arLoadProductArray = array(
            "NAME" => $data['name'],
        );

        $item = $this->getById($data['id']);

        if (empty($item)) {
            $item = $this->create($data);
        }

        if($el->Update($item['id'], $arLoadProductArray)){
            return $this->getById($data['id']);
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

        if ($el->Update($data['id'], $arLoadProductArray)) {
            return $this->getById($data['id']);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }
}
