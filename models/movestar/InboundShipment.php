<?php namespace MoveStar;

require_once __DIR__ . '/../../record.php';


class InBoundShipment extends \Record{

    const DRIVER = 'mssql';
    const DB = 'ezshare';
    const TABLE = 'tbl_shipment';
    const PRIMARYKEY = 'gbl_dps';

    public $id;
    public $gbl_dps;
    public $registration_date;
    public $shipment_management_date;
    public $registration_number;
    public $scac;
    public $mc_tid;
    public $driver_id;
    public $filed_rate;
    public $red_file;
    public $firearms;
    public $branch;
    public $rank;
    public $full_name;
    public $first_name;
    public $last_name;
    public $shipment_type;
    public $releasing_agent_name;
    public $releasing_agent_phone;
    public $receiving_agent_name;
    public $receiving_agent_phone;
    public $orig_agent_id;
    public $orig_address;
    public $orig_city;
    public $orig_zip;
    public $orig_county;
    public $orig_phone;
    public $secondary_phone;
    public $orig_primary_mobile;
    public $orig_secondary_mobile;
    public $primary_mobile;
    public $primary_email;
    public $secondary_email;
    public $dest_agent_id;
    public $dest_address;
    public $dest_city;
    public $dest_state;
    public $dest_zip;
    public $dest_county;
    public $gbloc_orig;
    public $gbloc_dest;
    public $dest_primary_phone;
    public $premove_survey_date;
    public $premove_received;
    public $requested_pack_date;
    public $requested_pickup_date;
    public $requested_latest_pickup_date;
    public $requested_delivery_date;
    public $pack_date;
    public $pickup_date;
    public $pickup_type;
    public $required_delivery_date;
    public $delivery_residence_date;
    public $g11_status;
    public $g11_authorized_date;
    public $g11_performed_date;
    public $sit_exp_date;
    public $sit_number;
    public $estimated_weight;
    public $actual_weight;
    public $gross_weight;
    public $tare_weight;
    public $progear;
    public $progear_weight;
    public $spouse_progear_weight;
    public $request_reweigh;
    public $reweigh_date;
    public $reweigh_gross_weight;
    public $reweigh_tare_weight;
    public $special_items;
    public $remarks;
    public $vehicles;
    public $shipper_satisfied;
    public $survey_date;
    public $tsp_score;
    public $miles;
    public $booked;
    public $rate;
    public $line_haul;
    public $status;
    public $eta;
    public $current_flags_list;
    public $driver_name;
    public $driver_phone;
    public $hauler_agent_id;
    public $scheduled_delivery;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $last_updated_date;
    public $status_id;

    public function __construct($gbl_dps){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$gbl_dps);
    }
}
