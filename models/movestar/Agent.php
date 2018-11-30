<?php namespace MoveStar;

require_once __DIR__ . '/../../record.php';
//require_once __DIR__ . '/../greatPlains/vendor.php';

class Agent extends \Record{

    const DRIVER = 'mssql';
    const DB = 'ezshare';
    const TABLE = 'tbl_movestar_agents';
    const PRIMARYKEY = 'AgentID';

    public $ID;
    public $MoveStarID;
    public $Name;
    public $Phone;
    public $Fax;
    public $TollFree;
    public $MailingAddress1;
    public $MailingAddress2;
    public $MailingCity;
    public $MailingState;
    public $MailingZip;
    public $MailingCountry;
    public $PhysicalAddress1;
    public $PhysicalAddress2;
    public $PhysicalCity;
    public $PhysicalState;
    public $PhysicalZip;
    public $PhysicalCountry;
    public $TimeZone;
    public $Website;
    public $Email;
    public $FromEasyDPS;
    public $a_409agree;
    public $AgentGBLOC;
    public $AgentID;
    public $Agreement;
    public $AllowATR;
    public $APUcharge;
    public $COG;
    public $CustomerID;
    public $DoDapproved;
    public $DOT;
    public $DPS409;
    public $DPSWhsID;
    public $EffectiveDate;
    public $EndDate;
    public $GBLOC;
    public $Haulingrate;
    public $MCICC;
    public $PlanningArea;
    public $ReceiveSIT;
    public $SCAC;
    public $SCACAcct;
    public $SCACID;
    public $ServiceRadius;
    public $StartDate;
    public $Status;
    public $VendorID;
    public $IsAdjuster;
    public $IsAgent;
    public $IsCarrier;
    public $IsContractor;
    public $IsCorporateaccount;
    public $IsCustomer;
    public $IsDestinationHauler;
    public $IsGBLOC;
    public $IsHauler;
    public $IsPacker;
    public $IsPort;
    public $IsPortAgent;
    public $IsPrimeHauler;
    public $IsRepairFirm;
    public $IsShuttle;
    public $IsSurveyor;
    public $IsVendor;
    public $IsWarehouse;
    public $LastUpdate;

    public function __construct($agentId){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$agentId);
    }
}
