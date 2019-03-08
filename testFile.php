<?php


require_once __DIR__ . '/models/comms/Template.php';
require_once __DIR__ . '/processes/tami/TamiBase.php';

$templates = Template::getTamiTemplates();
$noShipments = array();
$illegalTemplates = array();
$noGblocData = array();

foreach($templates as $template){
  try{
    $shipments = TamiBase::getShipments(strtolower($template->msg_name));
    echo count($shipments) ? $template->msg_name . "\n" : '';
    foreach($shipments as $shipment){
      if(TamiBase::isBlackList($shipment['gbl_dps'])){
        continue;
      }
      if(!TamiBase::isRedFile($shipment) && !TamiBase::isRedFileExempt($template->msg_name)){
        if(TamiBase::isBadger($template->msg_name) && TamiBase::hasIntroResponse($shipment['gbl_dps'])){
          continue;
        }
        if(TamiBase::sentToday($template->msg_name,$shipment['gbl_dps'])){
          continue;
        }
        try{
          $shipment = array_merge($shipment,TamiBase::getGblocInfo($shipment['dest_gbloc'],$shipment['dest_gbloc_area']));
        }catch(\Exception $e){
          $noGblocData[] = $shipment['gbl_dps'];
        }
        if($template->msg_name == "packdayeta" && TamiBase::premoveSurveyExists($shipment['gbl_dps'])){
          continue;
        }
        if(TamiBase::isBadger($template->msg_name) && !TamiBase::isSent('vcard',$shipment['gbl_dps'])){
          continue;
        }
        if(TamiBase::isBadger($template->msg_name) && TamiBase::sentToday('vcard',$shipment['gbl_dps'])){
          continue;
        }
        if(TamiBase::isBadger($template->msg_name) && TamiBase::BadgerSentToday($shipment['gbl_dps'])){
          continue;
        }
        if(TamiBase::isOneTimeMsg($template->msg_name) && TamiBase::isSent($template->msg_name)){
          continue;
        }
        /*todo What to make of Andrew's stop over / Tomms systems
        We are on roughly line 724 of the original tami CLI
        */
        //if($template->msg_name == "etadelivery"){}
        echo $shipment['gbl_dps'] . "\n";
      }
    }
  }catch(\Exception $e){
    if($e->getMessage() == "Invalid Template Name"){
      $illegalTemplates[] = $template->msg_name;
    }else{
      $noShipments[] = $e->getMessage();
    }
  }
}

print_r($illegalTemplates);
print_r($noShipments);
print_r($noGblocData);

exit;

require_once __DIR__ . '/models/rates/RateFactory.php';


function _buildLane($array){
  return trim(strtolower($array[1])) . " to " . trim(strtolower($array[2]));
}

$scacs = array(
  "AAMG",
  "ADVA",
  "ALMM",
  "AVLE",
  "AVLM",
  "AWVA",
  "CFVL",
  "EVAL",
  "EWVL",
  "EXDV",
  "FDVN",
  "GVLN",
  "HVNL",
  "MVUS",
  "MXSP",
  "NVYV",
  "OGVL",
  "PPVL",
  "PYVL",
  "USAV",
  "UVNL",
  "VVNL"
);
$scacs = array('FVNL');

$input = __DIR__ . '/processes/rates/data/input/hard_to_service_lanes.csv';
$outDir = __DIR__ . "/processes/rates/data/output/";
$csv = array_map('str_getcsv', file($input));
$hardToServiceLanes = array();
foreach($csv as $row){
  $hardToServiceLanes[] = _buildLane($row);
}

$PEAKLH = 51;
$PEAKSIT = 58;
$NONPEAKLH = 68;
$NONPEAKSIT = 58;
$HARDNONPEAKLH = 60;

foreach($scacs as $scac){
  $scac = RateFactory::buildScac($scac,2,2018);
  foreach($scac->peakLanes as $lane){
    $lane->lh_adj = $PEAKLH;
    $lane->sit_adj = $PEAKSIT;
    $update = array("lh_adj"=>$lane->lh_adj,"sit_adj"=>$lane->sit_adj);
    $lane->update($update,true);
  }
  foreach($scac->nonPeakLanes as $lane){
    if(in_array($lane->lane,$hardToServiceLanes)){
      $lane->lh_adj = $HARDNONPEAKLH;
    }else{
      $lane->lh_adj = $NONPEAKLH;
    }
    $lane->sit_adj = $NONPEAKSIT;
    $update = array("lh_adj"=>$lane->lh_adj,"sit_adj"=>$lane->sit_adj);
    $lane->update($update,false);
  }
}
foreach($scacs as $scac){
	$params = RateFactory::blankObject();
	$params->scac = $scac;
	$params->year = 2018;
	$params->round = 2;
	RateFactory::export($params);
}
$files = scandir($outDir);
foreach($files as $file){
  foreach($scacs as $scac){
    $pattern = "/" . $scac . "/";
    if(preg_match($pattern,$file) && !rename($outDir . $file, $outDir . strtolower($scac) . ".csv")){
      print_r(error_get_last());
      exit;
    }
  }
}
foreach($scacs as $scac){
  $file = $outDir . strtolower($scac) . '.csv';
  $handle = fopen($file,'a');
  $data = array(
    array($scac,"DHHG","US4965500","REGION 1","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 10","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 11","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 12","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 2","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 3","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 4","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 5","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 6","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 7","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 8","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT),
    array($scac,"DHHG","US4965500","REGION 9","D",$PEAKLH,$PEAKSIT,$HARDNONPEAKLH,$NONPEAKSIT)
  );
  foreach($data as $d){
    fputcsv($handle,$d);
  }
  fclose($handle);
}

exit;



require_once __DIR__ . '/processes/billing/RecoverRecEmail.php';

$gbls = array(
  "HAFC0414238",
  "BKAS0088652",
  "KKFA0569342",
  "BGAC0395555",
  "JEAT0264875",
  "CHAT0059649",
  "BGAC0375409"
);

foreach($gbls as $gbl){
  $r = new RecoverRecEmail($gbl);
}

exit;

require_once __DIR__ . '/models/rates/RateFactory.php';

// $label = 'us27 to region 2';
// echo $label . "\n";
// echo "PEAK LH BKAR: " . Lane::findBkar($label,2018,2,true,true) . "\n";
// echo "PEAK SIT BKAR: " . Lane::findBkar($label,2018,2,false,true) . "\n";
// echo "NONPEAK LH BKAR: " . Lane::findBkar($label,2018,2,true,false) . "\n";
// echo "NONPEAK SIT BKAR: " . Lane::findBkar($label,2018,2,false,false) . "\n";

// exit;

$scacs = array(
  "VVNL"=>1,
  "FDVN"=>0,
  "HVNL"=>-.25,
  "AAMG"=>-.5,
  "AVLE"=>-.75,
  "EXDV"=>-1,
  "PPVL"=>-1.25,
  "PYVL"=>-1.5
);

// RateFactory::round1SlapDash(2019,$scacs);
// RateFactory::round1SlapDash(2019,$scacs,false);

foreach($scacs as $scac=>$nothing){
	$params = RateFactory::blankObject();
	$params->scac = $scac;
	$params->year = 2018;
	$params->round = 2;
	RateFactory::export($params);
}


exit;

require_once __DIR__ . '/util/Messenger.php';

$message = array(
	"to"=>array("j.watson@allamericanmoving.com"),
	"subject"=>"I wrote you a message",
	"body"=>"And here it is",
	"replyTo"=>"webadmin@allamericanmoving.com",
	"from"=>"missingitemsmonitor@militaryshipment.com",
	"cc"=>array('k.thompson@allamericanmoving.com'),
	"attachemnts"=>array('/tmp/sendMessage.occurences')
);

$to = array(
	"qa@electronicvan.com",
	"qa@all-americanvan.com",
	"qa@all-americanmoving.net",
	"qa@all-wrightvan.com",
	"qa@americanvan.net",
	"qa@confederatevan.com",
	"qa@eastwestvan.com",
	"qa@exodusvan.com",
	"qa@genesisvan.com",
	"qa@heritagevan.com",
	"qa@navyvan.com",
	"qa@oldgloryvan.com",
	"qa@parkplacevan.com",
	"qa@planetaryvan.com",
	"qa@usavan.com"
);

$message = array(
	"to"=>$to,
	"subject"=>"MASS TEST",
	"body"=>_buildMsg(count($to))
);

function _buildMsg($numEmails){
	return "This is a mass test of " . $numEmails . " email addresses<br>Generated by " . $_SERVER['HOSTNAME'];
}

try{
	Messenger::send($message,'smtp.gmail.com',587,'johnjwatson@gmail.com','Youwonder1');
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
