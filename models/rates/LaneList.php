<?php

class LaneList{

    const DRIVER = 'mssql';
    const DATABASE = 'test';
    const TABLE = 'dps_rates_peak';

    public $lanes = array();

    public function __construct()
    {
        $this->buildLaneList();
    }
    private function buildLaneList(){
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select("distinct lane")
            ->orderBy("lane")
            ->get();
        if(!mssql_num_rows($results)){
            die('No Lanes!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->lanes[] = strtoupper($row["lane"]);
            }
        }
        asort($this->lanes);
        return $this;
    }
}
