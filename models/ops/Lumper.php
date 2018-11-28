<?php

require_once __DIR__ . '/../../record.php';


class Lumper extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_mobile_traffic_labor';
    const PRIMARYKEY = 'lumper_id';

    public $id;
    public $lumper_id;
    public $gbl_dps;
    public $gbl_confirmed;
    public $gbl_pending;
    public $gbl_history;
    public $labor_name;
    public $mobile;
    public $mobile_validation_status;
    public $mobile_validation_date;
    public $email;
    public $email_validation_status;
    public $email_validation_date;
    public $alias;
    public $labor_phone;
    public $labor_status;
    public $service_area;
    public $service_area_city;
    public $service_area_state;
    public $service_radius;
    public $role;
    public $agent_id;
    public $driver_id;
    public $redfile_incentive;
    public $star_rating;
    public $selfie_path;
    public $ref_points;
    public $comments;
    public $referred_by;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_date;
    public $bgs_hash;
    public $bgs_status;
    public $status_id;
    public $first_name;
    public $last_name;

    public function __construct($lumper_id = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$lumper_id);
    }
    public static function createId(){
        $ids = array();
        $letter = chr(65+rand(0,25));
        $number = rand(999,9999);
        $lumperId = $letter . $number;
        $results = $GLOBALS['db']
			->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select("distinct lumper_id")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row['lumper_id'];
        }
        if(in_array($lumperId,$ids)){
            self::createId();
        }
        return $lumperId;
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
