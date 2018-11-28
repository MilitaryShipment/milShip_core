<?php

require_once __DIR__ . '/../../../record.php';
// require_once __DIR__ . '/../messenger.php';
// require_once __DIR__ . '/../pdfWriter.php';
require_once __DIR__ . '/../Notification.php';


class Contract extends Record implements ContractBehavior{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_electronic_signatures';
    const PRIMARYKEY = 'id';
    const URL = 'http://www.militaryshipment.com/services/#!/contract/';
    const FROM = "qa@militaryshipment.com";
    const SUBJECT = 'Contract Offer';
    const WEBMAIL = 'webadmin@allamericanmoving.com';
    const QUAPMAIL = 'TQAP@allamericanmoving.com';
    const DISMAIL = 'dispatch@allamericanmoving.com';
    const BILLMAIL = 'Billing@allamericanmoving.com';
    const SIGNATURE = '/srv/www/htdocs/ms/services/data/sigs/signature.png';

    public $id;
    public $agent_id;
    public $scac;
    public $motor_carrier_number;
    public $template;
    public $contact_comment;
    public $company_name;
    public $contact_name;
    public $contact_title;
    public $contact_phone;
    public $mobile;
    public $comment;
    public $expiration_date;
    public $company_city;
    public $company_state;
    public $company_zip;
    public $company_address;
    public $agent_hash;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $reminder;
    public $complete;
    public $completed_date;
    public $status_id;
    public $file_path;

    protected $msgBody;
    protected $cc = array(
        self::QUAPMAIL,
        self::DISMAIL,
        self::BILLMAIL
    );
    protected $bcc = array(
        self::WEBMAIL
    );

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
    public static function get($key,$value){
        $data = array();
        $ids = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where($key,"=",$value)
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
    protected function _buildNotification(){
        $note = new Notification();
        $note->gbl_dps = 'NOASSOCGBL';
        $note->message_type = 'text';
        $note->message_to = $this->contact_phone;
        $note->message_filename = 'eContract';
        $note->message_from = 'militaryshipment.com';
        $note->message_sent_by = '/srv/www/htdocs/classes/API/contract.php';
        $note->message_sent_date = date("m/d/Y H:i:s");
        $note->message_code = 1;
        $note->message_status_code = "ok";
        $note->message = $this->msgBody;
        $note->process = '/srv/www/htdocs/classes/API/contract.php';
        $note->created_by = '/srv/www/htdocs/classes/API/contract.php';
        $note->created_date = date("m/d/Y H:i:s");
        $note->create();
        return $this;
    }
    public function buildTemplate($web){
        return $this;
    }
    public function buildPdf($signed){
        return $this;
    }
    public function send(){
        $this->_buildNotification();
        Messenger::send($this->contact_phone,self::FROM,self::FROM,self::FROM,$this->cc,$this->bcc,self::SUBJECT,$this->msgBody);
        return $this;
    }
    public function comment($comment){
        $this->contact_comment = $comment;
        $this->update();
        return $this;
    }
    public function reply($msg){
        $this->comment = $msg;
        $this->msgBody = $msg;
        $this->update()
            ->send();
        return $this;
    }
    public function revoke(){
        $this->expiration_date = date('m/d/Y H:i:s');
        $this->update();
        return $this;
    }
    public function unexpire(){
        $this->expiration_date = date('m/d/Y H:i:s',strtotime('24 hours'));
        $this->update();
        return $this;
    }

}
