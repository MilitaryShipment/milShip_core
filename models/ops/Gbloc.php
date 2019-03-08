<?php

require_once __DIR__ . '/../../record.php';

class Gbloc extends Record{

    public static $tamiData = array(
      "base_name",
      "ppso_inbound_phone",
      "e_mail_billing",
      "e_mail_cust_serv",
      "e_mail_dispatch",
      "e_mail_registrations",
      "ppso_email_tqap"
    );

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_gbloc';
    const PRIMARYKEY = 'gbloc';

    public $id;
    public $base_name;
    public $base_description;
    public $state;
    public $gbloc;
    public $area;
    public $gloc_2char;
    public $ppso_mail_name;
    public $ppso_address1;
    public $ppso_address2;
    public $ppso_city;
    public $ppso_state;
    public $ppso_zip;
    public $ppso_email_tqap;
    public $ppso_email_validation_status;
    public $ppso_email_validation_date;
    public $ppso_ivr_phone;
    public $ppso_inbound_phone;
    public $ppso_outbound_phone;
    public $ppso_nts_phone;
    public $ppso_qaqc_phone;
    public $ppso_to_phone;
    public $ppso_tmo_phone;
    public $ppso_tqap_home;
    public $ppso_county;
    public $ppso_inbound_fax;
    public $ppso_outbound_fax;
    public $ppso_address3;
    public $ppso_ivr_phone_ext;
    public $ppso_inbound_phone_ext;
    public $ppso_outbound_phone_ext;
    public $ppso_nts_phone_ext;
    public $ppso_qaqc_phone_ext;
    public $ppso_to_phone_ext;
    public $ppso_tqap_phone_ext;
    public $ppso_ivr_fax;
    public $ppso_nts_fax;
    public $ppso_qaqc_fax;
    public $ppso_to_fax;
    public $ppso_tmo_fax;
    public $ppso_tqap_fax;
    public $tqapsuper_login;
    public $base_physical_zipcode;
    public $service_states;
    public $unigroup_area;
    public $combo_gbloc_area;
    public $power_track_base;
    public $auto_send_oa_paperwork;
    public $comments;
    public $comments_2;
    public $comments_3;
    public $comments_4;
    public $comments_5;
    public $comments_6;
    public $comments_7;
    public $comments_8;
    public $field_69;
    public $dps_base;
    public $effective_date;
    public $e_mail_claims;
    public $e_mail_billing;
    public $e_mail_registrations;
    public $e_mail_cust_serv;
    public $e_mail_dispatch;
    public $e_mail_sales;
    public $e_mail_accounting;
    public $e_mail_doc_con;
    public $dps_base_status_id;
    public $record_number;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;


    public function __construct($gbloc = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,$gbloc);
    }
    public static function getTamiData($gbloc,$area){
      $data = array();
      $results = $GLOBALS['db']
          ->suite(self::DRIVER)
          ->driver(self::DRIVER)
          ->database(self::DB)
          ->table(self::TABLE)
          ->select(implode(","self::$tamiData))
          ->where("gbloc","=","'" . $gbloc . "'")
          ->andWhere("area","=","'" . $area . "'")
          ->andWhere("dps_base_status_id","=",1)
          ->andWhere("status_id","=",1)
          ->get();
      if(!mssql_num_rows($results)){
        throw new \Exception('No Tami data available');
      }
      while($row = mssql_fetch_assoc($results)){
        $data[] = $row;
      }
      return $data;
    }
}
