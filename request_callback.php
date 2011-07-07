<?php
    require_once "includes/oauth.php";
    
    
    $gauth = Empicamoto_Oauth_Google_Manager::singleton();
    
    #If oauth_verifier is in params (GET check), oauth consumer with request token then we may continue. 
    if (!empty($_GET) && isset($gauth->consumer) && $gauth->consumer->getLastRequestToken() != null) { 
        $gauth->setAccessToken( $consumer->getAccessToken($_GET, $gauth->consumer->getLastRequestToken()) );
    }
    
    header( "Location: " . admin_url("options-general.php?page=embpicamoto/includes/settings.php&tab=advanced-options"));

?>