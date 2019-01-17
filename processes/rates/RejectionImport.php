<?php

class RejectionImport{

    const DRIVER = 'mssql';
    const DATABASE = 'test';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';

    const REJECTIONDIR = 'data/rejections/';
    const PROCDIR = 'data/processed_rejections/';
    const YEARPATTERN = '/[0-9]{4}/';
    const REJECTIONSUBJPATTERN = '/Rate\(s\) Rejected for/';
    const REJECTIONSCACPATTERN = '/\(([A-Z]{4})\)/';
    const RECJECTIONFIELDSTR1 = '(Code of Service)';
    const RECJECTIONFIELDSTR2 = '(Origin)';
    const RECJECTIONFIELDSTR3 = '(Destination)';
    const RECJECTIONFIELDSTR4 = '(Error Code)';
    const RECJECTIONFIELDSTR5 = '(Component)';
    const REJECTIONSTRPATTERN = '/\s{2,}/';
    const ENDOFREJECTIONPATTERN = '(REJECTION CODES LEGEND)';
    const CHARATERPATTERN = '/[A-Z]/';

    private $year;
    private $round;
    private $rejectionMsgs = array();
    private $rejectionFields = array();
    private $scacs = array();
    private $scacRejectionCounts = array();
    public $rejections = array();

    public function __construct()
    {
        $this->inputDir = __DIR__ . self::REJECTIONDIR;
        $this->outputDir = __DIR__ . self::PROCDIR;
        $this->beginImport()
            ->getRejectionMsgs()
            ->parseRejections()
            ->getScacCounts()
            ->proofOfWork();
    }

    private function beginImport(){
        $line = RateFactory::readLine("Would you like to begin rejection Import? ");
        if($line == 'y'){
            $year = RateFactory::readLine("Year: ");
            while(!$this->validateYearInput($year)){
                $year = RateFactory::readLine("Year: ");
            }
            $this->year = $year;
            $round = RateFactory::readLine("Round: ");
            while(!$this->validateRoundInput($round)){
                $round = RateFactory::readLine("Round: ");
            }
            $this->round = $round;
        }else{
            echo "Goodbye\n";
            exit;
        }
        return $this;
    }

    private function getRejectionMsgs(){
        $results = scandir($this->inputDir);
        foreach($results as $result){
            if($result == '.' || $result == '..'){
                continue;
            }else{
                if(is_file($this->inputDir . $result)){
                    $pathInfo = pathinfo($this->inputDir . $result);
                    if(preg_match(self::REJECTIONSUBJPATTERN,$pathInfo['filename'])){
                        $this->rejectionMsgs[] = $result;
                    }
                }
            }
        }
        return $this;
    }
    private function parseRejections(){
        $rejections = array();
        $scac = '';
        foreach($this->rejectionMsgs as $msg){
            $msgFile = $this->inputDir .$msg;
            if(preg_match(self::REJECTIONSCACPATTERN,$msg,$matches)){
                $scac = $matches[1];
                if(!in_array($scac,$this->scacs)){
                    $this->scacs[] = $scac;
                }
            }
            $content = file($msgFile);
            $this->rejectionFields = $this->parseFields($content[6]);
            for($i = 8; $i < count($content); $i++){
                if(!preg_match(self::CHARATERPATTERN,$content[$i])){
                    continue;
                }
                if(preg_match(self::ENDOFREJECTIONPATTERN,$content[$i])){
                    break;
                }
                $rejections[] = $this->parseRejectionStr($content[$i],$scac);
            }
        }
        $this->rejections = $rejections;
        for($i = 0; $i < count($this->rejections);$i++){
            $this->rejections[$i]['lane'] = trim(strtolower($this->rejections[$i]["Origin"])) . ' to ' . trim(strtolower($this->rejections[$i]["Destination"]));
            switch ($this->rejections[$i]['Component']){
                case "Peak LH":
                    $this->rejections[$i]['peak'] = 1;
                    $this->rejections[$i]["rejection_field"] = "lh_rejection_code";
                    break;
                case "NonPeak SIT":
                    $this->rejections[$i]['peak'] = 0;
                    $this->rejections[$i]['rejection_field'] = "sit_rejection_code";
                    break;
                case "NonPeak LH":
                    $this->rejections[$i]['peak'] = 0;
                    $this->rejections[$i]['rejection_field'] = "lh_rejection_code";
                    break;
                case "Peak SIT":
                    $this->rejections[$i]['peak'] = 1;
                    $this->rejections[$i]['rejection_field'] = "sit_rejection_code";
                    break;
            }
        }
        return $this;
    }
    private function parseFields($fieldStr){
        $data = array();
        if(preg_match(self::RECJECTIONFIELDSTR1,$fieldStr,$matches)){
            $data[] = $matches[0];
        }
        if(preg_match(self::RECJECTIONFIELDSTR2,$fieldStr,$matches)){
            $data[] = $matches[0];
        }
        if(preg_match(self::RECJECTIONFIELDSTR3,$fieldStr,$matches)){
            $data[] = $matches[0];
        }
        if(preg_match(self::RECJECTIONFIELDSTR4,$fieldStr,$matches)){
            $data[] = $matches[0];
        }
        if(preg_match(self::RECJECTIONFIELDSTR5,$fieldStr,$matches)){
            $data[] = $matches[0];
        }
        return $data;
    }
    private function parseRejectionStr($str,$scac){
        $data = array();
        $str = preg_replace(self::REJECTIONSTRPATTERN,'_',$str);
        $pieces = explode('_',$str);
        $data["scac"] = $scac;
        $data[$this->rejectionFields[0]] = $pieces[1];
        $data[$this->rejectionFields[1]] = $pieces[2];
        $data[$this->rejectionFields[2]] = $pieces[3];
        $data[$this->rejectionFields[3]] = $pieces[4];
        $data[$this->rejectionFields[4]] = $pieces[5];
        return $data;
    }
    private function getScacCounts(){
        foreach($this->scacs as $scac){
            $this->scacRejectionCounts[$scac]['total'] = 0;
            $this->scacRejectionCounts[$scac]['LH'] = 0;
            $this->scacRejectionCounts[$scac]['SIT'] = 0;
            $this->scacRejectionCounts[$scac]['PEAK'] = 0;
            $this->scacRejectionCounts[$scac]['NONPEAK'] = 0;
            foreach($this->rejections as $rejection){
                if($scac == $rejection['scac']){
                    $this->scacRejectionCounts[$scac]['total']++;
                    switch ($rejection["rejection_field"]){
                        case "lh_rejection_code":
                            $this->scacRejectionCounts[$scac]['LH']++;
                            break;
                        case "sit_rejection_code":
                            $this->scacRejectionCounts[$scac]["SIT"]++;
                            break;
                    }
                    if($rejection['peak']){
                        $this->scacRejectionCounts[$scac]["PEAK"]++;
                    }else{
                        $this->scacRejectionCounts[$scac]["NONPEAK"]++;
                    }
                }
            }
        }
        return $this;
    }
    private function proofOfWork(){
        echo "Total Rejections: " . count($this->rejections) . "\n";
        echo "Scacs Processed: " . count($this->scacs) . "\n";
        foreach($this->scacRejectionCounts as $key=>$value){
            echo $key . ": " . print_r($value) . "\n";
        }
        $line = RateFactory::readLine("Would you like to import these rejections? ");
        if($line == 'y'){
            $this->importRejections();
        }else{
            echo "GoodBye\n";
            exit;
        }
        return $this;
    }
    private function validateYearInput($year){
        if(!preg_match(self::YEARPATTERN,$year)){
            echo "Invalid year. Try again.\n";
            return false;
        }
        return true;
    }
    private function validateRoundInput($round){
        if($round != 1 && $round!= 2){
            return false;
        }
        return true;
    }
    private function importRejections(){
        $counter = 0;
        echo "Importing " . count($this->rejections) . " records\n";
        foreach($this->rejections as $rejection){
            $data = array();
            $scac = $rejection['scac'];
            $lane = $rejection['lane'];
			if(!is_null($rejection["Error Code"])){
				$data[$rejection["rejection_field"]] = $rejection["Error Code"];
			}else{
				$data[$rejection["rejection_field"]] = 0;
			}
            if($rejection['peak']){
                $table = self::PEAK;
            }else{
                $table = self::NONPEAK;
            }
            $results = $GLOBALS['db']
                ->driver(self::DRIVER)
                ->database(self::DATABASE)
                ->table($table)
                ->data($data)
                ->update()
                ->where("scac = '$scac'")
                ->andWhere("lane = '$lane'")
                ->andWhere("year = '$this->year'")
                ->andWhere("round = '$this->round'")
                ->put();
            $counter++;
        }
        echo $counter . " rejections processed\n";
        $this->cleanUpRejectionMsgs();
        return $this;
    }
    private function cleanUpRejectionMsgs(){
        foreach($this->rejectionMsgs as $msg){
            $source = $this->inputDir . $msg;
            $destination = $this->outputDir . $msg;
            if(!rename($source,$destination)){
                die(print_r(error_get_last()));
            }
        }
        return $this;
    }


}
