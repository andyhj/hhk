<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Home\Controller;

/**
 * Description of BaseController
 *
 * @author Administrator
 */
use Think\Controller;
class BaseController extends Controller{
    public function verify_code() {
            $Verify =     new \Think\Verify();
            $Verify->fontSize = 15;
            $Verify->length   = 4;
            $Verify->useNoise = false;
            $Verify->useCurve = false;
            $Verify->imageH = 40;
            $Verify->imageW = 115;
            $Verify->fontttf = '4.ttf'; 
            // 设置验证码字符为纯数字
            $Verify->codeSet = '0123456789'; 
            $Verify->entry();
    }
}
