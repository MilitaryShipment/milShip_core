<?php

class GoogleDirections{

    const APIBASE = 'https://maps.googleapis.com/maps/api/directions/json?';
    const KEYLOCAL = '/srv/www/config/.googleDirections';
    const NOSRCERR = 'Api Key Source File does not exist';
    public function __construct(){}

    public static function getKey(){
      if(!file_exists(self::KEYLOCAL)){
        throw new \Exception(self::NOSRCERR);
      }
      $lines = file(self::KEYLOCAL);
      return trim($lines[0]);
    }

    public static function get($originCity,$originState,$destinationCity,$destinationState){
        $url = self::APIBASE . 'key=' . self::getKey() . '&origin=' . urlencode($originCity) . ',' . $originState . '&destination=' . urlencode($destinationCity) . ',' . $destinationState . '&senson=false';
        return json_decode(file_get_contents($url));
    }
}
