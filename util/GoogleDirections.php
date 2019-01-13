<?php

class GoogleDirections{

    const APIBASE = 'https://maps.googleapis.com/maps/api/directions/json?';
    const APIKEY = '123456';
    //origin=Boston,MA&destination=Concord,MA&waypoints=Charlestown,MA|Lexington,MA&sensor=false
    public function __construct(){}

    public static function get($originCity,$originState,$destinationCity,$destinationState){
        $url = self::APIBASE . 'key=' . self::APIKEY . '&origin=' . urlencode($originCity) . ',' . $originState . '&destination=' . urlencode($destinationCity) . ',' . $destinationState . '&senson=false';
        return json_decode(file_get_contents($url));
    }
}
