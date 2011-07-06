<?php
	require_once 'namespace_util.php';
	require_once 'oauth_util.php';
	
	const nsStr = "Embpicamoto_Oauth_Settings_";
	function ns($loc_name)
	{		
		return wrap_namespace(nsStr, $loc_name);		
	}
	
	//add oauth options page
	add_action( 'admin_menu', ns("admin_menu") );
	
	function admin_menu(){
		add_options_page('OAuth Settings', 'OAuth', 'manage_options', __FILE__, ns("page") ) ;	
	}	
	
	function Empicamoto_Oauth_Settings_page()
	{
		?>		
		<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Oauth Settings</h2>
		Enter authentication information to connect to the applications below.
		<form action="options.php" method="post">
		<?php settings_fields(Embpicamoto_Oauth_Util_Constants::SettingsId); ?>
		<?php do_settings_sections(__FILE__); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
		</div>
				
		<?php
	}
	
	//Register Oauth settings with wordpress
	add_action( 'admin_init', ns( "admin_init" ) );
	
	function Empicamoto_Oauth_Settings_admin_init(){
		register_setting( Embpicamoto_Oauth_Util_Constants::SettingsId, Embpicamoto_Oauth_Util_Constants::SettingsId, ns("validate"));
		
		//Google Oauth settings fields  
		add_settings_section(Embpicamoto_Oauth_Util_Constants::GSectionId, ucfirst(Embpicamoto_Oauth_Util_Constants::GSectionName), ns(Embpicamoto_Oauth_Util_Constants::GSectionId), __FILE__);
		
		Empicamoto_Oauth_Settings_Helper::add_gconsumer_settings_field(Embpicamoto_Oauth_Util_Constants::key);
		Empicamoto_Oauth_Settings_Helper::add_gconsumer_settings_field(Embpicamoto_Oauth_Util_Constants::secret);	
	}
	
	//Render functions
	
	function Empicamoto_Oauth_Settings_google_consumer_key_field_renderer(){
		return Empicamoto_Oauth_Settings_consumer_field_renderer(Embpicamoto_Oauth_Util_Constants::key);
	}
	
	function Empicamoto_Oauth_Settings_google_consumer_secret_field_renderer(){
		return Empicamoto_Oauth_Settings_consumer_field_renderer(Embpicamoto_Oauth_Util_Constants::secret);
	}
	
	function Empicamoto_Oauth_Settings_consumer_field_renderer($name)
	{
		$options = get_option(Embpicamoto_Oauth_Util_Constants::SettingsId);		
		$input_id = Embpicamoto_Oauth_Util_Constants::consumerId($name);
		echo "<input id='" . $input_id . "' name='" . Embpicamoto_Oauth_Util_Constants::SettingsId . "[" . $input_id . "]' size='40' type='text' value='" . $options[$input_id ] . "' />";		
	}
	
	function Empicamoto_Oauth_Settings_google_oauth_section() {
		echo '<p>Your Oauth information for Google Data Services</p>';
	}
	
	//Validations
	
	function Empicamoto_Oauth_Settings_validate($input){
		$input[Embpicamoto_Oauth_Util_Constants::consumerKeyId()] =  wp_filter_nohtml_kses($input[Embpicamoto_Oauth_Util_Constants::consumerKeyId()]);
		$input[Embpicamoto_Oauth_Util_Constants::consumerSecretId()]  =  wp_filter_nohtml_kses($input[Embpicamoto_Oauth_Util_Constants::consumerSecretId()]);		
		return $input;
	}
	
	//Setting defaults
	register_activation_hook(__FILE__, ns('add_defaults') );
	
	function Empicamoto_Oauth_Settings_add_defaults() {
	    update_option(Embpicamoto_Oauth_Util_Constants::SettingsId, array(
			Embpicamoto_Oauth_Util_Constants::consumerKeyId()   => Defaults::consumerKey,
			Embpicamoto_Oauth_Util_Constants::consumerSecretId() => Defaults::consumerSecret
			));
	}
	
	//Helper Classes
	
	class Empicamoto_Oauth_Settings_Helper {
		
		const renderFieldPostfix = '_field_renderer';
		
		/**
		 * Given a str, attach the render field to it
		 */ 
		private static function gconsumer_field_renderer_func_name($fieldName){
			return ns( Embpicamoto_Oauth_Util_Constants::google . "_consumer_" . $fieldName . "_field_renderer" );
		}	
		
		//Given a Oauth consumer parameter name register a settings field
		public static function add_gconsumer_settings_field($param_name){
			add_settings_field( Embpicamoto_Oauth_Util_Constants::consumerId($param_name), "Consumer " . ucfirst($param_name), self::gconsumer_field_renderer_func_name($param_name), __FILE__, Embpicamoto_Oauth_Util_Constants::GSectionId);
		}
		
	}

?>