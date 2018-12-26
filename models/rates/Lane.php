<?php

require_once __DIR__ . '/../../record.php';


class Lane{

    const MSSQL = 'mssql';
    const DRIVER = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';
    const BKAR = 'dps_rates_bkar';
    const LKAR = 'dps_rates_lkar';

    public $id;
    public $year;
    public $round;
    public $scac;
    public $lane;
    public $lh_discount;
    public $sit_discount;
    public $lh_bkar;
    public $sit_bkar;
    public $sit_adj;
    public $lh_adj;
    public $lh_rejection_code;
    public $sit_rejection_code;
    public $filed;
    public $dead_line;
    public $rejections_expected;
    public $rejections_received;
    public $locked_adj;
    public $rate_cycle;
    public $service_code;
    public $market_code;
    public $program_type;
    public $domestic_rate_area_code;
    public $domestic_rate_area;
    public $domestic_region_id;
    public $domestic_region;
    public $tariff;


    public function __construct($id = null,$peak = true)
    {
        if(!is_null($id)){
            $this->id = $id;
            $this->build($peak)
                ->getBkar($peak)
                ->getLkar($peak);
        }
    }
    private function build($peak){
        if($peak){
            $table = self::PEAK;
        }else{
            $table = self::NONPEAK;
        }
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table($table)
            ->select("*")
            ->where("id = '$this->id'")
            ->get();
        if(!mssql_num_rows($results)){
            throw new Exception('Unable to find lane');
        }else{
            while($row = mssql_fetch_assoc($results)){
                foreach ($row as $key => $value){
                    $this->$key = $value;
                }
            }
        }
        return $this;
    }
    private function getBkar($peak){
        if($peak){
            $col1 = 'lh_peak';
            $col2 = 'sit_peak';
        }else{
            $col1 = 'lh_non_peak';
            $col2 = 'sit_non_peak';
        }
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::BKAR)
            ->select($col1,$col2)
            ->where("lane","=","'$this->lane'")
            ->andWhere("year","=",$this->year)
            ->andWhere("round","=",$this->round)
            ->get();
        if(!mssql_num_rows($results)){
            $exceptionStr = $this->lane . " | " . $this->year . " | " . $this->round . " | No BKAR AVAILABLE";
            throw new \Exception($exceptionStr);
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->lh_bkar = $row[$col1];
                $this->sit_bkar = $row[$col2];
            }
        }
        return $this;
    }
    private function getLkar($peak){
        if($peak){
            $col1 = 'lh_peak';
            $col2 = 'sit_peak';
        }else{
            $col1 = 'lh_non_peak';
            $col2 = 'sit_non_peak';
        }
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::LKAR)
            ->select($col1,$col2)
            ->where("lane = '$this->lane'")
            ->andWhere("year = '$this->year'")
            ->andWhere("round = '$this->round'")
            ->get();
        if(!mssql_num_rows($results)){
            $exceptionStr = $this->lane . " | " . $this->year . " | " . $this->round . " | No LKAR AVAILABLE";
            throw new \Exception($exceptionStr);
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->lh_lkar = $row[$col1];
                $this->sit_lkar = $row[$col2];
            }
        }
        return $this;
    }
    public function update($data,$peak){
        if($peak){
            $table = self::PEAK;
        }else{
            $table = self::NONPEAK;
        }
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table($table)
            ->data($data)
            ->update()
            ->where("id = '$this->id'")
            ->put();
        return $this;
    }
    public function getKnownAcceptedRange($lh = true){
      $data = array();
      $GLOBALS['db']
        ->suite(self::DRIVER)
        ->driver(self::DRIVER)
        ->database(self::DATABSE)
        ->table(self::TABLE);
      if($lh){
        $select = "max(lh_discount) as max,min(lh_discount) as min";
      }else{
        $select = "max(sit_discount) as max,min(sit_discount) as min";
      }
      $results = $GLOBALS['db']
        ->select($select)
        ->where("round","=","'" . $this->round . "'")
        ->andWhere("year","=","'" . $this->year . "'")
        ->get();
      while($row = mssql_fetch_assoc($results)){
        $data['x'] = $row['max'];
        $data['y'] = $row['min'];
      }
      return $data;
    }
}

/*        ->select()
        ->where()
        ->andWhere()
        ->get();*/
