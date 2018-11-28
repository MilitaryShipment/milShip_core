<?php
require_once __DIR__ . '/../record.php';
require_once __DIR__ . '/../thirdParty/jwt.php';
require_once __DIR__ . '/../messenger.php';
require_once __DIR__ . '/invoice.php';


class Utilities{

    const MSSQL = 'mssql';
    const SANDBOX = 'Sandbox';
    const GBLOC = 'tbl_gbloc';
    const BLACKOUT = 'tbl_agents_blackouts';
    const INVOICECODES = 'ctl_invoice_line_item_codes';

    public function __construct()
    {
    }

    public static function getGlocList(){
        $gblocs = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::GBLOC)
            ->select("distinct gbloc")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $gblocs[] = strtoupper($row['gbloc']);
        }
        return $gblocs;
    }
    public static function getGblocAreas($gbloc){
        $areas = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::GBLOC)
            ->select("distinct area")
            ->where("gbloc = '$gbloc'")
            ->get();
        if(!mssql_num_rows($results)){
            throw new Exception('No Areas. Invalid Gbloc?');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $areas[] = $row['area'];
            }
        }
        return $areas;
    }
    public static function getBlackOutTypes(){
        $types = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::BLACKOUT)
            ->select("distinct type_blackout")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $types[] = $row['type_blackout'];
        }
        return $types;
    }
    public static function getBaseName($gbloc,$area){
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::GBLOC)
            ->select("base_name")
            ->where("gbloc = '$gbloc'")
            ->andWhere("area = '$area'")
            ->get("value");
        return $results;
    }
    public static function getInvoiceLineItemCodes(){
        $lineItems = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::INVOICECODES)
            ->select("id")
            ->orderBy("agent_description")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $lineItems[] = new LineItemCode($row['id']);
        }
        return $lineItems;
    }
    public static function updateACHInfo($vendorId,$accountNum,$routingNum){
        $secret = trim(file_get_contents("/home/tjjw1/.teh_secret"));
        $data = array("acountNum"=>$accountNum,"routingNum"=>$routingNum);
        $token = JWT::encode($data,$secret);
        $to = 'cfo@allamericanmoving.com';
        $to = 'j.watson@allamericanmoving.com';
        $subject = "ATTN: " . $vendorId . " ACH UPDATE";
        $body = "Please decode this updated ACH information and update in Great Plains:\n\n";
        $body .= $token;
        Messenger::send($to,"Centurion@militaryshipment.com",'Centurion','j.watson@allamericanmoving.com','','',$subject,$body,'');
        return $token;
    }
}


//$data = array(
//    "acountNum"=>1234,
//    "routingNum"=>5647
//);
//echo JWT::encode($data,"AFULLONSECRETKEY") . "\n";