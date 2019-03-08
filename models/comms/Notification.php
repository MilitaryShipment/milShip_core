<?php

require_once __DIR__ . '/../../record.php';

class Notification extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_notifications';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $message_filename;
    public $cog;
    public $scac;
    public $message_type;
    public $message_to;
    public $message_from;
    public $message_sent_by;
    public $message_sent_date;
    public $message_next_send_date;
    public $message_code;
    public $message_status_code;
    public $message;
    public $move_coordinator;
    public $registration_id;
    public $driver_id;
    public $driver_name;
    public $bases_id;
    public $agent_id;
    public $lumper_id;
    public $dispatcher_id;
    public $outbound_template_id;
    public $short_fuse_id;
    public $process;
    public $dump;
    public $day;
    public $subject;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null)
    {
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
            ->where($key,"=","'" . $value . "'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
    public static function sentToday($message_filename,$gbl_dps = null){
        $data = array();
        $ids = array();
        $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("message_filename","=","'" . $message_filename . "'")
            ->andWhere("cast(created_date as date)","=","cast(GETDATE() as date)");
        if(!is_null($gbl_dps)){
          $GLOBALS['db']->andWhere("gbl_dps","=","'" . $gbl_dps . "'");
        }
        $results = $GLOBALS['db']->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
}
