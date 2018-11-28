<?php namespace Amc;

class ChargeBack{

    const HTMLDIR = '/scan/fPImages/Reports/chargebacks/sent/';
    const PDFROOT = '/scan/fPImages/';
    const DOLLARPATT = '/\$\s(.*?)<\/strong>/';
    const BIGROLEPATT = '/Invoice #: (.*)<br \/>/';
    const FINALROLTEPATT = '/..$/';
    const DAYMONTHPATT = '/....$/';
    const YEARPATT = '/[0-9]{4}/';

    public function __construct()
    {
    }
    protected static function _buildYears(){
        $dirs = array();
        $results = scandir(self::PDFROOT);
        foreach($results as $result){
            if(is_dir(self::PDFROOT . $result) && preg_match(self::YEARPATT,$result)){
                $dirs[] = self::PDFROOT . $result . "/GOVDOC/";
            }
        }
        return $dirs;
    }
    protected static function _buildHtmlPattern($vendorId,$gbl){
        return "/ChargebackAgent_" . $vendorId . "_" . $gbl . "_/";
    }
    protected static function _parseDate($fileName){
        $pieces = explode("_",$fileName);
        if(preg_match(self::DAYMONTHPATT,$pieces[3],$matches)){
            return substr_replace($matches[0],"-", 2, 0);
        }
        return false;
    }
    public static function getHtml($vendorId,$gbl){
        $values = array();
        $pattern = self::_buildHtmlPattern($vendorId,$gbl);
        $results = scandir(self::HTMLDIR);
        foreach($results as $result){
            $newValue = new stdClass();
            if($result != '..' && $result != '..' && preg_match($pattern,$result)){
                $date = self::_parseDate($result);
                $file = self::HTMLDIR . $result;
                $lines = file_get_contents($file);
                if(preg_match(self::BIGROLEPATT,$lines,$matches)){
                    if(preg_match(self::FINALROLTEPATT,$matches[1],$roleMatches)){
                        $role = $roleMatches[0];
                    }
                }
                if(preg_match(self::DOLLARPATT,$lines,$matches)){
                    $dollarValue = $matches[1];
                    $newValue->role = $role;
                    $newValue->date = $date;
                    $newValue->amount = $dollarValue;
                    $values[] = $newValue;
                }
            }
        }
        return $values;
    }
    public static function getPdf($gbl,$role,$date){
        $invStr = "CL-Invoice";
        switch ($role){
            case "OA":
                $pattern = "/" . $invStr . "OA-" . $date . "/";;
                break;
            case "DA":
                $pattern = "/" . $invStr . "DA-" . $date . "/";;
                break;
            case "HA":
                $pattern = "/" . $invStr . "HA-" . $date . "/";
                break;
            default:
                throw new Exception('Unsupported Role');
        }
        $years = self::_buildYears();
        $data = array();
        foreach($years as $yearDir){
            $longDir = $yearDir . $gbl;
            if(is_dir($longDir)){
                $results = scandir($longDir);
                foreach($results as $res){
                    if(preg_match($pattern,$res)){
                        $data[] = $res;
                    }
                }
            }
        }
        return $data;
    }
    public function getMoveStar(){}
}
