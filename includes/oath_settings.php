<?php
namespace embpicamotoOAuth {
	
	//add oauth options page
	add_action( 'admin_menu', admin_menu);
	
	function admin_menu(){
		add_options_page('OAuth Settings', 'OAuth', 'manage_options', __FILE__, settings_page);	
	}
	
	
	function settings_page()
	{
		?>
		<div class=”wrap”>
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Oauth Settings</h2>
		Enter authentication information to connect to the applications below.
		<form action=”options.php” method=”post”>
		<?php	
			settings_fields( OAuth::SettingsId );
			do_settings_sections( OAuth::GSectionId );
		?>
		<input name=”Submit” class="button-primary" type=”submit” value=”Save Changes” />
		</form></div>
		
		<?php
	}
	
	//Register Oauth settings with wordpress
	add_action('admin_init', admin_init);
	
	function admin_init(){
		register_setting( OAuth::SettingsId, OAuth::SettingsId, OAuth::SettingsId . "_validate");
		
		//Google Oauth settings fields  
		add_settings_section(OAuth::GSectionId, OAuth::GSectionName, OAuth::GSectionId, OAuth::SettingsId);
		
		SettingsHelper::add_gconsumer_settings_field('key');
		SettingsHelper::add_gconsumer_settings_field('secret');	
	}
	
	//Field Renderers
	
	function google_consumer_key_field_renderer(){
		return consumer_field_renderer('key');
	}
	
	function google_consumer_secret_field_renderer(){
		return consumer_field_renderer('secret');
	}
	
	function consumer_field_renderer($name)
	{
		$options = get_option(OAuth::SettingsId);
		$input_id = OAuth::consumerName($name);
		echo "<input id='{$input_id}' name='{OAuth::SettingsId}[{$input_ud}]' size='40' type='text' value='{$options[$input_id]}' />";
	}
	
	//Validations
	
	function settings_validate(){
		
	}
	
	
	//Helper Classes
	
	class SettingsHelper {
		
		const renderFieldPostfix = '_field_renderer';
		
		/**
		 * Given a str, attach the render field to it
		 */ 
		private static function gconsumer_field_renderer_func_name($fieldName){
			return OAuth::google . "_consumer_" . $fieldName . "_field_renderer";
		}	
		
		//Given a Oauth consumer parameter name register a settings field
		public static function add_gconsumer_settings_field($param_name){
			add_settings_field( OAuth::consumerName($param_name), "Consumer {ucfirst($param_name)}", self::gconsumer_field_renderer_func_name($param_name), OAuth::SettingsId , OAuth::GSectionId);
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
}
?>