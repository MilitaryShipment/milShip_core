<?php

require_once __DIR__ . '/../../record.php';
require_once __DIR__ . '/../billing/EpayImage.php';

class Cog{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_agents';

    public $cogId;
    public $agents = array();

    public function __construct($cogId = null)
    {
        if(!is_null($cogId)){
            $this->cogId = $cogId;
            $this->_build();
        }
    }
    protected function _build(){
        $ids = array();
        $results = $GLOBALS['db']
			->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select("agentid_number")
            ->where("common_owner_groupid","=",$this->cogId)
            ->get();
        if(!sqlsrv_num_rows($results)){
            throw new Exception('Invalid Cog ID');
        }
        while($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC)){
            $ids[] = $row['agentid_number'];
        }
        foreach($ids as $id){
            $this->agents[] = new Agent($id);
        }
        return $this;
    }
    public function getEpayImages(){
        $ids = array();
        $data = array();
        $results = $GLOBALS['db']
			->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(EpayImage::TABLE)
            ->select("id")
            ->where("common_owner_group = '$this->cogId'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row['id'];
        }
        foreach($ids as $id){
            $data[] = new EpayImage($id);
        }
        return $data;
    }
}
