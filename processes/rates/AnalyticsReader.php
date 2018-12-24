<?php


class AnalyticsReader{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';
    const INPUTDIR = 'data/analytics/';
    const BASEPATH = '/srv/www/htdocs/watson/workspace/rates/';
    const YEARPATTERN = '/[0-9]{4}/';
    const SEASONPATTERN = '/_(.*)/';
    const SCACPATTERN = '/([A-Z]{4})/';
    const EXCELFILE = 'csv';

    private $files = array();
    private $lanes = array();
    private $rejections = array();
    private $analyticsRates = array();
    private $analyticsScacs = array();
    private $season;
    private $year;

    public function __construct()
    {
        $this->getLanes()
            ->getAnalyticsFiles(self::INPUTDIR)
            ->buildRates()
            ->verifyRates()
            ->idRejections();
        print_r($this->rejections);
    }
    private function getLanes(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::PEAK)
            ->select("distinct lane")
            ->get();
        if(!mssql_num_rows($results)){
            die('There are no Lanes!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->lanes[] = $row["lane"];
            }
        }
        return $this;
    }
    private function getAnalyticsFiles($dir){
        if($dir != self::INPUTDIR){
            $dir .= '/';
        }
        $results = scandir($dir);
        foreach($results as $result){
            if($result == '.' || $result == '..'){
                continue;
            }elseif(is_dir($dir . $result)){
                if(preg_match(self::YEARPATTERN,$dir . $result,$matches)){
                    $this->year = $matches[0];
                }
                if(preg_match(self::SEASONPATTERN,$dir . $result,$matches)){
                    $this->season = $matches[1];
                }
                $this->getAnalyticsFiles($dir . $result);
            }elseif(is_file($dir . $result)){
                $pathInfo = pathinfo($dir . $result);
                if(preg_match(self::SCACPATTERN,$dir . $result,$matches)){
                    $scac = $matches[0];
                    if(!in_array($scac,$this->analyticsScacs)){
                        $this->analyticsScacs[] = $scac;
                    }
                }
                if($pathInfo['extension'] == self::EXCELFILE){
                    $analytics = RateFactory::createAnalyticsFile();
                    $analytics->year = $this->year;
                    $analytics->season = $this->season;
                    $analytics->scac = $scac;
                    $analytics->fileName = self::BASEPATH . $dir . $result;
                    $this->files[] = $analytics;
                }
            }
        }
        return $this;
    }
    private function buildRates(){
        foreach($this->files as $file){
            $scac = $file->scac;
            $year = $file->year;
            $season = $file->season;
            $rows = array_map('str_getcsv',file($file->fileName));
            for($i = 0; $i < count($rows); $i++){
                if(!$i){
                    continue;
                }else{
                    $analytics = RateFactory::createAnalyticsRate();
                    $analytics->scac = $scac;
                    $analytics->year = $year;
                    $analytics->round = 2;
                    $analytics->season = $season;
                    $analytics->lane = trim(strtolower($rows[$i][0])) . ' to ' . trim(strtolower($rows[$i][1]));
                    $analytics->lh_discount = $rows[$i][2];
                    $analytics->sit_discount = $rows[$i][3];
                    $this->analyticsRates[] = $analytics;
                }
            }
        }
        return $this;
    }
    private function verifyRates(){
        foreach($this->analyticsRates as $aRate){
            if($aRate->season == 'peak'){
                $peak = true;
                $table = self::PEAK;
            }else{
                $peak = false;
                $table = self::NONPEAK;
            }
            $results = $GLOBALS['db']
                ->driver(self::MSSQL)
                ->database(self::DATABASE)
                ->table($table)
                ->select("sit_discount,lh_discount")
                ->where("lane = '$aRate->lane'")
                ->andWhere("year = '$aRate->year'")
                ->andWhere("scac = '$aRate->scac'")
                ->andWhere("round = '$aRate->round'")
                ->get();
            if(!mssql_num_rows($results)){
                die("No Rates found for " . $aRate->scac . ' ' . $aRate->lane . "\n");
            }else{
                while($row = mssql_fetch_assoc($results)){
                    $lh_discount = $row["lh_discount"];
                    $sit_discount = $row["sit_discount"];
                }
                if($lh_discount != $aRate->lh_discount){
                    $this->updateRate($aRate->scac,$aRate->lane,$aRate->lh_discount,$peak,false);
                }
                if($sit_discount != $aRate->sit_discount){
                    $this->updateRate($aRate->scac,$aRate->lane,$aRate->sit_discount,$peak,false);
                }
            }
        }
        return $this;
    }
    private function idRejections(){
        foreach($this->analyticsScacs as $scac){
            $analyticsLanes = array();
            foreach($this->analyticsRates as $aRate){
                if($aRate->scac != $scac){
                    continue;
                }else{
                    if(!in_array($aRate->lane,$analyticsLanes)){
                        $analyticsLanes[] = $aRate->lane;
                    }
                }
            }
            foreach($this->lanes as $lane){
                if(!in_array($lane,$analyticsLanes)){
                    $this->rejections[$scac][] = $lane;
                }
            }
        }
        return $this;
    }
    private function updateRate($scac,$lane,$discount,$peak = true,$lh = true){
        $data = array();
        if($lh){
            $data['lh_discount'] = $discount;
            $data['lh_adj'] = $discount;
        }else{
            $data['sit_discount'] = $discount;
            $data['sit_adj'] = $discount;
        }
        if($peak){
            $table = self::PEAK;
        }else{
            $table = self::NONPEAK;
        }
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table($table)
            ->data($data)
            ->update()
            ->where("scac = '$scac'")
            ->andWhere("lane = '$lane'")
            ->andWhere("year = '$this->year'")
            ->andWhere("round = 2")
            ->put();
        return $this;
    }
}
