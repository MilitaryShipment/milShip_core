<?php 


class LaneData{

    const MSSQL = 'mssql';
    const DATABASE = 'test';
    const LANES = 'dps_lanes';
    const PEAK = 'dps_rates_peak';
    const NONPEAK = 'dps_rates_non_peak';
    const PRIMARY = 'tbl_shipment_primary';
    const SANDBOX = 'Sandbox';
    //registration_date,

    private $lanes = array();
    private $years = array();
    private $shipments = array();
    private $laneData = array();
    private $verification = array();
    private $validShipments = array();

    public function __construct()
    {
        $this->buildLanes()
            ->buildYears()
            ->buildShipments()
            ->verifyData()
            ->calculateLaneData();
    }
    private function buildLanes(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::PEAK)
            ->select("distinct lane")
            ->get();
        if(!mssql_num_rows($results)){
            die('No Lanes!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->lanes[] = $row['lane'];
            }
        }
        return $this;
    }
    private function buildYears(){
        $results = $GLOBALS['db']
            ->driver(self::MSSQL)
            ->database(self::DATABASE)
            ->table(self::PEAK)
            ->select("distinct year")
            ->get();
        if(!mssql_num_rows($results)){
            die('No Years!');
        }else{
            while($row = mssql_fetch_assoc($results)){
                $this->years[] = $row['year'];
            }
        }
        return $this;
    }
    private function buildShipments(){
        foreach($this->years as $year){
            if($year != 2017){
                continue;
            }
            $results = $GLOBALS['db']
                ->driver(self::MSSQL)
                ->database(self::SANDBOX)
                ->table(self::PRIMARY)
                ->select("gbl_dps,channel,line_haul,tsp_score")
                ->where("YEAR(registration_date) = $year")
                ->get();
            if(!mssql_num_rows($results)){
                //no shipments this year!
            }else{
                while($row = mssql_fetch_assoc($results)){
                    $this->shipments[$year][$row['gbl_dps']] = array(
                        "channel"=>(empty($row['channel']) || is_null($row['channel']) ? "U" : trim(strtolower(preg_replace('/\(.*/','',$row['channel'])))),
                        "line_haul"=>(empty($row['line_haul']) || is_null($row['line_haul']) ? "U" : $row['line_haul']),
                        "tsp_score"=>(empty($row['tsp_score']) || is_null($row['tsp_score']) ? "U" : $row['tsp_score'])
                    );
                }
            }
        }
        return $this;
    }
    private function verifyData(){
        $noChannel = 0;
        $noLh = 0;
        $noScore = 0;
        $total = 0;
        $valid = 0;
        foreach($this->shipments as $year=>$data){
            foreach($data as $key=>$value){
                $total++;
                if($value['channel'] != "U" && $value['line_haul'] != "U" && $value['tsp_score'] != "U"){
                    //todo how to apply year??
                    $this->validShipments[] = $value;
                    $valid++;
                }elseif($value['channel'] == "U"){
                    $noChannel++;
                }elseif($value['line_haul'] == "U"){
                    $noLh++;
                }elseif($value['tsp_score'] == "U"){
                    $noScore++;
                }elseif(!in_array($value['line_haul'],$this->lanes)){
                    echo "Fake Lane!";
                    exit;
                }else{
                    echo "Never True";
                    exit;
                }
            }
        }
        $this->verification['total'] = $total;
        $this->verification['noChannel'] = $noChannel;
        $this->verification['noLh'] = $noLh;
        $this->verification['noScore'] = $noScore;
        $this->verification['valid'] = $valid;
        $this->verification['percent_invalid'] = round(($this->verification['noChannel'] + $this->verification['noLh'] + $this->verification['noScore']) / $this->verification['total'] * 100,2);
        $this->verification['percent_valid'] = 100 - $this->verification['percent_invalid'];
        print_r($this->verification);
        exit;
        return $this;
    }
    private function calculateLaneData(){
        foreach($this->validShipments as $valid){
            $this->laneData[$valid['channel']]['lh'][] = $valid['line_haul'];
            $this->laneData[$valid['channel']]['tsp'][] = $valid['tsp_score'];
        }
        foreach($this->laneData as $lane=>$data){
            echo $lane . "\n";
            echo "Lhs: " . count($data['lh']) . "\n";
            echo "Tsp: " . count($data['tsp']) . "\n";
        }
        return $this;
    }
}
