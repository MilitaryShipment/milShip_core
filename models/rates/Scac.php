<?php 

class Scac{

    /*
     * id == scac string
     * */
    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';

    public $scac;
    private $year;
    private $round;
    public $peakLanes = array();
    public $nonPeakLanes = array();

    public function __construct($id = null,$round = null,$year = null)
    {
        if(!is_null($id)){
            $this->scac = $id;
            if(is_null($round)){
                die('Cannot build a scac without a round');
            }else{
                $this->round = $round;
            }
            if(is_null($year)){
                die('Cannot build a scac without a year');
            }else{
                $this->year = $year;
            }
            $this->buildLanes()
                ->buildLanes(false);
        }
    }
    private function buildLanes($peak = true){
        if($peak){
            $table = self::PEAK;
        }else{
            $table= self::NONPEAK;
        }
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table($table)
            ->select("id")
            ->where("scac = '$this->scac'")
            ->andWhere("year = '$this->year'")
            ->andWhere("round = '$this->round'")
            ->get();
        if(!mssql_num_rows($results)){
            die('No Rates!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                if($peak){
                    $this->peakLanes[] = RateFactory::buildLane($row["id"],$peak);
                }else{
                    $this->nonPeakLanes[] = RateFactory::buildLane($row["id"],$peak);
                }
            }
        }
        return $this;
    }
}
