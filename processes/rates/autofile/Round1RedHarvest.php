<?php

require_once __DIR__ . '/../../../models/rates/RateFactory.php';

class Round1RedHarvest{

  const PEAKTMP = "/tmp/peakLanes.csv";
  const NONPEAKTMP = "/tmp/nonPeakLanes.csv";

  protected $allScacs = array();
  protected $redScacs = array();
  protected $harvestScacs = array();
  protected $peakLanes = array();
  protected $nonPeaksLanes = array();
  protected $sit_increments = array(
    -1.50,
    -1.75,
    -2,
    -2.25,
    -2.50,
    -2.75,
    -3,
    -3.25,
    -3.50,
    -3.75,
    -4,
    -4.25,
    -4.50,
    -4.75,
    -5
  );

  public function __construct($year,$redScacs,$harvestScacs){
    $this->year = $year - 1;
    $this->round = 2;
    $this->redScacs = $redScacs;
    $this->harvestScacs = $harvestScacs;
    $this->allScacs = array_merge($this->redScacs,$this->harvestScacs);
    if(!$this->_verifyLaneIncrements()){
      $this->_buildLaneIncrements();
    }
    $this->_autoFile();
  }
  protected function _buildLaneIncrements(){
    foreach($this->redScacs as $scacLabel){
      $scac = RateFactory::buildScac($scacLabel,$this->round,$this->year);
      foreach($scac->peakLanes as $lane){
        $lh_ehp = $lane->getEhpRange();
        $lh_increment = $lh_ehp / (count($this->harvestScacs) + count($this->redScacs));
        if(!isset($this->peakLanes[$lane->lane])){
          $this->peakLanes[$lane->lane] = $lh_increment;
        }elseif($this->peakLanes[$lane->lane] != $lh_increment){
          $this->_doError($scacLabel,$lane->lane,$lh_increment,$this->peaksLanes[$lane->lane]);
        }
      }
      foreach($scac->nonPeakLanes as $lane){
        $lh_ehp = $lane->getEhpRange(false,true);
        $lh_increment = $lh_ehp / (count($this->harvestScacs) + count($this->redScacs));
        if(!isset($this->nonPeakLanes[$lane->lane])){
          $this->nonPeaksLanes[$lane->lane] = $lh_increment;
        }elseif($this->nonPeaksLanes[$lane->lane] != $lh_increment){
          $this->_doError($scacLabel,$lane->lane,$lh_increment,$this->nonPeakLanes[$lane->lane]);
        }
      }
    }
    $this->_saveToCsv($this->peakLanes);
    $this->_saveToCsv($this->nonPeakLanes,false);
    return $this;
  }
  protected function _autoFile(){
    foreach($this->peakLanes as $laneLabel => $variance){
      $increment = Lane::findBkar($laneLabel,$this->year,$this->round,true,true);
      echo $laneLabel . "\n";
      echo "BKAR: " . $increment . "\n";
      $i = 0;
      foreach($this->allScacs as $scacLabel){
        $lane = Lane::getLane($laneLabel,$scacLabel,$this->year,$this->round,true);
        $rejection = $lane->getHighestRejection(true,true);
        $increment -= $this->peakLanes[$lane->lane];
        $lane->lh_adj = $increment;
        $lane->sit_adj = $lane->sit_bkar + $this->sit_increments[$i++];
        echo $scacLabel . " -> " . $lane->lh_adj . " | (" . $rejection . ")\n";
        echo "\t" . $lane->sit_adj . "\n";
      }
    }
    foreach($this->nonPeakLanes as $laneLabel => $variance){
      $increment = Lane::findBkar($laneLabel,$this->year,$this->round,true,false);
      echo $laneLabel . "\n";
      echo "BKAR: " . $increment . "\n";
      $i = 0;
      foreach($this->allScacs as $scacLabel){
        $lane = Lane::getLane($laneLabel,$scacLabel,$this->year,$this->round,false);
        $rejection = $lane->getHighestRejection(false,true);
        $increment -= $this->nonPeaksLanes[$lane->lane];
        $lane->lh_adj = $increment;
        $lane->sit_adj = $lane->sit_bkar + $this->sit_increments[$i++];
        echo $scacLabel . " -> " . $lane->lh_adj . " | (" . $rejection . ")\n";
        echo "\t\t" . $lane->sit_adj . "\n";
      }
    }
    return $this;
  }
  protected function _verifyLaneIncrements(){
    if(!is_file(self::PEAKTMP) || !is_file(self::NONPEAKTMP)){
      return false;
    }
    $this->peakLanes = $this->_readFromCsv();
    $this->nonPeakLanes = $this->_readFromCsv(false);
    if(!count($this->peakLanes) || !count($this->nonPeakLanes)){
      return false;
    }
    return true;
  }
  protected function _saveToCsv($data,$peak = true){
    $file = $peak ? self::PEAKTMP : self::NONPEAKTMP;
    $handle = fopen($file,"w");
    foreach($data as $lane=>$value){
      fputcsv($handle,array($lane,$value));
    }
    fclose($handle);
    return $this;
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
  protected function _doError($scac,$lane,$lh_variance,$otherVal){
    $errorStr = "Error: Differing variances discovered: ";
    $errorStr .= $scac . " | " . $lane . " | " . $lh_variance . " | " . $otherVal;
    throw new \Exception($errorStr);
  }
}
