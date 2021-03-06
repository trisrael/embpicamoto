<?php

//Register OAuth Settings
class Embpicamoto_Oauth_Util_Constants {
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
        return self::consumerId(key);
    }

    const secret = 'secret';

    public static function consumerSecretId() {
        return self::consumerId(secret);
    }

}

class Embpicamoto_Oauth_Util_Settings {

    static function get_consumer_key() {
        return self::retrieve_option_value(Embpicamoto_Oauth_Util_Constants::consumerKeyId());
    }

    static function get_consumer_secret() {
        return self::retrieve_option_value(Embpicamoto_Oauth_Util_Constants::consumerSecretId());
    }

    private static function retrieve_option_value($option_id) {
        $options = get_option(Embpicamoto_Oauth_Util_Constants::SettingsId);
        return $options[$option_id];
    }

}

class Embpicamoto_Oauth_Util_Defaults {
    const consumerKey = '';
    const consumerSecret = '';
}
