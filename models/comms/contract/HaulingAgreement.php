<?php

require_once __DIR__ . '/Contract.php';

class HaulingAgreement extends Contract{

    const TEMPLATE = '/srv/www/htdocs/ms/services/data/templates/hauling_agreement.out.txt';
    const TEMPLATECODE = 'haulingAgreement';

    public function __construct($id = null)
    {
        parent::__construct($id);
        if(!is_null($this->template) && $this->template != self::TEMPLATECODE){
            throw new Exception('Trying to build wrong contract type');
        }
    }
    public function buildTemplate($web = false){
        $template = file_get_contents(self::TEMPLATE);
        $template = preg_replace("/  /"," ",$template);
        if($web){
            $siha = "<a href='#'><b>SHIPMENT INTERCHANGE/HAULING AGREEMENT</b></a>";
            $aamg = "<b>All American Moving Group, LLC</b>";
            $sma = "<b>Subject Matter of Agreement</b>";
            $csa = "<b>Compensation and Billing</b>";
            $rbl = "<b>Receipts and Bills of Lading</b>";
            $elp = "<b>Equipment, Licenses and Permits</b>";
            $ead = "<b>Equipment and Drivers</b>";
            $s = "<b>Status</b>";
            $tat = "<b>Term and Termination</b>";
            $lol = "<b>Limitation of Liability</b>";
            $cl = "<b>Claims Handling.</b>";
            $i = "<b>Indemnity</b>";
            $woc = "<b>Waiver of Carrier's Lien</b>";
            $cbs = "<b>Co-Brokering/Sub-Contract Prohibition</b>";
            $insurance = "<b>Insurance</b>";
            $numStr = "<b>$1</b>";
            $p = "<b>Precedence</b>";
            $d = "<b>Disputes</b>";
            $m = "<b>Miscellaneous</b>";
            $dos = "<b>Description of Services</b>";
            $fhc = "<b>Functions of Hauling Carrier</b>";
            $ov = "<b>Overflow</b>";
            $clchs = "<b>Cargo Liability, Claims Handling and Settlement</b>";
            $re = "<b>Repair Estimates</b>";
            $cab = "<b>Compensation and Billing</b>";
            $scac = "<span style='background-color: yellow'><u>" . $this->scac . "</u></span>";
            $mcn = "<span style='background-color: yellow'><u>" . $this->mcn . "</u></span>";
            $month = "<span style='background-color: yellow'><u>" . date('M') . "</u></span>";
            $day = "<span style='background-color: yellow'><u>" . date('d') . "</u></span>";
            $year = "<span style='background-color: yellow'><u>" . date('Y') . "</u></span>";
            $address = "<span style='background-color:yellow'><u>" . $this->company_address . " " . $this->company_city . ", " . $this->company_state . " " . $this->company_zip . "</u></span>";
            $company = "<span style='background-color: yellow'><u>" . $this->company_name ."</u></span>";
            $name = "<span style='background-color: yellow'><u>" . $this->contact_name .  "</u></span>";
            $title = "<span style='background-color: yellow'><u>Owner</u></span>";
            $ourName = "<span style='background-color: yellow'><u>Gerald Wright</u></span>";
            $agentTitle = "<span style='background-color: yellow'><u>" . $this->contact_title . "</u></span>";
            $template = preg_replace('/Compensation and Billing/',$cab,$template);
            $template = preg_replace('/Subject Matter of Agreement/',$sma,$template);
            $template = preg_replace('/Compensation and Billing/',$csa,$template);
            $template = preg_replace('/Receipts and Bills of Lading/',$rbl,$template);
            $template = preg_replace('/Equipment, Licenses and Permits/',$elp,$template);
            $template = preg_replace('/Equipment and Drivers/',$ead,$template);
            $template = preg_replace('/Status/',$s,$template);
            $template = preg_replace('/Term and Termination/',$tat,$template);
            $template = preg_replace('/Limitation of Liability/',$lol,$template);
            $template = preg_replace('/Claims Handling[.]/',$cl,$template);
            $template = preg_replace('/Indemnity/',$i,$template);
            $template = preg_replace("/Waiver of Carrier's Lien/",$woc,$template);
            $template = preg_replace("/(33835.)/",$numStr,$template);
            $template = preg_replace('/Insurance/',$insurance,$template);
            $template = preg_replace("/Precedence/",$p,$template);
            $template = preg_replace("/Disputes/",$d,$template);
            $template = preg_replace("/Miscellaneous/",$m,$template);
            $template = preg_replace("/Description of Services/",$dos,$template);
            $template = preg_replace("/Functions of Hauling Carrier/",$fhc,$template);
            $template = preg_replace("/Overflow/",$ov,$template);
            $template = preg_replace("/Cargo Liability, Claims Handling and Settlement/",$clchs,$template);
            $template = preg_replace("/Repair Estimates/",$re,$template);
        }else{
            $address = $this->company_address . " " . $this->company_city . ", " . $this->company_state . " " . $this->company_zip;
            $name = $this->contact_name;
            $company = $this->company_name;
            $month = date('M');
            $day = date('d');
            $year = date('Y');
            $ourName = 'Gerald Wright';
            $title = 'Owner';
            $agentTitle = $this->contact_title;
            $scac = $this->scac;
            $mcn = $this->mcn;
        }
        $template = preg_replace('/{ADDRESS}/',$address,$template);
        $template = preg_replace('/{NAME}/',$name,$template);
        $template = preg_replace('/{ORGANIZATION}/',$company,$template);
        $template = preg_replace('/{MONTH}/',$month,$template);
        $template = preg_replace('/{DAY}/',$day,$template);
        $template = preg_replace('/{YEAR}/',$year,$template);
        $template = preg_replace('/{OURNAME}/',$ourName,$template);
        $template = preg_replace('/{TITLE}/',$title,$template);
        $template = preg_replace('/{AGENT_TITLE}/',$agentTitle,$template);
        $template = preg_replace('/{SCAC}/',$scac,$template);
        $template = preg_replace('/{MCNUM}/',$mcn,$template);
        if($web){
            return nl2br($template);
        }
        return $template;
    }
    public function buildPdf($signed = false){}
}
