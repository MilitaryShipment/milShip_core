<?php

require_once __DIR__ . '/../../record.php';

class Trigger extends Record{

  const DRIVER = 'mssql';
  const DB = 'Sandbox';
  const TABLE = 'tbl_trigger_message';
  const PRIMARYKEY = 'trigger_id';

  public $trigger_id;
  public $gbl_dps;
  public $message_name;
  public $send_message;
  public $error_code;
  public $error_msg;
  public $created_date;
  public $created_by;
  public $updated_date;
  public $updated_by;
  public $guid;
  public $status_id;

  public function __construct($id = null){
    parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }
  public static function hasDriverEtaChanged($gbl_dps){
    $results = $GLOBALS['db']
          ->suite(self::DRIVER)
          ->driver(self::DRIVER)
          ->database(self::DB)
          ->table(self::TRIGGER)
          ->select('send_message')
          ->take(1)
          ->where("gbl_dps","=","'" . $gbl_dps . "'")
          ->andWhere("send_message","=",1)
          ->orderBy('created_date desc')
          ->get();
    if(!mssql_num_rows($results)){
      return false;
    }
    while($row = mssql_fetch_assoc($results)){
      $trigger = new Self($row[self::PRIMARYKEY]);
    }
    $trigger->send_message = 0;
    $trigger->update();
    return true;
  }
}
