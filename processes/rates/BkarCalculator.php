<?php

require_once __DIR__ . '/../../models/rates/Lane.php';


class BkarCalculator{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const BKAR = 'dps_rates_bkar';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';

    private $year;
    private $round;

    private $lanes = array();
    private $combinedBkars = array();
    public $errors = array();

    public function __construct($year,$round)
    {
        $this->year = $year;
        $this->round = $round;
        $this->buildLanes();
        $peak_lh_Bkars = $this->build(true,true);
        $non_peak_lh_Bkars = $this->build(false,true);
        $peak_sit_Bkars = $this->build(true,false);
        $non_peak_sit_bkars = $this->build(false,false);
        foreach($this->lanes as $lane){
            $this->combinedBkars[$lane]["lh_peak"] = $peak_lh_Bkars[$lane];
            $this->combinedBkars[$lane]["lh_non_peak"] = $non_peak_lh_Bkars[$lane];
            $this->combinedBkars[$lane]["sit_peak"] = $peak_sit_Bkars[$lane];
            $this->combinedBkars[$lane]['sit_non_peak'] = $non_peak_sit_bkars[$lane];
        }
        $this->insert();
    }
    private function buildLanes(){
        $results = $GLOBALS['db']
            ->suite(Lane::DRIVER)
            ->driver(Lane::DRIVER)
            ->database(Lane::DATABASE)
            ->table(Lane::PEAK)
            ->select("distinct lane")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $this->lanes[] = $row['lane'];
        }
        return $this;
    }
    private function build($peak = true,$lh = true){
        $bkars = array();
        if($peak){
            $table = self::PEAK;
        }else{
            $table = self::NONPEAK;
        }
        if($lh){
            $col1 = 'lh_discount';
            $col2 = 'lh_rejection_code';
        }else{
            $col1 = 'sit_discount';
            $col2 = 'sit_rejection_code';
        }
        foreach($this->lanes as $lane){
            $bkars[$lane] = array();
            $results = $GLOBALS['db']
                ->driver(Lane::DRIVER)
                ->database(Lane::DATABASE)
                ->table($table)
                ->select($col1)
                ->where("lane = '$lane'")
                ->andWhere("$col2","=","0")
                ->andWhere("year","=","'$this->year'")
                ->andWhere("round","=","'$this->round'")
                ->get();
            if(!mssql_num_rows($results)){
                $this->errors[] = 'There are no values for selected period';
                return false;
            }else{
                while($row = mssql_fetch_assoc($results)){
                    $bkars[$lane][] = $row[$col1];
                }
            }
        }
        foreach ($bkars as $key=>$value){
            $bkars[$key] = min($value);
        }
        return $bkars;
    }
    private function insert(){
        foreach($this->combinedBkars as $lane => $vals){
            $vals['lane'] = $lane;
            $vals['round'] = $this->round;
            $vals['year'] = $this->year;
            $results = $GLOBALS['db']
                ->driver(Lane::DRIVER)
                ->database(LANE::DATABASE)
                ->table(Lane::BKAR)
                ->data($vals)
                ->insert()
                ->put();
        }
    }
}
