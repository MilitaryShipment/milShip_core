<?php

require_once __DIR__ . '/TonnageShipment.php';
require_once __DIR__ . '/TonnageList.php';
require_once __DIR__ . '/../../../util/GoogleDirections.php';
require_once __DIR__ . '/../ZipLocation.php';

class NearbyShipments{

    const MAXMILES = 50;

    protected $shipment;
    protected $availableShipments = array();

    public function __construct($tonnageShipment){
        $this->shipment = $tonnageShipment;
        $this->_buildAvailableShipments();
    }
    protected function _buildAvailableShipments(){
        $shipments = array();
        $list = new TonnageList();
        $this->availableShipments = $list->shipments;
        return $this;
    }
    protected function _buildGeoData($shipment,$origin = true){
        if($origin){
            $city = 'orig_city';
            $state = 'orig_state';
            $lat = 'orig_lat';
            $long = 'orig_lng';
        }else{
            $lat = 'dest_lat';
            $long = 'dest_lng';
            $city = 'dest_city';
            $state = 'dest_state';
        }
        if(empty($shipment->$lat) || is_null($shipment->$lat) || empty($shipment->$long) || is_null($shipment->$long)){
            return $this->_buildByCityState($shipment->$city,$shipment->$state);
        }
        return array("lat"=>$shipment->$lat,"long"=>$shipment->$long);
    }
    protected function _buildByCityState($city,$state){
        $zips = ZipLocation::getCityState($city,$state);
        return array("lat"=>$zips[0]->latitude,"long"=>$zips[0]->longitude);
    }
    protected function calculate_geographic_distance_miles($longitude1, $latitude1, $longitude2, $latitude2)
    {
        $theta = $longitude1 - $longitude2;
        $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        return $miles;
    }
    public function nearOrigin(){
        $shipments = array();
        $geoData = $this->_buildGeoData($this->shipment);
        foreach($this->availableShipments as $testShipment){
            $testData = $this->_buildGeoData($testShipment);
            $miles = $this->calculate_geographic_distance_miles($geoData['long'],$geoData['lat'],$testData['long'],$testData['lat']);
            if($miles <= self::MAXMILES && $testShipment->order_number != $this->shipment->order_number){
                $shipments[] = $testShipment;
            }
        }
        return $shipments;
    }
    public function nearDestination(){
        $shipments = array();
        $geoData = $this->_buildGeoData($this->shipment,false);
        foreach($this->availableShipments as $testShipment){
            $testData = $this->_buildGeoData($testShipment,false);
            $miles = $this->calculate_geographic_distance_miles($geoData['long'],$geoData['lat'],$testData['long'],$testData['lat']);
            if($miles <= self::MAXMILES && $testShipment->order_number != $this->shipment->order_number){
                $shipments[] = $testShipment;
            }
        }
        return $shipments;
    }
    public function onTheWay(){
        $shipments = array();
        $oderNumbers = array();
        $directions = GoogleDirections::get($this->shipment->orig_city,$this->shipment->orig_state,$this->shipment->dest_city,$this->shipment->dest_state);
        $legCounter = 0;
        foreach($directions->routes[0]->legs[0]->steps as $leg){
            $legCounter++;
            if($legCounter % 10 == 0){
                $legLat = $leg->start_location->lat;
                $legLong = $leg->start_location->lng;
                foreach($this->availableShipments as $testShipment){
                    $testData = $this->_buildGeoData($testShipment);
                    $miles = $this->calculate_geographic_distance_miles($legLong,$legLat,$testData['long'],$testData['lat']);
                    if($miles <= self::MAXMILES && $testShipment->order_number != $this->shipment->order_number && !in_array($testShipment->order_number,$oderNumbers)){
                        $oderNumbers[] = $testShipment->order_number;
                        $shipments[] = $testShipment;
                    }
                }
            }
        }
        return $shipments;
    }
}
