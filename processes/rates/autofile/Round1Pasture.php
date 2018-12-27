<?php


require_once __DIR__ . '/../../../models/rates/RateFactory.php';


class Round1Pasture{

  protected $year;
  protected $round;
  protected $scacs = array();
  protected $lh_variances = array(1,0,-1,-2);
  protected $sit_variances = array(2,1,0,-.25,-.5,-.75,-1,-1.25);
  protected $dataObjects = array();

  public function __construct($scacs){
    // $this->year = $year;
    // $this->round = $round;
    $this->scacs = $scacs;
    $this->_build();
    print_r($this->dataObjects);
  }
  protected function _build(){
    $breakPoint = round(count($this->scacs) / 2);
    for($i = 0; $i < count($this->scacs); $i++){
      $obj = RateFactory::blankObject();
      if($i < $breakPoint){
        $isHigh = 1;
        $lh_variance = $this->$lh_variances[$i];
      }else{
        $isHigh = 0;
        $index = ($i / 2) / 2;
        if($index == 1){
          $index = 0;
        }else{
          $index = round($index);
        }
        $lh_variance = $this->$lh_variance[$index];
      }
      $obj->scac = $this->scacs[$i];
      $obj->isHigh = $isHigh;
      $obj->lh_variance = $lh_variance;
      $obj->$sit_variance = $this->$sit_variances[$i];
      $this->dataObjects[] = $obj;
    }
    return $this;
  }
  protected function _autoFile(){}
}


/*
$scacs = array(
	"ADVA",
	"FVNL",
	"AVLE",
	"GVLN",
	"PYVL",
	"MXSP",
	"NVYV"
);

$lh_variances = array(1,0,-1,-2);

for($i = 0; $i < count($scacs); $i++){
	$breakPoint = round(count($scacs) / 2);
	if($i < $breakPoint){
		$isHigh = 1;
		$lh_variance = $lh_variances[$i];
		echo $scacs[$i] . " | " . $lh_variance . "<br>";
	}else{
		$isHigh = 0;
		$index = ($i / 2) / 2;
		if($index == 1){
			$index = 0;
		}else{
			$index = round($index);
		}
		$lh_variance = $lh_variances[$index];
		echo $scacs[$i] . " | " . $lh_variance . "<br>";
	}
}

*/

/*PASTURE ROUND 1 AUTOFILE*/

//TODO MAKESURE YOU VERIFY THE ISHIGH X AND Y BUSINESS. REMEMBER: YOU FLIPPED THEM!!!

// $year = 2018;
// $round = 2;
// $pasture = array(
//   "AAMG"=>array("isHigh"=>1,"lh"=>0,"sit"=>0),
//   "EVAL"=>array("isHigh"=>1,"lh"=>1,"sit"=>1),
//   "AVLE"=>array("isHigh"=>1,"lh"=>-1,"sit"=>2),
//   "MXSP"=>array("isHigh"=>1,"lh"=>-2,"sit"=>-.25),
//   "NVYV"=>array("isHigh"=>0,"lh"=>0,"sit"=>-.50),
//   "GVLN"=>array("isHigh"=>0,"lh"=>-1,"sit"=>-.75),
//   "PYVL"=>array("isHigh"=>0,"lh"=>-2,"sit"=>-1)
// );
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
