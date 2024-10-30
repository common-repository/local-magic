<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(MRYLM_PLUGIN_DIR . 'activate.php' );
mrylm_update_tables();
global $wpdb;
$mrylm_error_msg = "";
$mrylm_msg = "";

function mrylm_check_key_format($mrylm_key){
    
    if(!strpos($mrylm_key, '-')){
        return FALSE;
    }
    
    $arr = explode('-', $mrylm_key);
    if(strlen($arr[1]) != 10){
        return FALSE;
    }    
    return TRUE;
}

function get_multi_type_formatted_data($multi_type_data = null){
    
    $array_data = array();
    $text_data = json_decode($multi_type_data, true);
    $array = explode('|', $text_data);
    foreach($array as $value){

        $arr = explode('=>', $value);
        $array_data[$arr[0]] = $arr[1];
    }
    
    return $array_data;
}

function create_lm_pages($lm_setting, $lm_city, $lm_page_url){
    
    global $wpdb;
    
       // now we will create page for each city 
        $lm_parent_post_id = url_to_postid($lm_page_url);  
        $lm_post_id = url_to_postid($lm_page_url.$lm_city->lm_url_slug); 

        if($lm_city->lm_parent_id > 0){                
            $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities  WHERE lm_city_id = '$lm_city->lm_parent_id'";
            $lm_city_2 = $wpdb->get_row($lm_sql);
            $lm_parent_post_id = url_to_postid($lm_page_url.$lm_city_2->lm_url_slug); 
        }    

        // If there are no sub page then we hv to parent post id 0
        if ($lm_setting->lm_page_type == 'base_page') {
            $lm_parent_post_id = 0;
        }

        $mrylm_post = array(
          'ID'=>  $lm_post_id,
          'post_title'    => $lm_city->lm_city,
          'post_content'  => '',
          'post_status'   => 'publish',
          'post_type'   => 'page',
          'post_parent'   => $lm_parent_post_id,
          'post_category' => array()
        );

        //Insert the post into the database            
        $lm_insert_id = wp_insert_post( $mrylm_post );

        // to set template for the page 
        if ( ! add_post_meta( $lm_insert_id, '_wp_page_template', 'local-magic.php', true )) { 
            update_post_meta( $lm_insert_id, '_wp_page_template', 'local-magic.php' );
        } 
}


function mrylm_process_pages(){
    
    global $wpdb;
    
    $lm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting     = $wpdb->get_row($lm_setting_sql);

    if($lm_setting->lm_url_type == 'wp_remote_get'){        
        $mrylm_response   = wp_remote_get('https://localmagic.reviewmanager.app/plugin/cities/' . $lm_setting->lm_unique_id);
        $mrylm_cities  = json_decode($mrylm_response['body']);
        
    }else{                 
        $mrylm_response = file_get_contents('https://localmagic.reviewmanager.app/plugin/cities/' . $lm_setting->lm_unique_id);
        $mrylm_cities  = json_decode($mrylm_response);                   
    }
     
       if(!empty($mrylm_cities)){           
         
         // Trancating existing Cities table
        $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mrylm_cities"; 
        $wpdb->query($delete_sql);
         
        // Creating LM Pages as per city name
        foreach($mrylm_cities AS $obj){    
            
            $city = stripslashes_deep($obj->city);           
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
            
            // need to process multi type
             if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){

                $lm_multi_titles = get_multi_type_formatted_data($lm_setting->lm_multi_title);
                foreach($lm_multi_titles AS $key=>$lm_title){

                    $lm_page_url = $lm_setting->lm_org_url.$key.'/';
                    create_lm_pages($lm_setting, $lm_obj_city, $lm_page_url);    
                }

            }else{
                create_lm_pages($lm_setting, $lm_obj_city, $lm_setting->lm_page_url);
            }            
        }
        
        //for service area page page
        if($lm_setting->lm_is_service_area_page){
            create_service_area_page($lm_setting);
        }        
    }   
}


if (isset($_REQUEST['submit']) && is_user_logged_in()) {
        
    $mrylm_nonce = $_REQUEST['_wpnonce'];

    if (wp_verify_nonce($mrylm_nonce, 'submit_mrylm')) {

        $lm_unique_id = sanitize_text_field(sanitize_key($_REQUEST['lm_unique_id']));
        $lm_url_type = sanitize_option('lm_url_type', $_REQUEST['lm_url_type']);
        

        if (!$lm_unique_id) {

            $mrylm_error_msg = "Local Magic key should not be empty.";
        } else if (!preg_match("/^([0-9-])+$/i", $lm_unique_id)) {

            $mrylm_error_msg = "Local Magic key should be valid key.";
        } else if (!mrylm_check_key_format($lm_unique_id)) {

            $mrylm_error_msg = "Local Magic key should be valid format.";
        } else {
            
            // check Local Magic Key remote validity
            if($lm_url_type == 'wp_remote_get'){
                
                $auth = wp_remote_get('https://localmagic.reviewmanager.app/plugin/auth/' . $lm_unique_id); 
                $lm_setting = json_decode($auth['body']);
            }else{
                
                $auth = file_get_contents('https://localmagic.reviewmanager.app/plugin/auth/' . $lm_unique_id);
                $lm_setting  = json_decode($auth);                   
            }            
              
            if (!empty($lm_setting)) {             
                
                $delete_sql = "TRUNCATE TABLE {$wpdb->prefix}mrylm_setting"; 
                $wpdb->query($delete_sql); 
                  
                $data_array = array(
                    'lm_url_type' => $lm_url_type,
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
                
                // Now we need to process Page Create/Update
                mrylm_process_pages();
                
                if ($_REQUEST['lm_setting_id'] != '') {
                    $mrylm_msg = "Local Magic key updated successfully.";
                }else{
                    $mrylm_msg = "Local Magic key saved successfully.";
                }
                
            } else {

                $mrylm_error_msg = '<p>You have provide incorrect Local Magic Key. Please <a href="https://www.mrmarketingres.com/local-magic/" target="_blank">Contact</a> for correct Local Magic Key</p>';
            }
        }
    }    
}

$lm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
$lm_setting     = $wpdb->get_row($lm_setting_sql);

if(empty($lm_setting)){
   
     $data_array = array(
        'lm_url_type' => 'wp_remote_get'
    );
         
    $mrylm_table_name = $wpdb->prefix . "mrylm_setting";
    $wpdb->insert($mrylm_table_name, $data_array);
}

?>

<style type="text/css">
    .alternate, .striped>tbody>:nth-child(odd), ul.striped>:nth-child(odd){background: none;width: 80%; }
    .large-text{border:1px solid #e0e0e0 !important; padding: 3px !important; margin: 25px 0px 10px 0px;width:90%;}
    .title-icon{width:20px;padding-right: 4px;}
</style>
<div class="wrap">   
    <?php if($mrylm_msg){ ?>
        <h4 style="color: green;"><?php  echo $mrylm_msg; ?></h4>
    <?php } ?>
    <?php if($mrylm_error_msg){ ?>
        <h4 style="color: red;"><?php  echo $mrylm_error_msg; ?></h4>
    <?php } ?>
    
    <h2><img class="title-icon" src="<?php echo plugins_url('local-magic/assets/images/icon-lm-dark.png'); ?>" alt="" /> <?php echo __('Local Magic Setting', 'mrylm'); ?></h2>
    <div><hr/></div>

<form name="post" action="" method="post" id="post">
    <table class="alternate">
        <tbody>
            <?php if (isset($lm_setting) && !empty($lm_setting->lm_unique_id)) { ?>
                <tr class="alternate">
                    <td class="import-system row-title"><a>[mrylm_job_posting]</a></td>          
                    <td class="desc"><?php echo __('This shortcode contains Job posting data. Just copy this shortcode and paste into your expected page/ post where you want to see your job posting data', 'mrylm'); ?></td>
                </tr>   
            <?php } ?> 
                
            <tr class="alternate">
                <td width="35%" class="import-system row-title"><?php _e('Please Choose URL Type for Data Collection', 'mrylm') ?></td>          
                <td class="desc">
                    <select name="lm_url_type" id="lm_url_type" class="large-text">                       
                        <option value="file_get_content" <?php if(isset($lm_setting) &&  $lm_setting->lm_url_type == 'file_get_content') { echo 'selected="selected"'; } ?>>File Get Content</option>
                        <option value="wp_remote_get" <?php if(isset($lm_setting) &&  $lm_setting->lm_url_type == 'wp_remote_get') { echo 'selected="selected"'; } ?>>WP Remote Get</option>
                    </select>
                </td>
            </tr>                  
            <tr class="alternate">
                <td width="35%" class="import-system row-title"><?php _e('Please enter your Local Magic Key', 'mrylm') ?></td>          
                <td class="desc">
                    <input  type="text" class="large-text" name="lm_unique_id" id="lm_unique_id" value="<?php echo isset($lm_setting) ? esc_html($lm_setting->lm_unique_id) : ''; ?>" autocomplete="off"/>
                </td>
            </tr>                  
            <tr class="alternate">
                <td class="import-system row-title"></td>          
                <td class="desc">
                    For Local Magic Key please <a href="https://www.mrmarketingres.com/local-magic/" target="_blank">Contact</a>
                </td>
            </tr>  
            <tr class="alternate">
                <td colspan="2" class=""></td> 
            </tr> 
            <tr class="alternate">
                <td class="import-system row-title"></td>          
                <td class="desc">
                    <input type="hidden" name="lm_setting_id" id="lm_setting_id" value="<?php echo isset($lm_setting) ? esc_html($lm_setting->id) : ''; ?>" />
                    <input type="submit" name="submit" value="<?php _e('Save', 'mrylm') ?>"  class="button button-primary" />
                    <?php wp_nonce_field( 'submit_mrylm' ); ?>
                </td>
            </tr>        
        </tbody>
    </table>
</form>  
</div>