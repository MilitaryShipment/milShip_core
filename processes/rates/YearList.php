<?php

class YearList{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const TABLE = 'dps_rates_peak';

    public $years = array();

    public function __construct()
    {
        $this->buildYearList();
    }
    private function buildYearList(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select("distinct year")
            ->get();
        if(!mssql_num_rows($results)){
            die('No years!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->years[] = $row["year"];
            }
        }
        return $this;
    }
}
