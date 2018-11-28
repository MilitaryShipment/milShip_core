<?php

require_once __DIR__ . '/../../record.php';

class MobileResponse extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_mobile_responses';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $page;
    public $info;
    public $cog;
    public $scac;
    public $members_name;
    public $rank;
    public $mc;
    public $text_opt_out;
    public $undeliverable;
    public $orig_addr;
    public $extra_pickup;
    public $orig_authorized_individual;
    public $orig_authorized_individual_phone;
    public $orig_military_housing;
    public $shipment_type;
    public $dest_addr;
    public $dest_address_text;
    public $dest_authorized_individual;
    public $dest_authorized_individual_phone;
    public $gun_safe;
    public $firearms;
    public $est_weight;
    public $progear;
    public $progear_weight;
    public $rooms;
    public $sq_footage;
    public $oversize;
    public $oversize_items;
    public $washing_machine;
    public $bolts;
    public $arrival_date;
    public $military_housing;
    public $debris_removal;
    public $origin_tractor;
    public $dest_tractor;
    public $sit_delivery;
    public $sit_delivery_date;
    public $tractor_why;
    public $pack_service;
    public $delivery_serivce;
    public $load_service;
    public $overall_service;
    public $on_time_crew;
    public $damage_crew;
    public $amc_damage;
    public $missing_items;
    public $address;
    public $city;
    public $state;
    public $zip;
    public $pg_ref_material;
    public $pg_instruments;
    public $pg_specialized_clothing;
    public $pg_communications_equipment;
    public $pg_field_clothing;
    public $pg_service_componet_clothing;
    public $pg_weight;
    public $pg_spouse_weight;
    public $dependents;
    public $request_reweigh;
    public $delivery_service_loading;
    public $delivery_service_delivery;
    public $on_time_crew_load;
    public $on_time_crew_delivery;
    public $is_delivery_eta;
    public $mc_rating;
    public $generation;
    public $is_paypal;
    public $reviewed;
    public $notify_claims;
    public $base_time_wait;
    public $new_rdd;
    public $survey_date;
    public $exported;
    public $record_number;
    public $training;
    public $delivery_overall_move;
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
