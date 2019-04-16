<?php

require_once __DIR__ . "/../../record.php";

class PhoneNumber extends Record{

  const DRIVER = 'mssql';
  const DB = 'Sandbox';
  const TABLE = 'ctl_phone_carrier';
  const PRIMARYKEY = 'id';

  public $id;
  public $phone;
  public $sms;
  public $mms;
  public $carrier;
  public $wless;
  public $category;
  public $updated_date;
  public $last_name;
  public $first_name;

  public function __construct($id = null){
    parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }
  public static function exists($phoneNumber){
    $results = $GLOBALS['db']
        ->suite(self::DRIVER)
        ->driver(self::DRIVER)
        ->database(self::DB)
        ->table(self::TABLE)
        ->select(self::PRIMARYKEY)
        ->where("phone","=","'" . $phoneNumber . "'")
        ->get();
    if(!mssql_num_rows($results)){
      return false;
    }
    return true;
  }
}
