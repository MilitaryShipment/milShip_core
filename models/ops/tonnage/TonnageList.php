<?php

require_once __DIR__ . '/TonnageShipment.php';

class TonnageList{

    public $shipments = array();

    public function __construct(){
        $this->_build();
    }

    protected function _build(){
        $results = $GLOBALS['db']
            ->suite(TonnageShipment::DRIVER)
            ->driver(TonnageShipment::DRIVER)
            ->database(TonnageShipment::DATABASE)
            ->table(TonnageShipment::TABLE)
            ->select("id")
            ->where("(hauler = 'M431' OR hauler = '0')")
            ->andWhere("code_haul","!=","'A'")
            ->andWhere("code_haul","!=","'P'")
            ->andWhere("status_id","=","1")
            ->orderBy("id desc, pickup asc")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $this->shipments[] = new TonnageShipment($row['id']);
        }
        for($i = 0; $i < count($this->shipments);$i++){
            $this->shipments[$i]->rdd = date('m/d/Y',strtotime($this->shipments[$i]->rdd));
            $this->shipments[$i]->pickup = date('m/d/Y',strtotime($this->shipments[$i]->pickup));
        }
        return $this;
    }
}
