<?php


require_once __DIR__ . '/../../../models/rates/RateFactory.php';


class Round1Pasture{

  protected $year;
  protected $round;
  protected $scacs = array();
  protected $lh_variances = array(1,0,-1,-2);
  protected $sit_variances = array(2,1,0,-.25,-.5,-.75,-1,-1.25);
  protected $dataObjects = array();

  public function __construct($year,$scacs){
    $this->year = $year - 1;
    $this->round = 2;
    $this->scacs = $scacs;
    $this->_build();
  }
  protected function _build(){
    $breakPoint = round(count($this->scacs) / 2);
    for($i = 0; $i < count($this->scacs); $i++){
      $obj = RateFactory::blankObject();
      if($i < $breakPoint){
        $isHigh = 1;
        $lh_variance = $this->lh_variances[$i];
      }else{
        $isHigh = 0;
        $index = ($i / 2) / 2;
        $index = ($index == 1) ? 0 : round($index);
        $lh_variance = $this->lh_variances[$index];
      }
      $obj->scac = $this->scacs[$i];
      $obj->isHigh = $isHigh;
      $obj->lh_variance = $lh_variance;
      $obj->sit_variance = $this->sit_variances[$i];
      $this->dataObjects[] = $obj;
    }
    return $this;
  }
  protected function _autoFile(){
    foreach($this->dataObjects as $fileObj){
      $scac = RateFactory::buildScac($fileObj->scac,$this->round,$this->year);
      foreach($scac->peakLanes as $lane){
        $lh_range = $lane->getKnownAcceptedRange();
        print_r($lh_range);
        echo $lane->lh_bkar . "\n";
        $sit_range = $lane->getKnownAcceptedRange(true,false);
        if($fileObj->isHigh){
        }
      }
      // foreach($scac->nonPeakLanes as $lane){
      //   $lh_range = $lane->getKnownAcceptedRange(false,true);
      //   $sit_range = $lane->getKnownAcceptedRange(false,false);
      // }
    }
    return $this;
  }
}



/*PASTURE ROUND 1 AUTOFILE*/
//
// foreach($pasture as $scacLabel=>$variance){
//   $scac = RateFactory::buildScac($scacLabel,$round,$year);
//   foreach($scac->peakLanes as $lane){
//     $lh_range = $lane->getKnownAcceptedRange();
//     $sit_range = $lane->getKnownAcceptedRange(true,false);
//     if($variance['isHigh']){
//       $lane->lh_adj = $lh_range['x'] + $variance['lh'];
//     }else{
//       $lane->lh_adj = $lh_range['y'] + $variance['lh'];
//     }
//     $lane->sit_adj = $sit_range['x'] + $variance['sit'];
//   }
//   foreach($scac->nonPeakLanes as $lane){
//     $lh_range = $lane->getKnownAcceptedRange(false,true);
//     $sit_range = $lane->getKnownAcceptedRange(false,false);
//     if($variance['isHigh']){
//       $lane->lh_adj = $lh_range['x'] + $variance['lh'];
//     }else{
//       $lane->lh_adj = $lh_range['y'] + $variance['lh'];
//     }
//     $lane->sit_adj = $sit_range['x'] + $variance['sit'];
//   }
// }
/*END PASTURE ROUND 1 AUTOFILE*/
//exit;
