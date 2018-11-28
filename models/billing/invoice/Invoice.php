<?php

date_default_timezone_set('America/Chicago');

require_once __DIR__ . '/../record.php';
require_once __DIR__ . '/../pdfWriter.php';
require_once __DIR__ . '/shipment.php';
require_once __DIR__ . '/vendor.php';
require_once __DIR__ . '/agent.php';
require_once __DIR__ . '/chargeBack.php';
require_once __DIR__ . "/epayImage.php";
require_once __DIR__ . '/../dc/recEmail.php';
require_once __DIR__ . '/../movestar/agent.php';
require_once __DIR__ . '/../movestar/agentTranslation.php';

class Invoice{

    const MSSQL = 'mssql';
    const SANDBOX = 'Sandbox';
    const INVOICES = 'tbl_invoice_tracking';
    const CELLHEADER = '#DCDCDC';
    const LINECOLOR = '#ff0000';
    const DEFAULTCOLOR = '#000000';
    const WEBSTORAGE = '/srv/www/htdocs/ms/services/data/invoices/';
    const WEBPATH = '/services/data/invoices/';
    const PDFSTORAGE = '/scan/fPImages/';
    const EPAYSTORAGE = '/webpdf/epay/wip/processed/';
    const EPAYVENDOR = '/webpdf/';
    const BUCKET = '/scan/silo/mobiledata/oaPpwk/';
    const FIVEPERCENT = 'Fast Pay 5% - Next Business Day';
    const TWOPERCENT = 'Fast Pay 2% - 10 Days';
    const THREEPERCENT = 'Fast Pay 3% - 5 Days';
    const NET30 = 'Net 30';

    /*
     * PDFSTORAGE filename = /scan/fPImages/$year/GOVDOC/$shortGbl/INVOICE-E-$month-$day.pdf
     * EPAYSTORAGE filename == "/webpdf/epay/wip/processed/INV_" . $gbl_dps . "_" . $vendor_id . "." . $ext;
     * EPAYVENDOR filename == "/webpdf/$gbl_dps/ACCT/$vendor_id/INV_" . $vendor_id . ".pdf";
     * */

    protected $writer;
    public $gbl_dps;
    public $agentId;
    public $lineItems = array();
    public $webPath;

    public function __construct($gbl_dps,$agentId)
    {
        if(!is_null($gbl_dps) && !is_null($agentId)){
            $this->gbl_dps = $gbl_dps;
            $this->agentId = $agentId;
            $this->_build();
        }
    }
    public static function invoiceExists($gbl_dps,$role){
        switch (strtolower($role)){
            case "origin agent":
                $and = "OA = 1";
                break;
            case "destination agent":
                $and = "DA = 1";
                break;
            case "hauling agent":
                $and = "HA = 1";
                break;
            default:
                throw new Exception('Unsupported role');
        }
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::INVOICES)
            ->select("gbl_dps")
            ->where("gbl_dps = '$gbl_dps'")
            ->andWhere($and)
            ->get();
        if(!mssql_num_rows($results)){
            return false;
        }
        return true;
    }
    public static function delete($gbl_dps,$agentId){
        $ids = array();
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::INVOICES)
            ->select("id")
            ->where("gbl_dps = '$gbl_dps'")
            ->andWhere("agent_id = '$agentId'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row['id'];
        }
        foreach($ids as $id){
            $query = "delete from Sandbox.dbo." . self::INVOICES . " where id = " . $id;
            $GLOBALS['db']->query = $query;
            $GLOBALS['db']->put();
        }
        return true;
    }
    protected function _build(){
        $results = $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table(self::INVOICES)
            ->select("id")
            ->where("gbl_dps = '$this->gbl_dps'")
            ->andWhere("agent_id = '$this->agentId'")
            ->orderBy("line_item_no")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $this->lineItems[] = new LineItem($row['id']);
        }
        return $this;
    }
    protected function _buildFileName(){
        //$fileName = "LHNQ0011843_INVOICE-E.pdf";
        $fileName = "INVOICE-E_" . $this->gbl_dps . "_" . $this->lineItems[0]->vendorID . "_" . date('m') . "-" . date('d') . ".pdf";
        $this->webPath = self::WEBPATH . $fileName;
        return $fileName;
    }
    protected function _writeHeader(){
        try{
            $s = new Shipment($this->gbl_dps);
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        try{
            $a = new Agent($this->lineItems[0]->agent_id);
        }catch(Exception $e){
            $a = new \Movestar\Agent($this->lineItems[0]->agent_id);
            $a = \Movestar\AgentTranslation::translate($a);
        }
        $agentStr = $a->full_legal_name . " (" . $a->agentid_number . ", " . $this->lineItems[0]->vendorID . ")";
        $remitStr = "Remit to: " . $a->remit_to_addr1 . " " . $a->remit_to_city . " " . $a->remit_to_state . " " . $a->remit_to_zip;
        $toStr = $s->dest_city . " " . $s->dest_state;
        $fromStr = $s->orig_city . " " . $s->orig_state;
        $this->writer = new PdfWriter();
        $this->writer->setFontSize(16);
        $this->writer->write("Invoice: " . $this->gbl_dps . " | Invoice #: " . $this->lineItems[0]->client_doc_id);
        $this->writer->setX($this->writer->getX() + 20);
        $this->writer->write("Invoice Date: " . date('m/d/Y'));
        $this->writer->addLineBreak();
        $this->writer->setFontSize(14);
        $this->writer->filePath = self::WEBSTORAGE . $this->_buildFileName();
        $this->writer->setFillColor(self::CELLHEADER);
        $this->writer->write($agentStr);
        $this->writer->addLineBreak();
        $this->writer->setFontSize(10);
        $this->writer->write($remitStr);
        $this->writer->addLineBreak();
        $this->writer->setFontSize(8);
        $this->writer->addCell(40,8,"Shipper",1,0,"L",true);
        $this->writer->addCell(40,8,"From",1,0,"L",true);
        $this->writer->addCell(40,8,"To",1,0,"L",true);
        $this->writer->addCell(40,8,"Pickup",1,0,"L",true);
        $this->writer->addLineBreak();
        $this->writer->addCell(40,10,$s->full_name,1,0,"L",false);
        $this->writer->addCell(40,10,$fromStr,1,0,"L",false);
        $this->writer->addCell(40,10,$toStr,1,0,"L",false);
        $this->writer->addCell(40,10,date('m/d/Y',strtotime($s->pickup_date)),1,0,"L",false);
        $this->writer->addLineBreak();
        return $this;
    }
    protected function _writeBody(){
        $this->writer->addLineBreak();
        $this->writer->addCell(20,8,"Code",1,0,"L",true);
        $this->writer->addCell(75,8,"Description",1,0,"L",true);
        $this->writer->addCell(20,8,"Rate",1,0,"L",true);
        $this->writer->addLineBreak();
        foreach($this->lineItems as $lineItem){
            $this->writer->addCell(20,10,$lineItem->line_item_code,1,0,"L",false);
            $this->writer->addCell(75,10,$lineItem->line_item_description,1,0,"L",false);
            $this->writer->addCell(20,10,money_format("%i",$lineItem->payment_amount),1,0,"R",false);
            $this->writer->addLineBreak();
        }
        $this->writer->addLineBreak();
        return $this;
    }
    protected function _writeFooter(){
        $total = "$" . $this->calculateTotal();
        $this->writer->setX(-105);
        $this->writer->addCell(25,8,"Payment Option",1,0,"L",true);
        $this->writer->addCell(45,8,$this->lineItems[0]->preferred_payment_speed,1,0,"L",false);
        $this->writer->addLineBreak();
        $this->writer->setX(-105);
        $this->writer->addCell(25,8,"Sub Total",1,0,"L",true);
        $this->writer->addCell(45,8,$total,1,0,"R",false);
        $this->writer->addLineBreak();
        $this->writer->setX(-105);
        $this->writer->addCell(25,8,"Discount",1,0,"L",true);
        $this->writer->addCell(45,8,"$" . money_format("%i",$this->calculateDiscount()),1,0,"R",false);
        $this->writer->addLineBreak();
        $this->writer->setX(-105);
        $this->writer->addCell(25,8,"Grand Total",1,0,"L",true);
        $this->writer->addCell(45,8,"$" . money_format("%i",$this->calculateGrandTotal()),1,0,"R",false);
        $this->writer->addLineBreak();
        return $this;
    }
    protected function _writeHackedBody($verifiedValues){
        $this->writer->addLineBreak();
        $this->writer->addCell(20,8,"Code",1,0,"L",true);
        $this->writer->addCell(75,8,"Description",1,0,"L",true);
        $this->writer->addCell(20,8,"Rate",1,0,"L",true);
        $this->writer->addCell(20,8,"Actual Rate",1,0,"L",true);
        $this->writer->addLineBreak();
        foreach($this->lineItems as $lineItem){
            $this->writer->addCell(20,10,$lineItem->line_item_code,1,0,"L",false);
            $this->writer->addCell(75,10,$lineItem->line_item_description,1,0,"L",false);
            $x = $this->writer->getX();
            $y = $this->writer->getY();
            $this->writer->addCell(20,10,$lineItem->payment_amount,1,0,"R",false);
            $x2 = $this->writer->getX();
            $y2 = $this->writer->getY();
            if($verifiedValues[$lineItem->line_item_code] != $lineItem->payment_amount){
                $this->writer->setDrawColor(self::LINECOLOR);
                $this->writer->line($x,$y,$x2,($y2 + 5));
                $this->writer->setDrawColor(self::DEFAULTCOLOR);
                $lineItem->payment_amount = $verifiedValues[$lineItem->line_item_code];
            }
            $this->writer->addCell(20,10,$verifiedValues[$lineItem->line_item_code],1,0,"R",false);
            $this->writer->addLineBreak();
        }
        return $this;
    }
    public function writePdf($verifiedValues = array()){
        $this->_writeHeader();
        if(count($verifiedValues)){
            $this->_writeHackedBody($verifiedValues);
        }else{
            $this->_writeBody();
        }
        $this->_writeFooter();
        $this->writer->save();
        return $this;
    }
    public function previewPdf(){
        $this->_writeHeader();
        $this->_writeBody();
        $this->writer->sendToBrowser();
        return $this;
    }
    public function calculateTotal(){
        $total = 0;
        foreach($this->lineItems as $lineItem){
            $total += ($lineItem->payment_amount * $lineItem->line_item_quantity);
        }
        return $total;
    }
    public function calculateGrandTotal(){
        $total = $this->calculateTotal();
        if($this->lineItems[0]->preferred_payment_speed == self::FIVEPERCENT){
            $total *= (1 - 0.05);
        }elseif($this->lineItems[0]->preferred_payment_speed == self::TWOPERCENT){
            $total *= (1 - 0.02);
        }elseif($this->lineItems[0]->preferred_payment_speed == self::THREEPERCENT){
            $total *= (1 - 0.03);
        }
        return $total;
    }
    public function calculateDiscount(){
        $total = $this->calculateTotal();
        $discount = 0;
        if($this->lineItems[0]->preferred_payment_speed == self::FIVEPERCENT){
            $discount = $total * 0.05;
        }elseif($this->lineItems[0]->preferred_payment_speed == self::TWOPERCENT){
            $discount = $total * 0.02;
        }elseif($this->lineItems[0]->preferred_payment_speed == self::THREEPERCENT){
            $discount = $total * 0.03;
        }
        return $discount;
    }
    public function moveToScan(){
        if(!isset($this->webPath)){
            throw new Exception('Trying to move with no source!');
        }
        $source = self::WEBSTORAGE . $this->_buildFileName();
        $destination = self::PDFSTORAGE . date('Y') . '/GOVDOC/' . $this->gbl_dps . '/' . $this->_buildFileName();
        if(!copy($source,$destination)){
            $error = error_get_last();
            $errorStr = 'Failed to copy: ' . $error['message'];
            throw new Exception($errorStr);
        }
        if(!unlink($source)){
            $errorStr = 'Failed to clean up' . $source;
            throw new Exception($errorStr);
        }
        return $this;
    }
    public function copyToBucket(){
        if(!isset($this->webPath)){
            throw new Exception('Trying to move with no source!');
        }
        $source = self::WEBSTORAGE . $this->_buildFileName();
        $destination = self::BUCKET . $this->gbl_dps . "_INVOICE-E_" . $this->lineItems[0]->agent_id . ".pdf";
        if(!copy($source,$destination)){
            $error = error_get_last();
            $errorStr = 'Failed to copy: ' . $error['message'];
            throw new Exception($errorStr);
        }
        return $this;
    }
    public function getFileName(){
        return self::PDFSTORAGE . date('Y') . '/GOVDOC/' . $this->gbl_dps . '/' . $this->_buildFileName();
    }
}
class LineItem extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_invoice_tracking';

    public $id;
    public $gbl_dps;
    public $client_doc_id;
    public $client_cust_num;
    public $line_item_no;
    public $line_item_code;
    public $line_item_description;
    public $line_item_quantity;
    public $payment_amount;
    public $agent_id;
    public $vendorID;
    public $preferred_payment_speed;
    public $OA;
    public $HA;
    public $DA;
    public $created_date;
    public $updated_date;
    public $created_by;
    public $updated_by;
    public $status_id;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,$id);
    }

}
class LineItemCode extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_invoice_line_item_codes';
    const PRIMARYKEY = 'id';

    public $code;
    public $description;
    public $agent_description;
    public $type;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
class InvoiceMonitor{

    //todo _parseDebts can only find chargeback depts. Find solutions for GSA and misc_invoices
    //todo _parseDebts currently only handles agent in question. Expand to parse debts for whole COG.
    //todo if an invoice is created AFTER it is billed, InvoiceMonitor will never find it.

    const CUSTOMCODE = 'XX99';
    const DOCTYPE = 'INVOICE-E';

    protected $invoices;

    public function __construct()
    {
        $this->_build();
        foreach($this->invoices as $invoice){
            if(!$this->_hasBeenBilled($invoice->gbl_dps)){
                //gbl has not been billed. | Could have been billed in the past, no way to know.
                continue;
            }
            foreach($invoice->lineItems as $lineItem){
                if($lineItem->line_item_code == self::CUSTOMCODE){
                    //todo custom code found. Do not hack slash
                }
            }
            // There are no custom codes, do hack and slash
            $verifiedAmounts = $this->_compareValues($invoice);
            if(!$verifiedAmounts){
                $invoice->writePdf();
            }else{
                $invoice->writePdf($verifiedAmounts);
            }
            // End Hack and Slash
            $debts = $this->_parseDebts($invoice->lineItems[0]->agent_id);
            if(!count($debts)){
                //todo there are no depts
            }
            $invoice->moveToScan();
            $this->_createRecEmail($invoice);
//            echo $invoice->calculateTotal() . "\n";
//            print_r($debts);
        }
    }
    protected function _build(){
        $results = $GLOBALS['db']
            ->suite('mssql')
            ->driver('mssql')
            ->database('Sandbox')
            ->table('tbl_invoice_tracking')
            ->select("distinct gbl_dps")
            ->where("status_id = 1")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $this->invoices[] = new Invoice($row['gbl_dps']);
        }
        return $this;
    }
    protected function _hasBeenBilled($gbl){
        try{
            $bill = new GpBillReader();
            $docNumber = $bill->getDocNumber($gbl);
            if(!$docNumber){
                return false;
            }
        }catch(Exception $e){
            echo $e->getMessage() . "\n";
        }
        return true;
    }
    protected function _parseDebts($agent_id){
        $debts = array();
        $agent = new Agent($agent_id);
        $shipments = $agent->getShipments("history");
        foreach($shipments as $shipment){
            $gbl_dps = $shipment->gbl_dps;
            $result = $agent->hasOutStandingPayment($gbl_dps);
            if(!$result){continue;}
            $proof = ChargeBack::getHtml($agent->vendorid_number,$gbl_dps);
            if(!$proof){continue;}
            foreach($result as $res){
                foreach($proof as $p){
                    $pdf = ChargeBack::getPdf($gbl_dps,$p->role,$p->date);
                    if(round($res->amount,2) == round($p->amount,2)){
                        $debt = new stdClass();
                        $debt->amount = $p->amount;
                        $debt->proof = $pdf[0];
                        $debts[] = $debt;
                    }
                }
            }
        }
        return $debts;
    }
    protected function _compareValues($invoice){
        $data = array();
        $v = new StaticValues();
        if(!isset($v->gbls[$invoice->gbl_dps])){
            echo "Cannot compare values\n";
            return false;
        }else{
            $codes = $v->gbls[$invoice->gbl_dps][$invoice->lineItems[0]->vendorID]['codes'];
            $values = $v->gbls[$invoice->gbl_dps][$invoice->lineItems[0]->vendorID]['values'];
        }
        foreach($invoice->lineItems as $lineItem){
            if(!in_array($lineItem->line_item_code,$codes)){
                echo "Invalid Code: " . $lineItem->line_item_code . "\n";
                continue;
            }
            $index = array_search($lineItem->line_item_code,$codes);
            if($values[$index] == $lineItem->payment_amount){
                $data[$lineItem->line_item_code] = $values[$index];
            }elseif($values[$index] > $lineItem->payment_amount){
                $data[$lineItem->line_item_code] = $lineItem->payment_amount;
            }else{
                $data[$lineItem->line_item_code] = $values[$index];
            }
        }
        return $data;
    }
    protected function _createRecEmail($invoiceObj){
        try{
            $shipment = new Shipment($invoiceObj->gbl_dps);
            $agent = new Agent($invoiceObj->lineItems[0]->agent_id);
        }catch(Exception $e){
            echo $e->getMessage() . "\n";
        }
        $now = date("Y-m-d H:i:s");
        $rec = new RecEmail();
        $rec->gb = $shipment->getShortGbl();;
        $rec->gbl = $shipment->gbl_dps;
        $rec->order_number = $shipment->registration_number;
        $rec->member_name = $shipment->full_name;
        $rec->doc_date = $now;
        $rec->doc_type = self::DOCTYPE;
        $rec->doc_path = $invoiceObj->getFileName();
        $rec->rec_created = $now;
        $rec->rec_created = $now;
        $rec->rec_modified = $now;
        $rec->create_date = $now;
        $rec->updated_date = $now;
        $rec->tid = 'tsys1';
        $rec->status_id = 1;
        return $rec->create();
    }
}

class StaticValues{

    const SRC = '/Hybrid/gpshare/Movestar/f05EF001.txt';

    public $gbls = array();

    public function __construct(){
        $csv = array_map(function($v){return str_getcsv($v,"\t");}, file(self::SRC));
        $i = 0;
        foreach($csv as $c){
            if(!$i){
                $i++;
                continue;
            }
            $this->gbls[$c[0]][$c[4]]['codes'][] = $c[3];
            $this->gbls[$c[0]][$c[4]]['values'][] = $c[6];
        }
    }
}