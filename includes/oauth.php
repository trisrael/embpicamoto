<?php

//Google Oauth
		
	set_include_path ( implode ( PATH_SEPARATOR, array (realpath ( dirname ( __FILE__ ) . '/../library' ), get_include_path () ) ) );
	require_once 'Zend/Loader.php';
	require_once 'Zend/Oauth/Consumer.php';
	require_once 'namespace_util.php';
	require_once 'oauth_util.php';
	//Zend_Loader::loadClass ( 'Zend_OAuth_Consumer' );	
	

	interface AuthenticationUrls {
		public function get_request_token_url();
		public function get_request_callback_url();
	}
	
	class Empicamoto_OAuth_Google_Manager implements AuthenticationUrls {
		private static $instance;
		//Zend consumer object
		private $cons;
		
		private $reqToken;
		
		//A private constructor; prevents direct creation of object
		private function __construct() {
		}
		
		// The singleton method
		public static function singleton() {
			if (! isset ( self::$instance )) {
				$c = __CLASS__;
				self::$instance = new $c ();
			}
			
			return self::$instance;
		}
		
		//View logic helpers
		
		//Function testing whether user has changed their oauth consumer/secret from defaults
		public function is_using_defaults() {
			return ($this->get_consumer_key() == Embpicamoto_Oauth_Util_Defaults::consumerKey) && ($this->get_consumer_secret () == Embpicamoto_Oauth_Util_Defaults::consumerSecret);
		}
		
		//Test whether site has been authenticated correctly with Google services
		public function has_valid_accreditation() {
			if (! isset ( $cons )) {
				$config = array ('callbackUrl' => $this->get_request_callback_url (), 'siteUrl' => $this->get_request_token_url(), 'consumerKey' => $this->get_consumer_key (), 'consumerSecret' => $this->get_consumer_secret () );
				$cons = new Zend_Oauth_Consumer ( $config );
				// fetch a request token
				$reqToken = $cons->getRequestToken ();
				
				$reqToken->isValid ();
			}
		}
		
		//View output helper
		
		static $requestUrl = 'https://www.google.com/accounts/OAuthGetRequestToken';
		
		function get_consumer_key(){
			return Embpicamoto_Oauth_Util_Settings::get_consumer_key ();
		}
		
		function get_consumer_secret(){
			return Embpicamoto_Oauth_Util_Settings::get_consumer_secret();
		}
		
		function get_request_token_url() {
			return self::$requestUrl;
		}
		
		function get_request_callback_url() {
			return plugins_dir ( "request_callback.php", __FILE__ );
		}
	
	}


?>