<?php

require_once __DIR__ . '/../../models/rates/RateFactory.php';
require_once __DIR__ . '/../../models/rates/DpsBooking.php';


class BookingImport{

  const INPUTDIR = 'data/input/bookings/';
  const FILEPATT = "/([0-9]{4})\sbooking\sreport/i";
  const LANEPATT = "/(.*)\(/";

  protected $inDirDir;
  protected $inputFiles = array();

  public function __construct(){
    $this->inDir = __DIR__ . "/" . self::INPUTDIR;
    $this->_parseFileNames()->_parseInputFiles();
  }

  protected function _parseFileNames(){
    if(!is_dir($this->inDir)){
      throw new \Exception('Input does not exist');
    }
    $results = scandir($this->inDir);
    foreach($results as $result){
      if(preg_match(self::FILEPATT,$result,$matches)){
        $this->inputFiles[] = array("year"=>$matches[1],"file"=>$result);
      }
    }
    return $this;
  }
  protected function _parseInputFiles(){
    foreach($this->inputFiles as $file){
      $rows = RateFactory::readCsv($this->inDir . $file['file']);
      foreach($rows as $row){
        $newBooking = new DpsBooking();
        $newBooking->scac = $row[0];
        $newBooking->gbl_dps = $row[3];
        $newBooking->reg_number = $row[8];
        $newBooking->rate = $row[9];
        $newBooking->lane = $this->_trimLane($row[11]);
        $newBooking->oa_state = $row[4];
        $newBooking->da_state = $row[5];
        $newBooking->reg_date = $row[6];
        $newBooking->load_date = $row[2];
        $newBooking->load_status = $row[7];
        $newBooking->is_shortFuse = $row[10];
        $newBooking->create();
      }
    }
    return $this;
  }
  protected function _trimLane($lane){
    preg_match(self::LANEPATT,$lane,$matches);
    return trim(strtolower($matches[1]));
  }
}
