<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/19/2019
 * Time: 3:44 PM
 */

class DataLogger
{
    public function logdata($data, $die = false){
        date_default_timezone_set('America/Montevideo');
        $timestamp = "\n[".date("Y/m/d h:i:sa").'] ';
        // daily log files
        file_put_contents('daily logs/'.date("Y-m-d").'_log.log', $timestamp.$data , FILE_APPEND);
        if($die) die();
    }
}