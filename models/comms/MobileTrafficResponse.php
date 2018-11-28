<?php

require_once __DIR__ . '/../../record.php';

class MobileTrafficResponse extends Record{

    const DRIVER = 'mssql';
    const DB = 'SANDBOX';
    const TABLE = 'ctl_mobile_traffic_responses';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $cog;
    public $scac;
    public $page;
    public $members_name;
    public $rank;
    public $mc;
    public $agent_id;
    public $orig_agent_id;
    public $emailOA;
    public $dest_agent_id;
    public $driver_id;
    public $driver_name;
    public $driver_phone;
    public $driver_mobile;
    public $driver_eta_date;
    public $lumper_id;
    public $number_of_lumpers;
    public $lumpers_assigned;
    public $labor_name;
    public $labor_phone;
    public $labor_role;
    public $labor_status;
    public $load_eta_early_time;
    public $load_eta_late_time;
    public $delivery_eta_date;
    public $delivery_date_eta_early_time;
    public $delivery_date_eta_late_time;
    public $dispatcher_name;
    public $local_dispatch_cell;
    public $at_orig_agent_before_load;
    public $at_agent_eta_early;
    public $at_agent_eta_late;
    public $is_base;
    public $final_load_eta_date;
    public $pre_approvals;
    public $is_overflow;
    public $undeliverable;
    public $necessity_items_left;
    public $necessity_item_description;
    public $info;
    public $message;
    public $image_path;
    public $wait_time;
    public $gross_weight;
    public $tare_weight;
    public $net_weight;
    public $progear_weight;
    public $progear_spouse_weight;
    public $short_fuse_id;
    public $record_number;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;


    public function __construct($id = null)
    {
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
