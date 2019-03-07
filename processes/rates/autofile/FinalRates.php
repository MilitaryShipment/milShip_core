<?php

require_once __DIR__ . '/../../../models/rates/RateFactory.php';

class FinalRates{

  const PEAKSIT = 58;
  const PEAKLH = 51;
  const NONPEAKSIT = 68;
  const NONPEAKLH = 58;

  protected $year;
  protected $scacs = array();
  protected $peak;

  public function __construct($scacs,$peak = true){
    $this->year = date('Y');
    $this->round = 2;
    $this->scacs = $scacs;
    $this->peak = $peak;
  }
  protected function _autoFile(){
    foreach($this->scacs as $scac){
      $scac = RateFactory::buildScac($scac,$this->round,$this->year);
      if($this->peak){
        foreach($scac->peakLanes as $lane){
          $lane->lh_adj = self::PEAKLH;
          $lane->sit_adj = self::PEAKSIT;
          $update = array("lh_adj"=>$lane->lh_adj,"sit_adj"=>$lane->sit_adj);
          $lane->update($update,$this->peak);
        }
      }else{
        foreach($scac->nonPeakLanes as $lane){
          $lane->lh_adj = self::NONPEAKLH;
          $lane->sit_adj = self::NONPEAKSIT;
          $update = array("lh_adj"=>$lane->lh_adj,"sit_adj"=>$lane->sit_adj);
          $lane->update($update,$this->peak);
        }
      }
    }
    return $this;
  }
}
