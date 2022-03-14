<?php

namespace App\Services;

use App\Repositories\ClinicRepository;
use Bitrix\Main\DB\Exception;

class ClinicService{

    public function __construct()
    {
        $this->clinicRepository = new ClinicRepository();
    }

    public function getCurrentClinic()
    {
        $clinic = $this->clinicRepository->getByDomain($_SERVER['HTTP_HOST']);

        if(empty($clinic)){
            throw new Exception('incorrect_clinic_domain');
        }

        return $clinic;
    }
}
