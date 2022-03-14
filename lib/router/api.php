<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../env.php';

use App\Actions\Orders;
use App\Actions\Schedule;
use App\Actions\SoapWsdl;
use Klein\Request;
use App\Actions\Specializations;
use Klein\Klein;
use App\Actions\Personals;


$klein = new Klein();

$klein->respond('GET', '/api/personals', function () {
    return (new Personals())->index();
});

$klein->respond('GET', '/personals/[i:id]/day_schedules/[*:date_hash]', function () {
    return (new Personals())->personalsItem(new Request);
});

$klein->respond('GET', '/api/specializations', function () {
    return (new Specializations())->index();
});

$klein->respond('GET', '/api/day-schedule/[i:personals_id]/[*:date]', function ($request) {
    return (new Schedule())->getBypersonForDate($request);
});

$klein->respond('GET', '/api/day-schedule-disabled/[i:personals_id]/[*:date]', function ($request) {
    return (new Schedule())->getDisabledDaysByPersonForDate($request);
});

$klein->respond('POST', '/api/order-create', function (Request $request) {
    return (new Orders())->createOrder($request);
});

$klein->respond('POST', '/api/medetel_soap/action', function (Request $request) {
    return (new SoapWsdl())->index($request);
});

$klein->respond('GET', '/api/medetel_soap/action', function (Request $request) {
    return (new SoapWsdl())->index($request);
});

$klein->respond('GET', '/api/medetel_soap/wsdl', function (Request $request) {
    return (new SoapWsdl())->chema($request);
});

$klein->dispatch();
