<?php

require_once __DIR__ . '/../../models/rates/DpsBooking.php';


class BookingImport{

  const INPUTDIR = 'data/input/bookings/';
  const FILEPATT = "/([0-9]{4})\sDPS\sBOOKINGS\sBY\sLOAD\sDATE/";
  protected $inputFiles = array();

  public function __construct(){
    $this->_parseFileNames();
    print_r($this->inputFiles);
  }

  protected function _parseFileNames(){
    if(!is_dir(self::INPUTDIR)){
      thorw new \Exception('Input does not exist');
    }
    $results = scandir(self::INPUTDIR);
    foreach($results as $result){
      if(preg_match(self::FILEPATT,$result,$matches)){
        $this->inputFiles[] = array("year"=>$matches[1],"file"=>$result);
      }
    }
    return $this;
  }
}
