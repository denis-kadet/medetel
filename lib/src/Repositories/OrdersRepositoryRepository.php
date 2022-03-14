<?php

namespace App\Repositories;

use CFile;
use CIBlockElement;

class SchedulesRepository extends AbstractRepository
{
    public function getByPersonalId($personalId)
    {
        $out = [];

        $arFilter = ['IBLOCK_ID' => SCHEDULES_IBLOCK_ID, 'ACTIVE' => 'Y', 'PROPERTY_personals' => $personalId];
        $res = CIBlockElement::GetList([], $arFilter, false, false, []);
        while ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();
 
            $out[] = [
                'id'   => $fields['ID'],
                'schedule' => json_decode($properties['schedules']['~VALUE'], true),
                'date_start' => $properties['date_start']['VALUE'],
                'date_end' => $properties['date_end']['VALUE'],
                'client_time' => $properties['client_time']['VALUE'],
            ];
        }

        return $out;
    }
}
