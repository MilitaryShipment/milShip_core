<?php

require_once __DIR__ . '/RequestEmail.php';
require_once __DIR__ . '/../Contact.php';
require_once __DIR__ . '/../../../Messenger.php';


class TonnageRequest{

    /*
     * HaulingAuthorityScac
        agencyWorkflow
        anticipatedLoadDate
        directLoad
        driverName
        driverPhone
        gpuApuNeeded
     * */

    const TO = 'DISPATCH@allamericanmoving.com';
    const CC = 'webadmin@allamericanmoving.com';
    const DEVMAIL = 'j.watson@allamericanmoving.com';
    const SUBJECT = 'ATTN: New Request to Haul';

    protected $msgBody;
    protected $user;
    protected $loads;
    protected $request;

    public function __construct($user,$loads){
        $this->user = $user;
        $this->loads = $loads;
        $this->_saveUser()->_buildMsgBody()->_notifyDispatch();
    }
    protected function _saveUser(){
        $contact = new Contact();
        $contact->first_name = $this->user->firstName;
        $contact->last_name = $this->user->lastName;
        $contact->contact_name = $this->user->firstName . " " . $this->user->lastName;
        $contact->email_address = $this->user->email;
        $contact->phone = $this->user->phone;
        $contact->status_id = 1;
        //todo verify phone and email
        $contact->create();
        $this->from = $contact->email_address;
        return $this;
    }
    protected function _buildMsgBody(){
        $this->request = new RequestEmail($this->user,$this->loads);
        return $this;
    }
    protected function _notifyDispatch(){
        $recipients = array(self::DEVMAIL,self::CC,self::TO);
        foreach($recipients as $recipient){
            Messenger::send($recipient,$this->from,$this->from,$this->from,'','',self::SUBJECT,$this->request->msgBody);
        }
        return $this;
    }

}
