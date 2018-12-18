<?php

require_once __DIR__ . "/../../../record.php";

class TonnageShipment extends Record{

    const DRIVER = 'mssql';
    const DATABASE = 'ezshare';
    const TABLE = 'tbl_tonnage';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $order_number;
    public $order_number_dash;
    public $full_name;
    public $orig_city;
    public $orig_state;
    public $orig_lat;
    public $orig_lng;
    public $dest_city;
    public $dest_state;
    public $dest_lat;
    public $dest_lng;
    public $miles;
    public $pack;
    public $pickup;
    public $in_whs;
    public $rdd;
    public $weight;
    public $actual_weight;
    public $ulh;
    public $ilh;
    public $filed_rate;
    public $code_haul;
    public $hauler;
    public $bonus;
    public $notes;
    public $booked;
    public $viewable;
    public $viewable_type;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DATABASE,self::PRIMARYKEY,self::TABLE,$id);
    }
}
