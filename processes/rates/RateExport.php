<?php


class RateExport{

    const OUTROOT = 'data';
    const OUTPUTDIR = 'data/output/';
    const OUTPUTARCHIVE = 'data/output/rates.zip';

    private $scacLabel;
    private $round;
    private $year;
    private $scac;
    private $exportFile;
    private $export = array();
    protected $outRoot;
    protected $outDir;
    protected $outArchive;

    public function __construct($params)
    {
        $this->outRoot = __DIR__ . "/" . self::OUTROOT;
        $this->outDir = __DIR__ . "/" . self::OUTPUTDIR;
        $this->outArchive = __DIR__ . "/" . self::OUTPUTARCHIVE;
        $this->scacLabel = $params->scac;
        $this->round = $params->round;
        $this->year = $params->year;
        try{
          $this->scac = RateFactory::buildScac($this->scacLabel,$this->round,$this->year);  
        }catch(\Exception $e){
          throw new \Exception $e->getMessage();
        }
        $this->_prepForOutPut()
            ->buildOutPutFile()
            ->buildExport()
            ->buildExport(false)
            ->export()
            ->appendZip();
    }
    private function buildOutPutFile(){
        if($this->round == 1){
            $round = 2;
            $year = $this->year;
        }else{
            $round = 1;
            $year = $this->year + 1;
        }
        $this->exportFile = $this->outDir . $this->scacLabel . ' - Round ' . $round . ' - ' . $year . '.csv';
        return $this;
    }
    private function buildExport($peak = true){
        if($peak){
            $arr = $this->scac->peakLanes;
        }else{
            $arr = $this->scac->nonPeakLanes;
        }
        foreach($arr as $a){
            if($peak){
                $export = array();
                $this->export[$a->lane] = array();
                $export[] = $a->scac;
                $export[] = $a->market_code;
                $export[] = $a->domestic_rate_area_code;
                $export[] = $a->domestic_region_id;
                $export[] = $a->service_code;
                $export[] = $a->lh_adj;
                $export[] = $a->sit_adj;
                $this->export[$a->lane] = $export;
            }else{
                $this->export[$a->lane][] = $a->lh_adj;
                $this->export[$a->lane][] = $a->sit_adj;
            }
        }
        return $this;
    }
    private function export(){
        $file = fopen($this->exportFile,'w');
        if(!$file){
          $error = error_get_last();
          throw new \Exception($error['message']);
        }
        foreach($this->export as $lane=>$values){
            fputcsv($file,$values);
        }
        fclose($file);
        $lines = file_get_contents($this->exportFile);
        $lines = preg_replace("/\"/","",$lines);
        file_put_contents($this->exportFile,$lines);
        return $this;
    }
    private function appendZip(){
        $output = shell_exec('zip ' . $this->outArchive . ' ' . escapeshellarg($this->exportFile));
//        if($output){
//            die($output);
//        }
        return $this;
    }
    protected function _prepForOutPut(){
      if(!is_dir($this->outRoot) && !mkdir($this->outRoot)){
        $error = error_get_last();
        throw new \Exception($error['message']);
      }elseif(!is_dir($this->outDir) && !mkdir($this->outDir)){
        $error = error_get_last();
        throw new \Exception($error['message']);
      }
      return $this;
    }
}
