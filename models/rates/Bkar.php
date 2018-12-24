<?php


class Bkar{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const TABLE = 'dps_rates_bkar';

    public $id;

    public function __construct($id = null){
        if(!is_null($id)){
            $this->id = $id;
            $this->_build($id);
        }
    }
    protected function _build(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::TABLE)
            ->select("*")
            ->where("id = '$this->id'")
            ->get();
        if(!mssql_num_rows($results)){
            throw new Exception('Invalid resource ID');
        }
        while($row = mssql_fetch_assoc($results)){
            foreach($row as $key=>$value){
                $this->$key = $value;
            }
        }
        return $this;
    }
}
