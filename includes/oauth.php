<?php

//Google Oauth

set_include_path(implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__) . '/../library'), get_include_path())));
require_once 'Zend/Loader.php';
require_once 'Zend/Oauth/Consumer.php';
require_once 'util.php';
require_once 'oauth_util.php';

//Zend_Loader::loadClass ( 'Zend_OAuth_Consumer' );	


class Empicamoto_Oauth_Google_Manager{  
    
    //////////
    //Singleton 
    
    
    private static $instance;
   
    //Zend consumer object
    //A private constructor; prevents direct creation of object
    private function __construct() {
        //$this->clearAll();
    }

    /**
     *Returns the single object for this class.
     * NOTE: Wordpress seems to recreate this 'singleton' on every request so in reality it's not acting as a singleton at all.
     * @return type
     */
    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c ();
        }

        return self::$instance;
    }
    
    
    
    ////////
    // State checkers (for seeing what level of the Oauth 3-step we are in)

    
    //Function testing whether user has changed their oauth consumer/secret from defaults
    public function using_defaults() {
        return ($this->get_consumer_key() == Embpicamoto_Oauth_Util_Defaults::consumerKey) && ($this->get_consumer_secret() == Embpicamoto_Oauth_Util_Defaults::consumerSecret);
    } 


    //Simple existence check for _accessToken on singleton (NOTE: serialize and move into db using Settings API)
    public function has_access_token() {
        return $this->getAccessToken() != null;
    }

    public function can_authorize($get_params) {
        echo "<p>GET" . implode(" ", $get_params) . "</p>";
        echo "<p>GET_PARAMS non empty:" . (!empty($get_params) ? "true" : "false") . "</p>";
        echo "<p>HAS_OAUTH_PARAMS:" . ($this->has_oauth_access_params($get_params) ? "true" : "false") . "</p>";
        echo "<p>SESSION_CONSUMER:" . unserialize($_SESSION[self::requestTokenId]) . "</p>";
        
        echo "<p>HAS_REQUEST_TOKEN_IN_SESSION" . ($this->has_request_token() != null ? "true" : "false")  . "</p>";
        $val = !empty($get_params) && $this->has_oauth_access_params($get_params) && $this->has_request_token();
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

    /**
     *Checks whether Access token available can still access Google services.
     * 
     * TODO: Return something other than just true. 
     * @return type 
     */
    public function is_still_accessible() {
        return true;
    }
    
    public function has_request_token(){
        return $this->getRequestToken() != null;
    }
    
    public function has_valid_request_token(){
        return $this->has_request_token() && $this->getRequestToken()->isValid();
    }

    //Test whether site has been authenticated correctly with Google services
    public function has_valid_accreditation() {

        #check whether an attempt was made, and if so if it was a failure -> try again 
        $reqToken = $this->getRequestToken();
        if ($reqToken == null) {
            // fetch a request token
            $reqToken = $this->getConsumer()->getRequestToken(array('scope' => self::$scope_param));        
            $this->setRequestToken($reqToken);
        }       
        
        return $reqToken->isValid();
    }
    
    //////////////
    //Actions   
    
    public function authorize($get) {
        echo "<p>Made ti to authorize</p>";
        try {
            $tok = $this->getRequestToken();
            echo "<p>" . $tok . "</p>";
            $this->setAccessToken($this->getConsumer()->getAccessToken($get, $tok));
            delete_option(self::requestTokenId); #Remove saved request token as is no longer needed
            echo "<p>" . ((array) $this->getAccessToken()) . "</p>";
        } catch (Exception $er) {
            echo "<p>" . ((array) $er) . "</p>";
            return false;
        }
        return true;
    }   
    
    
    /**
     * Reset Oauth tokens and other member variables in singleton 
     */
    public function clearAll() {
        $this->setRequestToken(null);
        $this->setAccessToken(null);
    }
       
    
    //////////
    // URLs and Parameters
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
    
    ///////
    //Oauth
    
    
    var $consumer = null;
    const requestTokenId = "google_oauth_request_token";
    const accessTokenId = "google_oauth_access_token";
    
    function getConsumer(){        
        
        if($this->consumer == null)
        {
          $this->consumer = new Zend_Oauth_Consumer($this->getConfig());   
        }
        
        return $this->consumer;
    }
    
    function setRequestToken($tok){
        self::persist_token(self::requestTokenId, $tok);
    }
    
    function getRequestToken() {
        return self::retrieve_token(self::requestTokenId);
    }

    function setAccessToken($tok) {
        self::persist_token(self::accessTokenId, $tok);       
    }

    function getAccessToken() {
        return self::retrieve_token(self::accessTokenId);
    }
    
    #For setter functions, an id is passed for what to set the object to be serialize to
    protected static function persist_token($id, $tok)
    {
        update_option($id, $tok);
    }
    
    #For getter functions null is expected to be returned when unset, unserializing a value can result in error so instead return null when this is the case
    protected static function retrieve_token($id){
        return get_option($id, null);
    }
    
    

}

?>