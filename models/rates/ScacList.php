<?php

class ScacList{


    const TABLE = 'dps_rates_peak';
    public $scacs = array();

    public function __construct()
    {
        $this->buildScacList();
    }
    private function buildScacList(){
        $results = $GLOBALS['db']
            ->database(DATABASE)
            ->table(self::TABLE)
            ->select("distinct scac")
            ->orderBy("scac")
            ->get();
        if(!mssql_num_rows($results)){
            die('No Scacs!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->scacs[] = $row["scac"];
            }
        }
        return $this;
    }
}
