<?php


require_once __DIR__ . '/models/rates/RateFactory.php';

$year = 2018;
$round = 2;

$pasture = array(
  "AAMG"=>0,
  "EVAL"=>0,
  "AVLE"=>0,
  "MXSP"=>1,
  "NVYV"=>1,
  "GVLN"=>1
);
$harvest = array(
  "ADVA"=>0,
  "ALMM"=>0,
  "AVLM"=>0,
  "PYVL"=>0,
  "PPVL"=>1,
  "UVNL"=>1,
  "USAV"=>1,
  "VVNL"=>1
);
$redFile = array("AWVA","CFVL","MVUS");

foreach($pasture as $scac => $isHigh){
  $scac = RateFactory::buildScac($scac,$round,$year);
  foreach($scac->peakLanes as $lane){
    $range = $lane->getKnownAcceptedRange();
    print_r($range);
  }
  // if($isHigh){
  //   //todo one thing.
  // }else{
  //   //todo another thing.
  // }
}




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
