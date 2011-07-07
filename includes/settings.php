<?php
require_once 'util.php';

function Embpicamoto_Settings_ns($loc_name) {
    return wrap_constant_name("Embpicamoto_Settings", $loc_name);
}

//add plugin options page
add_action('admin_menu', Embpicamoto_Settings_ns('admin_menu'));

#Include jQuery and jQuery UI tabs with no conflict mode to HEAD (ensuring they are added first to avoid overwriting library code in Prototype, and other libraries)
#TODO: Find a better way of including this so it doesn't need to be added in all the time 

class Embpicamoto_Settings_ImageSizes {

    public static $thumbnails = array('32', '48', '64', '72', '104', '144', '150', '160', '180', '200', '240', '280', '320');
    public static $defThumb = '150';
    public static $fulls = array('94', '110', '128', '200', '220', '288', '320', '400', '512', '576', '640', '720', '800', '912', '1024', '1152', '1280', '1440', '1600');
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

function Embpicamoto_Settings_admin_menu() {
    add_options_page('Picasa settings', 'Picasa', 'manage_options', __FILE__, Embpicamoto_Settings_ns('page'));
}

function tabs($current = Embpicamoto_Settings_Helper::generalTabId) {

    $links = array();
    $opts_url = Embpicamoto_Settings_Helper::settingsPageRelUrl;
    foreach (Embpicamoto_Settings_Helper::settingsTabs() as $tab => $name) :
        if ($tab == $current) :
            $links [] = "<a class='nav-tab nav-tab-active' href='?page=$opts_url&tab=$tab'>$name</a>";
        else :
            $links [] = "<a class='nav-tab' href='?page=$opts_url&tab=$tab'>$name</a>";
        endif;
    endforeach
    ;
    echo '<h2>';
    foreach ($links as $link)
        echo $link;
    echo '</h2>';
}

function Embpicamoto_Settings_page() {
    ?>
    <div class="wrap">
        <div class="icon32" id="icon-options-general"><br>
        </div>
        <div id='tabs'>
    <?php
    isset($_GET ['tab']) ? $currTab = $_GET ['tab'] : $currTab = Embpicamoto_Settings_Helper::defaultTabId;
    tabs($currTab);
    ?>
            <?php
            if ($_GET ['page'] == Embpicamoto_Settings_Helper::settingsPageRelUrl) {

                switch ($currTab) :
                    case Embpicamoto_Settings_Helper::generalTabId :
                        Embpicamoto_Settings_general_options();
                        break;
                    case Embpicamoto_Settings_Helper::advancedTabId :
                        Embpicamoto_Settings_advanced_options();
                        break;
                endswitch
                ;
            }
            ?>					


        </div>
    </div>
    <?php
}

function Embpicamoto_Settings_general_options() {
    ?>
    <h2>General Settings</h2>
    	Enter a login and password for Picasa (if not using
    <a href='<?php echo ('?page=' . Embpicamoto_Settings_Helper::settingsPageRelUrl . '&tab=') . Embpicamoto_Settings_Helper::advancedTabId ?>'>Oauth</a>
    	) and edit image options if needed.
    <form action=”options.php” method=”post”>
    <?php
    settings_fields(Embpicamoto_Settings_Helper::SettingsId);
    do_settings_sections(__FILE__);
    ?>
        <p class="submit"><input name="Submit" type="submit" class="button-primary"	value="<?php esc_attr_e('Save Changes'); ?>" /></p>
    </form>
    </div>
    <?php
}

function Empicamoto_Settings_correct_oauth_creds_html($msg) {
    $sty = "-moz-border-radius: 6px 6px 6px 6px;";
    $sty = $sty . "-webkit-border-radius: 6px 6px 6px 6px;";
    $sty = $sty . "border-top-width: 1px; border-top-style: solid;";
    $sty = $sty . "-khtml-border-radius: 6px 6px 6px 6px;";
    $sty = $sty . "top-right-radius: 6px;";
    echo "<p style='$sty' class='update-nag'>$msg. Supply credentials at the <a href='?page=embpicamoto/includes/oauth_settings.php'>OAuth Settings page</a></p>";
}

function Embpicamoto_Settings_advanced_options() {
    ?>
    <div id='auth-settings'>
        <h2>Picasa Authentication</h2>

    <?php
    require_once plugin_dir_path(__FILE__) . "oauth.php";
    $gauth = Empicamoto_Oauth_Google_Manager::singleton(); //google oauth manager		
    

    if ($gauth->is_using_defaults()) {
        Empicamoto_Settings_correct_oauth_creds_html("No Google Oauth credentials supplied yet, unable to authorize");
    } else if ($gauth->has_valid_accreditation()) {
        echo "<p>Valid picasa credits supplied</p>";        
    } else {
        Empicamoto_Settings_correct_oauth_creds_html("Invalid Google Oauth credentials supplied, unable to authorize");
    }
     echo get_object_vars($gauth->getLastRequestToken());    
    ?>				
    </div>
        <?php
    }

/////////////////////////////////////////////////////////////////////
//register plugin options
    add_action('admin_init', Embpicamoto_Settings_ns('admin_init'));

    class Embpicamoto_Settings_Helper {
        /** relative url to this php file * */
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
            return array(self::defaultTabId => 'General', self::advancedTabId => 'Advanced');
        }

        public static function firstTab() {
            $arr = self::settingsTabs();
            return $arr [0];
        }

        public static function LoginId() {
            return self::pre(self::Login);
        }

        public static function PasswordId() {
            return self::pre(self::Password);
        }

        public static function ThumbId() {
            return self::pre(self::Thumb);
        }

        public static function FullId() {
            return self::pre(self::Full);
        }

        public static function CropId() {
            return self::pre(self::Crop);
        }

        public static function AuthSectionDesc() {
            return self::post_desc(self::AuthSectionId);
        }

        public static function ImageSectionDesc() {
            return self::post_desc(self::ImageSectionId);
        }

        //Helper Functions	
        //For html elements that are to be placed into wordpress settings in this context, add string wrapping to the parameter name given
        public static function html_name($param_name) {
            return "{self::SettingsId}[$param_name]";
        }

        public static function add_field($param_name, $desc, $section_id) {
            add_settings_field(self::pre($param_name), $desc, Embpicamoto_Settings_ns($param_name . self::renderFieldPostfix), __FILE__, $section_id);
        }

        //	Private	
        private static function pre($str) {
            return self::SettingsId . "_" . $str;
        }

        //Given a name, append 'desc' to it then namespace it as it should be a local const/var
        private static function post_desc($loc_name) {
            return Embpicamoto_Settings_ns($loc_name . "_desc");
        }

    }

    function Embpicamoto_Settings_admin_init() {
        wp_enqueue_script('jquery-ui-core');

        register_setting(Embpicamoto_Settings_Helper::SettingsId, Embpicamoto_Settings_Helper::SettingsId, Embpicamoto_Settings_ns('validate')); // group, name in db, validation func		

        add_settings_section(Embpicamoto_Settings_Helper::AuthSectionId, 'Authentication Settings', Embpicamoto_Settings_Helper::AuthSectionDesc(), __FILE__);
        Embpicamoto_Settings_Helper::add_field(Embpicamoto_Settings_Helper::Login, ucfirst(Embpicamoto_Settings_Helper::Login), Embpicamoto_Settings_Helper::AuthSectionId);
        Embpicamoto_Settings_Helper::add_field(Embpicamoto_Settings_Helper::Password, ucfirst(Embpicamoto_Settings_Helper::Password), Embpicamoto_Settings_Helper::AuthSectionId);

        add_settings_section(Embpicamoto_Settings_Helper::ImageSectionId, 'Image Settings', Embpicamoto_Settings_Helper::ImageSectionDesc(), __FILE__);
        Embpicamoto_Settings_Helper::add_field(Embpicamoto_Settings_Helper::Thumb, 'Thumbnail size', Embpicamoto_Settings_Helper::ImageSectionId);
        Embpicamoto_Settings_Helper::add_field(Embpicamoto_Settings_Helper::Full, 'Full image size', Embpicamoto_Settings_Helper::ImageSectionId);
        Embpicamoto_Settings_Helper::add_field(Embpicamoto_Settings_Helper::Crop, 'Crop images', Embpicamoto_Settings_Helper::ImageSectionId);
    }

//Section descriptions


    function Embpicamoto_Settings_auth_section_desc() {
        echo '<p>Your login and password in picasa</p>';
    }

    function Embpicamoto_Settings_img_section_desc() {
        echo '<p>Preferred image dimensions</p>';
    }

    function Embpicamoto_Settings_oauth_section_desc() {
        echo '<p>Allow access to your Picasa account via Oauth</p>';
    }

//Renderers
//simple wrapper for html inputs for the next two render methods
    function Embpicamoto_Settings_html_input($id, $type_val) {
        $options = get_option(Embpicamoto_Settings_Helper::SettingsId);
        echo "<input id=$id name='{Helper.html_name($id)}' size='40' type='$type_val' value='{$options[$id]}' />";
    }

    function Embpicamoto_Settings_login_field_renderer() {
        Embpicamoto_Settings_html_input(Embpicamoto_Settings_Helper::LoginId(), "text");
    }

    function Embpicamoto_Settings_password_field_renderer() {
        Embpicamoto_Settings_html_input(Embpicamoto_Settings_Helper::PasswordId(), "password");
    }

//simple wrapper for html select, selecting the option wish is currently selected
    function Embpicamoto_Settings_html_select($id, $items) {
        $options = get_option(Embpicamoto_Settings_Helper::SettingsId);
        echo "<select id='{$id}' name='{Helper.name_wrap($id})]'>";
        foreach ($items as $item) {
            $selected = ($options [$id] == $item) ? 'selected="selected"' : '';
            echo "<option value='$item' $selected>$item</option>";
        }
        echo "</select>";
    }

    function Embpicamoto_Settings_thumb_size_field_renderer() {
        Embpicamoto_Settings_html_select(Embpicamoto_Settings_Helper::ThumbId(), Embpicamoto_Settings_ImageSizes::thumbs());
    }

    function Embpicamoto_Settings_full_size_field_renderer() {
        Embpicamoto_Settings_html_select(Embpicamoto_Settings_Helper::FullId(), Embpicamoto_Settings_ImageSizes::fulls());
    }

    function Embpicamoto_Settings_crop_field_renderer() {
        Embpicamoto_Settings_html_select(Embpicamoto_Settings_Helper::CropId(), array('no', 'yes'));
    }

    function Embpicamoto_Settings_validate($input) {
        // strip all fields

        $filterInput = create_function("$param_name", "$input [$param_name] = wp_filter_nohtml_kses ( $input [$param_name] );");

        $filterInput(Embpicamoto_Settings_Helper::LoginId());
        $filterInput(Embpicamoto_Settings_Helper::PasswordId());
        $filterInput(Embpicamoto_Settings_Helper::ThumbId());
        $filterInput(Embpicamoto_Settings_Helper::FullId());

        // check image dimensions, defaulting to some size when not in valid options


        $items = Embpicamoto_Settings_ImageSizes::thumbs();
        if (!in_array($input [Embpicamoto_Settings_Helper::ThumbId()], $items)) {
            $input [Embpicamoto_Settings_Helper::ThumbId()] = Embpicamoto_Settings_ImageSizes::defaultThumb();
        }

        $items = Embpicamoto_Settings_ImageSizes::fulls();
        if (!in_array($input [Embpicamoto_Settings_Helper::FullId()], $items)) {
            $input [Embpicamoto_Settings_Helper::FullId()] = Embpicamoto_Settings_ImageSizes::defaultFull();
        }

        return $input;
    }

// Define default option settings
    register_activation_hook(__FILE__, Embpicamoto_Settings_ns('add_defaults'));

    function Embpicamoto_Settings_add_defaults() {
        update_option(Embpicamoto_Settings_Helper::SettingsId, array(Embpicamoto_Settings_Helper::LoginId() => 'LOGIN@gmail.com', Embpicamoto_Settings_Helper::PasswordId() => 'your password', Embpicamoto_Settings_Helper::ThumbId() => Embpicamoto_Settings_ImageSizes::defaultThumb(), Embpicamoto_Settings_Helper::FullId() => Embpicamoto_Settings_ImageSizes::defaultFull(), Embpicamoto_Settings_Helper::CropId() => 'no'));
    }
    ?>