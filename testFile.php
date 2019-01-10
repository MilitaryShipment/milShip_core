<?php


require_once __DIR__ . '/util/SendMessage.php';

$message = array(
	"to"=>array("j.watson@allamericanmoving.com"),
	"subject"=>"I wrote you a message",
	"body"=>"And here it is"
);

try{
	$m = new SendMessage($message);
}catch(\Exception $e){
	echo $e->getMessage() . "\n";
}


exit;
require_once __DIR__ . '/models/rates/RateFactory.php';


// RateFactory::importBookings();
// exit;

$pastureScacs = array("AAMG","EVAL","AVLE","MXSP","NVYV","GVLN","PYVL");
$redScacs = array("MXSP","ADVA","EWVL","HVNL","GVLN","FVNL","AWVA");
$harvestScacs = array("AVLM","FVNL","EVAL","PPVL","PYVL","ALMM","HVNL","EXDV");

$year = 2019;
$harvestPeak = array("AVLM","ADVA","ALMM","PYVL","EVAL","ADVA","FVNL","EWVL");
$harvestNonPeak = array("AVLM","ADVA","ALMM","PYVL","EVAL","ADVA","FVNL","EWVL");
$redPeak = array("AAMG","EVAL","AVLE","MXSP","GVLN","PPVL","HVNL");
$redNonPeak = array("AAMG","EVAL","AVLE","MXSP","GVLN","PPVL","HVNL");
$pasturePeak = array();
$pastureNonPeak = array();

RateFactory::round1RedHarvest($year,$redPeak,$harvestPeak,true);

exit;

for($i = 0; $i <= 1; $i++){
	$peak = $i;
	if($peak){
		RateFactory::round1Pasture($year,$pasturePeak,$peak);
    RateFactory::round1RedHarvest($year,$redPeak,$harvestPeak,$peak);
	}else{
		RateFactory::round1Pasture($year,$pastureNonPeak,$peak);
    RateFactory::round1RedHarvest($year,$redNonPeak,$harvestNonPeak,$peak);
	}
}
/*REDFILE ROUND 1 AUTOFLE*/

function _doError($scac,$lane,$lh_variance,$otherVal){
  $errorStr = "Error: Differing variances discovered: ";
  $errorStr .= $scac . " | " . $lane . " | " . $lh_variance . " | " . $otherVal;
  throw new \Exception($errorStr);
}
function _saveToCsv($data,$peak = true){
  if($peak){
    $file = "/tmp/peakLanes.csv";
  }else{
    $file = "/tmp/nonPeakLanes.csv";
  }
  $handle = fopen($file,"w");
  foreach($data as $lane=>$value){
    fputcsv($handle,array($lane,$value));
  }
  fclose($handle);
}
function _readFromCsv($peak = true){
  $data = array();
  if($peak){
    $file = "/tmp/peakLanes.csv";
  }else{
    $file = "/tmp/nonPeakLanes.csv";
  }
  $csv = array_map('str_getcsv', file($file));
  foreach($csv as $row){
    $data[$row[0]] = $row[1];
  }
  return $data;
}

//what to do about sit
function _doAdjustments($allScacs,$round,$year,$peakLanes,$nonPeaksLanes){
  foreach($peakLanes as $laneLabel => $variance){
    $increment = Lane::findBkar($laneLabel,$year,$round,true,true);
    echo $laneLabel . "\n";
    echo "BKAR: " . $increment . "\n";
    foreach($allScacs as $scacLabel){
      $lane = Lane::getLane($laneLabel,$scacLabel,$year,$round,true);
      $rejection = $lane->getHighestRejection(true,true);
      $increment -= $peakLanes[$lane->lane];
      $lane->lh_adj = $increment;
      echo $scacLabel . " -> " . $lane->lh_adj . " | (" . $rejection . ")\n";
    }
  }
  foreach($nonPeaksLanes as $laneLabel => $variance){
    $increment = Lane::findBkar($laneLabel,$year,$round,true,false);
    echo $laneLabel . "\n";
    echo "BKAR: " . $increment . "\n";
    foreach($allScacs as $scacLabel){
      $lane = Lane::getLane($laneLabel,$scacLabel,$year,$round,false);
      $rejection = $lane->getHighestRejection(false,true);
      $increment -= $nonPeaksLanes[$lane->lane];
      $lane->lh_adj = $increment;
      echo $scacLabel . " -> " . $lane->lh_adj . " | (" . $rejection . ")\n";
    }
  }
}

$year = 2018;
$round = 2;
$harvest = array("MXSP","ADVA","EWVL","HVNL","GVLN","FVNL","AWVA");
$redFiles = array("AVLM","FVNL","EVAL","PPVL","PYVL","ALMM","HVNL","EXDV");
$allScacs = array_merge($redFiles,$harvest);
$peakLanes = _readFromCsv();
$nonPeaksLanes = _readFromCsv(false);
_doAdjustments($allScacs,$round,$year,$peakLanes,$nonPeaksLanes);

exit;
/*BUILDING LANE DATA*/
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
_saveToCsv($peakLanes);
_saveToCsv($nonPeaksLanes,false);
/*END BUILDING LANE DATA*/
exit;
/*END REDFILE ROUND 1 AUTOFILE*/




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
