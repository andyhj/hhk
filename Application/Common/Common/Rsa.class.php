<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

/**
 * Description of Rsa
 *
 * @author Administrator
 */
class Rsa {
    private $privateKey = '-----BEGIN RSA PRIVATE KEY-----  
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAJRKhl8KtFts361jV8e5YKct0+Nw
jc6vmfY+1ZKPC75fwf87V4WaemHxAtFCftw/alV9vsnTTN5GiGyv2oPXNWWbP03UgQ5zG064aObH
c0pINCdH8D8d/SXM9YfjAH6O7t1OxZP2FBYouwZ7lJZXQ6fCmYOYABVET95jFk+2XJ55AgMBAAEC
gYBI1+bzzu1Tr8KciJ05Fd3doYxhQAvYyLfHl4wQB0aMiLtiJgNTNNQDQoHQy2pHxLr2LePHfo1W
7qzbPvMHKnMmp+m2eXFIHVgM1ejPnyabvTsjI4Blu58kVjGvLyohbH2OvIRI/AFXR9Mz/Hc7ATv0
vHaBQgvq/04/esBmJMrzZQJBAO54f0ea2zqlwKqVm/Tsftcmvit4wgvw6O3tVAOgvoLSHkRJw+a4
j5IuZ0QDkS+FrTct/9wglY3Q6lfB3ZPS4ssCQQCfMQsCLYaDX8HGPnEYiNLGUKyIDbd0R8RfDT/J
MXsEx9k15MaSJmt0U+znNZuSg7LMSGiW1L9T0S3Y/pKV7edLAkEA3/d9mtOe2Gr6E3wlmBdRXWI+
svdcT/i321XNVQbwRk9vK7WX7qYh+SnpxKARCG/k6fEi3ywfKa0vmrIyF3a1lwJATJSELjUGW5aq
GhsZvuq7MqnGheDLWwXXQr6V68yA2IjnRhTbLZ2L3bct5QAV6gKu9bTzk3Oe4sxjGNtGWxfKRwJB
AKNZranFoQQpus85IcKtqMc7RZH9SEyIhheXAl2baOxnnOuME+TFtC2XcvCGeNJ6LHU08S0e3yEC
tJiKYXu398k=
-----END RSA PRIVATE KEY-----';

    private $publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCUSoZfCrRbbN+tY1fHuWCnLdPjcI3Or5n2PtWS
jwu+X8H/O1eFmnph8QLRQn7cP2pVfb7J00zeRohsr9qD1zVlmz9N1IEOcxtOuGjmx3NKSDQnR/A/
Hf0lzPWH4wB+ju7dTsWT9hQWKLsGe5SWV0OnwpmDmAAVRE/eYxZPtlyeeQIDAQAB
-----END PUBLIC KEY-----';
    
    private $pi_key = '';
    private $pu_key = '';
    
    public function __construct()
    {
        $this->pi_key =  openssl_pkey_get_private($this->privateKey);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id  
        $this->pu_key = openssl_pkey_get_public($this->publicKey);//这个函数可用来判断公钥是否是可用的  
    }

    public function publicEncrypt($data)
    {
        openssl_public_encrypt($data, $encrypted, $this->pu_key);  //公钥加密
        return $encrypted;
    }

    public function publicDecrypt($data)
    {
        openssl_public_decrypt($data, $decrypted, $this->pu_key);  //公钥解密
        return $decrypted;
    }

    public function privateEncrypt($data)
    {
        openssl_private_encrypt($data, $encrypted, $this->pi_key);  //私钥加密
        return $encrypted;
    }

    public function privateDecrypt($data)
    {
        openssl_private_decrypt($data, $decrypted, $this->pi_key);  //私钥解密
        return $decrypted;
    }
    public function privateSHA1withRSAEncrypt($data){
	openssl_sign($data, $sign, $this->pi_key, OPENSSL_ALGO_SHA1);
	return $sign;
    }
    public function publicSHA1withRSADecryp($data, $sign){
	$result = openssl_verify($data, $sign, $this->pu_key, OPENSSL_ALGO_SHA1) === 1;
	return $result;
    }
    public function privateSHA1withRSADecryp($data, $sign){
	$result = openssl_verify($data, $sign, $this->pi_key, OPENSSL_ALGO_SHA1) === 1;
	return $result;
    }
}
