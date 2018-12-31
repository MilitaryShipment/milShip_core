<?php

require_once __DIR__ . '/../../record.php';


class DpsBooking extends \Record{

  const DRIVER = 'mssql';
  const DB = 'test';
  const TABLE = 'dps_rates_bookings';
  const PRIMARYKEY = 'id';

  public $id;
  public $scac;
  public $gbl_dps;
  public $reg_number;
  public $rate;
  public $line_haul;
  public $lane;
  public $oa_state;
  public $da_state;
  public $reg_date;
  public $load_date;
  public $load_status;
  public $is_shortFuse;

  public function __construct($id = null){
    parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }
}
