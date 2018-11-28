<?php namespace Amc;

require_once __DIR__ . '/../../record.php';

class LineItem extends \Record{

    const DRIVER = 'mssql';
    const DB = 'AfterMoveCare';
    const TABLE = 'tbl_amc_details';
    const PRIMARYKEY = 'record_number';

    public $record_number;
    public $tid_number;
    public $control_number;
    public $gbl_number;
    public $registration_number;
    public $control_number_reg;
    public $shippers_full_name;
    public $claim_number;
    public $agent_id;
    public $agent_vendor_number;
    public $total_detail_line_items;
    public $column_number;
    public $line_item_number;
    public $inventory_number;
    public $article_description;
    public $a1840_1840r_listed;
    public $year_purchase;
    public $repair_amount;
    public $replacement_amount;
    public $base_depreciation;
    public $total_depreciation;
    public $value_amount;
    public $carrier_estimate_amount;
    public $offer_amount;
    public $settlementcode;
    public $aa25_pct_salvage;
    public $weight;
    public $liability;
    public $charge_code;
    public $note_id;
    public $carton_available;
    public $original_purchase_cost;
    public $aaa;
    public $packer_amount;
    public $aaaa;
    public $hauler_1_amount;
    public $aaaaa;
    public $hauler_2_amount;
    public $aaaaaa;
    public $storage_1_amount;
    public $aaaaaaa;
    public $storage_2_amount;
    public $aaaaaaaa;
    public $other_1_amount;
    public $aaaaaaaaa;
    public $aaaaaaaaaa;
    public $aaaaaaaaaaa;
    public $aaaaaaaaaaaa;
    public $aaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaa;
    public $agent_rebutted;
    public $govt_rebutted;
    public $requested_amount;
    public $govt_notes;
    public $agent_notes;
    public $inspector_id_vendor;
    public $a1_description_of_damage_1;
    public $aaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaa;
    public $image1_path;
    public $image1_thumbnail_path;
    public $image2_path;
    public $image2_thumbnail_path;
    public $aaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaa;
    public $cash_settlement;
    public $repair_cost_16;
    public $inspection_fee;
    public $aaaaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaaaaaaaa;
    public $aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa;
    public $inspection_detail;
    public $notes_to_government;
    public $notes_to_agent;

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
