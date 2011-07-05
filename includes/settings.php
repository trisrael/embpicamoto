<?php
namespace embpicamoto\settings;

require_once 'namespace_util.php';

const nsStr = "embpicamoto\\settings";
function ns($loc_name) {
	return wrap_namespace ( nsStr, $loc_name );
}

//add plugin options page
add_action ( 'admin_menu', ns ( 'admin_menu' ) );

#Include jQuery and jQuery UI tabs with no conflict mode to HEAD (ensuring they are added first to avoid overwriting library code in Prototype, and other libraries)
#TODO: Find a better way of including this so it doesn't need to be added in all the time 


class ImageSizes {
	
	public static $thumbnails = array ('32', '48', '64', '72', '104', '144', '150', '160', '180', '200', '240', '280', '320' );
	
	public static $defThumb = '150';
	
	public static $fulls = array ('94', '110', '128', '200', '220', '288', '320', '400', '512', '576', '640', '720', '800', '912', '1024', '1152', '1280', '1440', '1600' );
	
	public static $defFull = '640';
	
	public function thumbs() {
		return self::$thumbnails;
	}
	
	#Returns the default size thumbnailed images images should use	
	public static function defaultThumb() {
		return self::$defThumb;
	}
	
	public static function fulls() {
		return self::$fulls;
	}
	
	#Returns the default size fullsized images should use
	public static function defaultFull() {
		return self::$defFull;
	}
}

function admin_menu() {
	add_options_page ( 'Picasa settings', 'Picasa', 'manage_options', __FILE__, ns ( 'page' ) );
}

function tabs($current = 'image-settings') {
	
	$links = array ();
	$opts_url = Helper::settingsPageRelUrl;
	foreach ( Helper::settingsTabs () as $tab => $name ) :
		if ($tab == $current) :
			$links [] = "<a class='nav-tab nav-tab-active' href='?page=$opts_url&tab=$tab'>$name</a>";
		 else :
			$links [] = "<a class='nav-tab' href='?page=$opts_url&tab=$tab'>$name</a>";
		endif;
	endforeach
	;
	echo '<h2>';
	foreach ( $links as $link )
		echo $link;
	echo '</h2>';
}

function page() {
	?>
<div class="wrap">
<div class="icon32" id="icon-options-general"><br>
</div>
<div id='tabs'>
			<?php
	tabs ()?>
			<?php
	if ($_GET ['page'] == Helper::settingsPageRelUrl) {
		isset ( $_GET ['tab'] ) ? $tab = $_GET ['tab'] : $tab = Helper::defaultTabId;
		switch ($tab) :
			case Helper::generalTabId :
				general_options ();
				break;
			case Helper::advancedTabId :
				advanced_options ();
				break;
		endswitch
		;
	}
	?>					
			
		
		</div>
</div>
<?php
}

function general_options() {
	?>
<h2>General Settings</h2>
Enter a login and password for Picasa (if not using
<a
	href='<?php
	echo ('?page=' . Helper::settingsPageRelUrl . '&tab=') . Helper::advancedTabId?>'>Oauth</a>
) and edit image options if needed.
<form action=”options.php” method=”post”>
	<?php
	settings_fields ( Helper::SettingsId );
	do_settings_sections ( __FILE__ );
	?>
	<p class="submit"><input name="Submit" type="submit"
	class="button-primary" value="<?php
	esc_attr_e ( 'Save Changes' );
	?>" />
</p>
</form>
</div>
<?php
}
use empicamoto\oauth\google\OAuth as GAuth;
function advanced_options() {
	?>
<div id='auth-settings'>
<h2>Picasa Authentication</h2>
<?php
	require_once plugin_dir_path ( __FILE__ ) . "oauth.php";
	$gauth = GAuth::singleton (); //google oauth manager
	

	if ($gauth->is_using_defaults ()) {
		$sty = "-moz-border-radius: 6px 6px 6px 6px;";
		$sty = $sty . "-webkit-border-radius: 6px 6px 6px 6px;";
		$sty = $sty . "border-top-width: 1px; border-top-style: solid;";
		$sty = $sty . "-khtml-border-radius: 6px 6px 6px 6px;";
		$sty = $sty . "top-right-radius: 6px;";				
		echo "<p style='$sty' class='update-nag'>No Google Oauth credentials supplied yet, unable to authorize. Supply credentials at the <a href='?page=embpicamoto/includes/oath_settings.php'>OAuth Settings page</a></p>";
	} else if ($gauth->has_valid_accreditation ()) {
	
	}
	
	?>				
	</div>
<?php
}

/////////////////////////////////////////////////////////////////////
//register plugin options
add_action ( 'admin_init', ns ( 'admin_init' ) );

class Helper {
	/** relative url to this php file **/
	const settingsPageRelUrl = 'embpicamoto/includes/settings.php';
	const renderFieldPostfix = '_field_renderer';
	
	const SettingsId = "empicamoto_options";
	const AuthSectionId = "auth_section";
	const ImageSectionId = "img_section";
	
	const generalTabId = 'general-options';
	const defaultTabId = self::generalTabId;
	const advancedTabId = 'advanced-options';
	
	const Login = "login";
	const Password = "password";
	const Thumb = "thumb_size";
	const Full = "full_size";
	const Crop = "crop";
	public static function settingsTabs() {
		return array (self::defaultTabId => 'General', self::advancedTabId => 'Advanced' );
	}
	public static function firstTab() {
		$arr = self::settingsTabs ();
		return $arr [0];
	}
	
	public static function LoginId() {
		return self::pre ( self::Login );
	}
	public static function PasswordId() {
		return self::pre ( self::Password );
	}
	public static function ThumbId() {
		return self::pre ( self::Thumb );
	}
	public static function FullId() {
		return self::pre ( self::Full );
	}
	public static function CropId() {
		return self::pre ( self::Crop );
	}
	public static function AuthSectionDesc() {
		return self::post_desc ( self::AuthSectionId );
	}
	public static function ImageSectionDesc() {
		return self::post_desc ( self::ImageSectionId );
	}
	//Helper Functions	
	

	//For html elements that are to be placed into wordpress settings in this context, add string wrapping to the parameter name given
	public static function html_name($param_name) {
		return "{self::SettingsId}[$param_name]";
	}
	
	public static function add_field($param_name, $desc, $section_id) {
		add_settings_field ( self::pre ( $param_name ), $desc, ns ( $param_name . self::renderFieldPostfix ), __FILE__, $section_id );
	}
	
	//	Private	
	private static function pre($str) {
		return self::SettingsId . "_" . $str;
	}
	
	//Given a name, append 'desc' to it then namespace it as it should be a local const/var
	private static function post_desc($loc_name) {
		return ns ( $loc_name . "_desc" );
	}

}

function admin_init() {
	wp_enqueue_script ( 'jquery-ui-core' );
	
	register_setting ( Helper::SettingsId, Helper::SettingsId, ns ( 'validate' ) ); // group, name in db, validation func	
	

	add_settings_section ( Helper::AuthSectionId, 'Authentication Settings', Helper::AuthSectionDesc (), __FILE__ );
	Helper::add_field ( Helper::Login, ucfirst ( Helper::Login ), Helper::AuthSectionId );
	Helper::add_field ( Helper::Password, ucfirst ( Helper::Password ), Helper::AuthSectionId );
	
	add_settings_section ( Helper::ImageSectionId, 'Image Settings', Helper::ImageSectionDesc (), __FILE__ );
	Helper::add_field ( Helper::Thumb, 'Thumbnail size', Helper::ImageSectionId );
	Helper::add_field ( Helper::Full, 'Full image size', Helper::ImageSectionId );
	Helper::add_field ( Helper::Crop, 'Crop images', Helper::ImageSectionId );
}

//Section descriptions


function auth_section_desc() {
	echo '<p>Your login and password in picasa</p>';
}

function img_section_desc() {
	echo '<p>Preferred image dimensions</p>';
}

function oauth_section_desc() {
	echo '<p>Allow access to your Picasa account via Oauth</p>';
}

//Renderers


//simple wrapper for html inputs for the next two render methods
function html_input($id, $type_val) {
	$options = get_option ( Helper::SettingsId );
	echo "<input id=$id name='{Helper.html_name($id)}' size='40' type='$type_val' value='{$options[$id]}' />";
}

function login_field_renderer() {
	html_input ( Helper::LoginId (), "text" );
}

function password_field_renderer() {
	html_input ( Helper::PasswordId (), "password" );
}

//simple wrapper for html select, selecting the option wish is currently selected
function html_select($id, $items) {
	$options = get_option ( Helper::SettingsId );
	echo "<select id='{$id}' name='{Helper.name_wrap($id})]'>";
	foreach ( $items as $item ) {
		$selected = ($options [$id] == $item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function thumb_size_field_renderer() {
	html_select ( Helper::ThumbId (), ImageSizes::thumbs () );
}

function full_size_field_renderer() {
	html_select ( Helper::FullId (), ImageSizes::fulls () );
}

function crop_field_renderer() {
	html_select ( Helper::CropId (), array ('no', 'yes' ) );
}

function validate($input) {
	// strip all fields
	

	$filterInput = function ($param_name) {
		$input [$param_name] = wp_filter_nohtml_kses ( $input [$param_name] );
	};
	
	$filterInput ( Helper::LoginId () );
	$filterInput ( Helper::PasswordId () );
	$filterInput ( Helper::ThumbId () );
	$filterInput ( Helper::FullId () );
	
	// check image dimensions, defaulting to some size when not in valid options
	

	$items = ImageSizes::thumbs ();
	if (! in_array ( $input [Helper::ThumbId ()], $items )) {
		$input [Helper::ThumbId ()] = ImageSizes::defaultThumb ();
	}
	
	$items = ImageSizes::fulls ();
	if (! in_array ( $input [Helper::FullId ()], $items )) {
		$input [Helper::FullId ()] = ImageSizes::defaultFull ();
	}
	
	return $input;
}

// Define default option settings
register_activation_hook ( __FILE__, ns ( 'add_defaults' ) );

function add_defaults() {
	update_option ( Helper::SettingsId, array (Helper::LoginId () => 'LOGIN@gmail.com', Helper::PasswordId () => 'your password', Helper::ThumbId () => ImageSizes::defaultThumb (), Helper::FullId () => ImageSizes::defaultFull (), Helper::CropId () => 'no' ) );
}
?>