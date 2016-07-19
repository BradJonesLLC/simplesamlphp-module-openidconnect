<?php

class OAuth2 {

	private $client_id;
	private $client_secret;
	private $redirect_uri;
	private $scope;
	private $access_token;
	private $url;
	private $URL_AUTH;
	private $URL_TOKEN;
	private $auth_type;
	private $session;
	private $grant_type;
	private $response_type;

	public function __construct($params){

		/* REQUIRED */
		$this->client_id 	   = $params["client_id"];
		$this->client_secret = $params["client_secret"];
		$this->redirect_uri  = $params["redirect_uri"];
		$this->URL_AUTH 	   = $params["auth"] . "?";
		$this->URL_TOKEN 	   = $params["token"] . "?";

		/* OPTIONAL */
		$this->auth_type 	   	 = isset($params["authorization_type"]) ? $params["authorization_type"] : "Bearer";
		$this->session 		   	 = isset($params["session"]) ? $params["session"] : false;
		$this->verify_ssl_peer = isset($params["verify"]) ? ($params["verify"] ? 1 : 0) : 1;
		$this->verify_ssl_host = $this->verify_ssl_peer === 1 ? 2 : 0;
		$this->grant_type 	   = isset($params["grant_type"]) ? $params["grant_type"] : "authorization_code";
		$this->response_type   = isset($params["response_type"]) ? $params["response_type"] : "code";
	}

	public function get_access_token($state = false,	$code  = false) {
		if(!$state) {
			$state = htmlspecialchars($_GET['state']);
		}
		if(!$code) {
			$code  = htmlspecialchars($_GET['code']);
		}
		if($this->session && $state) {
			if($_SESSION['state'] != $state) {
				die('States does not match');
			}
		}

		$access_token = $this->get_oauth_token($code);
		return $access_token;
	}

	private function get_oauth_token($code) {
		$params = array(
			'grant_type' 		=> $this->grant_type,
			'client_id'  		=> $this->client_id,
			'client_secret' => $this->client_secret,
			'code' 					=> $code,
			'redirect_uri'  => $this->redirect_uri,
		);

		$url_params = http_build_query($params);
		$url  			= $this->URL_TOKEN . $url_params;

		$result 	  	= curl_exec($this->create_curl($url, false, $params));
		$result_obj   = json_decode($result, true);
		$access_token = $result_obj['access_token'];

		return $access_token;
	}

	public function get_identity($access_token, $identity_url) {
		if(!$access_token || !$identity_url) throw new Exception("Missing either \$access_token or \$identity_url in get_identity function.");
		$params = array(
			'access_token' => $access_token,
		);
		$url_params = http_build_query($params);
		$url 				= $identity_url . "?" . $url_params;
		$result 		= curl_exec($this->create_curl($url, array('Authorization: ' . $this->auth_type . ' ' . $access_token), false));
		$result_obj = json_decode($result, true);

		return $result_obj;
	}

	public function redirect($state = false) {
		if(!$state) $state = uniqid('', true);
		$params = array(
			'client_id' 		=> $this->client_id,
			'response_type' => $this->response_type,
			'redirect_uri'  => $this->redirect_uri,
			'state' 				=> $state,
		);

		if($this->session) $_SESSION['state'] = $state;

		$url = $this->URL_AUTH . http_build_query($params);

		header("Location: $url");
		exit;
	}


	private function create_curl($url, $header, $extended) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if ($header){
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}
		if ($extended) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $extended);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl_peer);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $this->verify_ssl_host);
		}
		return $curl;
	}
}

?>
