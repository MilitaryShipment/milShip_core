<?php

require_once __DIR__ . '/../../models/ops/Shipment.php';
require_once __DIR__ . '/../../models/ops/Gbloc.php';
require_once __DIR__ . '/../../models/comms/MobileResponse.php';
require_once __DIR__ . '/../../models/comms/Notification.php';

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
      throw new \Exception('Unable to locate any shipments for ' . $msg_name);
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
  public static function sentToday($msg_name,$gbl_dps){
    $objs = Notification::sentToday($msg_name,$gbl_dps);
    if(count($objs)){
      return true;
    }
    return false;
  }
  public static function premoveSurveyExists($gbl_dps){
    return is_file(self::PREMOVEDIR . "mobile_" . $gbl_dps . "_premoveSurvey.jpg");

  }
}
