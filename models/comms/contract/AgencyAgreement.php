<?php

require_once __DIR__ . '/Contract.php';

class AgencyAgreement extends Contract{

    const TEMPLATE = '/srv/www/htdocs/ms/services/data/templates/New_Agency_Agreement _Final Draft_11_7_17.txt';
    const EXAMPLEIMGWEB = 'images/agentAgreementSized.png';
    const EXAMPLEIMGABS = '/srv/www/htdocs/ms/services/images/agentAgreementSized.png';
    const CTLSIGNATURE = '/srv/www/htdocs/ms/services/data/sigs/companySignature.png';
    const TEMPLATECODE = 'agencyAgreement';

    public function __construct($id = null)
    {
        parent::__construct($id);
        if(!is_null($this->template) && $this->template != self::TEMPLATECODE){
            throw new Exception('Trying to build wrong contract type');
        }
    }
    public function buildTemplate($web = false){
        if($web){
            $month = "<span style='background-color: yellow'><u>" . date('M') . "</u></span>";
            $day = "<span style='background-color: yellow'><u>" . date('d') . "</u></span>";
            $year = "<span style='background-color: yellow'><u>" . date('Y') . "</u></span>";
            $address = "<span style='background-color:yellow'><u>" . $this->company_address . " " . $this->company_city . ", " . $this->company_state . " " . $this->company_zip . "</u></span>";
            $company = "<span style='background-color: yellow'><u>" . $this->company_name ."</u></span>";
            $name = "<span style='background-color: yellow'><u>" . $this->contact_name .  "</u></span>";
            $title = "<span style='background-color: yellow'><u>Owner</u></span>";
            $ourName = "<span style='background-color: yellow'><u>Gerald Wright</u></span>";
            $agentTitle = "<span style='background-color: yellow'><u>" . $this->contact_title . "</u></span>";
            $example = "<img src='" . self::EXAMPLEIMG . "'>";
            $hhgA = "<a href='#'><b>HOUSEHOLD GOODS MILITARY AGENCY AGREEMENT</b></a>";
            $attA = "<a href='#'><b>ATTACHMENT A</b></a>";
            $dos = "<b>Description of Services</b>";
            $adr = "<b>AGENT DUTIES AND RESPONSIBILITIES</b>";
            $cdr = "<b>COMPANY DUTIES AND RESPONSIBILITIES</b>";
            $tac = "<b>TERMS AND TERMINATION</b>";
            $ov = "<b>Overflow</b>";
            $sb = "<a href='#'><b>SCHEDULE B</b></a>";
            $cchs = "<b>Cargo Liability, Claims Handling and Settlement</b>";
            $csf = "<b>COMPENSATION SCHEDULE FOR DEFENSE PERSONAL PROPERTY PROGRAM (DP3) SHIPMENTS</b>";
            $appA = "<a href='#'><b>APPENDIX A</b></a>";
            $template = preg_replace('/AGENT DUTIES AND RESPONSIBILITIES/',$adr,$template);
            $template = preg_replace('/HOUSEHOLD GOODS MILITARY AGENCY AGREEMENT/',$hhgA,$template);
            $template = preg_replace('/ATTACHMENT A/',$attA,$template);
            $template = preg_replace('/COMPANY DUTIES AND RESPONSIBILITIES/',$cdr,$template);
            $template = preg_replace('/TERMS AND TERMINATION/',$tac,$template);
            $template = preg_replace('/Description of Services/',$dos,$template);
            $template = preg_replace('/Overflow/',$ov,$template);
            $template = preg_replace('/Cargo Liability, Claims Handling and Settlement/',$cchs,$template);
            $template = preg_replace('/SCHEDULE B/',$sb,$template);
            $template = preg_replace('/COMPENSATION SCHEDULE FOR DEFENSE PERSONAL PROPERTY PROGRAM (DP3) SHIPMENTS/',$csf,$template);
            $template = preg_replace('/APPENDIX A/',$appA,$template);
        }else{
            $month = date('M');
            $day = date('d');
            $year = date('Y');
            $address = $this->company_address . " " . $this->company_city . ", " . $this->company_state . " " . $this->company_zip;
            $company = $this->company_name;
            $name = $this->contact_name;
            $title = 'Owner';
            $ourName = 'Gerald Wright';
            $agentTitle = $this->contact_title;
            $example = '';
        }
        $template = file_get_contents(self::TEMPLATE);
        $template = preg_replace('/~/',$example,$template);
        $template = preg_replace('/{ADDRESS}/',$address,$template);
        $template = preg_replace('/{NAME}/',$name,$template);
        $template = preg_replace('/{ORGANIZATION}/',$company,$template);
        $template = preg_replace('/{MONTH}/',$month,$template);
        $template = preg_replace('/{DAY}/',$day,$template);
        $template = preg_replace('/{YEAR}/',$year,$template);
        $template = preg_replace('/{OURNAME}/',$ourName,$template);
        $template = preg_replace('/{TITLE}/',$title,$template);
        $template = preg_replace('/{AGENT_TITLE}/',$agentTitle,$template);
        if($web){
            return nl2br($template);
        }
        return $template;
    }
    public function buildPdf($signed = false){
        $writer = new PdfWriter();
        $writer->filePath = __DIR__ . '/example.pdf';
        $template = explode(PHP_EOL,$this->buildTemplate());
        $lineCount = 0;
        foreach($template as $t){
            $writer->write($t);
            $writer->addLineBreak();
            $lineCount++;
            if($lineCount == 250){
                $writer->addImage(self::EXAMPLEIMGABS);
            }
            if($lineCount == 260 && $signed){
                $writer->addImage(parent::SIGNATURE);
                $writer->addImage(self::CTLSIGNATURE);
                /*$pdf->Image('',20,113,50,50,'PNG');
                $pdf->Image('',80,113,50,50,'PNG');
                 * */
            }
        }
        $writer->save();
        return $this;
    }
}
