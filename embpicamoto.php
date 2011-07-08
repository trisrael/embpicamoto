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
#Load wordpress plugin settings
require_once('includes/settings.php');
require_once('includes/oauth_settings.php');
require_once('includes/oauth.php');

class Embpicamoto_Photos {

    /**
     * Will create a service class for accessing Picasa Web Albums using Oauth as first priority, and falling back to ClientLogin when not available.
     * @return Zend_Gdata_Photos 
     */
    public static function getService() {
        Embpicamoto_include_library();
        require_once 'Zend/Loader.php';
        
        Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
        Zend_Loader::loadClass('Zend_Gdata_Photos');
        
        $gauth = Embpicamoto_Oauth_Google_Manager::singleton();

        $client = null; #Should cause an interesting exception if neither works
        if ($gauth->has_access_token()) {            
            $client = $gauth->getAccessToken()->getHttpClient($gauth->getConfig());
        } else {
            
            $login_val = Embpicamoto_Settings_Helper::getLogin();
            $pass_val = Embpicamoto_Settings_Helper::getPassword();
            $client = Zend_Gdata_ClientLogin::getHttpClient($login_val, $pass_val, Zend_Gdata_Photos::AUTH_SERVICE_NAME);
        }

        $service = new Zend_Gdata_Photos($client);

        return $service;
    }
    
      /**
      *
      * @param type $albumId, Picasa Web Albums Id of album to be rendered
      * @param type $per_page,number of photos to be shown per page
      * @param type $per_line, number of photos to show per row
      * @param type $picasaQueryParams, options for query being sent to Picasa (use ids in Empicamoto_Settings_Helper:: [CropId(), ThumbId(), FullId] for maintainability) 
      * @param type $no_lightbox, when set to true, img is not surrounded by lightbox metadata
      */
    public static function buildAlbum($albumId, $per_page, $per_line, $queryParams, $no_lightbox = false){            
        
           try {           
            
            Embpicamoto_include_library();
            require_once 'Zend/Loader.php';

            Zend_Loader::loadClass('Zend_Gdata');
            Zend_Loader::loadClass('Zend_Gdata_Query');                
            Zend_Loader::loadClass('Zend_Gdata_Photos_UserQuery');
            Zend_Loader::loadClass('Zend_Gdata_Photos_AlbumQuery');
            Zend_Loader::loadClass('Zend_Gdata_Photos_PhotoQuery');

            $service = Embpicamoto_Photos::getService();

            $photos = array();
            $query = new Zend_Gdata_Photos_AlbumQuery();
            $query->setAlbumId($albumId);

            // http://code.google.com/intl/ru/apis/picasaweb/docs/1.0/reference.html

            $suffix = $queryParams[Embpicamoto_Settings_Helper::CropId()] == 'no' ? 'u' : 'c';
            $query->setThumbsize($queryParams[Embpicamoto_Settings_Helper::ThumbId()] . $suffix);
            $query->setImgMax($queryParams[Embpicamoto_Settings_Helper::FullId()] . $suffix);
            $results = $service->getAlbumFeed($query);

            while ($results != null) {
                foreach ($results as $entry) {
                    foreach ($results as $photo) {
                        $photos[] = array(
                            'thumbnail' => $photo->mediaGroup->thumbnail[0]->url,
                            'fullsize' => $photo->mediaGroup->content[0]->url
                        );
                    }
                }
                try {
                    $results = $results->getNextFeed();
                } catch (Exception $e) {
                    $results = null;
                }
            }

            #TODO: Need to add ability to theme without touching codebase here

            $has_rows = isset($per_line); #Check if user supplied a number per line
            $has_pages = isset($per_page) && is_numeric($per_page) && $per_page > 0;
            if (!$has_pages) {
                $per_page = count($photos); #To avoid division by zero (and other possibly bad side effects), if no photos lower foreach won't enter
            }

            wp_enqueue_script('jquery-ui-core');

            $page_names = array();

            $html = "";

            $pageElId = create_function('$a', 'return "embpicamoto_album_' . $albumId . '_page_" . $a;'); #creates a unique id for an album page
            #foreach temporary variables
            $page_name = '';

            foreach ($photos as $index => $photo) {
                #Per page variables
                $has_new_page = $index == 0 || ($has_pages && ($index % $per_page) == 0); #If on first page add ul,  

                if ($has_new_page) {
                    $page_name = floor(($index + 1) / $per_page);
                    $page_names[] = $page_name;

                    if ($index > 0) {
                        $html = $html . '</ul></div>'; #End the last page  
                    }

                    #Add new page
                    $html = $html . "<div id='" . $pageElId($page_name) . "'><ul class='embpicamoto'>";
                }

                #only surround with lightbox metadata when $no_lightbox is false
                if(!$no_lightbox){
                    $html = $html . '<li>';
                    $html = $html . '<a rel="lightbox[' . $album['id'] . ']" target="_blank" href="' . $photo['fullsize'] . '">';
                }
                
                $html = $html . '<img src="' . $photo['thumbnail'] . '" />';
                
                if(!$no_lightbox){
                    $html = $html . '</a>';
                    $html = $html . '</li>';
                }
            }

            $html = $html . '</ul></div>'; #Finish the last page
            #Container html element variables
            $wrap_el_id = "embpicamoto_album_$albumId";
            $wrap_pre = "<div id='$wrap_el_id'>";
            $wrap_post = "</div>";
            if ($has_pages) { #Add in jQuery tabs conditionally
                //This line does not add in jQuery UI correctly
                #wp_enqueue_script('jquery-ui-tabs');#Ensure jquery-ui-tabs is available on clientside (will add in jQuery automatically)
                #Initiate pages using jQuery tabs
                $script = '<script type=\'text/javascript\'>';
                $script = $script . "(function(){";
                $script = $script . "var timesTried=0;";
                $script = $script . "var setTabs = function(){ jQuery('#$wrap_el_id').tabs(); };";
                $script = $script . "var jLoaded = function(){ return (typeof jQuery == 'function' && typeof jQuery.ui == 'object' && typeof jQuery.ui.tabs == 'function'); };";
                $script = $script . "var timed = function(){ setTimeout( function(){if(timesTried>20){return;} if(jLoaded()){ setTabs(); }else{ timesTried++; timed(); }  }, 500) };";
                $script = $script . "timed();})()";
                $script = $script . "</script>";

                $html_page_names = '<ul>';
                foreach ($page_names as $p_name) {
                    $p_id = $pageElId($p_name);
                    $html_page_names = $html_page_names . "<li><a href='#$p_id'>$p_name</a></li>";
                }
                $html_page_names = $html_page_names . '</ul>';
            }

            return $wrap_pre . $html_page_names . $html . $wrap_post . $script;
        } catch (Exception $ex) {
            return '<p style="color:red">' . $ex->getMessage() . '</p>';
        }    
        
        }
}
/////////////////////////////////////////////////////////////////////
// add the shortcode handler for picasa galleries
// http://brettterpstra.com/adding-a-tinymce-button/
function add_embpicamoto_shortcode($atts, $content = null) {
    extract(shortcode_atts(array("id" => '', "per_page" => '', "per_line" => ''), $atts));    
    
    if (empty($id))
        return '';#Without an id for an albumid can't do anything    
    
    $queryParams = array(
        Embpicamoto_Settings_Helper::CropId() => Embpicamoto_Settings_Helper::getCrop(),
        Embpicamoto_Settings_Helper::ThumbId() => Embpicamoto_Settings_Helper::getThumb(),
        Embpicamoto_Settings_Helper::FullId() => Embpicamoto_Settings_Helper::getFull()
    );
    
    echo Embpicamoto_Photos::buildAlbum($id, $per_page, $per_line, $queryParams);
}

add_shortcode('embpicamoto', 'add_embpicamoto_shortcode');

/////////////////////////////////////////////////////////////////////
// embed some javascript for tinymce plugin


class Embpicamoto_Dialog {

    private static $prefix = "embpicamoto_dlg_";

    public function customPerPageId() {
        return self::pre("custom_per_page");
    }

    public function perPageId() {
        return self::pre("per_page");
    }

    public function contentAlbumId() {
        return self::pre("album_id");
    }

    private static function pre($to_app) {
        return self::$prefix . $to_app;
    }

}

// add jquery ui styles
function embpicamoto_init() {
    if (is_admin() && current_user_can('edit_posts') && current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
        //In case of page not getting loaded with jquery-ui automatically
        wp_register_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/smoothness/jquery-ui.css', true);
        wp_enqueue_style('jquery-style');
    }

    if (!is_admin()) {
        wp_register_style('embpicamoto-style', plugins_url('embpicamoto.css', __FILE__));
        wp_enqueue_style('embpicamoto-style');
    }
}

add_action('init', 'embpicamoto_init');
add_action('edit_form_advanced', 'embpicamoto_embed_js');
add_action('edit_page_form', 'embpicamoto_embed_js');

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
            var album_id = jQuery("#<?php echo(Embpicamoto_Dialog::contentAlbumId()) ?>").val();
            var s_pp_val = jQuery("#<?php echo(Embpicamoto_Dialog::perPageId()) ?>").val();
            var c_pp_val = jQuery("#<?php echo(Embpicamoto_Dialog::customPerPageId()) ?>").val();
            var pp_val = -1; //Default to infinity (Show all images on one page)

            if ( !isNaN( Number(c_pp_val) ) ){
                pp_val = c_pp_val;
            }
            else if( !isNaN( Number(s_pp_val) ) )
            {
                pp_val = s_pp_val;
            }
    		
            var shortcode = '[embpicamoto id="'+album_id+'" per_page="'+pp_val+'"]';	

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
    
    $success = true;
    $msg = '';
    $opts = '';
    try {
        Embpicamoto_include_library();

        require_once 'Zend/Loader.php';

        Zend_Loader::loadClass('Zend_Gdata');
        Zend_Loader::loadClass('Zend_Gdata_Query');        
        Zend_Loader::loadClass('Zend_Gdata_Photos_UserQuery');
        Zend_Loader::loadClass('Zend_Gdata_Photos_AlbumQuery');
        Zend_Loader::loadClass('Zend_Gdata_Photos_PhotoQuery');


        $service = Embpicamoto_Photos::getService();

        $albums = array();

        $results = $service->getUserFeed();
        while ($results != null) {
            foreach ($results as $entry) {
                $album_id = $entry->gphotoId->text;
                $album_name = $entry->title->text;

                $albums[] = array(
                    'id' => $album_id,
                    'name' => $album_name
                );
            }

            try {
                $results = $results->getNextFeed();
            } catch (Exception $e) {
                $results = null;
            }
        }

        foreach ($albums as $album) {
            $opts = $opts . '<option value="' . $album['id'] . '">' . $album['name'] . '</option>';
        }

        $pp_opts = array("None", 3, 4, 5, 6, 8, 9, 10, 12, 14);
        $pp_opts_html = '';
        foreach ($pp_opts as $ind => $pp_opt) {
            $pp_opt_title = ($ind == 1) ? "All Images on Single Page'" : '';
            $pp_opts_html = $pp_opts_html . '<option ' . $pp_opt_title . ' value="' . $pp_opt . '">' . $pp_opt . '</option>';
        }
    } catch (Exception $ex) {
        echo "<p id='failure_message'>$ex->getMessage()</p>";
        $success = false;
        $msg = $ex->getMessage();
    }
    ?>

    <div class="hidden">
        <div id="embpicamoto_dlg" title="Picasa">
            <div class="embpicamoto_dlg_content">
                        <?php if ($success): ?>
                    <p>
                        <label>
        						Select album:
                            <select id="<?php echo(Embpicamoto_Dialog::contentAlbumId()) ?>" style="width:98%"><?php echo $opts; ?></select>
                        </label>
                    </p>
                    <p>
                        <label title="Images to Show per Album Page (default: All Images on Single Page)">
        						Per page:
                            <select id="<?php echo(Embpicamoto_Dialog::perPageId()) ?>">
                                
        <?php echo $pp_opts_html; ?>
                            </select>
                        </label>
                        <label title="Custom Image to Show per Album Page Value">Custom:<input style="width: 2.5em" type='text' id="<?php echo Embpicamoto_Dialog::customPerPageId() ?>"/>
                        </label>
                    </p>
                <?php else: ?>
                    <div style="padding:1em;" class="ui-state-error ui-corner-all"> 
                        <p><strong>ERROR</strong><br /><?php echo $msg ?></p>
                    </div>
    <?php endif; ?>
            </div>
        </div>
    </div>
    <style type="text/css">
        .ui-button-text-only .ui-button-text {padding:0;}
        .ui-widget-overlay {background:#AAAAAA;}
    </style>

    <?php
}

add_action('admin_footer', 'embpicamoto_js_dlg_markup');

/////////////////////////////////////////////////////////////////////
// add embpicamoto button into tinymce
// http://brettterpstra.com/adding-a-tinymce-button/
function add_embpicamoto_button() {
    if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
        return;
    if (get_user_option('rich_editing') == 'true') {
        add_filter('mce_external_plugins', 'add_embpicamoto_tinymce_plugin');
        add_filter('mce_buttons', 'register_embpicamoto_button');

        wp_enqueue_script('jquery-ui-dialog');  //Ensure jquery-ui-dialog is available on clientside (will add in jQuery automatically)
    }
}

add_action('init', 'add_embpicamoto_button');

function register_embpicamoto_button($buttons) {
    array_push($buttons, "|", "embpicamoto");
    return $buttons;
}

function add_embpicamoto_tinymce_plugin($plugin_array) {
    $plugin_array['embpicamoto'] = WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)) . 'embpicamoto.js';
    return $plugin_array;
}

// Trick/hack to tinymce refresh all files
function embpicamoto_refresh_mce($ver) {
    $ver += 3;
    return $ver;
}

add_filter('tiny_mce_version', 'embpicamoto_refresh_mce');

#Include jQuery and jQuery UI tabs with no conflict mode to HEAD (ensuring they are added first to avoid overwriting library code in Prototype, and other libraries)
#TODO: Find a better way of including this so it doesn't need to be added in all the time 

function js_includes() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-no-conflict', plugins_url('noconflict.js', __FILE__));
}

add_action('wp_head', js_includes, 1);


$EMPICAMOTO_LIBRARY_INCLUDED = false;
#Add libray to include path

function Embpicamoto_include_library() {

    if (!$EMPICAMOTO_LIBRARY_INCLUDED) { #Only let this get added to included once in this file
        set_include_path(implode(PATH_SEPARATOR, array(
                    realpath(dirname(__FILE__) . '/library'),
                    get_include_path(),
                )));
    }
    $EMPICAMOTO_LIBRARY_INCLUDED = true;
}
?>
