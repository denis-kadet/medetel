<?php

namespace App\Services;

use App\Repositories\PersonalsRepository;
use App\Repositories\SpecializationsRepository;

class PersonalsService
{
    /**
     * @var PersonalsRepository
     */
    private $personalsRepository;
    /**
     * @var SpecializationsService
     */
    private $specializationsService;
    /**
     * @var SpecializationsRepository
     */
    private $specializationsRepository;

    public function __construct()
    {
        $this->personalsRepository = new PersonalsRepository();
        $this->specializationsService = new SpecializationsService();
        $this->specializationsRepository = new SpecializationsRepository();
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
        $data['specializations'] = $this->specializationsRepository->getIdsBy1cIds($data['specializations']);

        return $this->personalsRepository->create($data);
    }

    public function update($data)
    {
        $data['specializations'] = $this->specializationsRepository->getIdsBy1cIds($data['specializations']);

        $result = $this->personalsRepository->update($data);

        $result['specialization_1c_ids']
            = $this->specializationsRepository->get1CIdsByBItrixIds($result['specializations']);

        return $result;
    }


    public function delete($data)
    {
        return $this->personalsRepository->delete($data);
    }
}
