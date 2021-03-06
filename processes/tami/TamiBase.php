<?php

require_once __DIR__ . '/../../models/ops/Shipment.php';
require_once __DIR__ . '/../../models/ops/Agent.php';
require_once __DIR__ . '/../../models/ops/Gbloc.php';
require_once __DIR__ . '/../../models/comms/MobileResponse.php';
require_once __DIR__ . '/../../models/comms/Notification.php';
require_once __DIR__ . '/../../util/Messenger.php';

abstract class TamiBase{

  const RECORDLIMIT = 2000;
  const EARLIESTDELIVERY = 8;
  const OURTIMEZONE = -6;
  const PREMOVEDIR = '/scan/silo/mobiledata/';

  protected static $_destPages = array(
    "etadelivery",
    "deliverydayeta",
    "deliveryday",
    "rddinfo",
    "rushsurvey"
  );
  protected static $_oneTimeMsgs = array(
    "intro",
    "intronts",
    "vcard"
  );
  protected static $_requireResponseMsgs = array(
    "firearms",
    "orig_addr",
    "dest_addr",
    "oversize"
  );
  protected static $_etaMsgs = array(
    "loaddayeta",
    "packdayeta",
    "packloaddayeta"
  );
  protected static $_loadMsgs = array(
    "loadday",
    "1stpackday",
    "packloadday"
  );
  protected static $_gblBlackList = array(
    "AGFM7000008"
  );
  protected static $_eventHour = array(
    'vcard' => 8,
    'intro' => 13,
    'introbadger2' => 9,
    'intronts' => 13,
    'introntsbadger2' => 9,
    'packdayeta' => 9,
    'packloaddayeta' => 9,
    '1stpackday' => 13,
    '2ndpackday' => 13,
    'lastpackday' => 13,
    'loaddayeta' => 9,
    'loadday' => 13,
    'packloadday' => 13,
    'etadelivery' => 9,
    'deliverydayeta' => 9,
    'deliveryday' => 13,
    'vanoperator' => 13,
    'rddinfo' => 9,
    'rushsurvey' => 9,
    'sitexpiration' => 9,
    'sitrequest' => 9,
    'vswelcome' => 9,
    'picsurveymember' => 9
  );
  protected static $_redFileExemptions = array(
    "introbadger2",
    "introntsbadger2",
    "rushsurvey"
  );
  protected static $_badgers = array(
    "introbadger2",
    "introntsbadger2"
  );
  protected static $_dateTimeBlackList = array(
    "created_date",
    "update_date",
    "registration_date"
  );
  protected static $_timeInclude = array(
    "early_delivery_eta",
    "late_delivery_eta"
  );
  protected static $_phoneFields = array(
    "transit",
    "shipper_cell_phone_number_1",
    "shipper_cell_phone_number_2",
    "orig_phone",
    "orig_alt_phone",
    "dest_phone"
  );

  public static function getShipments($msg_name){
    $data = array();
    $zipField = self::isDestMsg($msg_name) ? 'a.dest_zip' : 'a.orig_zip';
    $results = $GLOBALS['db']->suite(Shipment::DRIVER)
                              ->driver(Shipment::DRIVER)
                              ->database(Shipment::DB)
                              ->table(Shipment::TABLE . " a")
                              ->select(self::buildSelectStr())
                              ->join("tbl_zip_location b",$zipField,"=","b.zip")
                              //->join('tbl_zip_location b', "$zipField = b.zip")
                              ->where("(a.status NOT LIKE 'CAN%' AND a.status NOT LIKE 'ON%HOLD%' or a.status is null)")
                              ->andWhere("a.status"," NOT LIKE", "'%PULLBACK AND REAWARD%'")
                              ->andWhere("(a.text_opt_out != 'Y' OR a.text_opt_out is null)")
                              ->andWhere("a.full_name", "NOT LIKE","'%BLUEBARK%'")
                              ->andWhere("a.full_name","NOT LIKE","'%HUMANITY%'")
                              ->andWhere("a.pickup_type","NOT LIKE","'HAUL ONLY'")
                              ->andWhere("a.pickup_type","NOT LIKE","'%LONG%DELIVERY%'")
                              ->andWhere("a.pickup_type","NOT LIKE","'%BLUEBARK%'")
                              ->andWhere("a.gbl_dps","NOT LIKE","'MF%'")
                              ->andWhere("a.crm_date","is not","null")
                              ->andWhere("MMDDYY_TO_DATE(a.registration_date)",">=","'2016-04-27'")
                              ->andWhere(self::appendWhereStr($msg_name,self::buildWhereStr($msg_name)))
                              ->take(self::RECORDLIMIT)
                              ->get();
    if(!mssql_num_rows($results)){
      throw new \Exception('Unable to locate any shipments');
    }
    $i = 0;
    while($row = mssql_fetch_assoc($results)){
      foreach($row as $key=>$value){
        $data[$i][$key] = preg_replace("/'{1,}/", "''", $value);
      }
      $i++;
    }
    return $data;
  }
  public static function buildWhereStr($msg_name){
    $str = '';
    switch($msg_name){
      case "vcard":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%OVERFLOW%'
                    AND (DATEADD(day, 3, CAST(a.crm_date AS DATE)) >= CAST(GETDATE() AS DATE))";
      break;
      case "intro":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND a.pickup_type NOT LIKE '%OVERFLOW%'
                    AND (DATEADD(day, 3, CAST(a.crm_date AS DATE)) >= CAST(GETDATE() AS DATE))";
      break;
      case "intronts":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type LIKE '%nts%'
                    AND (DATEADD(day, 3, CAST(a.crm_date AS DATE)) >= CAST(GETDATE() AS DATE))";
      break;
      case "introbadger2":
        $str = "WEEKDAY(CURRENT_DATE()) < 6
                    AND (a.pickup_type NOT LIKE '%nts%' AND a.pickup_type NOT LIKE '%OVERFLOW%')
                    AND DAY_AFTER(CAST(a.crm_date AS DATE)) = CURRENT_DATE()";
      break;
      case "introntsbadger2":
        $str = "WEEKDAY(CURRENT_DATE()) < 6
                    AND a.pickup_type LIKE '%nts%'
                    AND DAY_AFTER(CAST(a.crm_date AS DATE)) = CURRENT_DATE()";
      break;
      case "packdayeta":
        $str = "a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date AS DATE) != CAST(a.pickup_date AS DATE)
                    AND DAY_BEFORE(CAST(a.pack_date AS DATE)) = CURRENT_DATE()";
      break;
      case "packloaddayeta":
        $str = "a.pack_date = a.pickup_date
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND DAY_BEFORE(CAST(a.pack_date AS DATE)) = CURRENT_DATE()";
      break;
      case "1stpackday":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date AS DATE) != CAST(a.pickup_date AS DATE)
                    AND CAST(a.pack_date AS DATE) = CURRENT_DATE()";
      break;
      case "2ndpackday":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date_2 AS DATE) != CAST(a.pickup_date AS DATE)
                    AND CAST(a.pack_date_2 AS DATE) = CURRENT_DATE()";
      break;
      case "lastpackday":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date_3 AS DATE) != CAST(a.pickup_date AS DATE)
                    AND CAST(a.pack_date_3 AS DATE) = CURRENT_DATE()";
      break;
      case "loaddayeta":
        $str = "a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date AS DATE) != CAST(a.pickup_date AS DATE)
                    AND DAY_BEFORE(CAST(a.pickup_date AS DATE)) = CURRENT_DATE()";
      break;
      case "loadday":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date   AS DATE) != CAST(a.pickup_date AS DATE)
                    AND CAST(a.pack_date_2 AS DATE) != CAST(a.pickup_date AS DATE)
                    AND CAST(a.pack_date_3 AS DATE) != CAST(a.pickup_date AS DATE)
                    AND CAST(a.pickup_date AS DATE) = CURRENT_DATE()";
      break;
      case "packloadday":
        $str = "WEEKDAY(CURRENT_DATE()) < 5
                    AND a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date AS DATE) = CAST(a.pickup_date AS DATE)
                    AND CAST(a.pickup_date AS DATE) = CURRENT_DATE()";
      break;
      case "etadelivery":
        $str = "a.registration_date >= '2016-08-01'
                AND CURRENT_DATE() >= DATE_SUB(CAST(a.pickup_date AS DATE), 1, day)
                AND CURRENT_DATE() < CAST(a.driver_eta_date AS DATE)";
      break;
      case "deliverydayeta":
        $str = "DAY_BEFORE(CAST(a.delivery_eta_date AS DATE)) = CURRENT_DATE()";
      break;
      case "deliveryday":
        $str = "CAST(a.delivery_eta_date AS DATE) = CURRENT_DATE()";
      break;
      case "residencedelivery":
        $str = "CAST(a.delivery_eta_date AS DATE) = CURRENT_DATE()";
      break;
      case "sitdelivery":
        $str = "CAST(a.delivery_eta_date AS DATE) = CURRENT_DATE()";
      break;
      case "rushsurvey":
        $str = "DATEADD(DAY, 2,(CAST(a.delivery_residence_date AS DATE))) = CURRENT_DATE()
                AND a.pickup_type NOT LIKE '%nts%'
                AND a.pickup_type NOT LIKE '%OVERFLOW%'";
      break;
      case "sitexpiration":
        $str = "( DATE_SUB(CURRENT_DATE(), 7, day) = CAST(a.sit_exp_date AS DATE)
                OR DATE_SUB(CURRENT_DATE(), 14, day) = CAST(a.sit_exp_date AS DATE) )
                and datepart(year, cast(delivery_residence_date as date)) < 2000";
      break;
      case "sitrequest":
        $str = "( DATE_SUB(CURRENT_DATE(), 7, day) = CAST(a.sit_exp_date AS DATE)
                OR DATE_SUB(CURRENT_DATE(), 3, day) = CAST(a.sit_exp_date AS DATE) )";
      break;
      case "rddinfo":
        $str = "a.send_rdd_text = 'y'
                    AND CURRENT_DATE() > cast(a.crm_date as date)
                    AND CURRENT_DATE() < cast(a.pickup_date as date)";
      break;
      case "picsurveymember":
        $str = "a.pickup_type NOT LIKE '%nts%'
                    AND CAST(a.pack_date AS DATE) != CAST(a.pickup_date AS DATE)
                    AND DATEADD(ww,-1,CAST(a.pickup_date AS DATE)) = CURRENT_DATE()";
      break;
      case "picsurveydriver":
        //todo trigger for a response from member
        $str = "";
      break;
      default:
        throw new \Exception('Invalid Template Name');
    }
    return $str;
  }
  public static function appendWhereStr($msg_name,$whereStr){
    switch($msg_name){
      case "vcard":
      break;
      case "intro":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND ( CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= " OR (CURRENT_HOUR() >= HOUR(a.registration_date) + (b.timezone - " .  self::OURTIMEZONE . ") + 1) )";
      break;
      case "intronts":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND ( CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= " OR (CURRENT_HOUR() >= HOUR(a.registration_date) + (b.timezone - " .  self::OURTIMEZONE . ") + 1) )";
      break;
      case "introbadger2":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "introntsbadger2":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "packdayeta":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "packloaddayeta":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "1stpackday":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= "OR CURRENT_HOUR() >= HOUR(a.pack_eta_late_time)";
        $whereStr .= " + (b.timezone - " . self::OURTIMEZONE . ") + 1)";
      break;
      case "2ndpackday":
        $whereStr .= "\n AND CURRENT_HOUR() >=" . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= "\n OR CURRENT_HOUR() >= HOUR(a.pack_eta_late_time) + (b.timezone - " . self::OURTIMEZONE . ") + 1)";
      break;
      case "lastpackday":
        $whereStr .= "\n AND CURRENT_HOUR() >=" . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= "\n OR CURRENT_HOUR() >= HOUR(a.pack_eta_late_time) + (b.timezone - " . self::OURTIMEZONE . ") + 1)";
      break;
      case "loaddayeta":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "loadday":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= " OR CURRENT_HOUR() >= HOUR(a.load_eta_late_time) + (b.timezone - " . self::OURTIMEZONE . ") + 1)";
      break;
      case "packloadday":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= " OR CURRENT_HOUR() >= HOUR(a.load_eta_late_time) + (b.timezone - " . self::OURTIMEZONE . ") + 1)";
      break;
      case "etadelivery":
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "deliverydayeta":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "deliveryday":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= " OR (CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . "))";
        $whereStr .= " >= (HOUR(a.late_delivery_eta_time) + 1) )";
      break;
      case "residencedelivery":
      break;
      case "sitdelivery":
      break;
      case "deliverydayrating":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND CURRNET_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
      break;
      case "rushsurvey":
      $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY .
                   "\nAND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ") >= " . self::$_eventHour[$msg_name];
      break;
      case "sitexpiration":
      $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY .
                   "\nAND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ") >= " . self::$_eventHour[$msg_name];
      break;
      case "rddinfo":
      $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY .
                   "\nAND CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ") >= " . self::$_eventHour[$msg_name];
      break;
      case "picsurveymember":
        $whereStr .= "\n AND CURRENT_HOUR() >= " . self::EARLIESTDELIVERY;
        $whereStr .= "\n AND ( CURRENT_HOUR() + (b.timezone - " . self::OURTIMEZONE . ")";
        $whereStr .= " >= " . self::$_eventHour[$msg_name];
        $whereStr .= " OR (CURRENT_HOUR() >= HOUR(a.registration_date) + (b.timezone - " . self::OURTIMEZONE . ") + 1) )";
      break;
      default:
        throw new \Exception('Invalid Template Name');
    }
    return $whereStr;
  }
  public static function buildSelectStr(){
    return "a.full_name
				  , a.actual_weight
				  , a.ccc_tid
				  , a.delivery_eta_date AS del_eta_date
				  , a.delivery_residence_date
				  , a.delivery_residence_date as del_residence_date
				  , a.delivery_warehouse_date
				  , a.dest_address
				  , a.dest_agent_id
				  , a.dest_city
				  , a.dest_gbloc_area
				  , a.dest_primary_phone AS dest_phone
				  , a.dest_state
				  , a.dest_zip
				  , a.driver_eta_date
				  , a.driver_id
				  , a.early_delivery_eta_time AS early_delivery_eta
				  , a.estimated_weight as est_weight
				  , a.first_name
				  , a.gbl_dps
				  , a.gbloc_orig as ogbloc
				  , a.last_name
				  , a.late_delivery_eta_time AS late_delivery_eta
				  , a.load_eta_early_time
				  , a.load_eta_late_time
				  , a.mc_tid
				  , a.orig_agent_id
				  , a.orig_gbloc_area
				  , a.orig_phone
				  , a.orig_primary_mobile AS transit
				  , a.orig_secondary_mobile AS shipper_cell_phone_number_2
				  , a.orig_zip
				  , a.pack_date
				  , a.pack_date_2
				  , a.pack_date_3
				  , a.pack_eta_early_time
				  , a.pack_eta_late_time
				  , a.pickup_date
				  , a.pickup_type
				  , a.premove_survey_date
				  , a.primary_email AS shipper_orig_email_addr
				  , a.red_file as redfile
				  , a.registration_date
				  , a.crm_date
				  , a.registration_number as order_number
				  , a.required_delivery_date AS delivery_date
				  , a.residence_delivery
				  , a.scac
				  , a.secondary_email AS shipper_alt_email_addr
				  , a.send_rdd_text
				  , a.sit_exp_date AS sit_exp
				  , a.sit_number
				  , a.storage_delivery
				  , a.survey_date
				  , a.text_opt_out
          , a.branch
          , a.gbloc_dest as dest_gbloc
          , a.orig_address
          , a.orig_city
          , a.orig_secondary_mobile as orig_alt_phone
          , a.orig_state
          , a.rank
          , a.ssn
          , a.hauler_agent_id
    			, b.zip
			    , b.state
				  , b.timezone";
  }
  public static function isDestMsg($msg_name){
    return in_array($msg_name,self::$_destPages);
  }
  public static function isBlackList($gbl_dps){
    return in_array($gbl_dps,self::$_gblBlackList);
  }
  public static function isRedFileExempt($msg_name){
    return in_array($msg_name,self::$_redFileExemptions);
  }
  public static function isBadger($msg_name){
    return in_array($msg_name,self::$_badgers);
  }
  public static function isOneTimeMsg($msg_name){
    return in_array($msg_name,self::$_oneTimeMsgs);
  }
  public static function isRequireResponse($msg_name){
    return in_array($msg_name,self::$_requireResponseMsgs);
  }
  public static function isEtaMsg($msg_name){
    return in_array($msg_name,self::$_etaMsgs);
  }
  public static function isLoadMsg($msg_name){
    return in_array($msg_name,self::$_loadMsgs);
  }
  public static function hasRequiredResponse($msg_name,$gbl_dps){
    if(!self::isRequireResponse($msg_name)){
      throw new \Exception('Not a required response message');
    }
    //todo implement util->is_text_info_required_response($msg_name,$gbl_dps,$key,$value)
  }
  public static function hasIntroResponse($gbl_dps){
    return MobileResponse::introExists($gbl_dps);
  }
  public static function isRedFile($tamiShipment){
    if(strtolower($tamiShipment['redfile']) == "y" || preg_match("/red/",$tamiShipment['pickup_type'])){
      return true;
    }
    return false;
  }
  public static function getGblocInfo($dest_gbloc,$dest_gbloc_area){
    try{
      return Gbloc::getTamiData($dest_gbloc,$dest_gbloc_area);
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
  }
  public static function getAgentInfo($agent_id){
    try{
      $agent = new Agent($agent_id);
      return array("agent_name"=>$agent->agent_name,"phone_number"=>$agent->phone_number);
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
  }
  public static function sentToday($msg_name,$gbl_dps){
    $objs = Notification::sentToday($msg_name,$gbl_dps);
    if(count($objs)){
      return true;
    }
    return false;
  }
  public static function isSent($msg_name,$gbl_dps){
    $objs = Notification::sent($msg_name,$gbl_dps);
    if(count($objs)){
      return true;
    }
    return false;
  }
  public static function BadgerSentToday($gbl_dps){
    foreach(self::$_badgers as $msg_name){
      if(self::sentToday($msg_name,$gbl_dps)){
        return true;
      }
    }
    return false;
  }
  public static function premoveSurveyExists($gbl_dps){
    return is_file(self::PREMOVEDIR . "mobile_" . $gbl_dps . "_premoveSurvey.jpg");
  }
  public static function validateIsset($var){
    if(!isset($var) || empty($var)){
      throw new \Exception("unset or empty");
    }
    return true;
  }
  public static function deliverydayetaOverride($shipment){
    if(10 <= (date('H') + $shipment['timezone'] - self::OURTIMEZONE)){
      if(isset($shipment['del_eta_date']) && !empty($shipment['del_eta_date'])){
        if((!isset($shipment['early_delivery_eta']) || empty($shipment['early_delivery_eta'])) || date('H:i', strtotime($shipment['early_delivery_eta'])) == '00:00'){
          $shipment['early_delivery_eta'] = "08:00";
        }
        if((!isset($shipment['late_delivery_eta']) || empty($shipment['late_delivery_eta'])) || date('H:i', strtotime($shipment['late_delivery_eta'])) == '00:00'){
          $shipment['late_delivery_eta'] = '17:00';
        }
      }
    }
    return $shipment;
  }
  public static function etaOverride($shipment,$template,$mode){
    if(16 <= (date('H') + $shipment['timezone'] - self::OURTIMEZONE)){
      if($mode == 2){
        $firstReplace = '{AGENT_INFO}';
        $target = 'AGENT';
        $target2 = 'origin agent';
        switch(strtolower($template->msg_name)){
          case 'packdayeta':
            $template->msg_body = preg_replace('/\{PACK_ETA_TIME_LATE\}/i', $firstReplace,$template->msg_body);
            $replaceME = '{PACK_ETA_TIME_LATE}';
          break;
          case 'loaddayeta':
            $template->msg_body = preg_replace('/\{LOAD_ETA_TIME_LATE\}/i', $firstReplace, $template->msg_body);
            $replaceME = '{LOAD_ETA_TIME_LATE}';
            $target = 'HAULER';
            $target2 = 'hauler agent';
          break;
          case 'packloaddayeta':
            $this->data["msg_body"] = preg_replace('/\{PACK_LOAD_ETA_TIME_LATE\}/i', $firstReplace, $this->data["msg_body"]);
            $replaceME = '{PACK_LOAD_ETA_TIME_LATE}';
          break;
        }
        $replacement = $replaceME . "\n\nIf you need more information regarding your arrival time please contact your ";
        $replacement .= $target2 . ", {" . $target . "_NAME} at {" . $target . "_PHONE_NUMBER}";
        $template->msg_body = preg_replace('/\{AGENT_INFO\}/i', $replacement, $template->msg_body);
        $template->msg_body = self::transformer('AGENT_NAME','orig_agent_name',$shipment,$template->msg_body);
        $template->msg_body = self::transformer('AGENT_PHONE_NUMBER','orig_agent_phone',$shipment,$template->msg_body);
        $template->msg_body = self::transformer('HAULER_NAME','hauler_agent_name',$shipment,$template->msg_body);
        $template->msg_body = self::transformer('HAULER_PHONE_NUMBER','hauler_agent_phone',$shipment,$template->msg_body);
        return $template;
      }else{
        switch(strtolower($template->msg_name)){
          case 'loaddayeta':
            $agentInfo = self::getAgentInfo($shipment['hauler_agent_id']);
            $shipment['hauler_agent_name'] = $agentInfo['agent_name'];
            $shipment['hauler_agent_phone'] = $agentInfo['agent_phone'];
            try{
              self::validateIsset($shipment['hauler_agent_id']);
              self::validateIsset($shipment['hauler_agent_phone']);
            }catch(\Exception $e){
              throw new \Exception("Required hauler info " . $e->getMessage());
            }
          break;
          default:
            $agentInfo = self::getAgentInfo($shipment['orig_agent_id']);
            $shipment['orig_agent_name'] = $agentInfo['agent_name'];
            $shipment['orig_agent_phone'] = $agentInfo['agent_phone'];
            try{
              self::validateIsset($shipment['orig_agent_id']);
            }catch(\Exception $e){
              throw new \Exception("Required Origin Agent info " . $e->getMessage());
            }
        }
        if((!isset($shipment['pack_eta_early_time']) || empty($shipment['pack_eta_early_time'])) || date('H:i', strtotime($shipment['pack_eta_early_time'])) == '00:00'){
          $shipment['pack_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['pack_eta_late_time']) || empty($shipment['pack_eta_late_time'])) || date('H:i', strtotime($shipment['pack_eta_late_time'])) == '00:00'){
          $shipment['pack_eta_late_time'] = '17:00';
        }
        if((!isset($shipment['load_eta_early_time']) || empty($shipment['load_eta_early_time'])) || date('H:i', strtotime($shipment['load_eta_early_time'])) == '00:00'){
          $shipment['load_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['load_eta_late_time']) || empty($shipment['load_eta_late_time'])) || date('H:i', strtotime($shipment['load_eta_late_time'])) == '00:00'){
          $shipment['load_eta_late_time'] = '17:00';
        }
        return $shipment;
      }
    }
    return false;
  }
  public static function dayOverride($msg_name,$shipment){
    if($msg_name == "1stpackday"){
      if(13 <= (date('H') + $shipment['timezone'] - self::OURTIMEZONE)){
        if((!isset($shipment['pack_eta_early_time']) || empty($shipment['pack_eta_early_time'])) || date('H:i', strtotime($shipment['pack_eta_early_time'])) == '00:00'){
          $shipment['pack_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['pack_eta_late_time']) || empty($shipment['pack_eta_late_time'])) || date('H:i', strtotime($shipment['pack_eta_late_time'])) == '00:00'){
          $shipment['pack_eta_late_time'] = '17:00';
        }
      }
    }elseif($msg_name == 'packloadday'){
      if(13 <= (date('H') + $shipment['timezone'] - self::OURTIMEZONE)){
        if((!isset($shipment['pack_eta_early_time']) || empty($shipment['pack_eta_early_time'])) || date('H:i', strtotime($shipment['pack_eta_early_time'])) == '00:00'){
          $shipment['pack_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['pack_eta_late_time']) || empty($shipment['pack_eta_late_time'])) || date('H:i', strtotime($shipment['pack_eta_late_time'])) == '00:00'){
          $shipment['pack_eta_late_time'] = '17:00';
        }
        if((!isset($shipment['load_eta_early_time']) || empty($shipment['load_eta_early_time'])) || date('H:i', strtotime($shipment['load_eta_early_time'])) == '00:00'){
          $shipment['load_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['load_eta_late_time']) || empty($shipment['load_eta_late_time'])) || date('H:i', strtotime($shipment['load_eta_late_time'])) == '00:00'){
          $shipment['load_eta_late_time'] = '17:00';
        }
      }
    }else{
      if(10 <= (date('H') + $shipment['timezone'] - self::OURTIMEZONE)){
        if((!isset($shipment['pack_eta_early_time']) || empty($shipment['pack_eta_early_time'])) || date('H:i', strtotime($shipment['pack_eta_early_time'])) == '00:00'){
          $shipment['pack_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['pack_eta_late_time']) || empty($shipment['pack_eta_late_time'])) || date('H:i', strtotime($shipment['pack_eta_late_time'])) == '00:00'){
          $shipment['pack_eta_late_time'] = '17:00';
        }
        if((!isset($shipment['load_eta_early_time']) || empty($shipment['load_eta_early_time'])) || date('H:i', strtotime($shipment['load_eta_early_time'])) == '00:00'){
          $shipment['load_eta_early_time'] = '08:00';
        }
        if((!isset($shipment['load_eta_late_time']) || empty($shipment['load_eta_late_time'])) || date('H:i', strtotime($shipment['load_eta_late_time'])) == '00:00'){
          $shipment['load_eta_late_time'] = '17:00';
        }
      }
    }
    return $shipment;
  }
  public static function transformer($pattern,$replacement,$shipment,$msg_body){
    if(preg_match('/date/',$replacement) && !in_array($replacement,self::$_dateTimeBlackList)){
      $replace = date('m/d/y',strtotime($shipment[$replacement]));
      $msg_body = preg_replace('/\{' . $pattern . '\}/i', $replace, $msg_body);
    }elseif(preg_match('/time/',$replacement) || in_array($replacement,self::$_timeInclude)){
      $replace = date('H:i:s', strtotime($shipment[$replacement]));
      $msg_body = preg_replace('/\{' . $pattern . '\}/i', $replace, $msg_body);
    }elseif(in_array($replacement,self::$_dateTimeBlackList)){
      $replace = date('m/d/y H:i:s', strtotime($shipment[$replacement]));
      $msg_body = preg_replace('/\{' . $pattern . '\}/i', $replace, $msg_body);
    }else{
      $msg_body = preg_replace('/\{' . $pattern . '\}/i', $shipment[$replacement], $msg_body);
    }
    return $msg_body;
  }
  public static function validatePhoneNumbers($shipment){
    foreach(self::$_phoneFields as $phoneField){
      try{
        $phoneData = Messenger::verify(preg_replace('/[\|\s]/','',$shipment[$phoneField]));
        if($phoneData->mms_address && strtolower($phoneData->wless) == 'y'){
          //todo about sendToarr??
          if(!Messenger::isSaved($shipment[$phoneField])){
            try{
              Messenger::saveNumber($shipment[$phoneField],$phoneData->sms_address,$phoneData->mms_address,$phoneData->carrier_name,$phoneData->wless,$phoneField);
            }catch(\Exception $e){
              throw new \Exception($e->getMessage());
            }
          }
        }
      }catch(\Exception $e){
        throw new \Exception($e->getMessage());
      }
    }
    return $shipment; // don't actually know what to return yet
  }
}
