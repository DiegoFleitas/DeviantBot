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
        file_put_contents('logs.log', $timestamp.$data , FILE_APPEND);
        if($die) die();
    }
}