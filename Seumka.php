<?php

namespace xf3;

class Seumka
{
	private $_username;
	private $_password;
	
	private $_connected = false;
	
	private $_curl;
	private $_cookieFile;
	
	public function __construct($username, $password)
	{
		$this->_username = $username;
		$this->_password = $password;
	}
	
	public function __destruct()
	{
		if ($this->_curl) {
			curl_close($this->_curl);
			
			if ($this->_cookieFile && file_exists($this->_cookieFile)) {
				unlink($this->_cookieFile);
			}
		}
	}
	
	private function _connect()
	{
		if ($this->_connected) {
			return;
		}
		
		$response = $this->_request('authuser', array(
			'username' => $this->_username,
			'password' => $this->_password,
		));
		
		if (!$response['status']) {
			throw new Exception('Failed connecting to API');
		}
		
		$this->_connected = true;
	}
	
	private function _setCurl()
	{
		if ($this->_curl) {
			return;
		}
		
		$this->_curl = curl_init();
		
		$this->_cookieFile = __DIR__ . DIRECTORY_SEPARATOR . 'seumka_cookie_' . rand(1000000, 9999999);
		
		curl_setopt_array($this->_curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_COOKIESESSION => true,
			CURLOPT_POST => true,
			CURLOPT_COOKIEFILE => $this->_cookieFile,
			CURLOPT_COOKIEJAR => $this->_cookieFile
		));
	}
	
	private function _request($task, $data = null)
	{
		$this->_setCurl();
		
		curl_setopt_array($this->_curl, array(
			CURLOPT_URL => 'http://seumka.ru/?option=com_seumka&controller=manager&task=' . $task . ($data !== null ? '&json=' . rawurlencode(json_encode($data)) : ''),
			CURLOPT_HTTPHEADER => array('Content-length: 0'),
		));
		
		$response = curl_exec($this->_curl);
		
		if (curl_errno($this->_curl) || !($json = json_decode($response, true))) {
			return array('status' => false);
		}
		
		return $json;
	}
	
	public function __call($name, $arguments) {
		if (!in_array($name, array(
			'deauthuser',
			'listsites',
			'getphrasesjson',
			'listses',
			'getposjson2',
			'geturlsjson',
			'getyamastatsjson',
			'changeseforsite',
			'saveyastat',
			'savemastat',
			'disablesite',
			'enablesite',
			'disablephrase',
			'enablephrase',
			'delsitehash',
		))) {
			throw new Exception('Method "' . $name . '" doesn\'t exist');
		}
		
		$this->_connect();
		
		return $this->_request($name, isset($arguments[0]) ? $arguments[0] : null);
	}
}
