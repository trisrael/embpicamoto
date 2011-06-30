<?php namespace embpicamoto;

//add plugin options page
add_action( 'admin_menu', 'embpicamoto_admin_menu' );

class ImageSizes {

	public static $thumbnails = array('32', '48', '64', '72', '104', '144', '150', '160', '180', '200', '240', '280', '320');

	public static $defThumb = '150';

	public static $fulls = array('94', '110', '128', '200', '220', '288', '320', '400', '512', '576', '640', '720', '800', '912', '1024', '1152', '1280', '1440', '1600');
	
	public static $defFull = '640';
	
	public function thumbs() { return self::$thumbnails; }	

	#Returns the default size thumbnailed images images should use	
	public function defaultThumb() { return self::$defThumb; }

	public function fulls() { return self::$fulls; }	

	#Returns the default size fullsized images should use
	public function defaultFull() { return self::$defFull; }
}

$embpica_img_sizes = new ImageSizes(); #Simple object containing size of pictures arrays 'namespacing' variables to avoid conflicts

function embpicamoto_admin_menu() {
	add_options_page('Picasa settings', 'Picasa', 'manage_options', __FILE__, 'embpicamoto_settings_page');
	add_options_page('OAuth Settings', 'OAuth', 'manage_options', __FILE__, 'oauth_settings_page');
}

function oauth_settings_page()
{
	
}

function embpicamoto_settings_page() {

?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Picasa settings</h2>
		Enter authentication parameters and select preferred image dimensions
		<form action="options.php" method="post">
		<?php settings_fields('embpicamoto_options'); ?>
		<?php do_settings_sections(__FILE__); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div>

<?php
}

/////////////////////////////////////////////////////////////////////
//register plugin options
add_action('admin_init', 'embpicamoto_admin_init' );

class SettingsHelper {
	
	const renderFieldPostfix = '_field_renderer';
	
	/**
	 * Given a str, attach the render field to it
	 */ 
	public static function renderFuncName($str){
		return $str . self::renderFieldPostfix;
	}	
}

//Register OAuth Settings
class OAuth {
	//Re-used strings
	const google = 'google';

	//Wordpress ids/variable names
	const SettingsId = 'embpicamoto_oauth_settings';
	const GSectionId = 'google_oauth_section';		
	const GSectionName = self::google;
	const GConsumerPre = "embpicamoto_oauth_google_consumer_";

	public static function consumerName($str){
		return self::GConsumerPre . $str;
	}
	
};

function embpicamoto_admin_init(){
	register_setting('embpicamoto_options', 'embpicamoto_options', 'embpicamoto_options_validate' ); // group, name in db, validation func
	
	add_settings_section('auth_section', 'Authentication Settings', 'embpicamoto_options_section_auth', __FILE__);
	add_settings_field('embpicamoto_options_login', 'Login', 'embpicamoto_options_login_field_renderer', __FILE__, 'auth_section');
	add_settings_field('embpicamoto_options_password', 'Password', 'embpicamoto_options_password_field_renderer', __FILE__, 'auth_section');	
	
	add_settings_section('img_section', 'Image Settings', 'embpicamoto_options_section_img', __FILE__);
	add_settings_field('embpicamoto_options_thumb_size', 'Thumbnail size', 'embpicamoto_options_thumb_size_field_renderer', __FILE__, 'img_section');
	add_settings_field('embpicamoto_options_full_size', 'Full image size', 'embpicamoto_options_full_size_field_renderer', __FILE__, 'img_section');
	add_settings_field('embpicamoto_options_crop', 'Crop images', 'embpicamoto_options_crop_field_renderer', __FILE__, 'img_section');
	
				
	register_setting( OAuth::SettingsId, OAuth::SettingsId, 'embpicamoto_oauth_settings_validate');
	
	//Google Oauth settings fields  
	add_settings_section(OAuth::GSectionId, OAuth::GSectionName, OAuth::GSectionId);
	
	$key_id = OAuth::consumerName('key');
	add_settings_field( $key_id , 'Consumer Key', SettingsHelper::renderFuncName($key_id), OAuth::SettingsId, OAuth::GSectionId );
	
	$secret_id = OAuth::consumerName('secret');
	add_settings_field( $secret_id, 'Consumer Secret', SettingsHelper::renderFuncName($secret_id), OAuth::SettingsId , OAuth::GSectionId);	
}

//Empicamoto Options functions

function embpicamoto_options_section_auth() {
	echo '<p>Your login and password in picasa</p>';
}

function embpicamoto_options_section_img() {
	echo '<p>Preferred image dimensions</p>';
}

function embpicamoto_options_login_field_renderer() {
	$options = get_option('embpicamoto_options');
	echo "<input id='embpicamoto_options_login' name='embpicamoto_options[embpicamoto_options_login]' size='40' type='text' value='{$options['embpicamoto_options_login']}' />";
}

function embpicamoto_options_password_field_renderer() {
	$options = get_option('embpicamoto_options');
	echo "<input id='embpicamoto_options_password' name='embpicamoto_options[embpicamoto_options_password]' size='40' type='password' value='{$options['embpicamoto_options_password']}' />";
}

function embpicamoto_options_thumb_size_field_renderer() {
	$options = get_option('embpicamoto_options');
	$items = $GLOBALS[embpica_img_sizes]->thumbs();
	echo "<select id='embpicamoto_options_thumb_size' name='embpicamoto_options[embpicamoto_options_thumb_size]'>";
	foreach($items as $item) {
		$selected = ($options['embpicamoto_options_thumb_size']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function embpicamoto_options_full_size_field_renderer() {
	$options = get_option('embpicamoto_options');
	$items = $GLOBALS[embpica_img_sizes]->fulls();
	echo "<select id='embpicamoto_options_full_size' name='embpicamoto_options[embpicamoto_options_full_size]'>";
	foreach($items as $item) {
		$selected = ($options['embpicamoto_options_full_size']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function embpicamoto_options_crop_field_renderer() {
	$options = get_option('embpicamoto_options');
	$items = array('no', 'yes');
	echo "<select id='embpicamoto_options_crop' name='embpicamoto_options[embpicamoto_options_crop]'>";
	foreach($items as $item) {
		$selected = ($options['embpicamoto_options_crop']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function embpicamoto_options_validate($input) {
	global $embpica_img_sizes;
	// strip all fields
	$input['embpicamoto_options_login'] 	 =  wp_filter_nohtml_kses($input['embpicamoto_options_login']);
	$input['embpicamoto_options_password']   =  wp_filter_nohtml_kses($input['embpicamoto_options_password']);
	$input['embpicamoto_options_thumb_size'] =  wp_filter_nohtml_kses($input['embpicamoto_options_thumb_size']);
	$input['embpicamoto_options_full_size']  =  wp_filter_nohtml_kses($input['embpicamoto_options_full_size']);
	
	// check image dimensions, defaulting to some size when not in valid options
	$items = $embpica_img_sizes->thumbs();
	if(!in_array($input['embpicamoto_options_thumb_size'], $items)) { 
		$input['embpicamoto_options_thumb_size'] = $embpica_img_sizes->defaultThumb();
	}
	
	$items = $embpica_img_sizes->fulls();
	if(!in_array($input['embpicamoto_options_full_size'], $items)) {
		$input['embpicamoto_options_full_size'] = $embpica_img_sizes->defaultFull();
	}
	
	return $input;
}

//Oauth Settings functions

function embpicamoto_oauth_google_consumer_key_field_renderer(){
	
}

function embpicamoto_oauth_google_consumer_secret_field_renderer(){
	
}


// Define default option settings
register_activation_hook(__FILE__, 'embpicamoto_options_add_defaults');

function embpicamoto_options_add_defaults() {
    update_option('embpicamoto_options', array(
		'embpicamoto_options_login' 	   => 'LOGIN@gmail.com',
		'embpicamoto_options_password'   => 'your password',
		'embpicamoto_options_thumb_size' => $embpica_img_sizes->defaultThumb(),
		'embpicamoto_options_full_size'  => $embpica_img_sizes->defaultFull(),
		'embpicamoto_options_crop'       => 'no'
	));
}

?>
