<?php

require_once __DIR__ . '/abstraction.php';

abstract class Record implements RecordBehavior{

    const MSSQL = 'mssql';
    const MYSQL = 'mysql';

    public $id;

    protected $suite;
    protected $driver;
    protected $database;
    protected $table;
    protected $primaryKey;

    public function __construct($suite,$driver,$database,$table,$primaryKey,$id)
    {
        $this->suite = $suite;
        $this->driver = $driver;
        $this->database = $database;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        if(!is_null($id)){
            $this->id = $id;
            $this->_build();
        }
    }
    protected function _mssqlGuidStr($binGuid){
        $unpacked = unpack('Va/v2b/n2c/Nd', $binGuid);
        return sprintf('%08X-%04X-%04X-%04X-%04X%08X', $unpacked['a'], $unpacked['b1'], $unpacked['b2'], $unpacked['c1'], $unpacked['c2'], $unpacked['d']);
    }
    protected function _build(){
        $results = $GLOBALS['db']
            ->suite($this->suite)
            ->driver($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->select("*")
            ->where($this->primaryKey,"=",$this->id)
            ->get();
        if($this->driver == self::MSSQL){
            if(!mssql_num_rows($results)){
                throw new Exception('Invalid Record ID');
            }
            while($row = mssql_fetch_assoc($results)){
                foreach($row as $key=>$value){
                    if($key == 'guid' && !is_null($value)){
                        $this->$key = $this->_mssqlGuidStr($value);
                    }else{
                        $this->$key = $value;
                    }
                }
            }
        }elseif($this->driver == self::MYSQL){
            if(!mysql_num_rows($results)){
                throw new Exception('Invalid Record ID');
            }
            while($row = mysql_fetch_assoc($results)){
                foreach($row as $key=>$value){
                    $this->$key = $value;
                }
            }
        }
        return $this;
    }
    protected function _buildId(){
        $results = $GLOBALS['db']
            ->suite($this->suite)
            ->database($this->database)
            ->table($this->table)
            ->select("$this->primaryKey")
            ->orderBy("$this->primaryKey desc")
            ->take(1)
            ->get();
        if(strtolower($this->driver) == self::MSSQL){
            while($row = mssql_fetch_assoc($results)){
                $this->id = $row[$this->primaryKey];
            }
        }else{
            while($row = mysql_fetch_assoc($results)){
                $this->id = $row[$this->primaryKey];
            }
        }
        return $this;
    }
    public function create(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'created_date' || $key == 'updated_date'){
                $upData[$key] = date("m/d/Y H:i:s");
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->$key;
            }
        }
        unset($upData['id']);
        $results = $GLOBALS['db']
            ->suite($this->suite)
            ->driver($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->insert($upData)
            ->put();
        $this->_buildId()->_build();
        return $this;
    }
    public function update(){
        $reflection = new \ReflectionObject($this);
        $data = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $upData = array();
        foreach($data as $obj){
            $key = $obj->name;
            if($key == 'updated_date'){
                $upData[$key] = date("m/d/Y H:i:s");
            }elseif(!is_null($this->$key) && !empty($this->$key)){
                $upData[$key] = $this->$key;
            }
        }
        if(isset($upData['created_date'])){
            unset($upData['created_date']);
        }
        unset($upData['id']);
        unset($upData['guid']);
        $key = $this->primaryKey;
        $results = $GLOBALS['db']
            ->suite($this->suite)
            ->driver($this->driver)
            ->database($this->database)
            ->table($this->table)
            ->update($upData)
            ->where($this->primaryKey,"=",$this->$key)
            ->put();
        return $this;
    }
    public function setFields($updateObj){
        if(!is_object($updateObj)){
            throw new Exception('Trying to perform object method on non object.');
        }
        foreach($updateObj as $key=>$value){
            $this->$key = $value;
        }
        return $this;
    }
}
