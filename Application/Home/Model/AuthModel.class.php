<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Model;
use Think\Model;
class AuthModel extends Model{
    private $signature = 'LfFHxRT52TS8';


    public function __construct() {
        
    }
    public function check($client_token,$sys_code){
        if(!$client_token){
            return false;
        }
        $server_token = $this->getToken($sys_code);
        if($client_token == $server_token){
            return true;
        }
        return false;
    }
    
    public function getToken($sys_code){
        $token = sha1($sys_code.$this->signature);
        return $token;
    }
}