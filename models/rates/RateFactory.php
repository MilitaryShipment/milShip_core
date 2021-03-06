<?php


require_once '/srv/www/htdocs/classes/excel_reader2.php';

require_once __DIR__ . '/Lane.php';
require_once __DIR__ . '/ScacList.php';
require_once __DIR__ . '/LaneList.php';
require_once __DIR__ . '/RegionList.php';
require_once __DIR__ . '/ChannelList.php';
require_once __DIR__ . '/YearList.php';
require_once __DIR__ . '/Scac.php';
require_once __DIR__ . '/../../processes/rates/RateSearch.php';
require_once __DIR__ . '/../../processes/rates/RateExport.php';
require_once __DIR__ . '/../../processes/rates/RateCopy.php';
require_once __DIR__ . '/../../processes/rates/RejectionImport.php';
require_once __DIR__ . '/../../processes/rates/BookingImport.php';
require_once __DIR__ . '/../../processes/rates/LkarCalculator.php';
require_once __DIR__ . '/../../processes/rates/BkarCalculator.php';
require_once __DIR__ . '/../../processes/rates/autofile/Round1Pasture.php';
require_once __DIR__ . '/../../processes/rates/autofile/Round1RedHarvest.php';
require_once __DIR__ . '/../../processes/rates/autofile/Round1SlapDash.php';

class RateFactory{

    public static function generateDB(){
        return new DB();
    }
    public static function initiateMain($params){
        return new Main($params);
    }
    public static function getScacList(){
        $s = new ScacList();
        return $s->scacs;
    }
    public static function getLaneList(){
        $l = new LaneList();
        return $l->lanes;
    }
    public static function getRegionList(){
        $l = new RegionList();
        return $l->regions;
    }
    public static function getChannelList(){
        $l = new ChannelList();
        return $l->channels;
    }
    public static function getYearList(){
        $y = new YearList();
        return $y->years;
    }
    public static function buildScac($scac,$round,$year){
        return new Scac($scac,$round,$year);
    }
    public static function buildLane($id = null,$peak = true){
        return new Lane($id,$peak);
    }
    public static function readLine($prompt = false){
        if($prompt){
            echo $prompt;
        }
        $fp = fopen('php://stdin','r');
        $line = rtrim(fgets($fp,1024));
        return $line;
    }
    public static function findRates($params){
        $r = new RateSearch($params);
        return $r->results;
    }
    public static function readXcel($inputFile){
        return new Spreadsheet_Excel_Reader($inputFile);
    }
    public static function readCsv($file){
      return array_map('str_getcsv', file($file));
    }
    public static function createAnalyticsFile(){
        return new AnalyticsFile();
    }
    public static function createAnalyticsRate(){
        return new AnalyticsRate();
    }
    public static function updateLane($updateObj){
        if($updateObj->peak){
            $peak = true;
        }else{
            $peak = false;
        }
        $data = array(
            "sit_adj"=>$updateObj->sit_adj,
            "lh_adj"=>$updateObj->lh_adj
        );
        $l = new Lane($updateObj->id,$peak);
        $l->update($data,$peak);
        return true;
    }
    public static function export($params){
        $r = new RateExport($params);
        return true;
    }
    public static function rateCopy($fromScac,$toScac,$round,$year){
        $r = new RateCopy($fromScac,$toScac,$round,$year);
        return true;
    }
    public static function blankObject(){
        return new stdClass();
    }
    public static function calculateLkar($year,$round){
      return new LkarCalculator($year,$round);
    }
    public static function calculateBkar($year,$round){
      return new BkarCalculator($year,$round);
    }
    public static function round1Pasture($year,$scacsArray){
      return new Round1Pasture($year,$scacsArray);
    }
    public static function round1RedHarvest($year,$redScacs,$harvestScacs){
      return new Round1RedHarvest($year,$redScacs,$harvestScacs);
    }
    public static function round1SlapDash($year,$scacs,$peak = true){
      return new Round1SlapDash($year,$scacs,$peak);
    }
    public static function importBookings(){
      return new BookingImport();
    }
    public static function importRejections(){
      return new RejectionImport();
    }
}
