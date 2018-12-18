<?php

require_once __DIR__ . "/../../record.php";


class TonnageShipment extends Record{

    const DRIVER = 'mssql';
    const DATABASE = 'Sandbox';
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
class TonnageSearch{

    /*Things I should be able to do:
     *
     * Between pickup dates
     * Between Rdds
     * Multi State
     * Origin State
     * Destination State
     * Order Number
     * Lane
     *
     * */
    const MSSQL = 'mssql';
    const SANDBOX = 'Sandbox';
    const TONNAGE = 'tbl_tonnage';

    public $results;
    protected $availableKeys = array(
        "pickup",
        "rdd",
        "origin",
        "destination",
        "order_number",
        "lane"
    );
    protected $key;
    protected $values;

    public function __construct($key,$values){
        if(!in_array($key,$this->availableKeys)){
            throw new Exception('Unsupported Search Key');
        }
        $this->key = $key;
        $this->values = $values;
    }
    protected function searchPickup(){
        if(count($this->values) == 0){
            $start = date('m/d/Y');
            $finish = date('m/d/Y');
        }elseif(count($this->values) == 1){
            $start = date('m/d/Y',strtotime($this->values[0]));
            $finish = date('m/d/Y',strtotime($this->values[0]));
        }else{
            $start = date('m/d/Y',strtotime($this->values[0]));
            $finish = date('m/d/Y',strtotime($this->values[1]));
        }
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::TONNAGE)
            ->select("id")
            ->where("cast(pickup as date) BETWEEN cast('$start' as date) and cast('$finish' as date)")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new TonnageShipment($row['id']);
        }
        return $data;
    }
    protected function searchRdd(){
        if(count($this->values) == 0){
            $start = date('m/d/Y');
            $finish = date('m/d/Y');
        }elseif(count($this->values) == 1){
            $start = date('m/d/Y',strtotime($this->values[0]));
            $finish = date('m/d/Y',strtotime($this->values[0]));
        }else{
            $start = date('m/d/Y',strtotime($this->values[0]));
            $finish = date('m/d/Y',strtotime($this->values[1]));
        }
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::TONNAGE)
            ->select("id")
            ->where("cast(rdd as date) BETWEEN cast('$start' as date) and cast('$finish' as date)")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new TonnageShipment($row['id']);
        }
        return $data;
    }
    protected function searchLocation($origin = true){
        if($origin){
            $inStr = 'orig_state in (';
        }else{
            $inStr = 'dest_state in (';
        }
        $data = array();
        $max = count($this->values);
        foreach($this->values as $value){
            if(--$max){
                $inStr .= "'$value',";
            }else{
                $inStr .= "'$value'";
            }
        }
        $inStr .= ")";
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::TONNAGE)
            ->select("id")
            ->where($inStr)
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new TonnageShipment($row['id']);
        }
        return $data;
    }
    protected function searchOrderNumber(){
        $reg = array();
        $dashed = array();
        $data = array();
        $max = count($this->values);
        $inStr = "order_number in (";
        $inDash = "order_number_dash in (";
        foreach($this->values as $value){
            if(preg_match("/-/",$value)){
                $dashed[] = $value;
            }else{
                $reg[] = $value;
            }
        }
        $max = count($dashed);
        foreach($dashed as $dash){
            if(--$max){
                $inDash .= "'$dash',";
            }else{
                $inDash .= "'$dash'";
            }
        }
        $inDash .= ")";
        $max = count($reg);
        foreach($reg as $r){
            if(--$max){
                $inStr .= "'$r',";
            }else{
                $inStr .= "'$r'";
            }
        }
        $inStr .= ")";
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::TONNAGE)
            ->select("id")
            ->where($inStr)
            ->orWhere($inDash)
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new TonnageShipment($row['id']);
        }
        return $data;
    }
    protected function searchLane(){
        $data = array();
        $origin = $this->values[0];
        $destination = $this->values[1];
        $start = date('m/d/Y',strtotime($this->values[2]));
        if(!isset($this->values[3])){
            $finish = date('m/d/Y',strtotime($this->values[2]));
        }else{
            $finish = date('m/d/Y',strtotime($this->values[3]));
        }
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::TONNAGE)
            ->select("id")
            ->where("orig_state = '$origin'")
            ->andWhere("dest_state = '$destination'")
            ->andWhere("cast(pickup as date) BETWEEN cast('$start' as date) and cast('$finish' as date)")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new TonnageShipment($row['id']);
        }
        return $data;
    }
    public function search(){
        switch($this->key){
            case "pickup":
                return $this->searchPickup();
                break;
            case "rdd":
                return $this->searchRdd();
                break;
            case "origin":
                return $this->searchLocation(true);
                break;
            case "destination":
                return $this->searchLocation(false);
                break;
            case "order_number":
                return $this->searchOrderNumber();
                break;
            case "lane":
                return $this->searchLane();
                break;
            default:
                throw new Exception('Unsupported Search Key');
        }
    }

}

//$t = new TonnageSearch('origin',array('TN'));
//print_r($t->search());
