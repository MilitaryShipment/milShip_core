<?php

class RegionList{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const TABLE = 'dps_rates_peak';

    public $regions = array();

    public function __construct()
    {
        $this->buildRegions();
    }
    private function buildRegions(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select("distinct domestic_region_id")
            ->orderBy("domestic_region_id")
            ->get();
        if(!mssql_num_rows($results)){
            die('There are no Regions!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->regions[] = $row["domestic_region_id"];
            }
        }
        return $this;
    }
}
