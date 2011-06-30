<?php

	try {
			set_include_path(implode(PATH_SEPARATOR, array(
					realpath(dirname(__FILE__) . '/library'),
					get_include_path(),
				)));
				
			require_once 'Zend/Oauth.php';
			
		$config = array(
		    'callbackUrl' => 'http://trisrael.net/callback.php',
		    'siteUrl' => 'http://www.google.com/accounts/OAuthGetRequestToken',
		    'consumerKey' => 'trisrael.net',
		    'consumerSecret' => 'XsTcv5NMmoGnuboIeasQXCgf'
		);
		
		$consumer = new Zend_Oauth_Consumer($config);

		// fetch a request token
		$token = $consumer->getRequestToken();
		
		$_SESSION['GOOGLE_REQUEST_TOKEN'] = serialize($token);
		
		//redirect the user
		$consumer->redirect();		
						
	} catch (Exception $e) {
		
	}

?>