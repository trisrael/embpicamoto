<?php namespace embpicamotoOAuth;

	//Register OAuth Settings
	class Consts {		
		//Re-used strings
		const google = 'google';
	
		//Wordpress ids/variable names
		const SettingsId = 'embpicamoto_oauth_settings';
		const GSectionId = 'google_oauth_section';		
		const GSectionName = self::google;
		const GConsumerPre = "embpicamoto_oauth_google_consumer_";		
	
		public static function consumerId($str){
			return self::GConsumerPre . $str;
		}
		
		const key = 'key';
		
		public static function consumerKeyId(){
			self::consumerId(key);
		}

		const secret = 'secret';
		public static function consumerSecretId(){
			self::consumerId(secret);
		}		
	};	
	
	function get_consumer_key(){
		get_option(Consts::consumerKeyId());
	}
	
	
	function get_consumer_secret(){
		get_option(Consts::consumerSecretId());
	}

class Defaults {
	const consumerKey = '';
	const consumerSecret = '';
}
