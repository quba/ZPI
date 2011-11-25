<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
 * Zakładam, że to będzie kontroler skonfigurowany jako serwis po to, by móc tutaj wrzucać jakieś globalne funkcje.
 * Jestem pewien, że można to zrobić bardziej elegancko.
 * Jakby nie użytwać go jako serwis, to zapewne wywołanie go wymagałoby napisania większej ilości kodu (use + wywołanie).
 */


class OverallController extends Controller
{
    // na razie zostawiam, może komuś się kiedyś przyda, żeby wrzucić tu jakąś globalną funkcję
    public function csvDownload($filename, $data)
    {
        $response =  new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set("Content-disposition", "attachment; filename=$filename-" . date("Y-m-d").".csv");
        $response->headers->set("Pragma", "no-cache");
        $response->send();
        ob_clean();
        flush();

        //$this->prepareCSV($data, ";");
        return $this->prepareCSV($data, ',');
        //echo $this->prepareCSV($data, ";");
    }
    
    private function prepareCSV($aData, $sSeparator)  
    {  
        $aCSV = array();  
        foreach($aData as $aRow) {  
            $aCSV[] = implode($sSeparator, $aRow);  
        }  
        
        return implode("\n", $aCSV);  
    } 
}
