<?php 

class RateCopy{

    const MSSQl = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';

    private $sourceScac;
    private $targetScac;
    private $round;
    private $year;
    private $peakSource = array();
    private $nonPeakSource = array();

    public function __construct($fromScac,$toScac,$round,$year)
    {
        $this->sourceScac = $fromScac;
        $this->targetScac = $toScac;
        $this->round = $round;
        $this->year = $year;
        $this->GetSourceVals()
            ->GetSourceVals(false)
            ->updateTargetVals()
            ->updateTargetVals(false);
    }
    private function GetSourceVals($peak = true){
        if($peak){
            $table = self::PEAK;
        }else{
            $table = self::NONPEAK;
        }
        $results = $GLOBALS['db']
            ->driver(self::MSSQl)
            ->database(self::DATABASE)
            ->table($table)
            ->select("lane,lh_adj,sit_adj")
            ->where("scac = '$this->sourceScac'")
            ->andWhere("year = '$this->year'")
            ->andWhere("round = $this->round")
            ->get();
        if(!mssql_num_rows($results)){
            die("No Source Values for " . $this->sourceScac . "\n");
        }else{
            while($row = mssql_fetch_assoc($results)){
                $lane = $row['lane'];
                if($peak){
                    $this->peakSource[$lane] = array();
                    $this->peakSource[$lane]['lh_adj'] = $row['lh_adj'];
                    $this->peakSource[$lane]['sit_adj'] = $row['sit_adj'];
                }else{
                    $this->nonPeakSource[$lane] = array();
                    $this->nonPeakSource[$lane]['lh_adj'] = $row['lh_adj'];
                    $this->nonPeakSource[$lane]['sit_adj'] = $row['sit_adj'];
                }
            }
        }
        return $this;
    }
    private function updateTargetVals($peak = true){
        if($peak){
            $table = self::PEAK;
            $arr = $this->peakSource;
        }else{
            $table = self::NONPEAK;
            $arr = $this->nonPeakSource;
        }
        foreach($arr as $lane=>$values){
            $update = array(
                "lh_adj"=>$values['lh_adj'],
                "sit_adj"=>$values['sit_adj']
            );
            $results = $GLOBALS['db']
                ->driver(self::MSSQl)
                ->database(self::DATABASE)
                ->table($table)
                ->data($update)
                ->update()
                ->where("scac = '$this->targetScac'")
                ->andWhere("lane = '$lane'")
                ->andWhere("year = '$this->year'")
                ->andWhere("round = '$this->round'")
                ->put();
        }
        return $this;
    }
}
