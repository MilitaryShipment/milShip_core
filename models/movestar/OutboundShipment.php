<?php namespace MoveStar;

require_once __DIR__ . '/../../record.php';

class OutBoundShipment extends \Record{

    const DRIVER = 'mssql';
    const DB = 'ezshare';
    const TABLE = 'ref_movestar';
    const PRIMARYKEY = 'gbl_dps';

    public $id;
    public $gbl_dps;
    public $rank;
    public $flag_name;
    public $flag_name_status;
    public $hauler_carrier_id;
    public $actual_weight;
    public $driver_eta_date;
    public $driver_id;
    public $driver_name;
    public $driver_cell;
    public $sitdate;
    public $gross_weight;
    public $tare_weight;
    public $net_weight;
    public $request_rewigh;
    public $progear_weight;
    public $spouse_progear_weight;
    public $releasing_agent_name;
    public $releasing_agent_phone;
    public $receiving_agent_name;
    public $receiving_agent_phone;
    public $actual_pack_date;
    public $actual_pickup_date;
    public $actual_delivery_date;
    public $haul_agent_phone;
    public $haul_agent_id;
    public $haul_agent_name;
    public $dest_address;
    public $dest_state;
    public $dest_city;
    public $dest_zip;
    public $dest_county;
    public $dest_primary_phone;
    public $dest_secondary_phone;
    public $dest_primary_email;
    public $orig_address;
    public $orig_state;
    public $orig_city;
    public $orig_zip;
    public $orig_county;
    public $orig_primary_phone;
    public $orig_secondary_phone;
    public $orig_primary_email;
    public $arrival_date;
    public $hasExtraPickup;
    public $flw_policy;
    public $notes;
    public $shipper_satisfied;
    public $orig_military_housing;
    public $dest_military_housing;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $last_updated_date;
    public $status_id;
    public $is_text_opt_out;
    public $is_firearm;
    public $premove_survey_date;
    public $is_premove_received;


    public function __construct($gbl_dps){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$gbl_dps);
    }
}
