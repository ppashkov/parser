<?php
namespace parser\utils;
class  Curl{
	const METHOD_GET='get';
	const METHOD_POST='post';
	private $instance;
	public function __construct(){
	$this->instance = curl_init(); 
	}
	public function __destruct(){
		curl_close($this->instance ); 
		$this->instance=null;
	}
	public function call($url='',$method = 'get', $params=[]){
		$method=mb_strtolower($method);
		curl_setopt($this->instance, CURLOPT_URL, $url); 
		curl_setopt($this->instance, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($this->instance, CURLOPT_HEADER, false);
		//curl_setopt($this->instance, CURLOPT_SSL_VERIFYPEER, false);






		if($method===self::METHOD_POST){
			curl_setopt($this->instance, CURLOPT_POST, true);
	    	curl_setopt($this->instance, CURLOPT_POSTFIELDS, http_build_query($params));
		}
		$output = curl_exec($this->instance); 
		return $output;
	}
}


