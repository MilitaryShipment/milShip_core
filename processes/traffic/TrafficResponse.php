<?php


interface TrafficResponseBehavior{
  const UNTOUCHEDTIME = '0:0';
  const PAGE;
}


abstract class TrafficResponse implements TrafficResponseBehavior{}
