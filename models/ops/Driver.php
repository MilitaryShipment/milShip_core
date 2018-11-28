<?php

require_once __DIR__ . '/../../record.php';
require_once __DIR__ . '/../comms/Notification.php';
require_once __DIR__ . '/../comms/MobileTrafficResponse.php';
require_once __DIR__ . '/Dispatcher.php';
require_once __DIR__ . '/Lumper.php';
require_once __DIR__ . '/Shipment.php';

class Driver extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_drivers';
    const PRIMARYKEY = 'driver_id';

    public $id;
    public $driver_id;
    public $cog_id;
    public $agent_id;
    public $van_id;
    public $vo_id;
    public $vendor;
    public $first_name;
    public $last_name;
    public $mobile;
    public $mobile_validation_status;
    public $mobile_validation_date;
    public $cell_number;
    public $phone_number;
    public $email_address;
    public $signature;
    public $agent_name;
    public $agent_rating;
    public $driver_rating;
    public $hhgd_rating;
    public $prev_unigrp_rating;
    public $unigrp_rating;
    public $star_rating;
    public $rating_eff_date;
    public $ratings_thru_date;
    public $rating_clm;
    public $rating_scr;
    public $raw_score;
    public $icss_score;
    public $qualified;
    public $driver_type;
    public $van_type;
    public $trailer_type;
    public $on_time;
    public $claims;
    public $safety;
    public $liab;
    public $authority;
    public $carb_compliant;
    public $miles;
    public $line_haul;
    public $shipment_hd;
    public $company_driver;
    public $customer_survey;
    public $survey;
    public $comments;
    public $update;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($driver_id = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$driver_id);
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
            ->where($key,"=",$value)
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
    public function getNotifications(){
        return Notification::get('driver_id',$this->driver_id);
    }
    public function getResponses(){
        return MobileTrafficResponse::get('driver_id',$this->driver_id);
    }
    public function getShipments($option){
        return Shipment::get('driver_id',$this->driver_id,$option);
    }
    public function getLumpers(){
        return Lumper::get('driver_id',$this->driver_id);
    }
    public function getDispatchers(){
        return Dispatcher::get('agent_id',$this->agent_id);
    }
    public function getSettlements(){
        return Settlement::get('vendor_id',$this->vendor);
    }
}
class Settlement extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_drivers_settlements';
    const PRIMARYKEY = 'id';

    public $id;
    public $guid;
    public $vendor_id;
    public $path_to_image;
    public $filename;
    public $doc_date;
    public $doc_timestamp;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
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
            ->where($key,"=",$value)
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
