<?php

require_once __DIR__ . "/../../record.php";

class ZipLocation extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_zip_location';
    const PRIMARYKEY = 'zip';

    public $zip;
    public $city;
    public $state;
    public $county;
    public $country;
    public $latitude;
    public $longitude;
    public $timezone;
    public $dst;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
