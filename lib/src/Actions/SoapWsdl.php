<?php


namespace App\Actions;


use App\Services\WsdlService;
use Illuminate\Http\Response;
use Klein\Request;

class SoapWsdl
{

    private $wsdlService;

    public function __construct()
    {
        $this->wsdlService = new WsdlService();
    }

    public function index(Request $request)
    {
        $response = new Response();
        $response->setContent($this->wsdlService->resolve($request->headers(), $request->body()));
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->sendHeaders();

        return $response->getContent();
    }

    public function chema(Request $request)
    {
        $response = new Response();
        $response->setContent($this->wsdlService->getChema());
//        $response->setContent(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/lib/mock/wsdl_chema.xml'));
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->sendHeaders();

        return $response->getContent();
    }
}
