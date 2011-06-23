<?php
/*
Plugin Name: Simple Picasa Albums
Plugin URI: http://wordpress.org/
Description: Embed picasa album into post or page with additional options
Author: Tristan Goffman
Version: 1.0
Author URI: http://github.com/trisrael
*/


//This plugin is a direct derivative 

require_once('includes/settings.php'); #Load wordpress settings

/////////////////////////////////////////////////////////////////////
// add the shortcode handler for picasa galleries
// http://brettterpstra.com/adding-a-tinymce-button/
function add_embpicamoto_shortcode($atts, $content = null) {
        extract(shortcode_atts(array( "id" => '', "per_page" => '', "per_line" => '' ), $atts));
        
		if(empty($id)) return ''; #Without an id for an albumid can't do anything
		
		$options = get_option('embpicamoto_options');
		
		if(!empty($options['embpicamoto_options_login']) && !empty($options['embpicamoto_options_password'])) {
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

				$client = Zend_Gdata_ClientLogin::getHttpClient($options['embpicamoto_options_login'], $options['embpicamoto_options_password'], Zend_Gdata_Photos::AUTH_SERVICE_NAME);
				$service = new Zend_Gdata_Photos($client); 
				
				$photos = array();
				$query = new Zend_Gdata_Photos_AlbumQuery();
				$query->setAlbumId($id);

				// http://code.google.com/intl/ru/apis/picasaweb/docs/1.0/reference.html
				$suffix = $options['embpicamoto_options_crop'] == 'no' ? 'u' : 'c';
				$query->setThumbsize($options['embpicamoto_options_thumb_size'] . $suffix);
				$query->setImgMax($options['embpicamoto_options_full_size'] . $suffix);
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


				$has_rows =	isset($per_line); #Check if user supplied a number per line
				$has_pages= isset($per_page) && is_numeric($per_page) && $per_page > 0;
				$page_names = array();


				$html = '';
				
				$pageElId = create_function("$loc_pid", "return 'embpicamoto_album_$id' . '_page_$loc_pid'"); #creates a unique id for an album page

				#foreach temporary variables
				$page_name = null;
 
				foreach($photos as $index => $photo) {
					#Per page variables
					$has_new_page = $index == 0 || ($has_pages  && ($index % $per_page) == 0); #If on first page add ul,  
					
					if($has_new_page){
						$page_name = $index / $per_page;
						$page_names << $page_name;

					   if($index > 0) { $html = $html . '</ul></div>'; #End the last page  }

						#Add new page
						$html = $html . "<div id='$pageElId($page_name)'><ul class='embpicamoto'>";					
					}

					$html = $html . '<li>';
					$html = $html . '<a rel="lightbox[' . $album['id'] . ']" target="_blank" href="' . $photo['fullsize'] . '">';
					$html = $html . '<img src="' . $photo['thumbnail'] . '" />';
					$html = $html . '</a>';
					$html = $html . '</li>';
				}
				$html = $html . '</ul></div>'; #Finish the last page

				#Container html element variables
			    $wrap_el_id = "embpicamoto_album_$id";
				$wrap_pre = "<div id='$wrap_el_id'>";
				$wrap_post = "</div>";

				if($has_pages){
					#Initiate pages using jQuery tabs
					$script = '<script type=”text/javascript”>';
					$script = $script . '(function($){';
					$script = $script . "$( '#$wrap_el_id' ).tabs();";
					$script = $script . '})(jQuery);</script>';						

					#Build the html for the jQuery tabs
					$html_page_names = '<ul>';
					foreach($p_name as $page_names){
						$p_id = $pageElId($p_name);
						$html_page_names = $html_page_names . "<li><a href='#$p_id'>$p_name</a></li>";			
					}
					$html_page_names = $html_page_names . '</ul>';
				}
				
				return $wrap_pre . $html_page_names . $html . $wrap_post . $script;
			} catch(Exception $ex) {
				return '<p style="color:red">' . $ex->getMessage() . '</p>';					
			}
		} else {
			return ''; //empty login or password
		}
		
		
		//TODO: here will be all zend gdata stufs to retrive album photos
		return '<p style="text-align:center">'.$id.'</p>';
}

add_shortcode('embpicamoto', 'add_embpicamoto_shortcode');

/////////////////////////////////////////////////////////////////////
// embed some javascript for tinymce plugin

// add jquery ui styles
function embpicamoto_init() {
	if(is_admin() && current_user_can('edit_posts') && current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {	
		//In case of page not getting loaded with jquery-ui automatically
		wp_register_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
		wp_enqueue_style( 'jquery-style' );	
	}
	
	if(!is_admin()) {
		wp_register_style( 'embpicamoto-style', plugins_url('embpicamoto.css', __FILE__));
		wp_enqueue_style( 'embpicamoto-style' );	
	}
}

add_action( 'init', 'embpicamoto_init' );
add_action( 'edit_form_advanced', 'embpicamoto_embed_js' );
add_action( 'edit_page_form', 'embpicamoto_embed_js' );

function embpicamoto_embed_js() {	
?>

<script type="text/javascript">
	function embpicamoto_dlg_open() {
		embpicamoto_dlg_close();
		
		jQuery("#embpicamoto_dlg").dialog({
			modal: true,
			draggable: false,
			resizable: false,
			buttons: {'Insert': embpicamoto_dlg_insert}
		});
		
		jQuery("#embpicamoto_dlg").dialog("open");
	}

	function embpicamoto_dlg_insert() {
		var album_id = jQuery("#embpicamoto_dlg_content_album").val();
		
		var shortcode = '[embpicamoto id="'+album_id+'"]';	

		if ( typeof tinyMCE != 'undefined' && ( ed = tinyMCE.activeEditor ) && !ed.isHidden() ) {
			ed.focus();
			if (tinymce.isIE)
				ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);

			ed.execCommand('mceInsertContent', false, shortcode);
		} else
			edInsertContent(edCanvas, shortcode);

		embpicamoto_dlg_close();
	}
	
	function embpicamoto_dlg_close() {
		jQuery("#embpicamoto_dlg").dialog("close");
	}
</script>

<?php
}


function embpicamoto_js_dlg_markup() {
$options = get_option('embpicamoto_options');
$success = true;
$msg = '';
$opts = '';

if(!empty($options['embpicamoto_options_login']) && !empty($options['embpicamoto_options_password'])) {
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

		$client = Zend_Gdata_ClientLogin::getHttpClient($options['embpicamoto_options_login'], $options['embpicamoto_options_password'], Zend_Gdata_Photos::AUTH_SERVICE_NAME);
		$service = new Zend_Gdata_Photos($client); 
		
		$albums = array();
	
		$results = $service->getUserFeed();
		while($results != null) {
			foreach($results as $entry) {
				$album_id = $entry->gphotoId->text;
				$album_name = $entry->title->text;
				
				$albums[] = array(
					'id' => $album_id,
					'name' => $album_name
				);
			}
			
			try {
				$results = $results->getNextFeed();
			}
			catch(Exception $e) {$results = null;}
		}
		
		foreach($albums as $album) {
			$opts = $opts . '<option value="' . $album['id'] . '">' . $album['name'] . '</option>';
		}
		
	} catch(Exception $ex) {
		$success = false;
		$msg = $ex->getMessage();		
	}
}
?>

<div class="hidden">
	<div id="embpicamoto_dlg" title="Picasa">
		<div class="embpicamoto_dlg_content">
			<?php if($success):?>
				<p>
					<label>
						Select album:
						<select id="embpicamoto_dlg_content_album" style="width:98%"><?php echo $opts;?></select>
					</label>
				</p>
			<?php else:?>
				<div style="padding:1em;" class="ui-state-error ui-corner-all"> 
					<p><strong>ERROR</strong><br /><?php echo $msg?></p>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>
<style type="text/css">
	.ui-button-text-only .ui-button-text {padding:0;}
	.ui-widget-overlay {background:#AAAAAA;}
</style>

<?php
}
add_action( 'admin_footer', 'embpicamoto_js_dlg_markup' );


/////////////////////////////////////////////////////////////////////
// add embpicamoto button into tinymce
// http://brettterpstra.com/adding-a-tinymce-button/
function add_embpicamoto_button() {
   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
   if ( get_user_option('rich_editing') == 'true') {		
     add_filter('mce_external_plugins', 'add_embpicamoto_tinymce_plugin');
     add_filter('mce_buttons', 'register_embpicamoto_button');
 
	 wp_enqueue_script('jquery-ui-dialog');	 //Ensure jquery-ui-dialog is available on clientside (will add in jQuery automatically)
   }
}

add_action('init', 'add_embpicamoto_button');

function register_embpicamoto_button($buttons) {
   array_push($buttons, "|", "embpicamoto");
   return $buttons;
}

function add_embpicamoto_tinymce_plugin($plugin_array) {
   $plugin_array['embpicamoto'] = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"", plugin_basename(__FILE__)) . 'embpicamoto.js';
   return $plugin_array;
}

// Trick/hack to tinymce refresh all files
function embpicamoto_refresh_mce($ver) {
  $ver += 3;
  return $ver;
} 

add_filter( 'tiny_mce_version', 'embpicamoto_refresh_mce');

?>
