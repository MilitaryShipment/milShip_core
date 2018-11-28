<?php namespace Amc;

require_once __DIR__ . '/../../record.php';

class Demand extends \Record{

    const DRIVER = 'mssql';
    const DB = 'AfterMoveCare';
    const TABLE = 'tbl_amc_demand';
    const PRIMARYKEY = 'record_number';

    public $record_number;
    public $demand_id;
    public $type;
    public $need_name;
    public $claim_number;
    public $received_date;
    public $base_zip;
    public $salvage_yesno;
    public $request_date;
    public $completed;
    public $due_date;
    public $delay_date;
    public $deny_date;
    public $salvage_amount;
    public $claim_amount;
    public $other_amount;
    public $offer_amount;
    public $status;
    public $date_paid;
    public $amount_paid;
    public $settle_pct;
    public $refund_amount;
    public $set_off_amount;
    public $set_off_date;
    public $admin_date;
    public $amount_requested;
    public $adjuster;
    public $agent_id;
    public $agent_liability;
    public $notes;
    public $ms_status;
    public $inspector_amount;
    public $inspector_date_paid;
    public $inspector_appointment_date;
    public $gbl_number;
    public $is_frv_claim;
    public $salvage_permitted;
    public $total_liability;
    public $less_salvage;
    public $total_offer;
    public $packer_charge_totals;
    public $hauler_one_charge_totals;
    public $hauler_two_charge_totals;
    public $storage_charge_totals;
    public $other_charge_totals;
    public $packer_liability_totals;
    public $hauler_one_liability_totals;
    public $hauler_two_liability_totals;
    public $storage_liability_totals;
    public $other_liability_totals;
    public $inspector_id;
    public $inspector_contact;
    public $inspector_init_date;
    public $inspector_due_date;
    public $inspector_suspense_date;
    public $inspector_completed_date;
    public $inspector_assigned_by;
    public $inspector_status;
    public $chargeback_packer_liability_amount;
    public $chargeback_hauler_one_liability_amount;
    public $chargeback_hauler_two_liability_amount;
    public $chargeback_storage_liability_amont;
    public $chargeback_other_liability_amount;
    public $css_score;
    public $dps_claims_score;
    public $items_to_mco;
    public $full_transfer_to_mco;

    public function __construct($record_number = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$record_number);
    }
    private function _getRecordNumber(){
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->orderBy(self::PRIMARYKEY . " desc")
            ->take(1)
            ->get('value');
        $this->record_number = $results + 1;
        return $this;
    }
    public function create(){
        $this->_getRecordNumber();
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'created_date' || $key == 'updated_date'){
                $upData[$key] = date("m/d/Y H:i:s");
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->$key;
            }
        }
        unset($upData['id']);
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->data($upData)
            ->insert()
            ->put();
        $this->_buildId()->_build();
        return $this;
    }
}
