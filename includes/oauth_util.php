<?php
namespace embpicamoto\oauth\util;

//Register OAuth Settings
class Constants {
	//Re-used strings
	const google = 'google';
	
	//Wordpress ids/variable names
	const SettingsId = 'embpicamoto_oauth_settings';
	const GSectionId = 'google_oauth_section';
	const GSectionName = self::google;
	const GConsumerPre = "embpicamoto_oauth_google_consumer_";
	
	public static function consumerId($str) {
		return self::GConsumerPre . $str;
	}
	
	const key = 'key';
	
	public static function consumerKeyId() {
		self::consumerId ( key );
	}
	
	const secret = 'secret';
	public static function consumerSecretId() {
		self::consumerId ( secret );
	}
}
;

class Settings {
	static function get_consumer_key() {
		return get_option ( Constants::consumerKeyId () );
	}
	
	static function get_consumer_secret() {
		return get_option ( Constants::consumerSecretId () );
	}
}

class Defaults {
	const consumerKey = '';
	const consumerSecret = '';
}
