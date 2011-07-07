<?php
    set_include_path(implode(PATH_SEPARATOR, array(realpath(dirname(__FILE__) . '/../includes'), realpath(dirname(__FILE__) . '/../library'), get_include_path())));
    require_once "simple_test/autorun.php";
    require_once "oauth.php";
    require_once "Zend/Oauth/Consumer.php";
    
    Mock::generate("Zend_Oauth_Consumer", "MockOauthConsumer", array("getRequestToken", "getLastRequestToken"));
    Mock::generate("Zend_Oauth_Token_Request", "MockRequestToken", array("isValid"));
    
    class HasValidAccreditationTest extends UnitTestCase{        

        var $gauth = null; #variable to hold google singleton
        
        
        function setUp() {
            parent::setUp();
            $this->$gauth = Embpicamoto_Oauth_Google_Manager::singleton();
            
        }
        
        function consumer_set_after_valid_accreditation(){
            
            $this->$gauth->has_valid_accreditation();
            $this->assertNotNull($gauth->getLastRequestToken());
        }       
        
        function tearDown() {
            parent::tearDown();
            $gauth->reset();
        }
    }
?>
