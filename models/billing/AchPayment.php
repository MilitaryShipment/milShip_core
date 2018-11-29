<?php

require_once __DIR__ . '/../../record.php';

class AchPayment extends Record{

  const DRIVER = 'mssql';
  const DB = 'Sandbox';
  const TABLE = 'tbl_ach_batch';
  const PRIMARYKEY = 'id';

  public $id;
  public $batch_date;
  public $company_number;
  public $filename;
  public $vendor_id;
  public $vendor;
  public $amount;
  public $text;
  public $guid;
  public $created_date;
  public $created_by;
  public $updated_date;
  public $updated_by;
  public $status_id;

  public function __construct($id = null){
    parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }
  public static function get($key,$value){
      $data = array();
      $ids = array();
      $results = $GLOBALS['db']
          ->suite(self::DRIVER)
          ->driver(self::DRIVER)
          ->database(self::DB)
          ->table(self::TABLE)
          ->select(self::PRIMARYKEY)
          ->where($key,"=","'" . $value . "'")
          ->get();
      while($row = mssql_fetch_assoc($results)){
          $ids[] = $row[self::PRIMARYKEY];
      }
      foreach($ids as $id){
          $data[] = new self($id);
      }
      return $data;
  }

}
