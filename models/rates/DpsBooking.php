<?php

require_once __DIR__ . '/../../record.php';


class DpsBooking extends \Record{

  const DRIVER = 'mssql';
  const DB = 'test';
  const TABLE = 'dps_rates_bookings';
  const PRIMARYKEY = 'id';

  public function __construct($id = null){
    parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
  }
}
