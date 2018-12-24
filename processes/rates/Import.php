<?php


class Import{

    const IMPORTDIR = 'data/import/';
    const PROCDIR = 'data/processed/';
    const LANECOUNT = 674;
    const DRIVER = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';
    const DEADLINE = '1/15/';
    const REJECTIONS = '-02-03';
    const ROUNDPATTERN = '/Round\s([0-9]{1})/';
    const RNDPATTERN = '/RD\s([0-9]{1})/';
    const YEARPATTERN = '/([0-9]{4})/';

    private $startTime;
    private $elapsedTime;
    private $deadLine;
    private $rejectionsDate;
    private $importFiles = array();
    private $scacs = array();
    private $nonPeak = array();
    private $peak = array();
    private $areaCodes = array();
    private $regions = array();
    private $lanes = array();
    private $scacLaneCountsPeak = array();
    private $scacLaneCountsNonPeak = array();
    private $errors = array();
    private $controlScacs = array();
    private $roundPatterns = array(
        self::ROUNDPATTERN,
        self::RNDPATTERN
    );




    public function __construct()
    {
        $this->startTime = microtime(true);
        $input = RateFactory::readLine("Would you like to begin Import? ");
        if($input == 'y'){
            $this->getControlScacs()
                ->getImportFiles()
                ->readRates()
                ->readRates(false)
                ->assignBkars()
                ->assignBkars(false)
                ->verifyLanes()
                ->verifyLanes(false)
                ->verifyLaneCounts()
                ->verifyLaneCounts(false)
                ->proofOfWork();
        }else{
            echo "Quiting...\nGoodBye\n";
        }
    }
    private function getImportFiles(){
        $results = scandir(self::IMPORTDIR);
        foreach($results as $result){
            if($result == '.' || $result == '..'){
                continue;
            }elseif(is_file(self::IMPORTDIR . $result)){
                $pathInfo = pathinfo(self::IMPORTDIR . $result);
                if($pathInfo['extension'] == 'csv'){
                    $this->importFiles[] = $result;
                }
            }
        }
        return $this;
    }
    private function readRates($peak = true){
        foreach($this->importFiles as $import){
            $data = array();
            foreach($this->roundPatterns as $pattern){
                if(preg_match($pattern,$import,$matches)){
                    $data["round"] = $matches[1];
                }
            }
            if(preg_match(self::YEARPATTERN,$import,$matches)){
                $data["year"] = $matches[1];
            }
            $csvEntries = array_map('str_getcsv', file(self::IMPORTDIR . $import));
            foreach ($csvEntries as $entry){
                $data["scac"] = $entry[0];
                if(!in_array($data["scac"],$this->scacs)){
                    $this->scacs[] = $data["scac"];
                }
                $data["market_code"] = $entry[1];
                $data["domestic_rate_area_code"] = $entry[2];
                $data["domestic_region_id"] = $entry[3];
                $data["service_code"] = $entry[4];
                $data["lane"] = trim(strtolower($data["domestic_rate_area_code"])) . ' to ' . trim(strtolower($data["domestic_region_id"]));
                if(!in_array($data["lane"],$this->lanes)){
                    $this->lanes[] = $data["lane"];
                }
                if($peak){
                    $data["lh_discount"] = $entry[5];
                    $data["sit_discount"] = $entry[6];
                    $data["lh_adj"] = $data["lh_discount"];
                    $data["sit_adj"] = $data["sit_discount"];
                    $data["filed"] = 1;
                    $this->peak[] = $data;
                }else{
                    $data["lh_discount"] = $entry[7];
                    $data["sit_discount"] = $entry[8];
                    $data["lh_adj"] = $data["lh_discount"];
                    $data["sit_adj"] = $data["sit_discount"];
                    $data["filed"] = 1;
                    $this->nonPeak[] = $data;
                }
            }
        }
        return $this;
    }
    private function getControlScacs(){
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table(self::PEAK)
            ->select("distinct scac")
            ->get();
        if(!mssql_num_rows($results)){
            $this->errors[] = "There are no Scacs in Database\n";
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->controlScacs[] = $row["scac"];
            }
        }
        return $this;
    }
    private function assignBkars($peak = true){
        if($peak){
            for($i = 0; $i < count($this->peak); $i++){
                foreach($this->lanes as $lane){
                    if($this->peak[$i]["lane"] == $lane){
                        $domesticInfo = $this->getDomesticInfo($lane);
                        //$this->peak[$i]["lh_bkar"] = $this->getBkar($lane,$this->peak[$i]['year'],$this->peak[$i]['round']);
                        //$this->peak[$i]["sit_bkar"] = $this->getBkar($lane,$this->peak[$i]['year'],$this->peak[$i]['round'],true,false);
                        $this->peak[$i]["domestic_region"] = $domesticInfo["domestic_region"];
                        $this->peak[$i]["domestic_rate_area"] = $domesticInfo["domestic_rate_area"];
						$this->peak[$i]["lh_rejection_code"] = 0;
						$this->peak[$i]["sit_rejection_code"] = 0;
                    }
                }
            }
        }else{
            for($i = 0; $i < count($this->nonPeak); $i++){
                foreach($this->lanes as $lane){
                    if($this->peak[$i]["lane"] == $lane){
                        $domesticInfo = $this->getDomesticInfo($lane);
                        //$this->nonPeak[$i]["lh_bkar"] = $this->getBkar($lane,$this->nonPeak[$i]['year'],$this->nonPeak[$i]['round'],false);
                        //$this->nonPeak[$i]["sit_bkar"] = $this->getBkar($lane,$this->nonPeak[$i]['year'],$this->nonPeak[$i]['round'],false,false);
                        $this->nonPeak[$i]["domestic_region"] = $domesticInfo["domestic_region"];
                        $this->nonPeak[$i]["domestic_rate_area"] = $domesticInfo["domestic_rate_area"];
						$this->nonPeak[$i]["lh_rejection_code"] = 0;
						$this->nonPeak[$i]["sit_rejection_code"] = 0;
                    }
                }
            }
        }
        return $this;
    }
    private function getBkar($lane,$year,$round = 2,$peak = true,$lh = true){
        if($round == 1){
            $year = $year - 1;
            $round = 2;
        }else{
            $round = 1;
        }
        if($peak){
            $table = self::PEAK;
        }else{
            $table = self::NONPEAK;
        }
        if($lh){
            $errorCol = 'lh_rejection_code';
            $select = "lh_bkar";
        }else{
            $errorCol = 'sit_rejection_code';
            $select = "sit_bkar";
        }
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table($table)
            ->select($select)
            ->where("lane = '$lane'")
            ->andWhere("round = '$round'")
            ->andWhere("year = '$year'")
            ->andWhere("$errorCol = 0")
            ->get("value");
        if(!$results){
            $this->errors[] = "There are no Previous Bkars. Genesis Import?\n";
        }
        return $results;
    }
    private function getDomesticInfo($lane){
        $data = array();
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DATABASE)
            ->table(self::PEAK)
            ->select("domestic_region,domestic_rate_area")
            ->where("lane = '$lane'")
            ->get();
        if(!mssql_num_rows($results)){
            $this->errors[] = "Cannot Find Domestic Info for " . $lane . "\n";
            die(print_r($this->errors));
            return false;
        }else{
            while($row = mssql_fetch_assoc($results)){
                $data["domestic_region"] = $row["domestic_region"];
                $data["domestic_rate_area"] = $row["domestic_rate_area"];
            }
        }
        return $data;
    }
    private function verifyScac($scac){
        if(!in_array($scac,$this->controlScacs)){
            $this->errors[] = $scac . " is unrecognized\n";
            return false;
        }
        return true;
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
                    $lane = $a['lane'];
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
    private function getElapsedTime(){
        $this->elapsedTime = microtime(true) - $this->startTime;
        if($this->elapsedTime < 60){
            echo $this->elapsedTime . " seconds\n";
        }else{
            echo $this->elapsedTime / 60 . " minutes\n";
        }
        return $this;
    }
    private function viewErrors(){
        if(count($this->errors)){
            echo count($this->errors) . " errors found. Would you like to View Them? [y]es [a]ll\n";
            $line = RateFactory::readLine();
            if($line == 'a'){
                foreach($this->errors as $error){
                    echo $error;
                }
            }elseif($line == 'y'){
                $counter = count($this->errors);
                while($counter--){
                    echo $this->errors[$counter];
                    RateFactory::readLine();
                }
            }else{
                $line = RateFactory::readLine("Would you like to import these rates? ");
                if($line == 'y'){
                    $this->importRates()
                        ->importRates(false)
                        ->cleanUpSourceFiles();
                }else{
                    echo "Goodbye\n";
                    exit;
                }
            }
        }else{
            $line = RateFactory::readLine("Would you like to import these rates? ");
            if($line == 'y'){
                $this->importRates()
                    ->importRates(false)
                    ->cleanUpSourceFiles();
            }else{
                echo "Goodbye\n";
                exit;
            }
        }
        return $this;
    }
    private function importRates($peak = true){
        if($peak){
            $table = self::PEAK;
            $arr = $this->peak;
        }else{
            $table = self::NONPEAK;
            $arr = $this->nonPeak;
        }
        foreach($arr as $a){
            $results = $GLOBALS['db']
                ->driver(self::DRIVER)
                ->database(self::DATABASE)
                ->table($table)
                ->data($a)
                ->insert()
                ->put();
        }
        return $this;
    }
    private function cleanUpSourceFiles(){
        foreach($this->importFiles as $importFile){
            $source = self::IMPORTDIR . $importFile;
            $destination = self::PROCDIR . $importFile;
            if(!rename($source,$destination)){
                $e = error_get_last();
                $this->errors[] = $e['message'];
            }
        }
        if(count($this->errors)){
            print_r($this->errors);
        }
        return $this;
    }

}
