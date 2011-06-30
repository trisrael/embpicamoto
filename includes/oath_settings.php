<?php
namespace embpicamotoOAuth {	
	require_once 'namespace_util.php';
	
	const nsStr = "embpicamotoOAuth";
	function ns($loc_name)
	{		
		return wrap_namespace(nsStr, $loc_name);		
	}
	
	//add oauth options page
	add_action( 'admin_menu', ns("admin_menu") );
	
	function admin_menu(){
		add_options_page('OAuth Settings', 'OAuth', 'manage_options', __FILE__, ns("settings_page") ) ;	
	}	
	
	function settings_page()
	{
		?>		
		<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>Oauth Settings</h2>
		Enter authentication information to connect to the applications below.
		<form action="options.php" method="post">
		<?php settings_fields(OAuth::SettingsId); ?>
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
	
	function admin_init(){
		register_setting( OAuth::SettingsId, OAuth::SettingsId, ns("validate"));
		
		//Google Oauth settings fields  
		add_settings_section(OAuth::GSectionId, ucfirst(OAuth::GSectionName), ns(OAuth::GSectionId), __FILE__);
		
		SettingsHelper::add_gconsumer_settings_field('key');
		SettingsHelper::add_gconsumer_settings_field('secret');	
	}
	
	//Render functions
	
	function google_consumer_key_field_renderer(){
		return consumer_field_renderer('key');
	}
	
	function google_consumer_secret_field_renderer(){
		return consumer_field_renderer('secret');
	}
	
	function consumer_field_renderer($name)
	{
		$options = get_option(OAuth::SettingsId);		
		$input_id = OAuth::consumerId($name);
		echo "<input id='" . $input_id . "' name='" . OAuth::SettingsId . "[" . $input_id . "]' size='40' type='text' value='" . $options[$input_id ] . "' />";		
	}
	
	function google_oauth_section() {
		echo '<p>Your Oauth information for Google Data Services</p>';
	}
	
	//Validations
	
	function validate($input){
		$input[OAuth::consumerKeyId()] =  wp_filter_nohtml_kses($input[OAuth::consumerKeyId()]);
		$input[OAuth::consumerSecretId()]  =  wp_filter_nohtml_kses($input[OAuth::consumerSecretId()]);		
		return $input;
	}

	
	//Setting defaults
	register_activation_hook(__FILE__, ns('add_defaults') );
	
	function add_defaults() {
	    update_option(OAuth::SettingsId, array(
			OAuth::consumerKeyId()   => '',
			OAuth::consumerSecretId() => ''
		));
	}
	
	//Helper Classes
	
	class SettingsHelper {
		
		const renderFieldPostfix = '_field_renderer';
		
		/**
		 * Given a str, attach the render field to it
		 */ 
		private static function gconsumer_field_renderer_func_name($fieldName){
			return ns( OAuth::google . "_consumer_" . $fieldName . "_field_renderer" );
		}	
		
		//Given a Oauth consumer parameter name register a settings field
		public static function add_gconsumer_settings_field($param_name){
			add_settings_field( OAuth::consumerId($param_name), "Consumer " . ucfirst($param_name), self::gconsumer_field_renderer_func_name($param_name), __FILE__, OAuth::GSectionId);
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
	
		public static function consumerId($str){
			return self::GConsumerPre . $str;
		}
		
		const key = 'key';
		public static function consumerKeyId(){
			self::consumerId(key);
		}

		const secret = 'secret';
		public static function consumerSecretId(){
			self::consumerId(secret);
		}		
	};
}

?>