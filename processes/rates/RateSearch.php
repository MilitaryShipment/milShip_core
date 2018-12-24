<?php 

class RateSearch{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';

    const NOSCAC = 'Search scac is not set';
    const NOROUND = 'search round is not set';
    const NOSEASON = 'search season is not set';
    const NOLANE = 'search lane is not set';
    const NOYEAR = 'search year is not set';
    const NORESULTS = 'No Results';

    private $scac;
    private $round;
    private $season;
    private $lane;
    private $year;
    private $peak;
    private $region;
    private $channel;

    public $results = array();
    public $errors = array();

    public function __construct($params = null)
    {
        if(!is_null($params)){
            foreach($params as $key=>$value){
                $this->$key = $value;
            }
            if(!isset($this->scac)){
                $this->errors[] = self::NOSCAC;
            }elseif(!isset($this->round)){
                $this->errors[] = self::NOROUND;
            }elseif(!isset($this->season)){
                $this->errors[] = self::NOSEASON;
            }elseif(!isset($this->lane)){
                $this->errors[] = self::NOLANE;
            }elseif(!isset($this->year)){
                $this->errors[] = self::NOYEAR;
            }else{
                $this->findRates();
            }
        }
    }
    public function findRates(){
        $andWhere = "round = '$this->round'";
        if($this->season == 'peak'){
            $this->peak = true;
            $table = self::PEAK;
        }else{
            $this->peak = false;
            $table = self::NONPEAK;
        }
        if($this->scac != 'all'){
            $andWhere .= " and scac = '$this->scac'";
        }
        if($this->lane != 'all'){
            $andWhere .= " and lane = '$this->lane'";
        }
        if($this->region != 'all'){
            $andWhere .= " and domestic_region_id = '$this->region'";
        }
        if($this->channel != 'all'){
            $andWhere .= " and domestic_rate_area_code = '$this->channel'";
        }
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table($table)
            ->select("id")
            ->where("year = '$this->year'")
            ->andWhere($andWhere)
            ->get();
        if(!mssql_num_rows($results)){
            $this->errors[] = self::NORESULTS;
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->results[] = RateFactory::buildLane($row['id'],$this->peak);
            }
        }
        return $this;
    }
}
