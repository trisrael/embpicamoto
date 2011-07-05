<?php
namespace embpicamoto {
	
	/** Zend_Oauth_Consumer*/
	use embpicamoto\AbstractOAuth as Abs;	
	
	#set_include_path( realpath(dirname(__FILE__) . '/../library') . PATH_SEPARATOR . get_include_path());
	require_once realpath(dirname(__FILE__) . '/../library/Zend/Loader.php');	
	Zend_Loader::loadClass('Zend_OAuth_Consumer');
	
	
	use embpicamotoOAuth\Defaults;
	
	require_once 'namespace_util.php';
	require_once 'oauth_util.php';
	
	interface OauthUrls {
		public function get_request_token_url();
		public function get_request_callback_url();
	}
	
	abstract class AbstractOAuth implements OauthUrls {
		
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
		
		//Function testing whether user has changed their oauth consumer/secret from defaults
		public function is_using_defaults() {
			return get_consumer_key () == Defaults::consumerKey && get_consumer_secret () == Defaults::consumerSecret;
		}
		
		//Test whether site has been authenticated correctly with Google services
		public function has_valid_accreditation() {
			if (! isset ( $cons )) {
				$config = array ('callbackUrl' => get_request_callback_url (), 'siteUrl' => get_request_token_url (), 'consumerKey' => get_consumer_key (), 'consumerSecret' => get_consumer_secret () );
				$cons = new Zend_Oauth_Consumer ( $config );
				// fetch a request token
				$reqToken = $cons->getRequestToken ();
				
				$reqToken->isValid ();
			}
		}
	}
}
//Google Oauth
namespace empicamoto\google {	
	
	use embpicamotoOAuth\get_consumer_secret;
	use embpicamotoOAuth\get_consumer_key;
	use embpicamotoOAuth\Defaults as Defaults;
	
	class OAuth extends Abs {
		static $requestUrl = 'https://www.google.com/accounts/OAuthGetRequestToken';
		
		function get_request_token_url() {			
			return self::$requestUrl;
		}		
		
		function get_request_callback_url() {
			return plugins_dir ( "request_callback.php", __FILE__ );
		}
	
	}

}

?>