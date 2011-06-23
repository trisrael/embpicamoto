<?php

//add plugin options page
add_action( 'admin_menu', 'embpicasa_admin_menu' );

function embpicasa_admin_menu() {
	add_options_page('Picasa settings', 'Picasa', 'manage_options', __FILE__, 'embpicasa_settings_page');
}

function embpicasa_settings_page() {
?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Picasa settings</h2>
		Enter auth params and select preferred image dimensions
		<form action="options.php" method="post">
		<?php settings_fields('embpicasa_options'); ?>
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
add_action('admin_init', 'embpicasa_admin_init' );
function embpicasa_admin_init(){
	register_setting('embpicasa_options', 'embpicasa_options', 'embpicasa_options_validate' ); // group, name in db, validation func
	
	add_settings_section('auth_section', 'Auth Settings', 'embpicasa_options_section_auth', __FILE__);
	add_settings_field('embpicasa_options_login', 'Login', 'embpicasa_options_login_field_renderer', __FILE__, 'auth_section');
	add_settings_field('embpicasa_options_password', 'Password', 'embpicasa_options_password_field_renderer', __FILE__, 'auth_section');	
	
	add_settings_section('img_section', 'Image Settings', 'embpicasa_options_section_img', __FILE__);
	add_settings_field('embpicasa_options_thumb_size', 'Thumbnail size', 'embpicasa_options_thumb_size_field_renderer', __FILE__, 'img_section');
	add_settings_field('embpicasa_options_full_size', 'Full image size', 'embpicasa_options_full_size_field_renderer', __FILE__, 'img_section');
	add_settings_field('embpicasa_options_crop', 'Crop images', 'embpicasa_options_crop_field_renderer', __FILE__, 'img_section');
}

function embpicasa_options_section_auth() {
	echo '<p>Your login and password in picasa</p>';
}

function embpicasa_options_section_img() {
	echo '<p>Preferred image dimensions</p>';
}

function embpicasa_options_login_field_renderer() {
	$options = get_option('embpicasa_options');
	echo "<input id='embpicasa_options_login' name='embpicasa_options[embpicasa_options_login]' size='40' type='text' value='{$options['embpicasa_options_login']}' />";
}

function embpicasa_options_password_field_renderer() {
	$options = get_option('embpicasa_options');
	echo "<input id='embpicasa_options_password' name='embpicasa_options[embpicasa_options_password]' size='40' type='password' value='{$options['embpicasa_options_password']}' />";
}

function embpicasa_options_thumb_size_field_renderer() {
	$options = get_option('embpicasa_options');
	$items = array('32', '48', '64', '72', '104', '144', '150', '160', '180', '200', '240', '280', '320');
	echo "<select id='embpicasa_options_thumb_size' name='embpicasa_options[embpicasa_options_thumb_size]'>";
	foreach($items as $item) {
		$selected = ($options['embpicasa_options_thumb_size']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function embpicasa_options_full_size_field_renderer() {
	$options = get_option('embpicasa_options');
	$items = array('94', '110', '128', '200', '220', '288', '320', '400', '512', '576', '640', '720', '800', '912', '1024', '1152', '1280', '1440', '1600');
	echo "<select id='embpicasa_options_full_size' name='embpicasa_options[embpicasa_options_full_size]'>";
	foreach($items as $item) {
		$selected = ($options['embpicasa_options_full_size']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function embpicasa_options_crop_field_renderer() {
	$options = get_option('embpicasa_options');
	$items = array('no', 'yes');
	echo "<select id='embpicasa_options_crop' name='embpicasa_options[embpicasa_options_crop]'>";
	foreach($items as $item) {
		$selected = ($options['embpicasa_options_crop']==$item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
}

function embpicasa_options_validate($input) {
	// strip all fields
	$input['embpicasa_options_login'] 	   =  wp_filter_nohtml_kses($input['embpicasa_options_login']);
	$input['embpicasa_options_password']   =  wp_filter_nohtml_kses($input['embpicasa_options_password']);
	$input['embpicasa_options_thumb_size'] =  wp_filter_nohtml_kses($input['embpicasa_options_thumb_size']);
	$input['embpicasa_options_full_size']  =  wp_filter_nohtml_kses($input['embpicasa_options_full_size']);
	
	// check image dimensions
	$items = array('32', '48', '64', '72', '104', '144', '150', '160');
	if(!in_array($input['embpicasa_options_thumb_size'], $items)) {
		$input['embpicasa_options_thumb_size'] = '150';
	}
	
	$items = array('32', '48', '64', '72', '104', '144', '150', '160');
	if(!in_array($input['embpicasa_options_full_size'], $items)) {
		$input['embpicasa_options_full_size'] = '640';
	}
	
	return $input;
}

// Define default option settings
register_activation_hook(__FILE__, 'embpicasa_options_add_defaults');
function embpicasa_options_add_defaults() {
    update_option('embpicasa_options', array(
		'embpicasa_options_login' 	   => 'LOGIN@gmail.com',
		'embpicasa_options_password'   => '',
		'embpicasa_options_thumb_size' => '150',
		'embpicasa_options_full_size'  => '640',
		'embpicasa_options_crop'       => 'no'
	));
}
