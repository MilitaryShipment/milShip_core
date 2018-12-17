<?php

require_once __DIR__ . '/../../record.php';

class AgentInquiry extends Record{

  const DRIVER = 'mssql';
  const DB = 'Sandbox';
  const TABLE = 'tbl_webusers';
  const PRIMARYKEY = 'id';

  public $id;
  public $gbl_dps;
  public $first_name;
  public $last_name;
  public $email;
  public $phone;
  public $remarks;
  public $created_date;
  public $created_by;
  public $status_id;

  public function __construct($id = null)
  {
    parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }
}
