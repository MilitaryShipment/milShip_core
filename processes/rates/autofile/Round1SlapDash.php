<?php

require_once __DIR__ . '/../../../models/rates/RateFactory.php';

class Round1SlapDash{

  const PEAKTMP = "/tmp/peakLanes.csv";
  const NONPEAKTMP = "/tmp/nonPeakLanes.csv";

  protected $year;
  protected $round;
  protected $lanes = array();

  public function __construct($year,$scacs,$peak = true){
    $this->scacs = $scacs;
    $this->year = $year - 1;
    $this->peak = $peak;
    $this->lanes = $this->_readFromCsv($this->peak);
    die(print_r($this->lanes));
    $this->_autoFile();
  }
  protected function _readFromCsv($peak = true){
    $data = array();
    $file = $peak ? self::PEAKTMP : self::NONPEAKTMP;
    $csv = array_map('str_getcsv', file($file));
    foreach($csv as $row){
      $data[$row[0]] = $row[1];
    }
    return $data;
  }
  protected function _autoFile(){
    foreach($this->lanes as $laneLabel => $nothing){
      $lh_bkar = Lane::findBkar($laneLabel,$this->year,$this->round,true,$this->peak);
      $sit_bkar = Lane::findBkar($laneLabel,$this->year,$this->round,false,$this->peak);
      foreach($this->scacs as $scacLabel => $increment){
        $lane = Lane::getLane($laneLabel,$scacLabel,$this->year,$this->round,$this->peak);
        $lane->lh_adj = $lh_bkar + $increment;
        $lane->sit_adj = $sit_bkar + $increment;
        echo $scacLabel . " -> LH:" . $lane->lh_adj . "\n";
        echo "\tSIT: " . $lane->sit_adj . "\n";
        // $lane->update(array("lh_adj"=>$lane->lh_adj,"sit_adj"=>$lane->sit_adj),$this->peak);
      }
    }
    return $this;
  }
}
