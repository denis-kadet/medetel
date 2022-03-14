<?php

namespace App\Repositories;

use Bitrix\Main\DB\Exception;
use CIBlockElement;
use CModule;

class AbstractRepository
{
    public $iblockId;
    public $startOrderNumber;

    public function __construct()
    {
        CModule::IncludeModule('iblock');
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


    public function getIdsBy1cIds($ids)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => $this->iblockId,
            'PROPERTY_id' => $ids,
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
            $out[] = $fields['ID'] ;
        }

        return $out;
    }



    public function get1CIdsByBItrixIds($ids)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => $this->iblockId,
            'ID' => $ids,
        ];

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            []
        );

        while ($item = $res->GetNextElement()) {
            $properties = $item->GetProperties();
            $out[] = $properties['id']['VALUE'] ;
        }

        return $out;
    }


    public function getLastId()
    {
        $arFilter = [
            'IBLOCK_ID' => $this->iblockId,
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

        return $this->startOrderNumber;
    }

}
