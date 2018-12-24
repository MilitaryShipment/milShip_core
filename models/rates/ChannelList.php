<?php

class ChannelList{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const TABLE = 'dps_rates_peak';

    public $channels = array();

    public function __construct()
    {
        $this->buildChannels();
    }
    private function buildChannels(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select("distinct domestic_rate_area_code")
            ->orderBy("domestic_rate_area_code")
            ->get();
        if(!mssql_num_rows($results)){
            die('There are not Channels!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->channels[] = $row["domestic_rate_area_code"];
            }
        }
        return $this;
    }
}
