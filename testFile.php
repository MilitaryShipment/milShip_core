<?php


require_once __DIR__ . '/models/rates/RateFactory.php';

$year = 2018;
$round = 2;

$pasture = array(
  array(0,"AAMG"),
  array(0,"EVAL"),
  array(0,"AVLE"),
  array(1,"MXSP"),
  array(1,"NVYV"),
  array(1,"GVLN")
);
$harvest = array(
  array(0,"ADVA"),
  array(0,"ALMM"),
  array(0,"AVLM"),
  array(0,"PYVL"),
  array(1,"PPVL"),
  array(1,"UVNL"),
  array(1,"USAV"),
  array(1,"VVNL")
);
$redFile = array("AWVA","CFVL","MVUS");

foreach($pasture as $isHigh => $scac){
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


function _getKnownAcceptedRange($lane,$round,$year){}


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
