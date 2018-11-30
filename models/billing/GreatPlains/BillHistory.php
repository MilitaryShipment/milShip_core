<?php namespace GP;

class BillHistory{

    const GBLPATT = '/[A-Z]{4}[0-9]{7}/';
    const CODEPATT = '/[0-9]{2,3}[A-Z]{1}/';
    const DIR = '/Hybrid/gpshare/Movestar/Completed/';
    const HEAD = 'f0411tabV2';
    const DETAIL = 'f4911tabV2';

    public function __construct(){
        $results = scandir(self::DIR);
        print_r($results);
    }
}
