<?php

namespace App\Repositories;

use CFile;
use CIBlockElement;

class ClinicRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct();
        $this->iblockId = CLINIC_IBLOCK_ID;
    }


    public function getById($id)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID' => CLINIC_IBLOCK_ID,
            'ID'        => $id,
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
                'id'      => $fields['ID'],
                'admin'   => $properties['admin']['VALUE'],
                'name'    => $fields['NAME'],
                'email'   => $properties['email']['VALUE'],
                'phone'   => $properties['phone']['VALUE'],
                'address' => $properties['address']['VALUE'],
                'color'   => $properties['color']['VALUE'],
                'site'    => $properties['site']['VALUE'],
                'logo'    => !empty($properties['logo']['VALUE']) ? CFile::GetPath(
                    $properties['logo']['VALUE']
                ) : false,
            ];
        }

        return $out;
    }

    public function getByDomain($domain)
    {
        $out = [];

        $arFilter = [
            'IBLOCK_ID'       => CLINIC_IBLOCK_ID,
            'PROPERTY_domain' => $domain,
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
                'id'      => $fields['ID'],
                'admin'   => $properties['admin']['VALUE'],
                'name'    => $fields['NAME'],
                'email'   => $properties['email']['VALUE'],
                'phone'   => $properties['phone']['VALUE'],
                'address' => $properties['address']['VALUE'],
                'color'   => $properties['color']['VALUE'],
                'site'    => $properties['site']['VALUE'],
                'logo'    => !empty($properties['logo']['VALUE']) ? CFile::GetPath(
                    $properties['logo']['VALUE']
                ) : false,
            ];
        }

        return $out;
    }
}
