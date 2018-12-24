<?php

const DB = "/srv/www/htdocs/classes/db.php";
const EXCELREADER = 'libs/excel_reader2.php';

const DATABASE = 'test';

require DB;
include EXCELREADER;


if(!isset($GLOBALS['db'])){
    $db = RateFactory::generateDB();
}
if(PHP_SAPI == "cli"){
    $params = $argv;
}elseif(isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "GET"){
    $params = $_REQUEST;
}else{
    $params = json_decode(file_get_contents('php://input',true));
}
if($params){
    //$a = new AnalyticsReader();
    //$rc = new RateCopy('AAMG','ADVA',2,2017);
    //RateFactory::initiateMain($params);
}

class Main{

    private $data = array();

    public function __construct($params)
    {
        if(PHP_SAPI == "cli"){
            $this->cliNavigate($params);
        }else{
            $this->navigate($params);
        }
    }
    private function cliNavigate($params){
        $this->showMenu();
        return $this;
    }
    private function navigate($params){
        foreach($params as $key=>$value){
            $this->data[$key] = $value;
        }
        if(isset($this->data["getScacList"])){
            echo JSON_ENCODE(RateFactory::getScacList());
        }elseif(isset($this->data["getLaneList"])){
            echo JSON_ENCODE(RateFactory::getLaneList());
        }elseif(isset($this->data["getChannelList"])){
            echo JSON_ENCODE(RateFactory::getChannelList());
        }elseif(isset($this->data["getRegionList"])){
            echo JSON_ENCODE(RateFactory::getRegionList());
        }elseif(isset($this->data["getYearList"])){
            echo JSON_ENCODE(RateFactory::getYearList());
        }elseif(isset($this->data["findRates"])){
            echo JSON_ENCODE(RateFactory::findRates($this->data["findRates"]));
        }elseif(isset($this->data["saveAdj"])){
            if(RateFactory::updateLane($this->data["saveAdj"])){
                echo "success";
            }else{
                echo "failure";
            }
        }elseif(isset($this->data["export"])){
            RateFactory::export($this->data["export"]);
        }elseif (isset($this->data['copyExport'])){
            if(RateFactory::export($this->data['copyExport'])){
                $sourceScac = $this->data['copyExport']->scac;
                $round = $this->data['copyExport']->round;
                $year = $this->data['copyExport']->year;
                $targetScac = $this->data['copyTo'];
                if(RateFactory::rateCopy($sourceScac,$targetScac,$round,$year)){
                    $params = RateFactory::blankObject();
                    $params->scac = $targetScac;
                    $params->round = $round;
                    $params->year = $year;
                    RateFactory::export($params);
                }
            }else{
                echo 'exportFailure';
            }
        }elseif(isset($this->data['cheapProjection'])){
            $c = new CheapProjection();
        }
        return $this;
    }
    private function showMenu(){
        echo "Welcome to the Rates Program CLI\n";
        echo "Please Make a selection: \n";
        echo "1. Genesis Data Import\n";
        echo "2. Stanard Import\n";
        echo "3. Rejection Import\n";
        echo "4. Calculate BKAR\n";
        echo "5. Build Lane Volume Data\n";
        echo "6. Calculate LKAR\n";
        $line = RateFactory::readLine();
        if($line == 1){
            $g = new GenesisImport();
        }elseif($line == 2){
            $i = new Import();
        }elseif($line == 3){
            $r = new RejectionImport();
        }elseif($line == 4){
            $year = RateFactory::readLine("Year: ");
            $round = RateFactory::readLine("Round: ");
            $b = new BkarCalculator($year,$round);
        }elseif($line == 5){
            $l = new LaneData();
        }elseif($line == 6){
            $year = RateFactory::readLine("Year: ");
            $round = RateFactory::readLine("Round: ");
            $l = new LkarCalculator($year,$round);
        }else{
            $this->showMenu();
        }
        return $this;
    }
}
