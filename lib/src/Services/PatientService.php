<?php

namespace App\Services;

use App\Repositories\PersonalsRepository;
use App\Repositories\SpecializationsRepository;

class PatientService
{
    /**
     * @var PersonalsRepository
     */
    private $personalsRepository;
    /**
     * @var SpecializationsService
     */
    private $specializationsService;

    public function __construct()
    {
        $this->personalsRepository = new PersonalsRepository();
        $this->specializationsService = new SpecializationsService();
    }

    public function getPersonalsList()
    {
        $out = [];
        $specializationsMapped = $this->specializationsService->getSpecializationsMapped();

        foreach ($this->personalsRepository->getAllPersonals($_REQUEST['filter']) as $key => $item) {
            $specializationsNames = [];
            $out[$key] = $item;
            foreach ($item['specializations'] as $specializationId){
                $specializationsNames[] = $specializationsMapped[$specializationId];
            }
            $out[$key]['specialization_string'] = join(', ', $specializationsNames);
        }

        return $out;
    }

    public function getPersonalsItem($personId)
    {
        $out = $this->personalsRepository->getPersonalById($personId);
        $specializationsMapped = $this->specializationsService->getSpecializationsMapped();

        foreach ($out['specializations'] as $specializationId){
            $specializationsNames[] = $specializationsMapped[$specializationId];
        }

        $out['specialization_string'] = join(', ', $specializationsNames);

        return $out;
    }

    public function create($data)
    {
        return $this->personalsRepository->create($data);
    }

    public function update($data)
    {
        return $this->personalsRepository->update($data);
    }


    public function delete($data)
    {
        return $this->personalsRepository->delete($data);
    }
}
