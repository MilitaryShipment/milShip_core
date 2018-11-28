<?php namespace Amc;

require_once __DIR__ . '/../../record.php';
require_once __DIR__ . '/LineItem.php';

class Claim extends \Record{

    const DRIVER = 'mssql';
    const DB = 'AfterMoveCare';
    const TABLE = 'tbl_amc_hdr';
    const PRIMARYKEY = 'id';
    const PARENTKEY = 'control_number';

    public $id;
    public $guid;
    public $record_number;
    public $claim_id;
    public $gbl_number;
    public $registration_number;
    public $scac_id;
    public $scac_name;
    public $pu_date;
    public $d1840_date;
    public $d1840_clean;
    public $altd1840_date;
    public $altd1840_clean;
    public $d1840r_date;
    public $d1840r_clean;
    public $altd1840r_date;
    public $altd1840r_clean;
    public $days;
    public $alt_days;
    public $legal_zip;
    public $date;
    public $first_name;
    public $last_name;
    public $d_addr;
    public $d_city;
    public $d_state;
    public $d_zip;
    public $d_county;
    public $home_phone;
    public $work_phone;
    public $cell_phone;
    public $e_mail;
    public $rank;
    public $branch;
    public $d1840_com_date;
    public $adjuster;
    public $pack_id;
    public $pack_liability;
    public $haul1_id;
    public $haul1_liability;
    public $haul2_id;
    public $haul2_liability;
    public $hauler_carrier_id;
    public $hauler_carrier_liability;
    public $stg1_id;
    public $stg1_liability;
    public $other_id;
    public $other_liability;
    public $missing_1840;
    public $initial_1840;
    public $due_1840;
    public $missing_1840r;
    public $initial_1840r;
    public $due_1840r;
    public $inspector_id;
    public $inspector_contact;
    public $inl_date;
    public $in_due_date;
    public $in_suspense_date;
    public $in_complete_date;
    public $ws_line;
    public $main_notes;
    public $tracer_notes;
    public $insp_notes;
    public $cback_notes;
    public $ship_cost;
    public $freight_notes;
    public $cost_pound;
    public $claim_adjustment_total_;
    public $pu_fwl_unearned_freight_liability;
    public $h1_ufw;
    public $h1_ufwl;
    public $h2_ufwl;
    public $su_fw;
    public $su_fwl;
    public $o_ufw;
    public $o_ufwl;
    public $cwt;
    public $actual_weight;
    public $weight_calculated;
    public $nts_year;
    public $frv_type;
    public $salvage_yn;
    public $on_ts;
    public $d1840_amount;
    public $altd1840_amount;
    public $d1840r_amount;
    public $altd1840r_amount;
    public $ws_liability;
    public $ws_less_salvage;
    public $ws_total_offer;
    public $in_adjuster;
    public $claim_type;
    public $work_email;
    public $inconvenience_notes;
    public $residence_damage_notes;
    public $ws_pickup_year;
    public $ws_pack_liab;
    public $ws_h1_liab;
    public $ws_h2_liab;
    public $ws_stg_liab;
    public $ws_other_liab;
    public $ms_dot_com_claim;
    public $ws_tot_packer;
    public $ws_tot_hauler1;
    public $ws_tot_hauler2;
    public $ws_tot_storage;
    public $ws_tot_other;
    public $css_score;
    public $tops_formatted_dps_gblno;
    public $address2;
    public $her_cell_hone;
    public $her_email;
    public $her_work_email;
    public $send_dps_fyi;
    public $send_dps_fyi_date;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
    public function getLineItems(){
        $lineItems = array();
        $ids = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(LineItem::TABLE)
            ->select(self::PRIMARYKEY)
            ->where(self::PARENTKEY . " = '$this->claim_id'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $lineItems[] = new LineItem($id);
        }
        return $lineItems;
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
