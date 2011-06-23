<?php
/*
Plugin Name: Embed picasa albums with easy Fotomoto
Plugin URI: http://wordpress.org/
Description: Embed picasa album into post or page, and easily add Fotomoto script to site.
Author: Tristan Goffman
Version: 1.0
Author URI: github.com/trisrael
*/


require_once('includes/settings.php'); #Load wordpress settings 

/////////////////////////////////////////////////////////////////////
// add the shortcode handler for picasa galleries
// http://brettterpstra.com/adding-a-tinymce-button/
function add_embpicasa_shortcode($atts, $content = null) {
        extract(shortcode_atts(array( "id" => '' ), $atts));
        
		if(empty($id)) return '';
		
		$options = get_option('embpicasa_options');
		
		if(!empty($options['embpicasa_options_login']) && !empty($options['embpicasa_options_password'])) {
			try {
				set_include_path(implode(PATH_SEPARATOR, array(
					realpath(dirname(__FILE__) . '/library'),
					get_include_path(),
				)));

				require_once 'Zend/Loader.php';

				Zend_Loader::loadClass('Zend_Gdata');
				Zend_Loader::loadClass('Zend_Gdata_Query');
				Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
				Zend_Loader::loadClass('Zend_Gdata_Photos');
				Zend_Loader::loadClass('Zend_Gdata_Photos_UserQuery');
				Zend_Loader::loadClass('Zend_Gdata_Photos_AlbumQuery');
				Zend_Loader::loadClass('Zend_Gdata_Photos_PhotoQuery');

				$client = Zend_Gdata_ClientLogin::getHttpClient($options['embpicasa_options_login'], $options['embpicasa_options_password'], Zend_Gdata_Photos::AUTH_SERVICE_NAME);
				$service = new Zend_Gdata_Photos($client); 
				
				$photos = array();
				$query = new Zend_Gdata_Photos_AlbumQuery();
				$query->setAlbumId($id);
				// http://code.google.com/intl/ru/apis/picasaweb/docs/1.0/reference.html
				$suffix = $options['embpicasa_options_crop'] == 'no' ? 'u' : 'c';
				$query->setThumbsize($options['embpicasa_options_thumb_size'] . $suffix);
				$query->setImgMax($options['embpicasa_options_full_size'] . $suffix);
				$results = $service->getAlbumFeed($query);
				while($results != null) {
					foreach($results as $entry) {
						foreach($results as $photo) {
							$photos[] = array(
								'thumbnail' => $photo->mediaGroup->thumbnail[0]->url,
								'fullsize' => $photo->mediaGroup->content[0]->url
							);
						}
					}
					try {
						$results = $results->getNextFeed();
					}
					catch(Exception $e) {$results = null;}
				}
				
				//TODO: here is theming, change it as u need
				
				$html = '<ul class="embpicasa">';
				
				foreach($photos as $photo) {
					$html = $html . '<li>';
					$html = $html . '<a rel="lightbox[' . $album['id'] . ']" target="_blank" href="' . $photo['fullsize'] . '">';
					$html = $html . '<img src="' . $photo['thumbnail'] . '" />';
					$html = $html . '</a>';
					$html = $html . '</li>';
					$opts = $opts . '<option value="' . $album['id'] . '">' . $album['name'] . '</option>';
				}
				$html = $html . '</ul>';
				
				//$html = $html . '<style type="text/css">';
				//$html = $html . '.embpicasa li {width:' . $options['embpicasa_options_thumb_size'] . 'px;height:' . $options['embpicasa_options_thumb_size'] . 'px;}';
				//$html = $html . '</style>';
				
				return $html;
				
			} catch(Exception $ex) {
				return '<p style="color:red">' . $ex->getMessage() . '</p>';					
			}
		} else {
			return ''; //empty login or password
		}
		
		
		//TODO: here will be all zend gdata stufs to retrive album photos
		return '<p style="text-align:center">'.$id.'</p>';
}
add_shortcode('embpicasa', 'add_embpicasa_shortcode');


require_once('includes/tinyMCE.php'); #Add picasa shortcode addition to tinyMCE

?>
