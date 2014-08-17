<?php


namespace Cache;
/**
 * All methods(except *exists) returns false on error,
 * so one should use Identical(if($ret === false)) to test the return value.
 */
class SimpleSSDB extends SSDB
{
    function __construct($host, $port, $timeout_ms=2000){
        parent::__construct($host, $port, $timeout_ms);
        $this->easy();
    }
}