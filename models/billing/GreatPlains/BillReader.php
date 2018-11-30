<?php namespace GP;

require_once __DIR__ . '/../invoice/Invoice.php';

class BillReader{

    const GBLPATT = '/[A-Z]{4}[0-9]{7}/';
    const CODEPATT = '/[0-9]{2,3}[A-Z]{1}/';
    const DIR = '/Hybrid/gpshare/Movestar/Completed/';
    const HEAD = 'f0411tabV2';
    const DETAIL = 'f4911tabV2';

    protected $targetHeader;
    protected $targetDetail;

    public $header;
    public $detail;

    public function __construct()
    {
        $this->targetHeader = $this->_buildFileName(true);
        $this->targetDetail = $this->_buildFileName(false);
        if(!is_file($this->targetHeader) || !is_file($this->targetDetail)){
            throw new \Exception('Required Source Docs Unavailable');
        }
        $this->header = $this->_readFile($this->targetHeader);
        $this->detail = $this->_readFile($this->targetDetail);
    }
    protected function _readFile($path){
        $data = array();
        $csv = array_map(function($v){return str_getcsv($v,"\t");}, file($path));
        foreach($csv as $index => $values){
            foreach($values as $value){
                $data[$index][] = trim($value);
            }
        }
        return $data;
    }
    protected function _getLineItemCodes(){
        $data = array();
        $results = $GLOBALS['db']
            ->suite('mssql')
            ->driver('mssql')
            ->database('Sandbox')
            ->table('ctl_invoice_line_item_codes')
            ->select('id')
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new \LineItemCode($row['id']);
        }
        return $data;
    }
    protected function _buildFileName($head = true){
        $path = self::DIR;
        if($head){
            $path .= self::HEAD . date('Y',strtotime('yesterday')) . date('m',strtotime('yesterday'));
        }else{
            $path .= self::DETAIL . date('Y',strtotime('yesterday')) . date('m',strtotime('yesterday'));
        }
        $dayofWeek = date('N');
        if($dayofWeek == 1){
            $path .= (date('d',strtotime('last friday')));
        }else{
            $path .= (date('d',strtotime('yesterday')));
        }
        $path .= ".txt";
        return $path;
    }
    public function getGbls(){
        $data = array();
        foreach($this->header as $index => $values){
            if(preg_match(self::GBLPATT,$values[5],$matches)){
                $data[] = $matches[0];
            }
        }
        return $data;
    }
    public function getDocNumber($gbl){
        foreach($this->header as $index => $values){
            if(preg_match(self::GBLPATT,$values[5],$matches)){
                if($matches[0] == $gbl){
                    return $values[0];
                }
            }
        }
        return false;
    }
    public function getTotal($gbl){
        foreach($this->header as $index => $values){
            if(preg_match(self::GBLPATT,$values[5],$matches)){
                if($matches[0] == $gbl){
                    return $values[4];
                }
            }
        }
        return false;
    }
    public function getLineItems($docNumber){
        $data = array();
        foreach($this->detail as $index=>$values){
            if($values[0] == $docNumber){
                $data[] = $values;
            }
        }
        return $data;
    }
    public function verifyCodes(){
        $codes = $this->_getLineItemCodes();
        $skippedCodes = array();
        $foundCodes = array();
        foreach($this->detail as $index=>$values){
            if(preg_match(self::CODEPATT,$values[4],$matches)){
                $testCode = $matches[0];
            }else{
                continue;
            }
            foreach($codes as $code){
                $pattern = "/" . $code->code . "/";
                if(preg_match($pattern,$values[4])){
                    if(!in_array($code->code,$foundCodes)){
                        $foundCodes[] = $code->code;
                    }
                }
            }
            if(!in_array($testCode,$foundCodes) && !in_array($testCode,$skippedCodes)){
                $skippedCodes[] = $testCode;
            }
        }
        print_r($foundCodes);
        return $skippedCodes;
    }
}
