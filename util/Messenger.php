<?php

require_once __DIR__ . '/SendMessage.php';
require_once __DIR__ . '/../record.php';
require_once __DIR__ . '/../models/comms/PhoneNumber.php';

abstract class Messenger implements MessageBehavior{

    const APIUSER = 'stvstew';
    const APIPASS = 'allamericanmoving';
    const APIBASE = 'https://api.data24-7.com/v/2.0?api=';
    public function __construct()
    {
    }

    public static function lookUpCellPhone($phoneNumber){
        $data = new stdClass();
        if($phoneNumber[0] != "1"){
            $phoneNumber = "1" . $phoneNumber;
        }
        $apiOption = "T";
        $url = self::APIBASE . $apiOption . "&user=" . self::APIUSER . "&pass=" . self::APIPASS . "&p1=" . $phoneNumber;
        $xml = simplexml_load_file($url);
        if(!$xml){
            throw new Exception("feed not loading");
        }
        $data->sms_address = (string)$xml->results->result[0]->sms_address;
        $data->mms_address = (string)$xml->results->result[0]->mms_address;
        $data->carrier_name = (string)$xml->results->result[0]->carrier_name;
        $data->wless = (string)$xml->results->result[0]->wless;
        return $data;
    }
    public static function lookUpEmail($email){
        $data = new stdClass();
        $apiOption = "E";
        $url = self::APIBASE . $apiOption . "&user=" . self::APIUSER . "&pass=" . self::APIPASS . "&p1=" . $email;
        $xml = simplexml_load_file($url);
        if(!$xml){
            throw new Exception("Feed not loading");
        }
        if((string)$xml->results->result->valid == "YES"){
            $data->valid = true;
        }else{
            $data->valid = false;
        }
        $data->message = (string)$xml->results->result->reason;
        return $data;
    }
    public static function verify($contact){
        if((int)$contact[0]){
            return self::lookUpCellPhone($contact);
        }elseif(preg_match("/@/",$contact)){
            return self::lookUpEmail($contact);
        }
        return false;
    }
    public static function send($msgArray,$host = null,$port = null,$username = null,$password = null){
        return new SendMessage($msgArray,$host,$port,$username,$password);
    }
    public static function isSaved($phoneNumber){
      return PhoneNumber::exists($phoneNumber);
    }
    public static function saveNumber($phone,$sms,$mms,$carrier,$wless,$category,$first_name = null,$last_name = null){
      $phone = new PhoneNumber();
      $phone->phone = $phone;
      $phone->sms = $sms;
      $phone->mms = $mms;
      $phone->carrier = $carrier;
      $phone->wless = $wless;
      return $phone->create();
    }
}
