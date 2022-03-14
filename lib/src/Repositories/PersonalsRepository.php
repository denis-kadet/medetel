<?php

namespace App\Repositories;

use Bitrix\Main\DB\Exception;
use CFile;
use CIBlockElement;

class PersonalsRepository extends AbstractRepository
{
    public $startOrderNumber = 31000;

    public function __construct()
    {
        parent::__construct();
        $this->iblockId = PERSONALS_IBLOCK_ID;
    }

    /**
     * @param null $filter
     * @return array
     */
    public function getAllPersonals($filter = null)
    {
        $scheduleRepository = new SchedulesRepository();
        global $currentClinic;

        $out = [];
        $arFilter = [
            'IBLOCK_ID' => PERSONALS_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'PROPERTY_admin' => $currentClinic['admin'],
        ];

        if ($filter) {
            $arFilter = array_merge($filter, $arFilter);
        }

        $res = CIBlockElement::GetList([], $arFilter, false, false, []);
        while ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();
            $out[] = [
                'id' => $fields['ID'],
                'name' => $properties['name']['VALUE'],
                'surname' => $properties['surname']['VALUE'],
                'patronymic' => $properties['patronymic']['VALUE'],
                'education' => $properties['education']['VALUE'],
                'specializations' => $properties['specializations']['VALUE'],
                'job' => $properties['job']['VALUE'],
                'thumb_url' => $fields['PREVIEW_PICTURE'] ? CFile::GetPath(
                    $fields['PREVIEW_PICTURE']
                ) : '/images/default_personal_small.jpg',
                'big_url' => $fields['DETAIL_PICTURE'] ? CFile::GetPath(
                    $fields['DETAIL_PICTURE']
                ) : '/images/default_personal_big.jpg',
                'about' => $fields['DETAIL_TEXT'],
                'schedules' => $scheduleRepository->getByPersonalId($fields['ID']),
            ];
        }

        return $out;
    }

    /**
     * @param $personalId
     * @return array
     */
    public function getPersonalById($personalId, $active = 'Y')
    {
        $scheduleRepository = new SchedulesRepository();

        $out = [];
        $arFilter = [
            'IBLOCK_ID' => PERSONALS_IBLOCK_ID,
            //'ACTIVE' => $active,
            [
                'LOGIC' => 'OR',
                'ID' => $personalId,
                'PROPERTY_id' => $personalId,
            ],
        ];

        $res = CIBlockElement::GetList([], $arFilter, false, false, []);
        if ($item = $res->GetNextElement()) {
            $fields = $item->GetFields();
            $properties = $item->GetProperties();

            $out = [
                'id' => $fields['ID'],
                'external_id' => $properties['id']['VALUE'],
                'name' => $properties['name']['VALUE'],
                'surname' => $properties['surname']['VALUE'],
                'patronymic' => $properties['patronymic']['VALUE'],
                'education' => $properties['education']['VALUE'],
                'specializations' => $properties['specializations']['VALUE'],
                'job' => $properties['job']['VALUE'],
                'thumb_url' => $fields['PREVIEW_PICTURE'] ? CFile::GetPath($fields['PREVIEW_PICTURE']) : '',
                'big_url' => $fields['DETAIL_PICTURE'] ? CFile::GetPath($fields['DETAIL_PICTURE']) : '',
                'about' => $fields['DETAIL_TEXT'],
                'schedules' => $scheduleRepository->getByPersonalId($fields['ID']),
            ];
        }

        return $out;
    }

    /**
     * @param $params
     * @return array
     * @throws Exception
     */
    public function create($params)
    {
        $el = new CIBlockElement;
        global $currentClinic;

        if(!$params['id']){
            $params['id'] = $this->getLastId();
        }

        $properties = [
            'specializations' => $params['specializations'],
            'name' => $params['name'],
            'email' => $params['email'],
            'patronymic' => $params['patronymic'],
            'surname' => $params['surname'],
            'id' => $params['id'],
            'admin' => $currentClinic['admin'],
        ];

        $arLoadProductArray = [
            "MODIFIED_BY" => 1,
            "CREATED_BY" => 1,
            "IBLOCK_ID" => PERSONALS_IBLOCK_ID,
            "PROPERTY_VALUES" => $properties,
            "NAME" => sprintf('%s %s %s', $params['surname'], $params['name'], $params['patronymic']),
            "ACTIVE" => "Y",
        ];

        if ($id = $el->Add($arLoadProductArray)) {

            return $this->getPersonalById($id);
        } else {
            throw new Exception($el->LAST_ERROR);
        }
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

        $properties = $params;

        $properties['admin'] = $currentClinic['admin'];

        $arLoadProductArray = [
            "MODIFIED_BY" => 1,
            "CREATED_BY" => 1,
            "IBLOCK_ID" => PERSONALS_IBLOCK_ID,
            "PROPERTY_VALUES" => $properties,
            "NAME" => sprintf('%s %s %s', $params['surname'], $params['name'], $params['patronymic']),
            "ACTIVE" => $params['active'] ? 'Y' : 'N',
        ];

        $personal = $this->getPersonalById($params['id']);

        if (empty($personal)) {
            $personal = $this->create($params);
        }

        if ($el->Update($personal['id'], $arLoadProductArray)) {
            return $this->getPersonalById($params['id']);
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
            return $this->getPersonalById($ids[0], 'N');
        } else {
            throw new Exception($el->LAST_ERROR);
        }
    }

}
