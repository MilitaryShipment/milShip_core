<?php namespace GP;

require_once __DIR__ . '/../record.php';

class Vendor extends \Record{

    const DRIVER = 'mssql';
    const DB = 'AAMG';
    const TABLE = 'PM00200';
    const PRIMARYKEY = 'VENDORID';

    public $VENDORID;
    public $VENDNAME;
    public $VNDCHKNM;
    public $VENDSHNM;
    public $VADDCDPR;
    public $VADCDPAD;
    public $VADCDSFR;
    public $VADCDTRO;
    public $VNDCLSID;
    public $VNDCNTCT;
    public $ADDRESS1;
    public $ADDRESS2;
    public $ADDRESS3;
    public $CITY;
    public $STATE;
    public $ZIPCODE;
    public $COUNTRY;
    public $PHNUMBR1;
    public $PHNUMBER2;
    public $PHONE3;
    public $FAXNUMBR;
    public $UPSZONE;
    public $SHIPMTHD;
    public $TAXSCHID;
    public $ACNMVNDR;
    public $TXIDNMBR;
    public $VENDSTTS;
    public $CURNCYID;
    public $TXRGNUUM;
    public $PARENID;
    public $TRDDISCT;
    public $TEN99TYPE;
    public $TEN99BOXNUMBER;
    public $MINORDER;
    public $PYMTRMID;
    public $MINPYTYP;
    public $MINYDLR;
    public $MXIAFVND;
    public $MAXINDLR;
    public $COMMENT1;
    public $COMMENT2;
    public $USERDEF1;
    public $USERDEF2;
    public $CRLMTDLR;
    public $PYMNTRPI;
    public $KPCALHST;
    public $KGLDSTHS;
    public $KPERHIST;
    public $KPTRXHST;
    public $HOLD;
    public $PTCSHACF;
    public $CREDTLMT;
    public $WRITEOFF;
    public $MXWOFAMT;
    public $SBPPSDED;
    public $PPSTAXRT;
    public $DXVARNUM;
    public $CRTCOMDT;
    public $CRTEXPDT;
    public $RTOBUTKN;
    public $XPDTOBLG;
    public $PRSPAYEE;
    public $PMAPINDX;
    public $PMCSHIDX;
    public $PMDAVIDX;
    public $PMDTKIDX;
    public $PMFINIDX;
    public $PMMSCHIX;
    public $PMFRTIDX;
    public $PMTAXIDX;
    public $PMWRTIDX;
    public $PMPRCHIX;
    public $PMRTNGIX;
    public $PMTDSCIX;
    public $ACPURIDX;
    public $PURPVIDX;
    public $NOTEINDX;
    public $CHEKBKID;
    public $MODIFDT;
    public $CREATDDT;
    public $RATETPID;
    public $Revalue_Vendor;
    public $Post_Results_To;
    public $FREEONBOARD;
    public $GOVCRPID;
    public $GOVINDID;
    public $DISGRPER;
    public $DOCFMTID;
    public $TaxInvRecvd;
    public $USERLANG;
    public $WithholdingType;
    public $WithholdingFormType;
    public $WithholdingEntityType;
    public $TaxFileNumMode;
    public $BRTHDATE;
    public $LaborPmtType;
    public $CCode;
    public $DECLID;
    public $CBVAT;
    public $Workflow_Approval_Status;
    public $Workflow_Priority;
    public $Workflow_Status;
    public $VADCD1099;
    public $DEX_ROW_TS;
    public $DEX_ROW_ID;

    public function __construct($vendorId){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$vendorId);
    }
}
