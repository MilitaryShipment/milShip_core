<?php

require_once __DIR__ . '/../record.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/webUser.php';

class Authenticator{

    const MSSQL = 'mssql';
    const SANDBOX = 'Sandbox';
    const WEBUSERS = 'tbl_webusers';
    const USERS = 'tbl_users';
    const UFIELD = 'username';
    const UPFIELD = 'passwd';
    const WEBUFIELD = 'user_login';
    const WEBPFIELD = 'user_password';

    public $user;
    private $table;
    private $web;
    private $userField;
    private $passField;
    private $username;
    private $password;
    private $agentId;

    public function __construct($username,$password,$agentId = null,$web = false)
    {
        if($web){
            $this->web = true;
            $this->table = self::WEBUSERS;
            $this->userField = self::WEBUFIELD;
            $this->passField = self::WEBPFIELD;
        }else{
            $this->web = false;
            $this->table = self::USERS;
            $this->userField = self::UFIELD;
            $this->passField = self::UPFIELD;
        }
        $this->username = $username;
        $this->password = $password;
        $this->agentId = $agentId;
        $this->user = $this->_authenticate();
        if(!$this->user){
            throw new Exception('Invalid Credentials');
        }
    }
    private function _authenticate(){
        $data = null;
        $GLOBALS['db']
            ->suite(self::MSSQL)
            ->driver(self::MSSQL)
            ->database(self::SANDBOX)
            ->table($this->table)
            ->select("id")
            ->where("$this->userField = '$this->username'")
            ->andWhere("$this->passField = '$this->password'");
        if($this->web){
            $GLOBALS['db']->andWhere("agent_number = '$this->agentId'");
        }
        $results = $GLOBALS['db']->get();
        if(!mssql_num_rows($results)){
            return false;
        }else{
            while($row = mssql_fetch_assoc($results)){
               if($this->web){
                   $data = new WebUser($row['id']);
               }else{
                   $data = new User($row['id']);
               }
            }
        }
        return $data;
    }
}