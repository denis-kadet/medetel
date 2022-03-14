<?php

namespace App\Services;

use App\Repositories\SpecializationsRepository;
use App\Repositories\PersonalsRepository;

class SpecializationsService
{
    /**
     * @var MockService
     */
    private $dataProvider;

    public function __construct()
    {
        $this->specializationRepository = new SpecializationsRepository();
        $this->personalRepository = new PersonalsRepository();

    }

    public function getActiveSpecializationList()
    {
        $out = [];
        $activeSpecializations = [];
        $personals = $this->personalRepository->getAllPersonals();
        foreach ($personals as $item){
            foreach ($item['specializations'] as $specialization){
                $activeSpecializations[] = $specialization;
            }
        }
 
        $activeSpecializations = array_unique($activeSpecializations);

        $specializations =  $this->specializationRepository->getAll();

        foreach ($specializations as $specialization){
            if(in_array($specialization['id'],$activeSpecializations )){
                $out[] = $specialization;
            }
        }

        usort($out, function ($a, $b)
        {
            return strcmp($a["name"], $b["name"]);
        });

        return $out;

    }

    public function getSpecializationList()
    {
        return $this->specializationRepository->getAll();
    }

    public function create($data)
    {
        return $this->specializationRepository->create($data);
    }


    public function update($data)
    {
        return $this->specializationRepository->update($data);
    }

    public function delete($data)
    {
        return $this->specializationRepository->delete($data);
    }

    public function getSpecializationsMapped()
    {
        $out = [];

        foreach ($this->specializationRepository->getAll() as $item) {
            $out[$item['id']] = $item['name'];
        }

        return $out;
    }
}
