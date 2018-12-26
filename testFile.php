<?php


require_once __DIR__ . '/models/rates/RateFactory.php';


/*REDFILE ROUND 1 AUTOFLE*/

function _doError($scac,$lane,$lh_variance,$otherVal){
  $errorStr = "Error: Differing variances discovered: ";
  $errorStr .= $scac . " | " . $lane . " | " . $lh_variance . " | " . $otherVal;
  throw new \Exception($errorStr);
}

$year = 2018;
$round = 2;
$harvest = array("MXSP","ADVA","EWVL","HVNL","GVLN","FVNL","AWVA");
$redFiles = array("AVLM","FVNL","EVAL","PPVL","PYVL","ALMM","HVNL","EXDV");
$allScacs = array_merge($redFiles,$harvest);
$peakLanes = array();
$nonPeaksLanes = array();
foreach($redFiles as $scacLabel){
  $scac = RateFactory::buildScac($scacLabel,$round,$year);
  foreach($scac->peakLanes as $lane){
    $lh_ehp = $lane->getEhpRange();
    $lh_variance = $lh_ehp / (count($harvest) + count($redFiles));
    if(!isset($peakLanes[$lane->lane])){
      $peakLanes[$lane->lane] = $lh_variance;
    }elseif($peakLanes[$lane->lane] != $lh_variance){
      _doError($scacLabel,$lane->lane,$lh_variance,$peaksLanes[$lane->lane]);
    }
  }
  foreach($scac->nonPeakLanes as $lane){
    $lh_ehp = $lane->getEhpRange(false,true);
    $lh_variance = $lh_ehp / (count($harvest) + count($redFiles));
    if(!isset($nonPeaksLanes[$lane->lane])){
      $nonPeaksLanes[$lane->lane] = $lh_variance;
    }elseif($nonPeaksLanes[$lane->lane] != $lh_variance){
      _doError($scacLabel,$lane->lane,$lh_variance,$nonPeaksLanes[$lane->lane]);
    }
  }
}
foreach($allScacs as $scacLabel){
  $scac = RateFactory::buildScac($scacLabel,$round,$year);
  foreach($scac->peakLanes as $lane){
    $lh_range = $lane->getKnownAcceptedRange();
    $lane->lh_adj = $lh_range['x'] + $peakLanes[$lane->lane];
    echo $scacLabel . " | " . $lane->lane . " | " . $lane->lh_discount . " + " . $peakLanes[$lane->lane]  "\n";
    echo $lane->lh_adj . "\n";
    print_r($lh_range);
    exit;
  }
}

exit;
/*END REDFILE ROUND 1 AUTOFILE*/

/*PASTURE ROUND 1 AUTOFILE*/

//TODO MAKESURE YOU VERIFY THE ISHIGH X AND Y BUSINESS. REMEMBER: YOU FLIPPED THEM!!!

$year = 2018;
$round = 2;
$pasture = array(
  "AAMG"=>array("isHigh"=>1,"lh"=>0,"sit"=>0),
  "EVAL"=>array("isHigh"=>1,"lh"=>1,"sit"=>1),
  "AVLE"=>array("isHigh"=>1,"lh"=>-1,"sit"=>2),
  "MXSP"=>array("isHigh"=>1,"lh"=>-2,"sit"=>-.25),
  "NVYV"=>array("isHigh"=>0,"lh"=>0,"sit"=>-.50),
  "GVLN"=>array("isHigh"=>0,"lh"=>-1,"sit"=>-.75),
  "PYVL"=>array("isHigh"=>0,"lh"=>-2,"sit"=>-1)
);
foreach($pasture as $scacLabel=>$variance){
  $scac = RateFactory::buildScac($scacLabel,$round,$year);
  foreach($scac->peakLanes as $lane){
    $lh_range = $lane->getKnownAcceptedRange();
    $sit_range = $lane->getKnownAcceptedRange(true,false);
    if($variance['isHigh']){
      $lane->lh_adj = $lh_range['x'] + $variance['lh'];
    }else{
      $lane->lh_adj = $lh_range['y'] + $variance['lh'];
    }
    $lane->sit_adj = $sit_range['x'] + $variance['sit'];
  }
  foreach($scac->nonPeakLanes as $lane){
    $lh_range = $lane->getKnownAcceptedRange(false,true);
    $sit_range = $lane->getKnownAcceptedRange(false,false);
    if($variance['isHigh']){
      $lane->lh_adj = $lh_range['x'] + $variance['lh'];
    }else{
      $lane->lh_adj = $lh_range['y'] + $variance['lh'];
    }
    $lane->sit_adj = $sit_range['x'] + $variance['sit'];
  }
}
/*END PASTURE ROUND 1 AUTOFILE*/
exit;


require_once __DIR__ . '/processes/traffic/VanOperator.php';

$gbl_dps = 'FDNT0000000';

$input = new stdClass();
$input->delivery_date_eta_early_time = "0:0";
$input->delivery_date_eta_late_time = "0:0";
$input->delivery_eta_date = "11/14/2018";
$input->final_load_eta_date = "1/1/1970";
$input->gross_weight = 0;
$input->is_overflow = false;
$input->necessity_item_description = "";
$input->necessity_items_left = false;
$input->tare_weight = 0;

$v = new VanOperator($gbl_dps,$input);
