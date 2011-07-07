<?php

//Google Oauth

set_include_path(implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__) . '/../library'), get_include_path())));
require_once 'Zend/Loader.php';
require_once 'Zend/Oauth/Consumer.php';
require_once 'util.php';
require_once 'oauth_util.php';

//Zend_Loader::loadClass ( 'Zend_OAuth_Consumer' );	


interface Empicamoto_Oauth_AuthenticationUrls {
    public function get_request_token_url();

    public function get_request_callback_url();
}

class Empicamoto_Oauth_Google_Manager implements Empicamoto_Oauth_AuthenticationUrls {

    private static $instance;
    var $consumer;

    //Zend consumer object
    //A private constructor; prevents direct creation of object
    private function __construct() {
        $this->clearAll();
    }

    // The singleton method
    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c ();
        }

        return self::$instance;
    }

    //View logic helpers
    //Function testing whether user has changed their oauth consumer/secret from defaults
    public function is_using_defaults() {
        return ($this->get_consumer_key() == Embpicamoto_Oauth_Util_Defaults::consumerKey) && ($this->get_consumer_secret() == Embpicamoto_Oauth_Util_Defaults::consumerSecret);
    }

    //Reset all state to begin oauth authentication process again. (Usually occurs after consumer credentials are changed by admin)
    public function clearAll() {
        $this->getConsumer(null);
        $this->setAccessToken(null);
    }

    const sessionId = "google_oauth_consumer";

    public function getConsumer() {
        #Check within local object first, if not existent get from session
        if(isset($this->consumer))
        {
            return $this->consumer;
        }
        
        return unserialize($_SESSION[self::sessionId]);
    }

    public function setConsumer($obj) {
        $this->consumer = $obj;
        $_SESSION[self::sessionId] = serialize($this->consumer);
    }

    //Simple existence check for _accessToken on singleton (NOTE: serialize and move into db using Settings API)
    public function has_access() {
        return $this->getAccessToken() != null;
    }

    public function can_authorize($get_params) {
        echo "<p>GET" . implode(" ", $get_params) . "</p>";
        echo "<p>GET_PARAMS non empty:" . (!empty($get_params) ? "true" : "false") . "</p>";
        echo "<p>HAS_OAUTH_PARAMS:" . ($this->has_oauth_access_params($get_params) ? "true" : "false") . "</p>";
        echo "<p>HAS_CONSUMER:" . ($this->getConsumer() != null ? "true" : "false") . "</p>";
        $val = !empty($get_params) && $this->has_oauth_access_params($get_params) && $this->getConsumer() != null;
        echo "<p>CAN_AUTHORIZE:" . ($val ? "true" : "false") . "</p>";
        return $val;
    }

    /**
     * Check whether get request contains 'oauth_verifier' and 'oauth_token'
     * @param type $get_params $_GET array of parameters
     */
    private function has_oauth_access_params($get) {
        return isset($get['oauth_verifier']) && isset($get['oauth_token']);
    }

    public function is_still_accessible() {
        return true;
    }

    public function authorize($get) {
        echo "<p>Made ti to authorize</p>";
        try {
            $this->setAccessToken($consumer->getAccessToken($get, $gauth->getConsumer()->getLastRequestToken()));

            echo "<p>" . ((array) $this->getAccessToken()) . "</p>";
        } catch (Exception $er) {
            echo "<p>" . ((array) $er) . "</p>";
            return false;
        }
        return true;
    }

    //Test whether site has been authenticated correctly with Google services
    public function has_valid_accreditation() {

        #check whether an attempt was made, and if so if it was a failure -> try again

        $last_attempt_invalid = create_function("\$consumer", "return \$consumer->getLastRequestToken() && \$consumer->getLastRequestToken()->isValid();");

        if ($this->getConsumer() == null || $last_attempt_invalid($this->getConsumer())) {
            $this->setConsumer(new Zend_Oauth_Consumer($this->getConfig()));
            echo "<p>settings consumer</p>";
        }
        // fetch a request token
        $reqToken = $this->getConsumer()->getRequestToken(array('scope' => self::$scope_param));
        
        return $reqToken->isValid();
    }

    //View output helper
    //Static constants GOOGLE URLS plus scope parameter
    static $requestUrl = 'https://www.google.com/accounts/OAuthGetRequestToken';
    static $userAuthUrl = 'https://www.google.com/accounts/OAuthAuthorizeToken';
    static $accessUrl = 'https://www.google.com/accounts/OAuthGetAccessToken';
    static $scope_param = 'http://picasaweb.google.com/data/';

    //Get Oauth config for use with Zend_Consumer
    private function getConfig() {
        return array(
            'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
            'callbackUrl' => $this->get_request_callback_url(),
            'siteUrl' => $this->get_request_token_url(),
            'signatureMethod' => 'HMAC-SHA1',
            'consumerKey' => $this->get_consumer_key(),
            'consumerSecret' => $this->get_consumer_secret(),
            'requestTokenUrl' => self::$requestUrl,
            'userAuthorizationUrl' => self::$userAuthUrl,
            'accessTokenUrl' => self::$accessUrl
        );
    }

    function get_consumer_key() {
        return Embpicamoto_Oauth_Util_Settings::get_consumer_key();
    }

    function get_consumer_secret() {
        return Embpicamoto_Oauth_Util_Settings::get_consumer_secret();
    }

    function get_request_token_url() {
        return self::$requestUrl;
    }

    function get_request_callback_url() {
        return admin_url("options-general.php?page=embpicamoto/includes/settings.php&tab=advanced-options");
    }

    const accessId = "google_oauth_access_token";

    function setAccessToken($tok) {
        $_SESSION[self::accessId] = serialize($tok);
    }

    function getAccessToken() {
        return unserialize($_SESSION[self::accessId]);
    }

}

?>