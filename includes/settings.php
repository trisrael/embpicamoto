<?php namespace embpicamotoSettings;
require_once 'namespace_util.php';
const nsStr = "embpicamotoSettings"; 
function ns($loc_name)
{		
	return wrap_namespace(nsStr, $loc_name);
}

//add plugin options page
add_action( 'admin_menu', ns('admin_menu') );

class ImageSizes {

	public static $thumbnails = array('32', '48', '64', '72', '104', '144', '150', '160', '180', '200', '240', '280', '320');

	public static $defThumb = '150';

	public static $fulls = array('94', '110', '128', '200', '220', '288', '320', '400', '512', '576', '640', '720', '800', '912', '1024', '1152', '1280', '1440', '1600');
	
	public static $defFull = '640';
	
	public function thumbs() { return self::$thumbnails; }	

	#Returns the default size thumbnailed images images should use	
	public static function defaultThumb() { return self::$defThumb; }

	public static function fulls() { return self::$fulls; }	

	#Returns the default size fullsized images should use
	public static function defaultFull() { return self::$defFull; }
}

function admin_menu() {
	add_options_page('Picasa settings', 'Picasa', 'manage_options', __FILE__, ns('page'));	
}

function page() {

?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Picasa settings</h2>
		Enter authentication parameters and select preferred image dimensions
		<form action="options.php" method="post">
		<?php settings_fields(Picasa::SettingsId); ?>
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
add_action('admin_init', ns('admin_init') );

class Picasa{
	const renderFieldPostfix = '_field_renderer';
	
	const SettingsId = "empicamoto_options";
	const AuthSectionId = "auth_section";
	const ImageSectionId = "img_section";
	const Login = "login";
	const Password = "password";
	const Thumb = "thumb_size";
	const Full = "full_size";
	const Crop = "crop";
	 
	public static function LoginId(){	return pre(self::Login);}	
	public static function PasswordId(){	return pre(self::Password);}
	public static function ThumbId(){ return pre(self::Thumb);}
	public static function FullId(){ return pre(self::Full);}
	public static function CropId(){ return pre(self::Crop);}
	public static function AuthSectionDesc(){return self::post_desc(self::AuthSectionId);}
	public static function ImageSectionDesc(){return self::post_desc(self::ImageSectionId);}
	//Helper Functions
	
	public static function add_field($param_name, $desc, $section_id)
	{
		add_settings_field(pre($param_name), $desc, ns(pre($param_name) . self::renderFieldPostfix) , __FILE__, $section_id );
	}
	
	//For html elements that are to be placed into wordpress settings in this context, add string wrapping to the parameter name given
	public static function html_name($param_name){
		return "{self::SettingsId}[$param_name]";
	}

	
	//Given a name, append 'desc' to it then namespace it as it should be a local const/var
	private static function post_desc($loc_name){return ns($loc_name . "_desc");}
		
	//Private	
	private static function pre($str){	return self::SettingsId . $str;}
	
}

function admin_init(){
	register_setting(Picasa::SettingsId, Picasa::SettingsId, ns('validate') ); // group, name in db, validation func
	
	add_settings_section(Picasa::AuthSectionId, 'Authentication Settings', Picasa::AuthSectionDesc(), __FILE__);
	Picasa.add_field(Picasa::Login, Picasa::Password, Picasa::AuthSectionId);
	Picasa.add_field(Picasa::Password, Picasa::Password,Picasa::AuthSectionId);	
	
	add_settings_section(Picasa::ImageSectionId, 'Image Settings', Picasa::ImageSectionDesc(), __FILE__);	
	Picasa.add_field(Picasa::Thumb, 'Thumbnail size', Picasa::ImageSectionId);
	Picasa.add_field(Picasa::Full, 'Full image size', Picasa::ImageSectionId);
	Picasa.add_field(Picasa::Crop, 'Crop images', Picasa::ImageSectionId);		
}

//Section descriptions

function auth_section_desc() {
	echo '<p>Your login and password in picasa</p>';
}

function img_section_desc() {
	echo '<p>Preferred image dimensions</p>';
}

//Renderers

//simple wrapper for html inputs for the next two render methods
function html_input($id, $type_val)
{
	$options = get_option(Picasa::SettingsId);
	echo "<input id=$id name='{Picasa.html_name($id)}' size='40' type='$type_val' value='{$options[$id]}' />";
}

function login_field_renderer() {	
	html_input( Picasa::LoginId(), "text");
}

function password_field_renderer() {	
	html_input(Picasa::PasswordId(), "password");
}

//simple wrapper for html select, selecting the option wish is currently selected
function html_select($id, $items)
{
	$options = get_option(Picasa::SettingsId);	
	echo "<select id='{$id}' name='{Picasa.name_wrap($id})]'>";
	foreach($items as $item) {
		$selected = ($options[$id]==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function thumb_size_field_renderer() {
	html_select(Picasa::ThumbId(), ImageSizes::thumbs());
}

function full_size_field_renderer() {	
	html_select(Picasa::FullId(), ImageSizes::fulls());	
}

function crop_field_renderer() {	
	html_select(Picasa::CropId(), array('no', 'yes'));	
}

function validate($input) {
	// strip all fields
	
	$filterInput = function ($param_name){ $input[$param_name]  =  wp_filter_nohtml_kses($input[$param_name]);};
	
	$filterInput( Picasa::LoginId() );
	$filterInput( Picasa::PasswordId() );
	$filterInput( Picasa::ThumbId() );
	$filterInput( Picasa::FullId() );
	
	// check image dimensions, defaulting to some size when not in valid options
	
	$items = ImageSizes::thumbs();
	if(!in_array($input[Picasa::ThumbId()], $items)) { 
		$input[Picasa::ThumbId()] = ImageSizes::defaultThumb();
	}
	
	$items = ImageSizes::fulls();
	if(!in_array($input[Picasa::FullId()], $items)) {
		$input[Picasa::FullId()] = ImageSizes::defaultFull();
	}
	
	return $input;
}

// Define default option settings
register_activation_hook(__FILE__, ns('add_defaults'));

function add_defaults() {
    update_option(Picasa::SettingsId, array(
		Picasa::LoginId() 	   => 'LOGIN@gmail.com',
		Picasa::PasswordId()   => 'your password',
		Picasa::ThumbId() => ImageSizes::defaultThumb(),
		Picasa::FullId()  => ImageSizes::defaultFull(),
		Picasa::CropId()  => 'no'
	));
}

?>
