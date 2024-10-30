<?php
/**
 * local-magic 
 *
 * @package   local-magic
 * @author    matthewrubin
 * @license   GPLv2 or later
 * @link      https://www.mrmarketingres.com/local-magic
 * @copyright 2020 matthewrubin
 *
 * @wordpress-plugin
 * Plugin Name:       Local Magic
 * Plugin URI:        https://www.mrmarketingres.com/local-magic
 * Description:       The Local Magic® WordPress plugin extends the functionality of the SaaS Local Magic® to WordPress so that the local news feed can be displayed on the WordPress website. The plugin is for customers of Local Magic® that have an active subscription with the company.
 * Version:           2.3.0
 * Requires at least: 3.5.1
 * Tested up to:      6.3.0
 * Requires PHP:      5.6.0
 * Author:            matthewrubin
 * Author URI:        https://www.mrmarketingres.com/
 * Text Domain:       local-magic
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

define('MRYLM_VERSION', '2.3.0');

define('MRYLM_PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, 'mrylm_activation');

register_deactivation_hook(__FILE__, 'mrylm_deactivation');

function mrylm_activation() {

    require_once(MRYLM_PLUGIN_DIR . 'activate.php' );
    mrylm_create_tables();
}

function mrylm_deactivation() {

    require_once(MRYLM_PLUGIN_DIR . 'deactivate.php' );
}

include_once 'include/function.php';

/* ACTION START */

function mrylm_enqueue_scripts() {   
}
add_action('wp_enqueue_scripts', 'mrylm_enqueue_scripts');

function mrylm_admin_menu() {

    $view_level = 'activate_plugins';
    add_menu_page(('local-magic'), __('Local Magic', 'local-magic'), $view_level, 'mrylm_admin_menu', 'mrylm_options', plugins_url('local-magic/assets/images/icon-lm-light.png'));
}
add_action('admin_menu', 'mrylm_admin_menu');

function mrylm_options() {

    if (!current_user_can('activate_plugins')) {

        wp_die(__('Local Magic Admin Area', 'local-magic'));
    }
    include_once 'admin/setting.php';
}


/* UPDATE ACTION START */
add_action( 'upgrader_process_complete', 'mrylm_upgrade_function',10, 2);
function mrylm_upgrade_function( $upgrader_object, $options ) {
    
    require_once(MRYLM_PLUGIN_DIR . 'activate.php' );
    mrylm_update_tables();
}
/* UPDATE ACTION END */


/* SHORT CODE START */
function mrylm_article_shortcode() {     
    return mrylm_local_magic_article();
}
add_shortcode('mrylm-article', 'mrylm_article_shortcode');

function mrylm_dropdown_menu_shortcode($attr, $content) {
    return mrylm_dropdown_menu($content);
}
add_shortcode('mrylm-menu', 'mrylm_dropdown_menu_shortcode');

function mrylm_news_shortcode() {
    return mrylm_news();
}
add_shortcode('mrylm-news', 'mrylm_news_shortcode');

function mrylm_service_area_shortcode() {
    return mrylm_service_area();
}
add_shortcode('mrylm-service-area', 'mrylm_service_area_shortcode');

function mrylm_near_me_menu_shortcode() {
    return mrylm_near_me_menu();
}
add_shortcode('mrylm-near-me-menu', 'mrylm_near_me_menu_shortcode');

function mrylm_job_posting_shortcode($attr, $content) {    
    return mrylm_job_posting($content);
}
add_shortcode('mrylm-job-posting', 'mrylm_job_posting_shortcode');

function mrylm_poi_shortcode($attr, $content) {    
    return mrylm_poi($content);
}
add_shortcode('mrylm_poi', 'mrylm_poi_shortcode');

/* SHORT CODE END */

// DESTROY LM
/* Manage/ Destroy Page data Start */
add_action('wp_ajax_nopriv_mrylm-manage-pages', 'mrylm_manage_pages');
function mrylm_manage_pages() {

    global $wpdb;
    
    $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting = $wpdb->get_row($mrylm_setting_sql);
    
    $lm_city_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities ORDER BY lm_parent_id DESC";
    $lm_cities = $wpdb->get_results($lm_city_sql);
    
    if (isset($lm_cities) && $lm_cities != '') {
        
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                foreach ($lm_cities as $obj) {
                    $postid = url_to_postid($lm_setting->lm_org_url. $key.'/'. $obj->lm_url_slug);
                    if ($postid) {
                        wp_delete_post($postid, true);
                    }
                }
            }
            
        }else{
            foreach ($lm_cities as $obj) {
                $postid = url_to_postid($lm_setting->lm_page_url . $obj->lm_url_slug);
                if ($postid) {
                    wp_delete_post($postid, true);
                }
            }
        }            
    }
    
    // manage/delete for service area page
    $service_area_page =  $lm_setting->lm_is_service_area_page ? $lm_setting->lm_service_area_page_title : 'Communities Served';
    $postid = url_to_postid($lm_setting->lm_org_url . $service_area_page);
    if ($postid) {
        wp_delete_post($postid, true);
    }
    
    // Delete all tables
    $sql_setting = "DROP TABLE {$wpdb->prefix}mrylm_setting";
    $wpdb->query($sql_setting);

    $sql_cities = "DROP TABLE {$wpdb->prefix}mrylm_cities";
    $wpdb->query($sql_cities);

    $sql_news = "DROP TABLE {$wpdb->prefix}mrylm_news";
    $wpdb->query($sql_news);
    
    $sql_jobs = "DROP TABLE {$wpdb->prefix}mrylm_jobs";
    $wpdb->query($sql_jobs);
    
}

/* Manage/Delete Page data End */
// DESTROY LM END


/* Update Setting data Start */
add_action('wp_ajax_nopriv_mrylm-update-setting', 'mrylm_update_setting');

function mrylm_update_setting() {

    $lm_setting = $_REQUEST['setting'];

    if (isset($lm_setting)) {
        
        // UPDATE SQL
        mrylm_upgrade_function(array(), array());
        
        $lm_setting = stripcslashes(str_replace('\"', '"', $lm_setting));
        $lm_setting = json_decode($lm_setting);

        global $wpdb;
        $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mrylm_setting";
        $wpdb->query($delete_sql);

        $data_array = array(
            'lm_unique_id' => $lm_setting->unique_id,            
            'lm_head_line' => $lm_setting->head_line,
            'lm_display_type' => $lm_setting->display_type,
            'lm_custom_css' => $lm_setting->custom_css,
            'lm_custom_footer_css' => $lm_setting->custom_footer_css,
            'lm_custom_js' => $lm_setting->custom_js,
            'lm_org_name' => $lm_setting->org_name,
            'lm_org_type' => $lm_setting->org_type,
            'lm_org_url' => $lm_setting->org_url,
            'lm_page_url' => $lm_setting->org_page_url,
            'lm_org_logo_url' => $lm_setting->org_logo_url,
            'lm_org_address' => $lm_setting->org_address,
            'lm_org_address_line' => $lm_setting->org_address_line,
            'lm_org_city' => $lm_setting->org_city,
            'lm_org_state' => $lm_setting->org_state,
            'lm_org_zipcode' => $lm_setting->org_zipcode,
            'lm_title_tag' => $lm_setting->title_tag,
            'lm_site_title' => $lm_setting->site_title,
            'lm_meta_description' => $lm_setting->meta_description,
            'lm_form_id' => $lm_setting->form_id,
            'lm_phone' => $lm_setting->phone,
            'lm_keyword' => $lm_setting->keyword,
            'lm_keyword_separator' => $lm_setting->keyword_separator,
            'lm_page_type' => $lm_setting->page_type,
            'lm_default_city' => $lm_setting->default_city,
            'lm_template_path' => $lm_setting->template_path,
            'lm_template_other_path' => $lm_setting->template_other_path,
            'lm_api_url' => $lm_setting->api_url,
            'lm_is_replace_image' => $lm_setting->is_replace_image,
            'lm_image_keyword' => $lm_setting->image_keyword,
            'lm_is_job_post' => $lm_setting->is_job_post,
            'lm_latitude' => $lm_setting->latitude,
            'lm_longitude' => $lm_setting->longitude,
            'lm_job_headline' => $lm_setting->job_headline,
            'lm_service_area_page_title' => $lm_setting->service_area_page_title,
            'lm_embed_code' => $lm_setting->embed_code,
            'lm_is_multi_location' => $lm_setting->is_multi_location,
            'lm_is_service_area_page' => $lm_setting->is_service_area_page,
            'lm_is_point_of_interest' => $lm_setting->is_point_of_interest,
            'lm_is_pause' => $lm_setting->is_pause,
            'lm_is_rss_feed' => $lm_setting->is_rss_feed,
            'lm_is_multi_type' => $lm_setting->is_multi_type,
            'lm_multi_title' => $lm_setting->multi_title,
            'lm_multi_meta_desc' => $lm_setting->multi_meta_desc,
            'lm_multi_keyword' => $lm_setting->multi_keyword,
            'lm_multi_head' => $lm_setting->multi_head,
            'lm_multi_near_me' => $lm_setting->multi_near_me
        );

        $mrylm_table_name = $wpdb->prefix . "mrylm_setting";
        $wpdb->insert($mrylm_table_name, $data_array);
    }

    echo TRUE;
}

/* Update Setting data End */


/* Manage Cities data Start */
add_action('wp_ajax_nopriv_mrylm-manage-cities', 'mrylm_manage_cities');
function mrylm_manage_cities() {

    // functional code will go here    
    $lm_cities = $_REQUEST['cities'];
    require_once(MRYLM_PLUGIN_DIR . 'activate.php' );
    mrylm_update_tables();
     
    if (isset($lm_cities)) {

        global $wpdb;
        $lm_cities = stripcslashes(str_replace('\"', '"', $lm_cities));
        $lm_cities = json_decode($lm_cities);

        $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mrylm_cities";
        $wpdb->query($delete_sql);

        $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($mrylm_setting_sql);
         
        foreach ($lm_cities as $obj) {

            $city = stripslashes($obj->city);
            $data_arr = array(
                'lm_city_id' => $obj->id,
                'lm_parent_id' => $obj->parent_id,
                'lm_city' => $city,
                'lm_state' => $obj->state,
                'lm_slug' => $obj->slug,
                'lm_url_slug' => $obj->url_slug,
                'lm_google_map_id' => $obj->google_map_id,
                'lm_form_id' => $obj->form_id,
                'lm_city_keyword_separator' => $obj->city_keyword_separator,
                'lm_location' => $obj->location,
                'lm_city_phone' => $obj->city_phone,
                'lm_city_mayor' => $obj->city_mayor,
                'lm_population' => $obj->population,
                'lm_avg_home_value' => $obj->avg_home_value,
                'lm_avg_household_income' => $obj->avg_household_income,
                'lm_avg_temperature' => $obj->avg_temperature,
                'lm_price_range' => $obj->price_range,
                'lm_image' => $obj->image,
                'lm_description' => $obj->description,
                'lm_telephone' => $obj->telephone,
                'lm_educational_institution' => $obj->educational_institution,
                'lm_state_park' => $obj->state_park,
                'lm_historic_landmark' => $obj->historic_landmark,
                'lm_assisted_living' => $obj->assisted_living,
                'lm_restaurant' => $obj->restaurant
            );

            $mrylm_table_name = $wpdb->prefix . "mrylm_cities";
            $wpdb->insert($mrylm_table_name, $data_arr);

            $lm_city = array();
            $lm_city['lm_parent_id'] = $obj->parent_id;
            $lm_city['lm_url_slug'] = $obj->url_slug;
            $lm_city['lm_city'] = $obj->city;
            $lm_obj_city = (object) $lm_city; 

            if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){

                $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
                foreach($lm_multi_titles AS $key=>$lm_title){

                    $lm_page_url = $lm_setting->lm_org_url.$key.'/';
                    create_lm_page($lm_setting, $lm_obj_city, $lm_page_url);    
                }

            }else{
                create_lm_page($lm_setting, $lm_obj_city, $lm_setting->lm_page_url);
            }

        }
        
    }
    
    // for service area page page
    if($lm_setting->lm_is_service_area_page){        
        create_service_area_page($lm_setting);
    } 
    
    echo TRUE;
}

/* Manage Cities data End */


/* Add single type cities data Start */
add_action('wp_ajax_nopriv_mrylm-manage-single-types', 'mrylm_manage_single_types');
function mrylm_manage_single_types() {

    // functional code will go here    
    $lm_cities = $_REQUEST['cities'];
    $lm_url_type = $_REQUEST['lm_type'];
     

    if (isset($lm_cities)) {

        global $wpdb;
        $lm_cities = stripcslashes(str_replace('\"', '"', $lm_cities));
        $lm_cities = json_decode($lm_cities);
        
        $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($mrylm_setting_sql);
        
        // we can create parent page , LM type page
        require_once(MRYLM_PLUGIN_DIR . 'activate.php' );
        mrylm_update_tables();
        $lm_page_url = $lm_setting->lm_org_url.$lm_url_type.'/';
         
        foreach ($lm_cities as $obj) {
            
            $city_name = stripslashes($obj->city);  
            $city = array();
            $city['lm_parent_id'] = $obj->parent_id;
            $city['lm_url_slug'] = $obj->url_slug;
            $city['lm_city'] = $city_name;
            $lm_city = (object) $city; 

            if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
               
                create_lm_page($lm_setting, $lm_city, $lm_page_url);
            }
        }        
    }   
    
    echo TRUE;
}
/* Add single type cities data END */


/* Add City data Start*/
add_action('wp_ajax_nopriv_mrylm-add-city', 'mrylm_add_city');
function mrylm_add_city() {

    $lm_city = $_REQUEST['city'];
    mrylm_upgrade_function(array(), array());
    
    if (isset($lm_city)) {
        $lm_city = stripcslashes(str_replace('\"', '"', $lm_city));
        $lm_city = json_decode($lm_city);

        global $wpdb;

        $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($mrylm_setting_sql);
  
        // Create city data        
        $city = stripslashes($lm_city->city);
        $data_arr = array(
            'lm_city_id' => $lm_city->id,
            'lm_parent_id' => $lm_city->parent_id,
            'lm_city' => $city,
            'lm_state' => $lm_city->state,
            'lm_slug' => $lm_city->slug,
            'lm_url_slug' => $lm_city->url_slug,
            'lm_google_map_id' => $lm_city->google_map_id,
            'lm_form_id' => $lm_city->form_id,
            'lm_city_keyword_separator' => $lm_city->city_keyword_separator,
            'lm_location' => $lm_city->location,
            'lm_city_phone' => $lm_city->city_phone,
            'lm_city_mayor' => $lm_city->city_mayor,
            'lm_population' => $lm_city->population,
            'lm_avg_home_value' => $lm_city->avg_home_value,
            'lm_avg_household_income' => $lm_city->avg_household_income,
            'lm_avg_temperature' => $lm_city->avg_temperature,
            'lm_price_range' => $lm_city->price_range,
            'lm_image' => $lm_city->image,
            'lm_description' => $lm_city->description,
            'lm_telephone' => $lm_city->telephone,
            'lm_educational_institution' => $lm_city->educational_institution,
            'lm_state_park' => $lm_city->state_park,
            'lm_historic_landmark' => $lm_city->historic_landmark,
            'lm_assisted_living' => $lm_city->assisted_living,
            'lm_restaurant' => $lm_city->restaurant
        );
        $mrylm_table_name = $wpdb->prefix . "mrylm_cities";
        $wpdb->insert($mrylm_table_name, $data_arr);
        
        // get city        
        $obj = array();
        $obj['lm_parent_id'] = $lm_city->parent_id;
        $obj['lm_url_slug'] = $lm_city->url_slug;
        $obj['lm_city'] = $lm_city->city;
        $lm_obj_city = (object) $obj; 
        
        // now process multi page         
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){

            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){

                $lm_page_url = $lm_setting->lm_org_url.$key.'/';
                create_lm_page($lm_setting, $lm_obj_city, $lm_page_url);    
            }

        }else{
            create_lm_page($lm_setting, $lm_obj_city, $lm_setting->lm_page_url);
        } 
    }
}
/* Add City data End*/

function create_lm_page($lm_setting, $lm_city, $lm_page_url){
    
     global $wpdb;
     
    // now we will create page for each city 
    $lm_parent_post_id = url_to_postid($lm_page_url);                
    $lm_post_id  = url_to_postid($lm_page_url . $lm_city->lm_url_slug);

    if ($lm_city->lm_parent_id > 0) {
        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities  WHERE lm_city_id = '$lm_city->lm_parent_id'";
        $lm_city_2 = $wpdb->get_row($lm_sql);
        $lm_parent_post_id = url_to_postid($lm_page_url. $lm_city_2->lm_url_slug);
    }

    // If there are no sub page then we hv to parent post id 0
    if ($lm_setting->lm_page_type == 'base_page') {
        $lm_parent_post_id = 0;
    }

    $mrylm_post = array(
        'ID' => $lm_post_id,
        'post_title' => $lm_city->lm_city,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_parent' => $lm_parent_post_id,
        'post_category' => array()
    );

    //Insert the post into the database            
    $lm_insert_id = wp_insert_post($mrylm_post);

    // to set template for the page 
    if (!add_post_meta($lm_insert_id, '_wp_page_template', 'local-magic.php', true)) {
        update_post_meta($lm_insert_id, '_wp_page_template', 'local-magic.php');
    }
}

/* Update City data Start*/
add_action('wp_ajax_nopriv_mrylm-update-city', 'mrylm_update_city');
function mrylm_update_city() {

    $lm_city = $_REQUEST['city'];
    mrylm_upgrade_function(array(), array());
    
    if (isset($lm_city)) {
        
        $lm_city = stripcslashes(str_replace('\"', '"', $lm_city));
        $lm_city = json_decode($lm_city);

        global $wpdb;

        $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($mrylm_setting_sql);
        
        // Old city 
        $lm_sql_old  = "SELECT * FROM {$wpdb->prefix}mrylm_cities  WHERE lm_city_id = '$lm_city->id'";
        $lm_city_old = $wpdb->get_row($lm_sql_old);
        
        // Update city data        
        $city = stripslashes($lm_city->city);        
        $data_arr = "UPDATE {$wpdb->prefix}mrylm_cities
                        SET lm_parent_id = '" . $lm_city->parent_id . "', 
                        lm_city = '" . $city . "', 
                        lm_state = '" . $lm_city->state . "',
                        lm_slug = '" . $lm_city->slug . "',
                        lm_url_slug = '" . $lm_city->url_slug . "',
                        lm_google_map_id = '" . $lm_city->google_map_id . "',
                        lm_form_id = '" . $lm_city->form_id . "',
                        lm_city_keyword_separator = '" . $lm_city->city_keyword_separator . "',
                        lm_location = '" . $lm_city->location . "',
                        lm_city_phone = '" . $lm_city->city_phone . "',    
                        lm_city_mayor = '" . $lm_city->city_mayor . "',    
                        lm_population = '" . $lm_city->population . "',    
                        lm_avg_home_value = '" . $lm_city->avg_home_value . "',    
                        lm_avg_household_income = '" . $lm_city->avg_household_income . "',    
                        lm_avg_temperature = '" . $lm_city->avg_temperature . "',    
                        lm_price_range = '" . $lm_city->price_range . "',    
                        lm_image = '" . $lm_city->image . "',    
                        lm_description = '" . $lm_city->description . "',    
                        lm_telephone = '" . $lm_city->telephone . "',    
                        lm_educational_institution = '" . $lm_city->educational_institution . "',    
                        lm_state_park = '" . $lm_city->state_park . "',    
                        lm_historic_landmark = '" . $lm_city->historic_landmark . "',   
                        lm_assisted_living = '" . $lm_city->assisted_living . "' ,  
                        lm_restaurant = '" . $lm_city->restaurant . "'   
                    WHERE lm_city_id = " . $lm_city->id . "";

        $wpdb->query($data_arr);
         
        $obj = array();
        $obj['lm_parent_id'] = $lm_city->parent_id;
        $obj['lm_url_slug'] = $lm_city->url_slug;
        $obj['lm_city'] = $lm_city->city;
        $lm_obj_city = (object) $obj; 
        
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
                    
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                
                $lm_page_url = $lm_setting->lm_org_url.$key.'/';             
                $lm_post_id = url_to_postid($lm_page_url . $lm_city_old->lm_url_slug);                
                update_lm_page($lm_setting, $lm_obj_city, $lm_page_url, $lm_post_id);    
            }

        }else{            
            
            $lm_post_id = url_to_postid($lm_setting->lm_page_url . $lm_city_old->lm_url_slug);            
            update_lm_page($lm_setting, $lm_obj_city, $lm_setting->lm_page_url, $lm_post_id);
        }
                
        // now we will create page for city 
        
    }
}
/* Update City data End */

function update_lm_page($lm_setting, $lm_city, $lm_page_url, $lm_post_id){
    
    global $wpdb;    
    
    $lm_parent_post_id = url_to_postid($lm_page_url);  
       
    if($lm_city->lm_parent_id > 0){                
        $lm_sql_2 = "SELECT * FROM {$wpdb->prefix}mrylm_cities  WHERE lm_city_id = '$lm_city->lm_parent_id'";
        $lm_city_2 = $wpdb->get_row($lm_sql_2);
        $lm_parent_post_id = url_to_postid($lm_page_url.$lm_city_2->lm_url_slug); 
    }  

    // If there are no sub page then we hv to parent post id 0
    if ($lm_setting->lm_page_type == 'base_page') {
        $lm_parent_post_id = 0;
    }

    $mrylm_post = array(
        'ID' => $lm_post_id,
        'post_title' => $lm_city->lm_city,
        'post_name' => $lm_city->lm_url_slug,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_parent' => $lm_parent_post_id,
        'post_category' => array()
    );
    
    //Insert the post into the database            
    $lm_insert_id = wp_update_post($mrylm_post);

    // to set template for the page 
    if (!add_post_meta($lm_insert_id, '_wp_page_template', 'local-magic.php', true)) {
        update_post_meta($lm_insert_id, '_wp_page_template', 'local-magic.php');
    }        
}


/* Manage News data Start */
add_action('wp_ajax_nopriv_mrylm-manage-news', 'mrylm_manage_news');
function mrylm_manage_news() {

    $lm_news = $_REQUEST['news'];
    $lm_slug = trim($_REQUEST['slug']);
    $lm_city_id = trim($_REQUEST['city_id']);

    if (isset($lm_news)) {

        global $wpdb;
        $lm_news = stripcslashes(str_replace('\"', '"', $lm_news));
        $lm_news = json_decode($lm_news);

        $delete_sql = "DELETE FROM {$wpdb->prefix}mrylm_news WHERE lm_city_id = '$lm_city_id'";
        $wpdb->query($delete_sql);

        foreach ($lm_news as $obj) {

            // News
            $news = str_replace('<br>', '', $obj->news);
            $news = preg_replace("/\r|\n/", "", $news);
            $news = str_replace("\'", "'", $news);
            $news = str_replace('\n', '', $news);
            $news = stripslashes($news);

            // City
            $city = stripslashes($obj->city);

            // Title
            $title = stripslashes($obj->title);

            $data_arr = array(
                'lm_city_id' => $obj->city_id,
                'lm_slug' => $lm_slug,
                'lm_city' => $city,
                'lm_title' => $title,
                'lm_news' => $news,
                'lm_detail_url' => $obj->detail_url,
                'lm_author' => $obj->author,
                'lm_source' => $obj->source,
                'lm_created_at' => $obj->created_at,
                'lm_updated_at' => $obj->updated_at
            );

            $mrylm_table_name = $wpdb->prefix . "mrylm_news";
            $wpdb->insert($mrylm_table_name, $data_arr);
        }
    }

    echo TRUE;
    
}
/* Manage News data End */


/* Delete a City Start */
add_action('wp_ajax_nopriv_mrylm-delete-city', 'mrylm_delete_city');
function mrylm_delete_city() {

    global $wpdb;
    $lm_city_id = $_REQUEST['city_id'];

    $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting = $wpdb->get_row($mrylm_setting_sql);

    $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities  WHERE lm_city_id = '$lm_city_id'";
    $lm_city = $wpdb->get_row($lm_sql);

    $lm_del_sql = "DELETE FROM {$wpdb->prefix}mrylm_cities WHERE lm_city_id = '$lm_city_id'";
    $wpdb->query($lm_del_sql);

    $lm_del_news_sql = "DELETE FROM {$wpdb->prefix}mrylm_news WHERE lm_city_id = '$lm_city_id'";
    $wpdb->query($lm_del_news_sql);
    
    if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
                
        $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
        foreach($lm_multi_titles AS $key=>$lm_title){
           $postid = url_to_postid($lm_setting->lm_org_url. $key.'/'. $lm_city->lm_url_slug);
            if ($postid) {
                wp_delete_post($postid, true);                
            }
        }
        
    }else{
        
        $postid = url_to_postid($lm_setting->lm_page_url . $lm_city->lm_url_slug);
        if ($postid) {
            wp_delete_post($postid, true);           
        }        
    }
}
/* Delete City End */


/* Jobs Start API*/
add_action('wp_ajax_nopriv_api-post-job', 'mrylm_post_job');
function mrylm_post_job(){    
    
    $jobs = $_REQUEST['jobs'];  
   	
    if(isset($jobs)){
        
        require_once(MRYLM_PLUGIN_DIR . 'activate.php' );
        mrylm_update_tables();
        
        global $wpdb;
       	$mrylm_jobs = stripcslashes(str_replace('\"', '"', $jobs));  
        $mrylm_jobs = json_decode($mrylm_jobs, true);	        

        $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mrylm_jobs"; 
        $wpdb->query($delete_sql); 
        
        foreach($mrylm_jobs as $key=>$obj){                      
            
            $data_arr = array(                              
                'title' => stripcslashes(esc_sql($obj['title'])), 
                'description' => stripcslashes(esc_sql($obj['description'])), 
                'post_date' => esc_sql($obj['post_date']), 
                'industry' => stripcslashes(esc_sql($obj['industry'])),                                                    
                'category' => stripcslashes(esc_sql($obj['category'])),                                                    
                'employment_type' => stripcslashes(esc_sql($obj['employment_type'])),                                                    
                'work_hour' => stripcslashes(esc_sql($obj['work_hour'])),                                                    
                'hour_value' => stripcslashes(esc_sql($obj['hour_value'])),                                                    
                'responsibility' => stripcslashes(esc_sql($obj['responsibility'])),                                                    
                'edu_requirement' => stripcslashes(esc_sql($obj['edu_requirement'])),                                                    
                'work_experience' => stripcslashes(esc_sql($obj['work_experience'])),                                                    
                'skill' => stripcslashes(esc_sql($obj['skill'])),                                                    
                'benefit' => stripcslashes(esc_sql($obj['benefit'])),                                                    
                'incentive' => stripcslashes(esc_sql($obj['incentive'])),                                                    
                'job_url' => stripcslashes(esc_sql($obj['job_url'])),                                                    
                'is_publish' => $obj['is_publish'],                                                    
                'created_at' => $obj['created_at'],                                         
                'updated_at' => $obj['updated_at'] 
            );

            $mrylm_table_name = $wpdb->prefix . "mrylm_jobs";		
            $wpdb->insert($mrylm_table_name, $data_arr); 
        }
    }
    
    echo TRUE;
    // do whatever you want to do
}

/* Jobs End */


/* RSS FEED START */

function mrylm_get_rss(){
   
    require_once(MRYLM_PLUGIN_DIR . 'RSS2.php' );
    $feed = new RSS2();
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
        
        $lm_city = mrylm_get_city();

        $lm_sql_setting = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql_setting);  


        $full_url_link =  get_permalink();
        $url_array = explode('/', $full_url_link);
        
        // Process Multi Type LM 
        $lm_title_tag = '';
        $lm_description = '';
        
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
            
            // set title                
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                if (in_array($key, $url_array)){
                    $lm_title_tag = $lm_title;
                    break;
                }
            }            
            
            // set description
            if($lm_setting->lm_multi_meta_desc != ''){
                
                $lm_multi_metas = get_multi_type_data($lm_setting->lm_multi_meta_desc);
                foreach($lm_multi_metas AS $key=>$lm_meta){
                    if (in_array($key, $url_array)){
                        $lm_description = $lm_meta;
                        break;
                    }
                }
            }            
            
        }else{
            
            $lm_title_tag   = $lm_setting->lm_title_tag;
            $lm_description = $lm_setting->lm_meta_description;
        }
        
        
        $lm_title_tag = str_replace('CITY_NAME', $lm_city->lm_city, $lm_title_tag);
        $lm_title_tag = str_replace('STATE_NAME', $lm_city->lm_state, $lm_title_tag);
        
        $lm_description = str_replace('CITY_NAME', $lm_city->lm_city, $lm_description);
        $lm_description = str_replace('STATE_NAME', $lm_city->lm_state, $lm_description); 

        $feed->setTitle($lm_title_tag.' | '.$lm_setting->lm_site_title);
        $feed->setLink($lm_setting->lm_org_url);
        $feed->setDescription($lm_description);
        $feed->setChannelElement('language', 'en-US');
        $feed->setDate(time());
        $feed->setChannelElement('pubDate', date(\DATE_RSS, strtotime("yesterday")));
        $feed->setAtomLink($full_url_link.'rss-feed/', 'self', 'application/rss+xml');
        $feed->setImage($lm_setting->lm_org_logo_url, $lm_title_tag.' | '.$lm_setting->lm_site_title, $lm_setting->lm_org_url);

        // $feed->addGenerator();    

        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_news WHERE lm_city_id = '$lm_city->lm_city_id'";
        $lm_news = $wpdb->get_results($lm_sql);

        if (!empty($lm_news)) {
            $counter = 1;
            foreach ($lm_news as $obj) {

                $item = $feed->createNewItem();
                $desc = strip_tags(substr($obj->lm_news, 0, 350)); 
                $item->addElementArray(array('title' => $obj->lm_title,  'description' => $desc.'...', 'link' => $obj->lm_detail_url, 'guid' =>$full_url_link.'?'.$counter++, 'pubDate' => date(\DATE_RSS, strtotime($obj->lm_created_at))));
                $feed->addItem($item);
            }
        }

        // OK. Everything is done. Now generate the feed.
        // Then do anything (e,g cache, save, attach, print) you want with the feed in $output.
        $output = $feed->generateFeed();

        // If you want to send the feed directly to the browser, use the printFeed() method.
        $feed->printFeed();

    }        
}

add_filter( 'query_vars', 'add_query_vars');
function add_query_vars($vars){
    $vars[] = "rss-feed";
    return $vars;
}

add_action('init', 'mrylm_add_endpoints');
function mrylm_add_endpoints()
{
    add_rewrite_endpoint('rss-feed', EP_PAGES);
}

/* RSS FED END */


// This one is Alternative title tag
function mrylm_set_title_tag() {
            
    global $wpdb;
    $mrylm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting = $wpdb->get_row($mrylm_setting_sql);
    if($lm_setting->lm_is_pause){
        header('Location: '.$lm_setting->lm_org_url.'404');
        die();
    }
    
    add_filter('pre_get_document_title', 'mrylm_title_tag_alternative', 9999999);
    add_filter('document_title_parts', 'mrylm_title_tag', 10, 1);
    add_filter('document_title_separator', 'mrylm_title_seperator', 10, 1);
    
    // Rss feed function
    if($lm_setting->lm_is_rss_feed){
        $full_url_link = $_SERVER['REQUEST_URI'];
        $url_array = explode('/', $full_url_link);

        if (in_array('rss-feed', $url_array)) {   
            mrylm_get_rss();
            die();
        }
    }
}

add_action('wp_head', 'mrylm_meta_description');
add_action('wp_footer', 'mrylm_script');