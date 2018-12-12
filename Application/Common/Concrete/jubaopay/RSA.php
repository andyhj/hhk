<?php
namespace Common\JuBaoPay;
class RSA
{
	private $_priContent;
	private $_pubContent;
	
	private $_privKey;
	private $_pubKey;
	
	private $_algo ;
	private $_psw;

	public function __construct($conf)
	{
//		$xml = new DOMDocument();
//		$xml->load($conf);
//		$items = $xml->getElementsByTagName("items");		
		
		$this->_priContent = '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDB55jObJmLS8oU84cJ+l2nnvkgfZrlPUxDn4HSAB2Y7Y6udVKP
bOpKbR151C9+R4ZIWwFEC5S+JULXDaVu3TPA8IO3kd7vxrO5QEiq45TX3aiDHuhz
c0LcYTwJOrrnSG0uBxkntxq4KOThaqgEPlxx4WpMWkcumKdb+63CvBjMmwIDAQAB
AoGADatR7lthh5xUJp30SxPHPmXKkivIm5hyo+G+uRsg+wLkKBBFPa8j5RNEHK6E
mpZYNQmVUUKvvafKynY/z0zwtO2m+IS/GEtNS96cFN1zHEwZhCq+8OvSP3MTQ/w5
idlW383H+kCEuWfbDfsTBrk0HeIupqmKEShLWOTB8bcjxyECQQD4N0/Crl2pbrPf
p4BMDVxl2uEUMQ8ihfrsoTbyxG+WXntDvgi/usc43tUx10sdULSVbNY7pQLxrwjG
ABWEAtMVAkEAx/xFeFOIc2bBx+s5lDS2P2epV3R5N/WphxKP0NreYR3trzLHnFXc
St9hNzVAGSGC4HZuy3nnQTT9bWAvfH3M7wJBAIvvCt1TVeRWT7vP/6lggu29NtUe
T00EQZEz1fmJOuuH+nAXa3FDyFrMbV665FLzk8sF38UiYwWDyyttErQor30CQBQl
W7I1aAeIEHldPt2pObrFbI+80gFLJBrRSd+WTNSnuSvvB6C+HYUFX7u6B40AjTij
daHV0/ADsIv2JpJ58c0CQGXROgvymrVLfkpgmrknfsi8ndPkAYzN76e1m0oUrAeU
7Qf/1l5yfSyf9DuZ3AoXLT4KrDiNrJAAF5vPiZJGtyk=
-----END RSA PRIVATE KEY-----';
		$this->_pubContent = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCk5V5YMRar1+LuWkW9SvRA6zAk
hCzmUFv3750TjgFRWHI3kfCjd1smdZWtJpAoqLICqNU4Rqc7hMxMkMOY8hHX6wuU
QwBwWXWREdm5lyBRpi8teQTG05GsJ60d3W3Nn5arsShaqvpX3bsSZHbCv1k9N6PR
kj2arrhSzEBJdPjsVQIDAQAB
-----END PUBLIC KEY-----';
		$this->_algo = OPENSSL_ALGO_SHA1;
		$this->_psw = 'fe424d073f0e44f98118b49359f2237f';
	}
	
	public function __destruct()
	{
		@ fclose($this->_privKey);
		@ fclose($this->_pubKey);
	}

	public function setupPrivKey()
	{
		if(is_resource($this->_privKey)){
			return true;
		}

		$this->_privKey = openssl_pkey_get_private($this->_priContent);
		return true;
	}
	 
	public function setupPubKey()
	{
		if(is_resource($this->_pubKey)){
			return true;
		}

		$this->_pubKey = openssl_pkey_get_public($this->_pubContent);
		return true;
	}
	
	public function pubEncrypt($data)
	{
		if(!is_string($data)){
			return null;
		}
			
		$this->setupPubKey();
			
		$r = openssl_public_encrypt($data, $encrypted, $this->_pubKey);
		if($r){
			return base64_encode($encrypted);
		}
		return null;
	}
	
	public function sign($data)
	{
		$digest=$data.$this->_psw;
		openssl_sign($digest, $signature, $this->_priContent, $this->_algo);
		return base64_encode($signature);		
	}
	
	public function privDecrypt($encrypted)
	{
		if(!is_string($encrypted)){
			return null;
		}
			
		$this->setupPrivKey();
			
		$encrypted = base64_decode($encrypted);
	
		$r = openssl_private_decrypt($encrypted, $decrypted, $this->_privKey);
		if($r){
			return $decrypted;
		}
		return null;
	}
	
	public function verify($data,$signature)
	{				
		$digest=$data.$this->_psw;
		return openssl_verify($digest, base64_decode($signature), $this->_pubContent, $this->_algo );		 
	}
	
	public function privEncrypt($data)
	{
		if(!is_string($data)){
			return null;
		}
		 
		$this->setupPrivKey();
		 
		$r = openssl_private_encrypt($data, $encrypted, $this->_privKey);
		if($r){
			return base64_encode($encrypted);
		}
		return null;
	}
	 
	public function pubDecrypt($crypted)
	{
		if(!is_string($crypted)){
			return null;
		}
		 
		$this->setupPubKey();
		 
		$crypted = base64_decode($crypted);

		$r = openssl_public_decrypt($crypted, $decrypted, $this->_pubKey);
		if($r){
			return $decrypted;
		}
		return null;
	}

}
