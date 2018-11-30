<?php namespace GP;

require_once __DIR__ . '/../../../record.php';


//$vendors = array(
//    18303=>array('CHAT0059649','KKFA0566545'),
//    11004=>array('AGFM0277199','AGFM0286276','BGAC0096628','BGNC0426873','CNNQ0468531','HAFC0414565','LKNQ0251020'),
//    11008=>array('BGNC0410769'),
//    11188=>array('BGAC0396440'),
//    11170=>array('KKFA0510401'),
//    11145=>array('HAFC0414854'),
//    11197=>array('AGFM0258877','AGFM0286364','BGNC0427629','JEAT0269233'),
//    11203=>array('CNNQ0435160','LKNQ0251285'),
//    11204=>array('BKAS0088318','HAFC0414948')
//    );
//
//foreach($vendors as $key=>$values){
//    $g = new GreatPlains($key);
//    echo $key . "\n";
//    foreach($values as $value){
//        if(!$payment = $g->hasOutStandingPayment($value)){
//            echo $value . " No Outstanding Debt\n";
//        }else{
//            foreach($payment as $p){
//                echo $value . " " . $p->amount . "\n";
//            }
//        }
//    }
//}


class GreatPlains{

    const GP = 'greatplains';
    const MSSQL = 'mssql';
    const AAMG = 'AAMG';
    const GL2 = 'GL20000';
    const GL3 = 'GL30000';
    const OPENTRANS = 'RM20101';
    const ACH = 'SY06000';
    public $vendorId;

    public function __construct($vendorId = null)
    {
        if(!is_null($vendorId)){
            $this->vendorId = $vendorId;
        }
    }
    public function hasOutStandingPayment($gbl_dps){
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::GP)
            ->driver(self::MSSQL)
            ->database(self::AAMG)
            ->table(self::OPENTRANS)
            ->select("*")
            ->where("CUSTNMBR","=",$this->vendorId)
            ->andWhere("CSPORNBR","=",$gbl_dps)
            ->andWhere("RMDTYPAL","=",1)
            ->andWhere("cast(DINVPDOF as date)","like","cast('1/1/1900' as date)")
            ->get();
        if(!sqlsrv_num_rows($results)){
            return false;
        }
        $i = 0;
        while($row = mssql_fetch_assoc($results)){
            $data[$i] = new \stdClass();
            $data[$i]->docNumber = $row['DOCNUMBR'];
            $data[$i]->batchNumber = $row['BACHNUMB'];
            $data[$i]->batchSource = $row['BCHSOURC'];
            $data[$i]->amount = $row['ORTRXAMT'];
            $i++;
        }
        return $data;
    }
    public function getAchInfo(){
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::GP)
            ->driver(self::MSSQL)
            ->database(self::AAMG)
            ->table(self::ACH)
            ->select("EFTBankAcct,EFTTransitRoutingNo")
            ->where("CustomerVendor_ID","=",$this->vendorId)
            ->get();
        if(!mssql_num_rows($results)){
            return false;
        }
        while($row = mssql_fetch_assoc($results)){
            $data['acct_num'] = trim($row['EFTBankAcct']);
            $data['routing_num'] = trim($row['EFTTransitRoutingNo']);
        }
        return $data;
    }
}
