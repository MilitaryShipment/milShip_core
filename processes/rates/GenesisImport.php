<?php 


class GenesisImport{

    const GENESISBKAR = 'data/genesis/2016_Best_Peak_LH_Rate.csv';
    const GENESISPEAK = 'data/genesis/peak.csv';
    const GENESISNONPEAK = 'data/genesis/non_peak.csv';
    const GENESISSTATEKEY = 'data/genesis/DPSconusStates.csv';
    const YEARPATTERN = '/[0-9]{4}/';
    const LANECOUNT = 674;
    const DRIVER = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';
    const BKAR = 'dps_rates_bkar';
    const DEADLINE = '1/15/';
    const REJECTIONS = '-02-03';

    private $startTime;
    private $elapsedTime;
    private $genesisYear;
    private $deadLine;
    private $rejectionsDate;
    private $scacs = array();
    private $nonPeak = array();
    private $nonPeakLhBkars = array();
    private $nonPeakSitBkars = array();
    private $peakSitBkars = array();
    private $peakLhBkars = array();
    private $peak = array();
    private $areaCodes = array();
    private $regions = array();
    private $lanes = array();
    private $scacLaneCountsPeak = array();
    private $scacLaneCountsNonPeak = array();
    private $errors = array();

    public function __construct()
    {
        $input = RateFactory::readLine("Would you like to begin Genesis Import? ");
        if($input == 'y'){
            $year = RateFactory::readLine("Genesis Year: ");
            while(!$this->verifyYearInput($year)){
                $year = RateFactory::readLine("Genesis Year: ");
            }
            $this->genesisYear = $year;
            $this->deadLine = self::DEADLINE . $this->genesisYear;
            $this->rejectionsDate = $this->genesisYear . self::REJECTIONS;
            $this->startTime = microtime(true);
            $this->readGenesisRates()
                ->readGenesisRates(false)
                ->buildLanes()
                ->verifyLanes()
                ->verifyLanes(false)
                ->verifyLaneCounts()
                ->verifyLaneCounts(false)
                ->buildBkars()
                ->insertBkars()
//                ->getGenesisBkar()
//                ->getGenesisBkar(false)
                ->proofOfWork();
            $line = RateFactory::readLine('Would You like to Import these rates? ');
            if($line == 'y'){
                $this->insertGenesisRates()
                    ->insertGenesisRates(false);
            }else{
                echo "Quiting\n";
                exit;
            }
        }else{
            echo "Quiting\n";
            exit;
        }
    }

    private function readGenesisRates($peak = true){
        if($peak){
            $genesisFile = self::GENESISPEAK;
        }else{
            $genesisFile = self::GENESISNONPEAK;
        }
        $data = array();
        $csvEntries = array_map('str_getcsv', file($genesisFile));
        foreach($csvEntries as $csvEntry){
            if(!in_array($csvEntry[1],$this->scacs)){
                $this->scacs[] = $csvEntry[1];
            }
            if(!in_array($csvEntry[6],$this->areaCodes)){
                $this->areaCodes[] = $csvEntry[6];
            }
            if(!in_array($csvEntry[8],$this->regions)){
                $this->regions[] = $csvEntry[8];
            }
            $data["rate_cycle"] = $csvEntry[0];
            $data["scac"] = $csvEntry[1];
            $data["service_code"] = $csvEntry[2];
            $data["market_code"] = $csvEntry[3];
            $data["program_type"] = $csvEntry[4];
            $data["domestic_rate_area_code"] = $csvEntry[6];
            $data["domestic_rate_area"] = $csvEntry[7];
            $data["domestic_region_id"] = $csvEntry[8];
            $data["domestic_region"] = $csvEntry[9];
            $data["tariff"] = $csvEntry[10];
            $data["lh_discount"] = $csvEntry[11];
            $data["sit_discount"] = $csvEntry[12];
            if($peak){
                $this->peak[] = $data;
            }else{
                $this->nonPeak[] = $data;
            }
        }
        return $this;
    }
    private function buildLanes(){
        foreach($this->areaCodes as $areaCode){
            foreach($this->regions as $region){
                $lane = trim(strtolower($areaCode)) . " to " . trim(strtolower($region));
                if(!in_array($lane,$this->lanes)){
                    $this->lanes[] = $lane;
                }
            }
        }
        return $this;
    }
    private function verifyLanes($peak = true){
        if($peak){
            $arr = $this->peak;
        }else{
            $arr = $this->nonPeak;
        }
        foreach($this->scacs as $scac){
            if($peak){
                $this->scacLaneCountsPeak[$scac] = 0;
            }else{
                $this->scacLaneCountsNonPeak[$scac] = 0;
            }
            foreach($arr as $a){
                if($a["scac"] != $scac){
                    continue;
                }else{
                    $lane = trim(strtolower($a["domestic_rate_area_code"])) . ' to ' . trim(strtolower($a["domestic_region_id"]));
                    if(in_array($lane,$this->lanes)){
                        if($peak){
                            $this->scacLaneCountsPeak[$scac]++;
                        }else{
                            $this->scacLaneCountsNonPeak[$scac]++;
                        }
                    }else{
                        echo "UNKNOWN LANE\n";
                        echo $lane . "\n";
                        exit;
                    }
                }
            }
        }
        return $this;
    }
    private function verifyLaneCounts($peak = true){
        if($peak){
            $arr = $this->scacLaneCountsPeak;
        }else{
            $arr = $this->scacLaneCountsPeak;
        }
        foreach($arr as $key=>$value){
            if($value != self::LANECOUNT){
                $errorStr = $key . " has incorrect lane count " . $value;
                if($peak){
                    $peakStr = " In Peak Season\n";
                }else{
                    $peakStr = " In NON Peak Season\n";
                }
                $errorStr .= $peakStr;
                if(!in_array($errorStr,$this->errors)){
                    $this->errors[] = $errorStr;
                }
            }
        }
        return $this;
    }
    private function proofOfWork(){
        echo "Non Peak Rows: " . count($this->nonPeak) . "\n";
        echo "Peak Rows: " . count($this->peak) . "\n";
        echo "Scacs: " . count($this->scacs) . "\n";
        echo "AreaCodes: " . count($this->areaCodes) . "\n";
        echo "Regions: " . count($this->regions) . "\n";
        echo "Lanes: " . count($this->lanes) . "\n";
        $noLane = 0;
        $nolhBkar = 0;
        $noSitBkar = 0;
        foreach($this->peak as $peak){
            if(!isset($peak["lane"])){
                $noLane++;
            }
            if(!isset($peak["lh_bkar"])){
                $nolhBkar++;
            }
            if(!isset($peak["sit_bkar"])){
                $noSitBkar++;
            }
        }
        echo "Peak With No Lane: " . $noLane . "\n";
        echo "Peak With No LINEHAUL BKAR: " . $nolhBkar . "\n";
        echo "Peak With No SIT BKAR: " . $noSitBkar . "\n";
        $noLane = 0;
        $nolhBkar = 0;
        $noSitBkar = 0;
        foreach($this->nonPeak as $nonPeak){
            if(!isset($nonPeak["lane"])){
                $noLane++;
            }
            if(!isset($nonPeak["lh_bkar"])){
                $nolhBkar++;
            }
            if(!isset($nonPeak["sit_bkar"])){
                $noSitBkar++;
            }
        }
        echo "NonPeak With No Lane: " . $noLane . "\n";
        echo "NonPeak With No LINEHAUL BKAR: " . $nolhBkar . "\n";
        echo "NonPeak With No SIT BKAR: " . $noSitBkar . "\n";
        $this->getElapsedTime()
            ->viewErrors();
        return $this;
    }
    private function getGenesisBkar($peak = true){
        if($peak){
            $csvEntries = array_map('str_getcsv', file(self::GENESISBKAR));
            foreach($csvEntries as $csvEntry){
                $lane = trim(strtolower($csvEntry[0])) . ' to ' . trim(strtolower($csvEntry[2]));
                $bkar = $csvEntry[3];
                $calculatedBkar = min($this->peakLhBkars[$lane]);
                for($i = 0; $i < count($this->peak); $i++){
                    if($lane == $this->peak[$i]["lane"]){
                        if($calculatedBkar != $bkar){
                            $errorStr = "Calculated " . $this->peak[$i]["scac"] . " " . $lane . " BKAR at " . $calculatedBkar . " csv shows " . $bkar . "\n";
                            if(!in_array($errorStr,$this->errors)){
                                $this->errors[] = $errorStr;
                            }
                            if($calculatedBkar < $bkar){
                                $this->peak[$i]["lh_bkar"] = $calculatedBkar;
                            }else{
                                $this->peak[$i]["lh_bkar"] = $bkar;
                            }
                        }else{
                            $this->peak[$i]["lh_bkar"] = $bkar;
                        }
                        $this->peak[$i]["sit_bkar"] = min($this->peakSitBkars[$lane]);
                    }
                }
            }
        }else{
            for($i = 0; $i < count($this->nonPeak); $i++){
                foreach($this->lanes as $lane){
                    if($this->nonPeak[$i]["lane"] == $lane){
                        $this->nonPeak[$i]["lh_bkar"] = min($this->nonPeakLhBkars[$lane]);
                        $this->nonPeak[$i]["sit_bkar"] = min($this->nonPeakSitBkars[$lane]);
                    }
                }
            }
        }
        return $this;
    }
    private function buildBkars(){
        foreach ($this->lanes as $lane){
            $this->nonPeakLhBkars[$lane] = array();
            $this->nonPeakSitBkars[$lane] = array();
            $this->peakLhBkars[$lane] = array();
            $this->peakSitBkars[$lane] = array();
            for($i = 0; $i < count($this->nonPeak); $i++){
                $this->nonPeak[$i]["lane"] = trim(strtolower($this->nonPeak[$i]["domestic_rate_area_code"])) . ' to ' . trim(strtolower($this->nonPeak[$i]["domestic_region_id"]));
                if($this->nonPeak[$i]["lane"] == $lane){
                    if(!in_array($this->nonPeak[$i]["lh_discount"],$this->nonPeakLhBkars[$lane])){
                        $this->nonPeakLhBkars[$lane][] = $this->nonPeak[$i]["lh_discount"];
                    }
                    if(!in_array($this->nonPeak[$i]["sit_discount"],$this->nonPeakSitBkars[$lane])){
                        $this->nonPeakSitBkars[$lane][] = $this->nonPeak[$i]["sit_discount"];
                    }
                }
            }
            for($i = 0; $i < count($this->peak); $i++){
                $this->peak[$i]["lane"] = trim(strtolower($this->peak[$i]["domestic_rate_area_code"])) . ' to ' . trim(strtolower($this->peak[$i]["domestic_region_id"]));
                if($this->peak[$i]["lane"] == $lane){
                    if(!in_array($this->peak[$i]["lh_discount"],$this->peakLhBkars[$lane])){
                        $this->peakLhBkars[$lane][] = $this->peak[$i]["lh_discount"];
                    }
                    if(!in_array($this->peak[$i]["sit_discount"],$this->peakSitBkars[$lane])){
                        $this->peakSitBkars[$lane][] = $this->peak[$i]["sit_discount"];
                    }
                }
            }
        }
        return $this;
    }
    private function insertBkars(){
        foreach($this->lanes as $lane){
            if(!isset($this->peakLhBkars[$lane])){
                echo "No PEAK LH BKAR for: " . $lane . "\n";
            }
            if(!isset($this->nonPeakLhBkars[$lane])){
                echo "No NON PEAK LH BKAR for: " . $lane . "\n";
            }
            if(!isset($this->peakSitBkars[$lane])){
                echo "No PEAK SIT BKAR for: " . $lane . "\n";
            }
            if(!isset($this->nonPeakSitBkars[$lane])){
                echo "No NON PEAK SIT BKAR for: " . $lane . "\n";
            }
            $data = array(
                "lane"=>$lane,
                "round"=>2,
                "year"=>$this->genesisYear,
                "lh_peak"=>min($this->peakLhBkars[$lane]),
                "lh_non_peak"=>min($this->nonPeakLhBkars[$lane]),
                "sit_peak"=>min($this->peakSitBkars[$lane]),
                "sit_non_peak"=>min($this->nonPeakSitBkars[$lane])
            );
            $results = $GLOBALS['db']
                ->driver(self::DRIVER)
                ->database(self::DATABASE)
                ->table(self::BKAR)
                ->data($data)
                ->insert()
                ->put();
        }
        return $this;
    }
    private function getElapsedTime(){
        $this->elapsedTime = microtime(true) - $this->startTime;
        if($this->elapsedTime < 60){
            echo $this->elapsedTime . " seconds\n";
        }else{
            echo $this->elapsedTime / 60 . " minutes\n";
        }
        return $this;
    }
    private function verifyYearInput($year){
        if(!preg_match(self::YEARPATTERN,$year)){
            echo "Invalid year. Try again.\n";
            return false;
        }
        return true;
    }
    private function viewErrors(){
        echo count($this->errors) . " errors found. Would you like to View Them? ";
        $line = RateFactory::readLine();
        if($line == 'y'){
            foreach($this->errors as $error){
                echo $error;
            }
        }
        return $this;
    }
    private function insertGenesisRates($peak = true){
        if($peak){
            $arr = $this->peak;
            $table = self::PEAK;
        }else{
            $arr = $this->nonPeak;
            $table = self::NONPEAK;
        }
        $i = 0;
        foreach($arr as $a){
            $data = array(
                "year"=>$this->genesisYear,
                "round"=>2,
                "sit_adj"=>$a["sit_discount"],
                "lh_adj"=>$a["lh_discount"],
                "filed"=>1,
                "lh_rejection_code"=>0,
                "sit_rejection_code"=>0,
                "dead_line"=>$this->deadLine,
                "rejections_expected"=>$this->rejectionsDate,
                "rejections_received"=>1,
                "locked_adj"=>0
            );
            foreach($a as $key=>$value){
                $data[$key] = $value;
            }
            $results = $GLOBALS['db']
                ->driver(self::DRIVER)
                ->database(self::DATABASE)
                ->table($table)
                ->data($data)
                ->insert()
                ->put();
            $i++;
        }
        echo $i . " records processed\n";
        return $this;
    }
}
