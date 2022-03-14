<?php

namespace App\Services;

class MockService{

    public function personalsJson()
    {
        return file_get_contents(__DIR__.'/../../mock/personals.json');
    }

    public function specializationsJson()
    {
        return file_get_contents(__DIR__.'/../../mock/specializations.json');
    }

    public function personalsItemJson()
    {
        return file_get_contents(__DIR__.'/../../mock/personals_item.json.json');
    }
}
