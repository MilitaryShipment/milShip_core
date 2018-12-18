<?php

require_once __DIR__ . '/../../../record.php';

class TonnageRef extends Record{
    const DRIVER = 'mssql';
    const DATABASE = 'Sandbox';
    const TABLE = 'ref_tonnage_nearby';
    const PRIMARYKEY = 'tonnageId';

    public $id;
    public $tonnageId;
    public $near_origin;
    public $near_destination;
    public $on_the_way;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DATABASE,self::TABLE,self::PRIMARYKEY,$id);
        if(!is_null($id)){
            $this->_init();
        }
    }
    protected function _build(){
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select("*")
            ->where("tonnageId = '$this->id'")
            ->get();
        if(!mssql_num_rows($results)){
            throw new Exception('Invalid Record ID');
        }
        while($row = mssql_fetch_assoc($results)){
            foreach($row as $key=>$value){
                $this->$key = $value;
            }
        }
        return $this;
    }
    protected function _buildId(){
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->orderBy("id desc")
            ->take(1)
            ->get("value");
        $this->id = $results;
        return $this;
    }
    protected function _init(){
        $this->near_origin = explode(',',$this->near_origin);
        $this->near_destination = explode(',',$this->near_destination);
        $this->on_the_way = explode(',',$this->on_the_way);
        return $this;
    }
    public function buildShipments($idArray){
        $data = array();
        foreach($idArray as $id){
            $data[] = new TonnageShipment($id);
        }
        return $data;
    }
}
