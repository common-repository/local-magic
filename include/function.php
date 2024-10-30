<?php

function mrylm_get_url_slug(){
    
    $url =  get_permalink();
    $arr = explode('/', trim($url, '/'));
    $slug = end($arr);
    return $slug;
}

function get_multi_type_data($multi_type_data = null){
    
    $array_data = array();
    $text_data = json_decode($multi_type_data, true);
    $array = explode('|', $text_data);
    foreach($array as $value){

        $arr = explode('=>', $value);
        $array_data[$arr[0]] = $arr[1];
    }
    
    return $array_data;
}

function mrylm_get_city(){
    
    global $wpdb;
    
    $url  = get_permalink();
    $arr  = explode('/', trim($url, '/'));
    $slug = end($arr);   
    $prev = prev($arr); 
    
    $full_slug = $prev.'/'.$slug;
    
    $lm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting     = $wpdb->get_row($lm_setting_sql);
    if ($lm_setting->lm_org_url == $url) {
        $full_slug = $lm_setting->lm_default_city;
    }    
    
    $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities WHERE lm_url_slug = '$full_slug'";
    $lm_city = $wpdb->get_row($lm_sql);
    
    if(empty($lm_city)){
        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities WHERE lm_url_slug = '$slug'";
        $lm_city = $wpdb->get_row($lm_sql);
    } 
    
    return $lm_city;
}

function mrylm_title_tag() {
       
   global $wpdb; 
   
   $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
        
        $lm_sql_setting = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql_setting);  

        $lm_city = mrylm_get_city();
   
        if(!empty($lm_city)){
            
            if(get_page_template_slug() == 'local-magic.php'){
            
                $full_url_link = $_SERVER['REQUEST_URI'];
                $url_array = explode('/', $full_url_link);

                // Process Multi Type LM 
                $lm_title_tag = '';

                if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){

                    // set title  
                    $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
                    foreach($lm_multi_titles AS $key=>$lm_title){
                        if (in_array($key, $url_array)){
                            $lm_title_tag = $lm_title;
                            break;
                        }
                    }               

                }else{

                    $lm_title_tag = $lm_setting->lm_title_tag;
                }


                $lm_title_tag = str_replace('CITY_NAME', $lm_city->lm_city, $lm_title_tag);
                $lm_title_tag = str_replace('STATE_NAME', $lm_city->lm_state, $lm_title_tag);
                $keyword_separator = $lm_city->lm_city_keyword_separator ? $lm_city->lm_city_keyword_separator : 'in';
                $lm_title_tag = str_replace('KEYWORD_SEPARATOR', $keyword_separator, $lm_title_tag);

                $content = array(
                  'title' => $lm_title_tag,    
                  'site'  => $lm_setting->lm_site_title
                 );

                return $content;            
            }
        }   
    }   
}

function mrylm_title_tag_alternative() {
    
   global $wpdb; 
   
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
        
        $lm_city = mrylm_get_city();

        $lm_sql_setting = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql_setting);  

        if(!empty($lm_city)){ 
            
            if(get_page_template_slug() == 'local-magic.php'){
                        
                $full_url_link = $_SERVER['REQUEST_URI'];
                $url_array = explode('/', $full_url_link);

                // Process Multi Type LM 
                $lm_title_tag = '';

                if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){

                    // set title   
                    $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
                    foreach($lm_multi_titles AS $key=>$lm_title){
                        if (in_array($key, $url_array)){
                            $lm_title_tag = $lm_title;
                            break;
                        }
                    }                

                }else{

                    $lm_title_tag = $lm_setting->lm_title_tag;
                }

                $lm_title_tag = str_replace('CITY_NAME', $lm_city->lm_city, $lm_title_tag);
                $lm_title_tag = str_replace('STATE_NAME', $lm_city->lm_state, $lm_title_tag);
                $keyword_separator = $lm_city->lm_city_keyword_separator ? $lm_city->lm_city_keyword_separator : 'in';
                $lm_title_tag = str_replace('KEYWORD_SEPARATOR', $keyword_separator, $lm_title_tag);

                return sprintf("%s | %s", $lm_title_tag, $lm_setting->lm_site_title);

            }
        }
    }
}

function mrylm_title_seperator() {
   return '|';
}

function mrylm_meta_description() {
    
    global $wpdb; 
   
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
        
        $lm_sql_setting = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql_setting);  

        $lm_city = mrylm_get_city();

        if(!empty($lm_city)){
            
            
            if(get_page_template_slug() == 'local-magic.php'){
            
                $full_url_link = $_SERVER['REQUEST_URI'];
                $url_array = explode('/', $full_url_link);

                $lm_description = '';

                if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_meta_desc != ''){

                    // set description
                    $lm_multi_metas = get_multi_type_data($lm_setting->lm_multi_meta_desc);
                    foreach($lm_multi_metas AS $key=>$lm_desc){
                        if (in_array($key, $url_array)){
                            $lm_description = $lm_desc;
                            break;
                        }
                    } 
                }else{
                    $lm_description = $lm_setting->lm_meta_description;
                }        

                $lm_description = str_replace('CITY_NAME', $lm_city->lm_city, $lm_description);
                $lm_description = str_replace('STATE_NAME', $lm_city->lm_state, $lm_description);    
                echo '<meta name="description" content="'.$lm_description.'" />';

                $mrylm_rss_url =  get_permalink();
                $mrylm_rss_title = str_replace(' ', '-', $lm_setting->lm_site_title.' '.$lm_city->lm_city.' '.$lm_city->lm_state. ' rss');
                echo '<link href="'.$mrylm_rss_url.'rss-feed" rel="alternate" type="application/rss+xml" title="'.$mrylm_rss_title.'"/>';
            }
        }
        
    }else{        
        echo '';
    }   
}

function mrylm_local_magic_article() {
    
    global $wpdb;
    
    $lm_setting_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting = $wpdb->get_row($lm_setting_sql);
    
    if ($lm_setting->lm_unique_id == '') {
        return '<p>You have missing your Local Magic Key. Please <a href="https://www.mrmarketingres.com/local-magic" target="_blank">Contact</a> for Local Magic Key</p>';
    } 
    
    
    $lm_city = mrylm_get_city();
    
        
    if(empty($lm_city)){ return; }
    
    
    // Process for file get content    
    $content = '';
    $full_url_link = $_SERVER['REQUEST_URI'];
    $url_array = explode('/', $full_url_link);
    
    // single LM type url slug
    $arr = explode('/', rtrim($lm_setting->lm_page_url, '/'));
    $url_slug = end($arr);
    
    if($lm_setting->lm_page_url == $lm_setting->lm_org_url){
        $url_slug = 'page';
    }
    
    if ($lm_setting->lm_template_path == 'template_directory') {   
       
       if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
           
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                if (in_array($key, $url_array)){
                    $url_slug = $key;
                    break;
                }
            }           
          
       }
                 
        $content = file_get_contents(get_template_directory_uri().'/lm-'.$url_slug.'-content.php'); 
       
    }else if($lm_setting->lm_template_path == 'home_path'){
        
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
                
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                if (in_array($key, $url_array)){
                    $url_slug = $key;
                    break;
                }
            }
        }
        
        $content = file_get_contents(get_home_path().'/lm-'.$url_slug.'-content.php');         
       
    }else if($lm_setting->lm_template_path == 'theme_file_path'){
        
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
                            
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                if (in_array($key, $url_array)){
                    $url_slug = $key;
                    break;
                }
            }
        }            
      
        $content = file_get_contents(get_theme_file_path().'/lm-'.$url_slug.'-content.php');        
       
    }else if($lm_setting->lm_template_path == 'other'){
        
        if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
                            
            $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
            foreach($lm_multi_titles AS $key=>$lm_title){
                if (in_array($key, $url_array)){
                    $url_slug = $key;
                    break;
                }
            }
        }
  
        $content = file_get_contents($lm_setting->lm_template_other_path.'/lm-'.$url_slug.'-content.php');
    }   
    
    
       // Process for replacing / Rename images
    if($lm_setting->lm_is_replace_image){
        $content = mrylm_process_image_replacing($content, $lm_city, $lm_setting);
    }

    $content = str_replace('STATE_NAME', $lm_city->lm_state, $content);
    $content = str_replace('CITY_NAME', $lm_city->lm_city, $content);
    
    // process quote form 
    if($lm_city->lm_form_id){
        $content = str_replace('JOT_FORM', $lm_city->lm_form_id, $content);
    }else{
        $content = str_replace('JOT_FORM', $lm_setting->lm_form_id, $content);
    }
    
    // Process phone 
    if($lm_city->lm_city_phone){
        $content = str_replace('PHONE_NUMBER', $lm_city->lm_city_phone, $content);
    }else{
        $content = str_replace('PHONE_NUMBER', $lm_setting->lm_phone, $content);
    }
   
    
    // Process Keyword Separator 
    if($lm_city->lm_city_keyword_separator){
        $content = str_replace('KEYWORD_SEPARATOR', $lm_city->lm_city_keyword_separator, $content);
    }else{
        $content = str_replace('KEYWORD_SEPARATOR', 'in', $content);
    }
    
    // process near me menu listing
    $near_me_menu = mrylm_near_me_menu();
    $content = str_replace('NEAR_ME_MENU', $near_me_menu, $content);
     
    // process menu dropdown
    $dropdown_menu = mrylm_dropdown_menu('sidebar');
    $content = str_replace('DROPDOWN_MENU', $dropdown_menu, $content);
    
    // process google map if map exist
    if($lm_city->lm_google_map_id){        
        $google_map = '<iframe src="'.$lm_city->lm_google_map_id.'" width="100%" height="480"></iframe>';
        $content = str_replace('GOOGLE_MAP', $google_map, $content);
    }else if($lm_setting->lm_embed_code){
        $google_map = '<iframe src="'.$lm_setting->lm_embed_code.'" width="100%" height="480"></iframe>';
        $content = str_replace('GOOGLE_MAP', $google_map, $content);
    }else{
        $content = str_replace('GOOGLE_MAP', '', $content);
    }     
    
    // process news feed    
    $news_feed = mrylm_news();    
    $content = str_replace('NEWS_FEED', $news_feed, $content);
       
    // process review  widget
    $reviews = mrylm_review_testimonial($lm_city); 
    $content = str_replace('REVIEW_FEED', $reviews, $content);
    
    
    // process POI  widget
    if($lm_setting->lm_is_point_of_interest){
        $poi_data = mrylm_poi($type = ''); 
        $content = str_replace('POI_FEED', $poi_data, $content);
    }
    
    return $content;  
    
}

function mrylm_process_image_replacing($content, $lm_city, $lm_setting){
    
    $base_url = '';
    $dom = new DOMDocument();
    $dom->loadHTML($content, LIBXML_NOERROR);
    $images = $dom->getElementsByTagName('img');
    
    $keyword = $lm_setting->lm_keyword;
    $image_keyword = $lm_setting->lm_image_keyword;
    $city = $lm_city->lm_city;
    $city_slug = $lm_city->lm_slug;
    $state = $lm_city->lm_state;
    
    $keyword_slug = str_replace(' ', '-', $keyword);
    $img_keyword_arr = array();
    
    // Process Multi Type LM 
    $full_url_link =  get_permalink();
    $url_array = explode('/', $full_url_link);
    
    if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_keyword != ''){
           
        $lm_multi_keywords = get_multi_type_data($lm_setting->lm_multi_keyword);
        foreach($lm_multi_keywords AS $key=>$lm_keyword){
            if (in_array($key, $url_array)){
                $image_keyword = $lm_keyword;
                $keyword_slug  = $key;
                break;
            }
        }       
    }
        
    if($image_keyword){
        $img_keyword_arr = explode(',', $image_keyword);
    }else{
        $img_keyword_arr[] = $keyword;
    }
    
    $total_keyword = count($img_keyword_arr);
    $counter = 0;
    
    foreach ($images as $key=>$image) {

        $old_src = ltrim($image->getAttribute('src'), '/');
        $itemid = $image->getAttribute('itemid');

        if($itemid){

            // now we need to check expected image exist in server
            $old_base_name = basename($old_src);
            $ext = pathinfo($old_src, PATHINFO_EXTENSION);

            $new_old_src = $base_url.$old_src;

            $new_base_name = strtolower($keyword_slug.'-'.$city_slug.'-'.$state.'-'.$itemid.'.'.$ext);
            $new_src  = str_replace($old_base_name, $new_base_name, $new_old_src);

            if(file_exists($new_src)){
                $new_src = $new_src;
            }else{
               $copy = @copy($new_old_src , $new_src);
               $new_src = $copy ? $new_src : $old_src;                   
            }

            // if not created image then will put default image
            if(!$new_src){
                $new_src = $old_src;
            }

            $new_src = '/'.$new_src;
            $image->setAttribute('src', $new_src);

            // Set img title  and alt attributes
            $title_text = ucwords($img_keyword_arr[$counter].' '.$city.', '.$state);
            $image->setAttribute('alt', $title_text);
            $image->setAttribute('title', $title_text);
            
            // Process keyword count with image
            $counter++;
            if($counter == $total_keyword){
                $counter = 0;
            }
        }    
    }

    $new_content = $dom->saveHTML();
    return $new_content;
    
}

function mrylm_dropdown_menu($type = null) {
    
    global $wpdb;
    $url = rtrim(get_permalink(),'/');  
    $content = '';
    
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {        
   
        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql);  

        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities ORDER BY lm_url_slug ASC";
        $lm_cities = $wpdb->get_results($lm_sql);

        if(!empty($lm_cities)){

            if($type == 'footer'){
                $content .= '<select id="lm-seo-footer" name="lm-seo-menu" class="lm-dropdown-menu lm-seo-footer" onchange="get_local_seo_by_city(this.value)">';
            }else{
                $content .= '<select id="lm-seo-sidebar" name="lm-seo-menu" class="lm-dropdown-menu lm-seo-sidebar" onchange="get_local_seo_by_city(this.value)">';
            }

            $content .='<option value="" selected="">--Select City--</option>';

                $selected = '';
                $lm_link_url = '';
                $full_url_link = $_SERVER['REQUEST_URI'];
                $url_array = explode('/', $full_url_link);
                
                if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_title != ''){
                    $lm_multi_titles = get_multi_type_data($lm_setting->lm_multi_title);
                    foreach($lm_multi_titles AS $key=>$lm_title){
                        if (in_array($key, $url_array)){
                            $lm_link_url = $lm_setting->lm_org_url.$key.'/';
                            break;
                        }
                    }
                }else{
                    $lm_link_url = $lm_setting->lm_page_url;
                }
                
                if($lm_setting->lm_is_multi_type && $type == 'footer'){
                  
                    $page_array = array();

                    if($lm_setting->lm_multi_head != ''){
                        $lm_multi_heads = get_multi_type_data($lm_setting->lm_multi_head);
                        foreach($lm_multi_heads AS $key=>$lm_head){
                            $page_array[$lm_setting->lm_org_url.$key.'/'] = $lm_head;                               
                        }
                    }

                    foreach($page_array AS $key=>$value){

                        $content .= '<optgroup label="'.$value.'">'; 

                        foreach($lm_cities as $obj){

                            $child = '';
                            if($obj->lm_parent_id > 0){
                                $child = '--';
                            }

                            $selected = $key.$obj->lm_url_slug == $url ? 'selected="selected"' : '';
                            $content .= '<option '.$selected.' value="'.$key.$obj->lm_url_slug.'/">'.$child.$obj->lm_city.'</option>';
                        }

                        $content .= '</optgroup>'; 
                    }

                }else{                    
                    
                    foreach($lm_cities as $obj){

                        $child = '';
                        if($obj->lm_parent_id > 0){
                            $child = '--';
                        }
                        $selected = $lm_link_url.$obj->lm_url_slug == $url ? 'selected="selected"' : '';
                        $content .= '<option '.$selected.' value="'.$lm_link_url.$obj->lm_url_slug.'/">'.$child.$obj->lm_city.'</option>';
                    }                    
                }

            $content .= '</select>';
        }
    }
    
    return $content; 
    
}
  
function mrylm_near_me_menu() {
   
    global $wpdb;
    $url = rtrim(get_permalink(),'/');  
    $content = '';
    $css     = '';
    
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
            
        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql);  

        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities ORDER BY lm_url_slug ASC";
        $lm_cities = $wpdb->get_results($lm_sql);


        if(!empty($lm_cities)){

           $content .= '<div class="lm-near-me-menu">';
           $content .= '<ul>';
           $counter = 1;
           $rest_content = '';
           $lm_link_url = '';
           $lm_keyword = '';
            
           // Process Multi Parent Start
            $full_url_link = $_SERVER['REQUEST_URI'];
            $url_array   = explode('/', $full_url_link);       
                     
            if($lm_setting->lm_is_multi_type && $lm_setting->lm_multi_near_me != ''){
                
                $lm_multi_near_me = get_multi_type_data($lm_setting->lm_multi_near_me);
                foreach($lm_multi_near_me AS $key=>$lm_near_me){
                    if (in_array($key, $url_array)){
                        $lm_link_url = $lm_setting->lm_org_url.$key.'/';
                        $lm_keyword = $lm_near_me;
                        break;
                    }
                }
            }else{
                $lm_link_url = $lm_setting->lm_page_url;
                $lm_keyword = $lm_setting->lm_keyword;
            }
           // Process Multi Parent Start

            foreach($lm_cities as $obj){

                $child = '';
                if($obj->lm_parent_id > 0){
                    $child = '--';
                }
             
                $text = $lm_keyword.' '.$lm_setting->lm_keyword_separator.' '. $obj->lm_city.', '. $obj->lm_state;
                
                if($counter <= 5){
                    $content .= '<li><a href="'.$lm_link_url.$obj->lm_url_slug.'/" title="'.$text.'">'.$child.$text.'</a></li>';
                }else{
                    $rest_content .= '<li><a href="'.$lm_link_url.$obj->lm_url_slug.'/" title="'.$text.'">'.$child.$text.'</a></li>';
                }

                $counter++;
            }
            
            $content .= '<div class="fn_lm_more_link_block" style="display:none;">'.$rest_content.'</div>'; 
            

            $content .= '</ul>'; 
            $content .= '</div>'; 
            
            if($counter > 6){
                $content .= '<div class="fn_lm_more_link">Read More</div>'; 
            }
            
            $css .= "<style type='text/css'>
                    .lm-near-me-menu {margin-top: 15px;}
                    .lm-near-me-menu ul li{padding-bottom: 8px;font-size: 14px;list-style: none;margin: 0;}
                    .lm-near-me-menu ul li a {text-decoration: none;text-transform: initial;padding-bottom: 8px;color: #000;}
                    .lm-near-me-menu ul li a:hover{color: #8e81fa;margin-left:0px !important;}
                    .fn_lm_more_link{font-size: 14px;background: #48529b;width: max-content;padding: 5px 10px;border-radius: 5px;color: #fff;cursor: pointer;margin-top: 10px;text-transform: capitalize;}
                </style>";
        }    
    }
    
    return $content.$css;
    
}
 
// Local Magic News
function mrylm_news(){
        
    // get schema setting  data
    global $wpdb;

    $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting = $wpdb->get_row($lm_sql);

    $lm_city = mrylm_get_city();

    $data = ''; 
    $style = '';
    $js = '';

    if(!empty($lm_setting)){        

        // get news data as per keyword   
        $lm_news = mrylm_get_news_list($lm_setting, $lm_city);

        if($lm_setting->lm_display_type == 'list'){
            $data .= mrylm_get_list_data($lm_setting, $lm_news, $lm_city);
        }else if($lm_setting->lm_display_type == 'accordion'){
            $data .= mrylm_get_accordion_data($lm_setting, $lm_news, $lm_city);
        }

        $style =  '<style>';
        $style .= '.lm-widget-title{font-weight:700;font-size:60px;font-family:"Saira Condensed",sans-serif;margin-bottom:30px}.lm-accordion-container{margin:0px;}.lm-accordion-inner{margin:0px;box-shadow:0 0 5px 0 rgba(0,0,0,.1);border-radius:3px;-webkit-animation:lm-slide .5s forwards}.lm-accordion-title{background:0 0;font-family:"Saira Condensed",sans-serif;color:#222;cursor:pointer;padding:18px;width:100%;border:0;text-align:left;outline:0;font-size:32px;text-decoration:underline;transition:.4s;margin:0;line-height: 50px;padding-bottom: 0;}.lm-accordion-title:hover,.lm-active{color:#e5262c !important;}.lm-accordion-content{padding:0 18px;display:none;background-color:#fff;overflow:hidden;animation:fadeIn 1s linear 1}h2.lm-accordion-title.lm-active:before{content:"\2212";background:#e5262c}h2.lm-accordion-title:before{content:"\002B";color:#fff;font-weight:700;float:left;margin-right:8px;width:30px;height:30px;line-height:26px;text-align:center;background:#000;margin-top:10px}.lm-read-more{display:none;animation:fadeIn 1s linear 1}.lm-read-more h2{font-size:30px;} .lm-slider-container{margin:0px}.lm-slider-inner{position:relative;background:#fff}.lm-slides{display:none;padding:30px 100px;text-align:left;background:#fff;box-shadow:0 0 5px 0 rgba(0,0,0,.1);border-radius:3px;-webkit-animation:lm-slide .5s forwards;-webkit-animation-delay:2s;animation:lm-slide .5s forwards;animation-delay:2s}.lm-slides p{font-size:18px;line-height:1.4;font-weight:400}.lm-slide{position:absolute;left:-2000px;height:370px;background:#00f;-webkit-animation:lm-slide .5s forwards;-webkit-animation-delay:2s;animation:lm-slide .5s forwards;animation-delay:2s}.lm-next,.lm-prev{cursor:pointer;position:absolute;top:50%;width:auto;margin-top:-30px;padding:16px;color:#888;font-weight:700;font-size:20px;border-radius:0 3px 3px 0}.lm-next{position:absolute;right:15px;border-radius:50px;width:15px;height:15px;line-height:0px;transition:all .3s ease;border:1px solid #000}.lm-prev{position:absolute;left:15px;border-radius:50px;width:15px;height:15px;line-height:0px;transition:all .3s ease;border:1px solid #000}.lm-next:hover,.lm-prev:hover{background-color:#e5262c;color:#fff;border-color:#e5262c}.lm-dot-container{text-align:center;padding:20px;background:0 0}.lm-dots{cursor:pointer;display:inline-block;position:relative;width:16px;height:16px;background:0 0;border:2px solid #babcbe;border-radius:50%;transform:scale(.8);transition:all .3s ease}.lm-active,.lm-dots:hover{background:0 0;border-color:#e5262c}.lm-active{transform:scale(1)}.lm-fade-in{animation:fadeIn 1s linear 1}.lm-btn,.lm-btn:focus{padding:6px 10px;border-radius:none;border:0.5px solid #000;margin:5px 0;font-size:16px;background:0 0;cursor:pointer;color:#000;font-weight:400;outline:0}.lm-btn:hover{color:#e5262c;border-color:#e5262c}.lm-headline,.lm-listing-title{color:#222;font-size:32px;margin:0 0 10px;font-style:normal;font-weight:700;font-family:"Saira Condensed",sans-serif;line-height: 34px;letter-spacing:0}.lm-listing-container{margin:0px}.lm-listing-item{padding:20px;box-shadow:0 0 5px 0 rgba(0,0,0,.1);margin-bottom:15px}';
        $style .= '.nice-select{display:none !important;}#lm-seo-sidebar, #lm-seo-footer{display:inline !important; border-radius:6px;width:auto;color: #2c2c2c;padding: 8px 10px;}#lm-seo-sidebar{width:100%;}';
        $style .= '@media only screen and (max-width: 768px){ .lm-widget-title{font-size: 35px;} .lm-accordion-title{ line-height: 24px;padding: 6px;font-size: 17px;} .lm-accordion-content{padding: 0 8px;} .lm-accordion-content p{line-height: 22px;font-size: 15px;} .lm-headline, .lm-listing-title{font-size: 17px;line-height: 24px;} .lm-next{ width: 8px;height: 8px;right: 3px;padding: 12px;} .lm-prev{ width: 8px;height: 8px;left: 3px;padding: 12px;} .lm-slides{padding: 30px 30px;} .lm-slides p{font-size: 15px;} .lm-listing-content p {font-size: 15px; line-height: 22px;} }';
        $style .= '.lm-read-less p, .lm-read-more p{padding-bottom:15px;} .disclaimer-block{padding:30px;background-color: #ffe48c;margin: 40px 0px;border-radius: 10px;float:left;text-align: left;} .disclaimer-block h4{color: #000000;font-size: 25px;font-weight: bolder;} .disclaimer-block div{font-size: 17px; line-height: 24px; color: #000000;} .brand-logo{width: 60px !important;float: right;background: black; border-radius: 6px; padding: 3px;margin-top: 5px;}';
        $style .= $lm_setting->lm_custom_css;
        $style .= '</style>';            

        $data .= '<div class="disclaimer-block">
                        <h4>Disclaimer:</h4>
                        <div class="disclaimer-content">
                            This website publishes news articles that contain copyrighted material
                            whose use has not been specifically authorized by the copyright owner.
                            The non-commercial use of these news articles for the purposes of local news
                            reporting constitutes "Fair Use" of the copyrighted materials 
                            as provided for in Section 107 of the US Copyright Law.
                            <img src="'.plugins_url('local-magic/assets/images/local-magic-logo.png').'" alt="Local Magic SEO - Charleston SC" class="brand-logo" />
                        </div>
                    </div>';          


    }else{       

        $data = '<p>You have provide incorrect Local Magic Key. Please <a href="https://www.mrmarketingres.com/local-magic" target="_blank">Contact</a> for Local Magic Key</p>';
    }       

    $data .= $style.$js;        
    return $data;         
}
   
function mrylm_get_news_list($lm_setting, $lm_city){
        
        global $wpdb;
        $lm_news = array();
        
        if($lm_setting->lm_api_url){            

            $url = $lm_setting->lm_api_url.$lm_city->lm_slug;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            $outputArray = json_decode($response);

            foreach($outputArray->data AS $obj){ 

                $std = new stdClass();
                $std->lm_title = str_replace("â€™", "'", $obj->title);
                $std->lm_news =  str_replace("â€™", "", $obj->news);
                $std->lm_created_at = $obj->created_at;
                $std->lm_updated_at = $obj->updated_at;
                $std->lm_author = $lm_setting->lm_org_name;
                $lm_news[] = $std;
            } 

        }else{

            $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_news WHERE lm_city_id = '$lm_city->lm_city_id'";
            $lm_news = $wpdb->get_results($lm_sql);
        }
        
        return $lm_news;
}    
  

function mrylm_get_list_data($lm_setting, $lm_news, $lm_city){
        
    $separator  = 'in'; 
    $data = '';        

    $data .= '<div class="lm-listing-container" >';        

            if(isset($lm_news) && !empty($lm_news)){

                $data .= '<h2 class="lm-widget-title">'.$lm_setting->lm_head_line.' '.$separator.' '. $lm_city->lm_city .', '. $lm_city->lm_state .'</h2>';
                
                // foreach start
                foreach($lm_news as $obj){

                    $detail  = trim(preg_replace("/<figure[^>]*>(.+?)<\/figure>/", "", $obj->lm_news)); 
                    $detail  = trim(preg_replace("/<iframe[^>]*>(.+?)<\/iframe>/", "", $detail)); 
                    $detail  = trim(preg_replace("/<img[^>]*>(.+?)<\/img>/", "", $detail)); 
                    $detail  = preg_replace('/(<a\b[^><]*)>/i', '$1 rel="nofollow;" target="_blank">',$detail);

                    $author = $obj->lm_author ? $obj->lm_author :  $obj->lm_source;
                    $source = $obj->lm_source ? $obj->lm_source : $lm_setting->lm_org_name;

                    $data .= '<div class="lm-listing-item" itemscope itemtype="http://schema.org/NewsArticle">';
                        $data .= '<meta itemscope itemprop="mainEntityOfPage"  itemType="https://schema.org/WebPage" itemid="'.$obj->lm_detail_url.'"/>';
                        $data .= '<h2 class="lm-listing-title" itemprop="headline">'.$obj->lm_title.'</h2>';
                        $data .= '<h3 style="display:none;" itemprop="author" itemscope itemtype="https://schema.org/Person">';
                            $data .= '<span itemprop="name">'.$author.'</span>';
                            $data .= '<span itemprop="url">'.$obj->lm_detail_url.'</span>';
                        $data .= '</h3>';
                        $data .= '<div class="lm-listing-content">';
                            $data .= '<div class="lm-read-less"><p>'. strip_tags(substr($detail, 0, 550)). '...</p></div>';                               
                            $data .= '<div class="lm-read-more" itemprop="description">'. $detail. '</div>';  
                            $data .= '<button class="lm-btn">Read more</button>';
                        $data .= '</div>';

                        $data .= '<div itemprop="image" itemscope itemtype="https://schema.org/ImageObject">   
                                        <meta itemprop="url" content="'.$lm_setting->lm_org_logo_url.'">   
                                      </div>';
                        $data .= '<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                                    <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">      
                                      <meta itemprop="url" content="'.$lm_setting->lm_org_logo_url.'">      
                                    </div>
                                    <meta itemprop="name" content="'.$source.'">
                                  </div>';
                        $data .= '<meta itemprop="datePublished" content="'.date('Y-m-d H:i:s', strtotime($obj->lm_created_at)).'"/>';
                        $data .= '<meta itemprop="dateModified" content="'.date('Y-m-d H:i:s', strtotime($obj->lm_updated_at)).'"/>';

                    $data .= '</div>';
                } 

            }else{
                $data .= ''; '<div class="news-block">No news data found.</div>';
            }        

    $data .= '</div>';

    return $data;
}
   
function mrylm_get_accordion_data($lm_setting, $lm_news, $lm_city){
        
    $separator  = 'in';         
    $data = ''; 

    $data .= '<div class="lm-accordion-container">';

            if(isset($lm_news) && !empty($lm_news)){

                $data .= '<h2 class="lm-widget-title">'.$lm_setting->lm_head_line.' '.$separator.' '. $lm_city->lm_city .', '. $lm_city->lm_state .'</h2>';
                
                // foreach start
                $data .= '<div class="lm-accordion-inner">';

                foreach($lm_news as $obj){

                    $detail  = trim(preg_replace("/<figure[^>]*>(.+?)<\/figure>/", "", $obj->lm_news)); 
                    $detail  = trim(preg_replace("/<iframe[^>]*>(.+?)<\/iframe>/", "", $detail)); 
                    $detail  = trim(preg_replace("/<img[^>]*>(.+?)<\/img>/", "", $detail)); 
                    $detail  = preg_replace('/(<a\b[^><]*)>/i', '$1 rel="nofollow;" target="_blank">',$detail);

                    $active = 'none;';
                    $active_class = '';

                    if($key == 0){$active_class = 'lm-active';  $active = 'block;'; } 

                   $author = $obj->lm_author ? $obj->lm_author :  $obj->lm_source;
                   $source = $obj->lm_source ? $obj->lm_source : $lm_setting->lm_org_name;

                    $data .= '<div class="lm-accordion-item" itemscope itemtype="http://schema.org/NewsArticle">';
                        $data .= '<meta itemscope itemprop="mainEntityOfPage"  itemType="https://schema.org/WebPage" itemid="'.$obj->lm_detail_url.'"/>';
                        $data .= '<h2 class="lm-accordion-title '.$active_class.'"  itemprop="headline">'.$obj->lm_title.'</h2>';
                        $data .= '<h3 style="display:none;" itemprop="author" itemscope itemtype="https://schema.org/Person">';
                            $data .= '<span itemprop="name">'.$author.'</span>';
                            $data .= '<span itemprop="url">'.$obj->lm_detail_url.'</span>';
                        $data .= '</h3>';
                        $data .= '<div class="lm-accordion-content" style="display:'.$active.'">';
                            $data .= '<div class="lm-read-less"><p>'. strip_tags(substr($detail, 0, 550)). '...</p></div>';                               
                            $data .= '<div class="lm-read-more" itemprop="description">'. $detail. '</div>';  
                            $data .= '<button class="lm-btn">Read more</button>';
                        $data .= '</div>';

                        $data .= '<div itemprop="image" itemscope itemtype="https://schema.org/ImageObject">   
                                        <meta itemprop="url" content="'.$lm_setting->lm_org_logo_url.'">   
                                      </div>';
                        $data .= '<div itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
                                    <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">      
                                      <meta itemprop="url" content="'.$lm_setting->lm_org_logo_url.'">      
                                    </div>
                                    <meta itemprop="name" content="'.$source.'">
                                  </div>';
                        $data .= '<meta itemprop="datePublished" content="'.date('Y-m-d H:i:s', strtotime($obj->lm_created_at)).'"/>';
                        $data .= '<meta itemprop="dateModified" content="'.date('Y-m-d H:i:s', strtotime($obj->lm_updated_at)).'"/>';

                    $data .= '</div>';
                }

                $data .= '</div>';

            }else{
                $data .= ''; '<div class="news-block">No news data found.</div>';
            }        

    $data .= '</div>';

    return $data;
    
}
    
function mrylm_review_testimonial($lm_city = null){      
    
    global $wpdb;        
       
    $table_name = $wpdb->prefix . 'mryrm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
        
        $mryrm_sql     = "SELECT * FROM {$wpdb->prefix}mryrm_setting";
        $mryrm_setting = $wpdb->get_row($mryrm_sql);
        
        $mrylm_sql     = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $mrylm_setting = $wpdb->get_row($mrylm_sql);
        
        $location = $lm_city->lm_location;
        if($lm_city->lm_parent_id){
            
            $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities WHERE lm_city_id = '$lm_city->lm_parent_id'";
            $lm_city = $wpdb->get_row($lm_sql);
            $location = $lm_city->lm_location;  
        }
        
        $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews ORDER BY created_at DESC"; 
        if($mrylm_setting->lm_is_multi_location){
            $review_sql = "SELECT * FROM {$wpdb->prefix}mryrm_reviews WHERE location = '$location' ORDER BY created_at DESC"; 
        }
        $reviews    = $wpdb->get_results($review_sql); 
            
        $mryrm_data = '';
        $mryrm_str_data = '';   
        $total_review = count($reviews);
                
        if($total_review > 0){ 
            
                //initialized variable
                $mryrm_icon = ''; 
                $list_author = '';
                $widget_type = '';
                $mrm_slides  = '';
                
                // define widget type
                if($mryrm_setting->widget_type == 'list'){                    
                     $list_author = 'float:left;';
                     $mrm_slides = 'border-bottom: 2px solid #eaeaea;';
                     $widget_type = '_';
                }
                
                $mryrm_keywords = explode(',', rtrim($mryrm_setting->review_keyword, ','));
                $mryrm_total_keyword = count($mryrm_keywords);
                $mryrm_counter = 0;

                $is_show_head_title = $mryrm_setting->head_title ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_date = $mryrm_setting->is_show_date ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_title = $mryrm_setting->is_show_title ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_rating = $mryrm_setting->is_show_rating ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_author = $mryrm_setting->is_show_author ? 'style="display:block;"' : 'style="display:none;"';
                $is_show_bullet = $mryrm_setting->is_show_bullet ? 'display:block;' : 'display:none;';
                $is_show_icon = $mryrm_setting->is_show_icon ? 'style="display:block;"' : 'style="display:none;"';
                $is_location_new_line = $mryrm_setting->is_location_new_line > 0 ? '<br/>' : '';                
                
                $mryrm_icon_right_of_rating =  'display:block;';
                                  
                 // custom css processing  
                $mryrm_css = '<style type="text/css">' .  
                        '.mrm-container{text-align:'.$mryrm_setting->content_align.';background-color: ' . $mryrm_setting->bg_color . ';}'.
                        '.mrm-icon-right-of-rating{'.$mryrm_icon_right_of_rating.'}'.                       
                        '.owl-dots{'.$is_show_bullet.'}'.
                        '.list-author{'.$list_author.'}'.
                        '.mrm-slides{'.$mrm_slides.'}'.  
                        '.mrm-5star{color: ' . $mryrm_setting->star_color . '; float:left;}' .                        
                        '.mrm-title-sm{color: ' . $mryrm_setting->title_color . ' !important;}' .
                        '.mrm-review-text{color: ' . $mryrm_setting->text_color . ';}' .
                        '.mrm-review-footer{color: ' . $mryrm_setting->author_color . ';}' .
                        'button.owl-dot{' . $mryrm_setting->nav_css . '}' .
                        'button.owl-dot.active{' . $mryrm_setting->nav_active_css . '}' .
                        $mryrm_setting->custom_css.
                        '</style>';
                
                $mryrm_outer_start = '<div class="mrm-container" itemscope="" itemtype="http://schema.org/LocalBusiness">';                  
                    $mryrm_outer_start .= '<div class="mrm-slider-header">';
                        $mryrm_outer_start .= '<h2 class="mrm-header-title mrm-title-lg" '.$is_show_head_title.'>' . $mryrm_setting->head_title . '</h2>';                     
                    $mryrm_outer_start .= '</div>';
                    $mryrm_outer_start .= '<meta itemprop="name" content="' . $mryrm_setting->org_name . '">';
                    $mryrm_outer_start .= '<div itemprop="address" itemscope="" itemtype="http://schema.org/PostalAddress">';
                        $mryrm_outer_start .= '<meta itemprop="streetAddress" content="' . $mryrm_setting->org_address_line . '">';
                        $mryrm_outer_start .= '<meta itemprop="addressLocality" content="' . $mryrm_setting->org_city . '">';
                        $mryrm_outer_start .= '<meta itemprop="addressRegion" content="' . $mryrm_setting->org_state . '">';
                        $mryrm_outer_start .= '<meta itemprop="postalCode" content="' . $mryrm_setting->org_zipcode . '">';
                        $mryrm_outer_start .= '<meta itemprop="addressCountry" content="US">';
                    $mryrm_outer_start .= '</div>';
                    $mryrm_outer_start .= '<meta itemprop="url" content="' . $mryrm_setting->org_url . '">';
                    $mryrm_outer_start .= '<meta itemprop="logo" content="' . $mryrm_setting->org_logo_url . '">';
                    $mryrm_outer_start .= '<meta itemprop="image" content="' . $mryrm_setting->org_logo_url . '">';
                    $mryrm_outer_start .= '<meta itemprop="priceRange" content="$$$">';
                    $mryrm_outer_start .= '<meta itemprop="telePhone" content="' . $mryrm_setting->org_phone . '">';
                    
                    $mryrm_outer_start .= '<div id="mrm-testimonial-carousel'.$widget_type.'" class="mrm-slider-content owl-carousel'.$widget_type.' owl-theme">';
                     
                    
                foreach($reviews as $obj){ 

                    $mryrm_rating[] = $obj->rating;

                    // keywords manage
                    $mryrm_counter++;
                    if ($mryrm_counter == $mryrm_total_keyword) {
                        $mryrm_counter = 0;
                    }

                    // Rating star color design
                    $mryrm_rRating = '';
                    for ($i = 1; $i <= $obj->rating; $i++) {
                        $mryrm_rRating .= '&#9733;'; // orange star
                    }
                    for ($i = $obj->rating + 1; $i <= 5; $i++) {
                        $mryrm_rRating .= '&#9734;'; // white star
                    }

                    //keyword processing
                    $mryrm_keyword = '';
                    $mryrm_keyword_title = '';
                    if ($obj->keyword != '') {
                        $mryrm_keyword = $obj->keyword ? $obj->keyword : '';
                        $mryrm_keyword_title = $obj->keyword ? $obj->keyword . ' ' . $mryrm_setting->keyword_separator . ' ' . $obj->author : '';
                    } else {
                        $mryrm_keyword = isset($mryrm_keywords[$mryrm_counter]) ? $mryrm_keywords[$mryrm_counter] : '';
                        $mryrm_keyword_title = isset($mryrm_keywords[$mryrm_counter]) ? $mryrm_keywords[$mryrm_counter] . ' ' . $mryrm_setting->keyword_separator . ' ' . $obj->author : '';
                    }

                    // state & city processing
                    $mryrm_state_n_city = '';
                    if ($obj->city) {
                        $mryrm_state_n_city .= ' - ' . $obj->city;
                    }
                    if ($obj->city && $obj->state) {
                        $mryrm_state_n_city .= ' ' . $mryrm_setting->keyword_separator . ' ' . $obj->state;
                    }                   
                    
                    // multi city
                    if($mrylm_setting->lm_is_multi_location && $mryrm_state_n_city == ''){
                        $mryrm_state_n_city = '-'. $lm_city->lm_city . ' ' . $lm_city->lm_state;
                    }
                    
                    $location_new_line = $mryrm_state_n_city != '' ? $is_location_new_line : '';
                                    
                    $mryrm_icon = '<img class="source-icon" src="'. $mryrm_setting->org_url .'/wp-content/plugins/review-manager/assets/images/icon/' . strtolower(str_replace(' ', '_', trim($obj->source))) . '.png" alt="' . $obj->source . '"  title="' . $obj->source . '"  />';

                    // Main review content processing
                    $mryrm_str = '<div class="item mrm-slides" itemprop="Reviews"  itemscope="" itemtype="http://schema.org/Review">' .
                                    '<div class="mrm-review-header">' .                                       
                                        '<h3 class="mrm-title-sm" ' . $is_show_title . '>'. $mryrm_keyword_title .'</h3>'.                                                                
                                        '<div>'.                                          
                                            '<div class="mrm-5star" ' . $is_show_rating . '>' . $mryrm_rRating . '</div>'. 
                                            '<span class="mrm-icon-right-of-rating"  ' . $is_show_icon . '>'.$mryrm_icon.'</span>'.
                                        '</div>'.
                                        '<div class="clear"></div>' .                                       
                                        '<span itemprop="itemReviewed" itemscope="" itemtype="http://schema.org/Service">' . // added
                                            '<div itemprop="name" style="display:none;">' .
                                                '<a href="' . $mryrm_setting->org_url . '"> ' . $mryrm_keyword . ' </a>' .
                                            '</div>' .
                                        '</span>' .                                      
                                        '<span class="mrm-date" ' . $is_show_date . '>' .
                                            '<time datetime="' . $obj->created_at . '">' . date('M j, Y', strtotime($obj->created_at)) . '</time>' .
                                        '</span>' .
                                    '</div>' .                                  
                                    '<div class="mrm-review-text" itemprop="reviewBody">' . $obj->review . '</div>'. 
                                   
                                    '<div class="mrm-review-footer" itemprop="author" itemscope="" itemtype="http://schema.org/Person">' .
                                        '<span itemprop="name" style="display: none;">' . $obj->author . '</span>' .
                                        '<div class="mrm-author" '.$is_show_author.'><strong class="list-author">' . $obj->author. ' </strong> '. $location_new_line .'<span class="list-author">'. $mryrm_state_n_city . '</span></div>' .
                                    '</div>' .                            
                                    '<div itemprop="publisher" itemscope="" itemtype="http://schema.org/Organization">' .
                                        '<span itemprop="name" style="display: none;">' . $obj->source . '</span>' .
                                    '</div>' .                                   
                                '</div>';

                    $mryrm_str_data .= $mryrm_str;
                }

                $mryrm_outer_end = '</div>';
                $mryrm_container_end = '</div>';

                $mryrm_score_count = count($mryrm_rating);
                $mryrm_score_sum = array_sum($mryrm_rating);
                $mryrm_avg_rating = $mryrm_score_sum / $mryrm_score_count;

                // Aggregating content processing    
                $mryrm_aggregate = '<div itemprop="AggregateRating" itemscope itemtype="schema.org/AggregateRating">         
                                        <meta itemprop="ratingValue" content="' . $mryrm_avg_rating . '.0">
                                        <meta itemprop="bestRating" content="5.0">
                                        <meta itemprop="worstRating" content="1.0">
                                        <meta itemprop="reviewCount" content="' . $mryrm_score_count . '">                                      
                                        <meta itemprop="name" content="' . $mryrm_setting->org_name . '">                                     
                                    </div>';  

                $mryrm_data = $mryrm_outer_start . $mryrm_str_data . $mryrm_outer_end . $mryrm_aggregate . $mryrm_container_end . $mryrm_css;
            } else {
                $mryrm_data = "<p>No Review data found.</p>";
            }
            
        return $mryrm_data;
    }
}

function mrylm_service_area(){
    
    global $wpdb;
    $url = rtrim(get_permalink(),'/');  
    $content = '';
    
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {
        
        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql);  

        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities ORDER BY lm_url_slug ASC";
        $lm_cities = $wpdb->get_results($lm_sql);

        $word = "alwaysbestcare.com";
        $mystring = $lm_setting->lm_org_url;

        if(!empty($lm_cities)){        

            // ABC NEW
              if(strpos($mystring, $word) !== false){ 
                  
                $content .= '<div class="c-block__btn c-btn-bar">';                
                foreach($lm_cities as $obj){

                    $child = '';
                    if($obj->lm_parent_id > 0){ $child = '--'; }

                     $content .=  '<a href="'.$lm_setting->lm_page_url.$obj->lm_url_slug.'" target="_blank" class="c-btn -normal -inverted has-icon icon-right">
                                    <span class="c-btn__txt">'.$child.$obj->lm_city.'</span>
                                    <span class="c-btn__ico">
                                        <svg width="30" height="30" class="icon icon-lib-icon-arrow-wide " aria-hidden="true" role="img">
                                        <use xlink:href="#lib-icon-arrow-wide"></use>
                                        </svg>
                                    </span>
                                </a>';               
                }              
                $content .= '</div>'; 

                if($lm_setting->lm_embed_code){
                     $content .= '<div class="c-block__btn c-btn-bar lm-community-map">';
                         $content .= '<iframe src="'.$lm_setting->lm_embed_code.'" width="100%" height="480"></iframe>';;
                     $content .= '</div>';
                }

              }else{
                  // NON ABC and ABC OLD
                    $content .= '<section class="lm-service-area">';
                    $content .= '<div class="container-fluid container">';
                        $content .= '<div class="row">';
                            $content .= '<div class="col-lg-12">';
                                $content .= '<h1 class="lm-community-title">'.$lm_setting->lm_service_area_page_title.'</h1>';
                            $content .= '</div>';
                        $content .= '</div>';
                        $content .= '<div class="row lm-city-item-block">';

                        foreach($lm_cities as $obj){

                            $child = '';
                            if($obj->lm_parent_id > 0){$child = '--'; } 
                            $content .= '<div class="col-lg-3">';
                                $content .= '<span class="lm-city-item"><a href="'.$lm_setting->lm_page_url.$obj->lm_url_slug.'">'.$child.$obj->lm_city.'</a></span>';
                            $content .= '</div>';
                        }

                        $content .= '</div>';

                        if($lm_setting->lm_embed_code){
                            $content .= '<div class="row">';
                                $content .= '<div class="col-lg-12 lm-community-map">';
                                    $content .= '<iframe src="'.$lm_setting->lm_embed_code.'" width="100%" height="480"></iframe>';;
                                $content .= '</div>';
                            $content .= '</div>';
                        }

                    $content .= '</div>';              
                    $content .= '</section>';                
              } 
        }

    }
    
    return $content; 
}

/* Job Start */

function mrylm_job_posting($type = null) {
    
    global $wpdb;
    
    $mrylm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $setting = $wpdb->get_row($mrylm_sql);
      
    if ($setting->lm_unique_id == '') {
        return '<p>You have missing your Local Magic Key. Please <a href="https://www.mrmarketingres.com/local-magic" target="_blank">Contact</a> for Local Magic Key</p>';
    }     
 
    $job_sql = "SELECT * FROM {$wpdb->prefix}mrylm_jobs WHERE is_publish = 1 ORDER BY created_at DESC";
    $jobs = $wpdb->get_results($job_sql); 
     
    $str = '';
    if($setting->lm_is_job_post && !empty($jobs)){
        
        $str .= '<div class="lm-job-content">'; // job container
            $str .= '<div class="lm-job-header"><h2 class="lm-job-title-lg">'.$setting->lm_job_headline.'</h2></div>';
            $str .= '<div id="lm-job-posting">'; // job posting container 
            
            foreach($jobs AS $job){

                $str .= '<div class="lm-job-posting-inner">'; // Item wrapper
                
                        $str .= '<div class="lm-job-accordion-toggle">'; // top wrapper
                            $str .= '<div class="lm-job-media-con">';
                                $str .= '<div class="lm-job-img-box">';
                                    $str .= '<img src="'.$setting->lm_org_logo_url.'" alt="">';
                                $str .= '</div>';
                                $str .= '<div class="lm-job-media-body">';
                                    $str .= '<h3 class="lm-job-title">'.$job->title.'</h3>';
                                    $str .= '<div class="lm-job-bar-lg">'.$job->industry.'</div>';
                                    $str .= '<div class="lm-job-bar-sm">'.$job->category.'</div>';
                                    $str .= '<ul class="lm-job-list-tems">';
                                    
                                        $date = mrylm_nice_time(date('Y-m-01'));
                                        
                                        $mrylm_clock_icon = '<img class="icon" style="width: auto;" src="'. $setting->lm_org_url .'wp-content/plugins/local-magic/assets/images/clock.png" alt="Clock"  title="Clock"  />'; 
                                        $mrylm_brif_icon =  '<img class="icon" style="width: auto;" src="'. $setting->lm_org_url .'wp-content/plugins/local-magic/assets/images/brifcase.png" alt="Brifcase"  title="Brifcase"  />'; 
                                        
                                        $str .= '<li>'.$mrylm_clock_icon.' '.$date.'</li>';
                                        $str .= '<li>'.$mrylm_brif_icon.' '.$job->employment_type.'</li>';
                                        $str .= '<li style="float:right;"><a class="lm-job-btn" href="'.$job->job_url.'">Apply Now</a></li>';
                                        
                                    $str .= '</ul>';
                                $str .= '</div>';
                            $str .= '</div>';                            
                        $str .= '</div>'; // top wrapper end
                
                        $str .= '<div class="lm-job-accordion-content" itemscope itemtype="https://schema.org/JobPosting" >'; // detail wrapper              
                        $str .= '<div class="lm-job-details">'; // detail inner 
                                $str .= '<meta itemprop="specialCommitments" content="VeteranCommit" />';                           
                        
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Job Title</h4>';
                                    $str .= '<p itemprop="title" class="lm-job-right-side">'.$job->title.'</p>';
                                $str .= '</div>';   
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Description</h4>';
                                    $str .= '<p itemprop="description" class="lm-job-right-side">'.$job->description.'</p>';
                                $str .= '</div>';   
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Post Date</h4>';
                                    $str .= '<p itemprop="datePosted" class="lm-job-right-side">'.date('Y-m-01').'</p>';
                                    $str .= '<meta itemprop="url" content="'.$job->job_url.'">';
                                    $today = strtotime(date("Y-m-d"));
                                    $validThrough = date('Y-m-d', strtotime('+2 months', $today));                
                                    $str .= '<meta itemprop="validThrough" content="'.$validThrough.'">';
                                $str .= '</div>';   
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Employment Type</h4>';
                                    $str .= '<p itemprop="employmentType" class="lm-job-right-side">'.$job->employment_type.'</p>';
                                $str .= '</div>';   
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Industry</h4>';
                                    $str .= '<p itemprop="industry" class="lm-job-right-side">'.$job->industry.'</p>';
                                $str .= '</div>';   
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Category</h4>';
                                    $str .= '<p itemprop="occupationalCategory" class="lm-job-right-side">'.$job->category.'</p>';
                                $str .= '</div>';   
                                $str .= '<div class="lm-job-details-innner" style="display:none;">';
                                    $str .= '<span itemprop="jobLocation" itemscope itemtype="https://schema.org/Place">';
                                        $str .= '<span itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
                                            $str .= '<span itemprop="streetAddress">'.$setting->lm_org_address_line.'</span>';
                                            $str .= '<span itemprop="addressLocality">'.$setting->lm_org_city.'</span>';
                                            $str .= '<span itemprop="addressRegion">'.$setting->lm_org_state.'</span>';
                                            $str .= '<span itemprop="postalCode">'.$setting->lm_org_zipcode.'</span>';
                                            $str .= '<span itemprop="addressCountry">US</span>';                
                                        $str .= '</span>';

                                        if($setting->lm_latitude && $setting->lm_longitude){                        
                                            $str .= '<span itemprop="geo" itemscope itemtype="https://schema.org/GeoCoordinates">';
                                                $str .= '<span itemprop="latitude">'.$setting->lm_latitude.'</span>';
                                                $str .= '<span itemprop="longitude">'.$setting->lm_longitude.'</span>';
                                            $str .= '</span>';                    
                                        }
                                    $str .= '</span>';
                                $str .= '</div>';
                                
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Work Hour</h4>';
                                    $str .= '<p class="lm-job-right-side">'.$job->work_hour.'</p>';
                                $str .= '</div>';
                                $str .= '<div class="lm-job-details-innner">';
                                    $str .= '<h4 class="lm-job-label-title lm-job-left-side">Hour Value</h4>';
                                    $str .= '<p class="lm-job-right-side">'.$job->hour_value.'</p>';
                                $str .= '</div>';
                                
                                $str .= '<div itemprop="baseSalary" itemscope itemtype="https://schema.org/MonetaryAmount" style="display:none;">';
                                    $str .= '<span itemprop="currency">USD</span>';
                                    $str .= '<span itemprop="employmentType">'.$job->employment_type.'</span>';
                                    $str .= '<span itemprop="workHours">'.$job->work_hour.'</span>';
                                    $str .= '<span itemprop="value" itemscope itemtype="https://schema.org/QuantitativeValue">';
                                        $str .= '<span itemprop="value">'.$job->hour_value.'</span>';
                                        $str .= '<span itemprop="unitText">Hour</span>';                    
                                    $str .= '</span>';
                                $str .= '</div>';
                                $str .= '<div itemprop="hiringOrganization" itemscope itemtype="https://schema.org/Organization" style="display:none;">';
                                    $str .= '<span itemprop="name">'.$setting->lm_org_name.'</span>';
                                    $str .= '<span itemprop="logo">'.$setting->lm_org_logo_url.'</span>';
                                $str .= '</div>';
                                
                                if($job->responsibility){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">Responsibilities</h4>';
                                        $str .= '<p itemprop="responsibilities" class="lm-job-right-side">'.$job->responsibility.'</p>';
                                    $str .= '</div>';   
                                }
                                if($job->edu_requirement){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">Educational requirements</h4>';
                                        $str .= '<p itemprop="educationalRequirements" class="lm-job-right-side">'.$job->edu_requirement.'</p>';
                                    $str .= '</div>';   
                                }
                                if($job->work_experience){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">Work Experience</h4>';
                                        $str .= '<p itemprop="workExperience" class="lm-job-right-side">'.$job->work_experience.'</p>';
                                    $str .= '</div>';   
                                }
                                if($job->skill){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">Desired Skills</h4>';
                                        $str .= '<p itemprop="skills" class="lm-job-right-side">'.$job->skill.'</p>';
                                    $str .= '</div>';   
                                }
                                if($job->benefit){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">Benefits</h4>';
                                        $str .= '<p itemprop="jobBenefits" class="lm-job-right-side">'.$job->benefit.'</p>';
                                    $str .= '</div>';   
                                }
                                if($job->incentive){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">Incentives</h4>';
                                        $str .= '<p itemprop="incentiveCompensation" class="lm-job-right-side">'.$job->incentive.'</p>';
                                    $str .= '</div>';   
                                }
                                if($job->job_url){
                                    $str .= '<div class="lm-job-details-innner">';
                                        $str .= '<h4 class="lm-job-label-title lm-job-left-side">&nbsp;</h4>';
                                        $str .= '<p class="lm-job-right-side"><a class="lm-job-btn" href="'.$job->job_url.'">Apply Now</a></p>';
                                    $str .= '</div>';   
                                }
                                
                            $str .= '</div>'; // detail inner end                              
                        $str .= '</div>'; // detail wrapper end                                  
                
                $str .= '</div>';  // Item wrapper end
            }

            $str .= '</div>'; // job posting container end 
        $str .= '</div>';  // job container end
        
        mrylm_job_script();
    }
    
    return $str;    
}

function mrylm_nice_time($date) {

    if (empty($date)) {
        return ""; //"No date provided"; "2 months ago";       
    }

    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

    $now = time();
    $unix_date = strtotime($date);

    // check validity of date
    if (empty($unix_date)) {
        return "2 months ago"; // "Bad date";
    }

    // is it future date or past date
    if ($now > $unix_date) {
        $difference = $now - $unix_date;
        $tense = "ago";
    } else {
        $difference = $unix_date - $now;
        $tense = "from now";
    }

    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
        $difference /= $lengths[$j];
    }

    $difference = round($difference);

    if ($difference != 1) {
        $periods[$j] .= "s";
    }

    return "$difference $periods[$j] {$tense}";
}

function mrylm_job_script(){
?>
<script type="text/javascript">

    jQuery(document).ready(function () {
        jQuery('#lm-job-posting').find('.lm-job-accordion-toggle').click(function () {
            var isActive = jQuery(this).hasClass("lm-job-active");
            jQuery('.lm-job-accordion-toggle').removeClass('lm-job-active')
            if (!isActive) {
                jQuery(this).toggleClass('lm-job-active');
            }
            jQuery(this).next().slideToggle('fast');
            jQuery(".lm-job-accordion-content").not(jQuery(this).next()).slideUp('fast');
        });
    });

</script>
<style type="text/css">
.lm-job-content {max-width: 1200px;margin: 0 auto; box-shadow:0px 10px 35px 0px rgba(56, 71, 109, 0.075);background: #fff;-webkit-border-radius: 0.85rem;border-radius: 0.85rem;margin-bottom: 60px;}
.lm-job-header {background:#6dac4b;border-top-left-radius: 0.85rem;border-top-right-radius: 0.85rem;padding: 5px 20px;}
.lm-job-title-lg {margin: 0px!important;padding: 0px;color: #fff;font-size: 32px;}
#lm-job-posting {padding: 10px 0px;}
.lm-job-accordion-toggle {cursor: pointer;}
.lm-job-accordion-content {display: none;}
.lm-job-posting-inner{ margin-top: 0px;border-bottom: 1px solid #efefef;padding-bottom: 20px;}
.lm-job-posting-inner:last-child{border-bottom: none; }
.lm-job-details{padding:20px;box-shadow:0px 10px 35px 0px rgba(56, 71, 109, 0.075);background: #f5f5f5;margin-top: 30px;border-radius: 6px;border: 1px solid #f5f5f5;}
.lm-job-details-innner{overflow: hidden; padding: 15px 0px;}
.lm-job-details-innner:last-child{margin-bottom: 0;}
.lm-job-left-side {width: 20%;float: left;padding-right:2%;}
.lm-job-right-side {width: 76%;float: right;padding-left:2%;line-height: 24px;}
.lm-job-label-title {font-size: 16px;color: #717171; margin-bottom: 0px !important;}
.lm-job-media-con {display: -webkit-box;display: -ms-flexbox;display: flex;-webkit-box-align: start; -ms-flex-align: start;align-items: flex-start;}
.lm-job-media-con img {vertical-align: middle;border-style: none;width: 120px;height: auto;}
.lm-job-img-box{border-radius: 0.475rem !important;padding: 20px; margin:0px 20px 0px 10px;border: 1px solid #d8d8d8}
.lm-job-media-con .lm-job-media-body {-webkit-box-flex: 1; -ms-flex: 1;flex: 1; line-height: 20px;}
.lm-job-media-con .lm-job-title{font-size: 18px;color: #717171; margin:0 0 10px 0!important; margin: 0 0 4px 0!important;}
.lm-job-bar-lg {font-size: 16px;color: #6089c7;}
.lm-job-bar-sm {margin-top: 2px;font-size: 15px;}
.lm-job-list-tems {margin: 2px 0 0 0;padding: 0;}
.lm-job-list-tems li{display: inline-block;margin-right: 20px; margin-left: 0px; margin-top: 0px;}
.lm-job-right-side ul {margin: 0;padding: 0;}
.lm-job-right-side ul li {margin: 10px 0;list-style: none;}
.lm-job-btn{float: right; margin-right: 21px; color: #fff !important; background: #638c4b;text-align: center; padding: 8px 15px; border-radius: 5px;}

.lm-job-accordion-toggle:before, .lm-job-active:before {content:url('/wp-content/plugins/local-magic/assets/images/light-angle.png');margin-right: 15px;display: inline-block;float: right;width: 30px;height: 30px;line-height: 33px;border-radius: 100%;background: #4072AF;color: #fff;text-align: center; margin-top: 35px;}
.lm-job-active:before {content:url('/wp-content/plugins/local-magic/assets/images/light-angle-down.png');line-height: 30px;margin-right: 15px;} 

@media only screen and (max-width: 767px) {
    .lm-job-left-side, .lm-job-right-side {width: 100%;float: none;padding-left:0;padding-right:0;}
    .lm-job-media-con img {width: 80px;}
    .lm-job-img-box {padding: 5px;}
    .lm-job-list-tems {font-size: 13px;}
    .lm-job-bar-lg, .lm-job-bar-sm {height: 8px;}
    #lm-job-posting {padding: 0px;}
}
</style>
<?php   
}

/* Job End */


/* POI Start */
function mrylm_poi($city_slug = null){
    
    global $wpdb;

    $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
    $lm_setting = $wpdb->get_row($lm_sql);

    $lm_city = mrylm_get_city();
    
    if(empty($lm_city)){
        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_cities WHERE lm_url_slug = '$city_slug'";
        $lm_city = $wpdb->get_row($lm_sql);
    }
    
    $poi = '';
    $poi .= '<div class="lm-poi-container" itemscope itemtype="https://schema.org/LocalBusiness">';
    
        /* General Section Start */
        $poi .= '<div class="lm-poi-row">';
            $poi .= '<div class="lm-poi-col-12">';
                $poi .= '<div class="lm-poi-box-shadow">';
                    $poi .= '<h2 class="lm-poi-title-lg lm-poi-title-bg lm-poi-p-2" itemprop="name">Points of Interest in '.$lm_city->lm_city.', '.$lm_city->lm_state.'</h2>';
                    $poi .= '<div class="lm-poi-p-2">';
                        $poi .= '<ul class="lm-poi-items-content">'; 
                        
                        if($lm_city->lm_city_mayor){
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>City Mayor</strong></span>';
                                $poi .= '<span class="lm-poi-right" itemprop="CityMayor">'.$lm_city->lm_city_mayor.'</span>';
                            $poi .= '</li>';
                        }   
                        if($lm_city->lm_population){
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Population Of City</strong></span>';
                                $poi .= '<span class="lm-poi-right" itemprop="PopulationOfCity">'.$lm_city->lm_population.'</span>';
                            $poi .= '</li>';
                        }
                        if($lm_city->lm_avg_home_value){    
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Average Home Value</strong></span>';
                                $poi .= '<span class="lm-poi-right" itemprop="AverageHomeValueOfCity">$'.$lm_city->lm_avg_home_value.'</span>';
                            $poi .= '</li>';
                        }
                        if($lm_city->lm_avg_household_income){    
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Average Household Income</strong></span>';
                                $poi .= '<span class="lm-poi-right" itemprop="AverageHouseholdIncomeOfCity">$'.$lm_city->lm_avg_household_income.'</span>';
                            $poi .= '</li>';
                        }
                        if($lm_city->lm_avg_temperature){    
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Average Temperature Of City</strong></span>';
                                $poi .= '<span class="lm-poi-right"  itemprop="AverageTemperatureOfCity">'.$lm_city->lm_avg_temperature.'</span>';
                            $poi .= '</li>';
                        }
                        if($lm_city->lm_price_range){    
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Average Price Range Of City</strong></span>';
                                $poi .= '<span class="lm-poi-right" itemprop="PriceRange">$'.$lm_city->lm_price_range ? $lm_city->lm_price_range : '10000-25000'.'</span>';
                            $poi .= '</li>';
                        }
                        if($lm_setting->lm_org_logo_url){    
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Image of City</strong></span>';
                                $poi .= '<span class="lm-poi-right lm-poi-city-logo"><img src="'.$lm_setting->lm_org_logo_url.'" alt="" /></span>';
                            $poi .= '</li>';
                        }
                        if($lm_city->lm_description){   
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Description Of City</strong></span>';
                                $poi .= '<span class="lm-poi-right"  itemprop="description">'.$lm_city->lm_description.'</span>';
                            $poi .= '</li>';
                        }
                        if($lm_setting->lm_phone){   
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Phone Of City</strong></span>';
                                $poi .= '<span class="lm-poi-right"  itemprop="telephone"><a href="tel:'.$lm_setting->lm_phone.'">'.$lm_setting->lm_phone.'</a></span>';
                            $poi .= '</li>';
                        }
                            $poi .= '<li>';
                                $poi .= '<span class="lm-poi-left"><strong>Postal Address Of City</strong></span>';
                                $poi .= '<span class="lm-poi-right"  itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">';
                                    $poi .= '<span itemprop="streetAddress">'.$lm_setting->lm_org_address.'</span>';
                                    $poi .= '<span itemprop="addressLocality">'.$lm_city->lm_city.'</span>';
                                    $poi .= '<span itemprop="addressRegion">'.$lm_city->lm_state.'</span>';
                                $poi .= '</span>';
                            $poi .= '</li>'; 
                            
                        $poi .= '</ul>';
                    $poi .= '</div>';
                $poi .= '</div>';
            $poi .= '</div>';  
        $poi .= '</div>';
        /* General Section End */
        
        /* Education Section Start */
         if($lm_city->lm_educational_institution){
             
            $poi .= '<div class="lm-poi-row lm-poi-mt-4">';
                $poi .= '<div class="lm-poi-col-12">';
                    $poi .= '<div class="lm-poi-box-shadow">';
                        $poi .= '<h2 class="lm-poi-title-md lm-poi-title-bg lm-poi-p-2" itemprop="EducationalInstitution">Educational Institution in '.$lm_city->lm_city.', '.$lm_city->lm_state.'</h2>';
                        $poi .= '<div class="lm-poi-content-box-inner">';
                            $poi .= '<div class="lm-poi-row">';
                        
                            $educations = explode('|', $lm_city->lm_educational_institution);
                            if(!empty($educations)){
                                
                                $counter = 0;
                                foreach($educations as $key=>$education){
                                    
                                    if($counter > 3){
                                      $counter = 0;
                                    }                                    
                                    $odd_even = $counter < 2 ? 'lm-poi-odd-row' : 'lm-poi-even-row';
                                    
                                    $mrylm_edu_icon = '<img class="lm-poi-icon" src="'. $lm_setting->lm_org_url .'/wp-content/plugins/local-magic/assets/images/education.png" alt="'.$education.'"  title="'.$education.'"  />'; 
                                    $poi .= '<div class="lm-poi-col-6 '.$odd_even.'">';
                                        $poi .= '<div class="lm-poi-content-box">';
                                            $poi .= '<div class="lm-poi-single-con lm-poi-plrb-2">';
                                                $poi .= '<div class="lm-poi-icon">'.$mrylm_edu_icon.'</div>';
                                                $poi .= '<div class="lm-poi-single-text" itemprop="EducationalInstitution-'.++$key.'" itemscope itemtype="https://schema.org/Place">';
                                                    $poi .= '<h4 itemprop="name">'.$education.'</h4>';
                                                    $poi .= '<p style="display:none;">'.$lm_city->lm_city.', '.$lm_city->lm_state.'</p>';
                                                    $poi .= '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="display:none;">';
                                                        $poi .= '<span itemprop="addressLocality">'.$lm_city->lm_city.'</span>';
                                                        $poi .= '<span itemprop="addressRegion">'.$lm_city->lm_state.'</span>';
                                                    $poi .= '</div>';
                                                $poi .= '</div>';
                                            $poi .= '</div>';
                                        $poi .= '</div>';
                                    $poi .= '</div>';
                                    
                                    $counter++;
                                }
                            }
                            
                            $poi .= '</div>';
                        $poi .= '</div>';
                    $poi .= '</div>';
                $poi .= '</div>';             
            $poi .= '</div>';
         }
        
        /* Educaton Section End */
         
        /* Park Section End */
         if($lm_city->lm_state_park){
             
             $poi .= '<div class="lm-poi-row lm-poi-mt-4">';
                $poi .= '<div class="lm-poi-col-12">';
                    $poi .= '<div class="lm-poi-box-shadow">';
                        $poi .= '<h2 class="lm-poi-title-md lm-poi-title-bg lm-poi-p-2" itemprop="Parks">State Parks in '.$lm_city->lm_city.', '.$lm_city->lm_state.'</h2>';
                        $poi .= '<div class="lm-poi-content-box-inner">';
                            $poi .= '<div class="lm-poi-row">';
                        
                            $parks = explode('|', $lm_city->lm_state_park);
                            if(!empty($parks)){
                                
                                $counter = 0;
                                foreach($parks as $key=>$park){
                                    
                                    if($counter > 3){
                                      $counter = 0;
                                    }                                    
                                    $odd_even = $counter < 2 ? 'lm-poi-odd-row' : 'lm-poi-even-row';
                                    
                                    $mrylm_park_icon = '<img class="lm-poi-icon" src="'. $lm_setting->lm_org_url .'/wp-content/plugins/local-magic/assets/images/park.png" alt="'.$park.'"  title="'.$park.'"  />';  
                                     $poi .= '<div class="lm-poi-col-6 '.$odd_even.'">';
                                        $poi .= '<div class="lm-poi-content-box">';
                                            $poi .= '<div class="lm-poi-single-con lm-poi-plrb-2">';
                                                $poi .= '<div class="lm-poi-icon">'.$mrylm_park_icon.'</div>';
                                                $poi .= '<div class="lm-poi-single-text" itemprop="Park-'.++$key.'" itemscope itemtype="https://schema.org/Place">';
                                                    $poi .= '<h4 itemprop="name">'.$park.'</h4>';
                                                    $poi .= '<p style="display:none;">'.$lm_city->lm_city.', '.$lm_city->lm_state.'</p>';
                                                    $poi .= '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="display:none;">';
                                                        $poi .= '<span itemprop="addressLocality">'.$lm_city->lm_city.'</span>';
                                                        $poi .= '<span itemprop="addressRegion">'.$lm_city->lm_state.'</span>';
                                                    $poi .= '</div>';
                                                $poi .= '</div>';
                                            $poi .= '</div>';
                                        $poi .= '</div>';
                                    $poi .= '</div>';
                                    $counter++;
                                }
                            }
                            
                            $poi .= '</div>';
                        $poi .= '</div>';
                    $poi .= '</div>';
                $poi .= '</div>';             
            $poi .= '</div>';
             
         }         
        /* Park Section End */
         
         /* Landmark Section End */
         if($lm_city->lm_historic_landmark){
             
             $poi .= '<div class="lm-poi-row lm-poi-mt-4">';
                $poi .= '<div class="lm-poi-col-12">';
                    $poi .= '<div class="lm-poi-box-shadow">';
                        $poi .= '<h2 class="lm-poi-title-md lm-poi-title-bg lm-poi-p-2" itemprop="Landmarks">Historic Landmarks in '.$lm_city->lm_city.', '.$lm_city->lm_state.'</h2>';
                        $poi .= '<div class="lm-poi-content-box-inner">';
                            $poi .= '<div class="lm-poi-row">';
                        
                           $landmarks = explode('|', $lm_city->lm_historic_landmark);
                            if(!empty($landmarks)){
                                $counter = 0;
                                foreach($landmarks as $key=>$landmark){
                                    
                                    if($counter > 3){
                                      $counter = 0;
                                    }                                    
                                    $odd_even = $counter < 2 ? 'lm-poi-odd-row' : 'lm-poi-even-row';
                                    
                                    $mrylm_landmark_icon = '<img class="lm-poi-icon" src="'. $lm_setting->lm_org_url .'/wp-content/plugins/local-magic/assets/images/landmark.png" alt="'.$landmark.'"  title="'.$landmark.'"  />';  
                                    $poi .= '<div class="lm-poi-col-6 '.$odd_even.'">';
                                        $poi .= '<div class="lm-poi-content-box">';
                                            $poi .= '<div class="lm-poi-single-con lm-poi-plrb-2">';
                                                $poi .= '<div class="lm-poi-icon">'.$mrylm_landmark_icon.'</div>';
                                                $poi .= '<div class="lm-poi-single-text" itemprop="Landmark-'.++$key.'" itemscope itemtype="https://schema.org/LandmarksOrHistoricalBuildings">';
                                                    $poi .= '<h4 itemprop="name">'.$landmark.'</h4>';
                                                    $poi .= '<p style="display:none;">'.$lm_city->lm_city.', '.$lm_city->lm_state.'</p>';
                                                    $poi .= '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="display:none;">';
                                                        $poi .= '<span itemprop="addressLocality">'.$lm_city->lm_city.'</span>';
                                                        $poi .= '<span itemprop="addressRegion">'.$lm_city->lm_state.'</span>';
                                                    $poi .= '</div>';
                                                $poi .= '</div>';
                                            $poi .= '</div>';
                                        $poi .= '</div>';
                                    $poi .= '</div>';
                                    
                                     $counter++;
                                }
                            }
                            
                            $poi .= '</div>';
                        $poi .= '</div>';
                    $poi .= '</div>';
                $poi .= '</div>';             
            $poi .= '</div>';
             
         }         
        /* Park Section End */
         
         
        /* Assisted Living Section Start */
         if($lm_city->lm_assisted_living){
             
             $poi .= '<div class="lm-poi-row lm-poi-mt-4">';
                $poi .= '<div class="lm-poi-col-12">';
                    $poi .= '<div class="lm-poi-box-shadow">';
                        $poi .= '<h2 class="lm-poi-title-md lm-poi-title-bg lm-poi-p-2" itemprop="Nursing">Assisted Living in '.$lm_city->lm_city.', '.$lm_city->lm_state.'</h2>';
                        $poi .= '<div class="lm-poi-content-box-inner">';
                            $poi .= '<div class="lm-poi-row">';
                        
                           $assisted_living = explode('|', $lm_city->lm_assisted_living);
                            if(!empty($assisted_living)){
                                $counter = 0;
                                foreach($assisted_living as $key=>$aliving){
                                    
                                    if($counter > 3){
                                      $counter = 0;
                                    }                                    
                                    $odd_even = $counter < 2 ? 'lm-poi-odd-row' : 'lm-poi-even-row';
                                    
                                    $mrylm_aliving_icon = '<img class="lm-poi-icon" src="'. $lm_setting->lm_org_url .'/wp-content/plugins/local-magic/assets/images/landmark.png" alt="'.$aliving.'"  title="'.$aliving.'"  />';  
                                    $poi .= '<div class="lm-poi-col-6 '.$odd_even.'">';
                                        $poi .= '<div class="lm-poi-content-box">';
                                            $poi .= '<div class="lm-poi-single-con lm-poi-plrb-2">';
                                                $poi .= '<div class="lm-poi-icon">'.$mrylm_aliving_icon.'</div>';
                                                $poi .= '<div class="lm-poi-single-text" itemprop="Assisted-Living-'.++$key.'" itemscope itemtype="https://schema.org/Nursing">';
                                                    $poi .= '<h4 itemprop="name">'.$aliving.'</h4>';
                                                    $poi .= '<p style="display:none;">'.$lm_city->lm_city.', '.$lm_city->lm_state.'</p>';
                                                    $poi .= '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="display:none;">';
                                                        $poi .= '<span itemprop="addressLocality">'.$lm_city->lm_city.'</span>';
                                                        $poi .= '<span itemprop="addressRegion">'.$lm_city->lm_state.'</span>';
                                                    $poi .= '</div>';
                                                $poi .= '</div>';
                                            $poi .= '</div>';
                                        $poi .= '</div>';
                                    $poi .= '</div>';
                                    
                                     $counter++;
                                }
                            }
                            
                            $poi .= '</div>';
                        $poi .= '</div>';
                    $poi .= '</div>';
                $poi .= '</div>';             
            $poi .= '</div>';
             
         }         
        /* Assisted Living Section End */ 
         
        /* Restaurant Section Start */
         if($lm_city->lm_restaurant){
             
             $poi .= '<div class="lm-poi-row lm-poi-mt-4">';
                $poi .= '<div class="lm-poi-col-12">';
                    $poi .= '<div class="lm-poi-box-shadow">';
                        $poi .= '<h2 class="lm-poi-title-md lm-poi-title-bg lm-poi-p-2" itemprop="Restaurant">Restaurants in '.$lm_city->lm_city.', '.$lm_city->lm_state.'</h2>';
                        $poi .= '<div class="lm-poi-content-box-inner">';
                            $poi .= '<div class="lm-poi-row">';
                        
                           $restaurants = explode('|', $lm_city->lm_restaurant);
                            if(!empty($restaurants)){
                                $counter = 0;
                                foreach($restaurants as $key=>$restaurant){
                                    
                                    if($counter > 3){
                                      $counter = 0;
                                    }                                    
                                    $odd_even = $counter < 2 ? 'lm-poi-odd-row' : 'lm-poi-even-row';
                                    
                                    $mrylm_restaurant_icon = '<img class="lm-poi-icon" src="'. $lm_setting->lm_org_url .'/wp-content/plugins/local-magic/assets/images/landmark.png" alt="'.$restaurant.'"  title="'.$restaurant.'"  />';  
                                    $poi .= '<div class="lm-poi-col-6 '.$odd_even.'">';
                                        $poi .= '<div class="lm-poi-content-box">';
                                            $poi .= '<div class="lm-poi-single-con lm-poi-plrb-2">';
                                                $poi .= '<div class="lm-poi-icon">'.$mrylm_restaurant_icon.'</div>';
                                                $poi .= '<div class="lm-poi-single-text" itemprop="Restaurant-'.++$key.'" itemscope itemtype="https://schema.org/Restaurant">';
                                                    $poi .= '<h4 itemprop="name">'.$aliving.'</h4>';
                                                    $poi .= '<p style="display:none;">'.$lm_city->lm_city.', '.$lm_city->lm_state.'</p>';
                                                    $poi .= '<div itemprop="address" itemscope itemtype="https://schema.org/PostalAddress" style="display:none;">';
                                                        $poi .= '<span itemprop="addressLocality">'.$lm_city->lm_city.'</span>';
                                                        $poi .= '<span itemprop="addressRegion">'.$lm_city->lm_state.'</span>';
                                                    $poi .= '</div>';
                                                $poi .= '</div>';
                                            $poi .= '</div>';
                                        $poi .= '</div>';
                                    $poi .= '</div>';
                                    
                                     $counter++;
                                }
                            }
                            
                            $poi .= '</div>';
                        $poi .= '</div>';
                    $poi .= '</div>';
                $poi .= '</div>';             
            $poi .= '</div>';
             
         }         
        /* Restaurant Section End */ 
        
    $poi .= '</div>';

    
    mrylm_poi_script();
    return $poi;
    
}

function mrylm_poi_script(){
?>
<style type="text/css">
    .lm-poi-row {display: flex;flex-wrap: wrap;}
    .lm-poi-col-12 {width: 100%;}
    .lm-poi-col-6 {width: 50%;}
    .lm-poi-box-shadow{box-shadow:0px 10px 35px 0px rgba(56, 71, 109, 0.075);background: #fff;-webkit-border-radius: 0.30rem;border-radius: 0.30rem;}
    .lm-poi-title-lg {font-size: 32px;}
    .lm-poi-title-md {font-size: 27px;}
    .lm-poi-title-bg{background-color: #61a7db;box-shadow: 0px 10px 35px 0px rgb(56 71 109 / 8%);color: #fff;}
    .lm-poi-p-2{padding:0px 20px !important;margin-top: 20px;}
    .lm-poi-items-content{margin-top: 20px;display: flex;flex-direction: row;flex-wrap: wrap;width: 100%;padding-left: 0;}
    .lm-poi-items-content li{display: flex;flex-direction: row;flex-wrap: wrap; width: 100%;padding: 10px; margin-bottom: 5px; -webkit-border-radius: 0.30rem; border-radius: 0.30rem; border-top:1px solid rgba(0,0,0,.09);}
    .lm-poi-items-content li span.lm-poi-left{width:33%;padding-right: 4%; }
    .lm-poi-items-content li span.lm-poi-right{width: 63%;}
    .lm-poi-items-content li a{text-decoration: none; color: inherit;}
    .lm-poi-city-logo img{max-height: 60px;}
    .lm-poi-mt-4{margin-top: 40px;}
    .lm-poi-odd-row {background-color: #f7f7f7;}
    .lm-poi-even-row {background-color: #ffffff;}
    .lm-poi-content-box > div{padding-top: 18px;}
    .lm-poi-single-con{display: flex;}
    .lm-poi-plrb-2{ padding: 0px 20px 20px 15px;}
    .lm-poi-single-con .lm-poi-icon {margin-right: 8px;}
    .lm-poi-single-text p{font-size: 14px;padding-top: 0px;}
    .lm-poi-single-con h4{font-size: 22px; margin: 0px!important;}

@media only screen and (max-width: 767px) {
    
    .lm-poi-col-12, .lm-poi-col-6{width: 100%;}
    .lm-poi-items-content li{display: block;}
    .lm-poi-items-content li span{text-align: left;display: block;padding: 10px 0;width: 100%;}
    .lm-poi-items-content li span.lm-poi-left{width:100%;padding-right: 0; }
    .lm-poi-items-content li span.lm-poi-right{ width: 100%;}
    .lm-poi-title-lg {font-size: 1.5rem;}
    .lm-poi-title-md {font-size: 1.3rem;}
    .lm-poi-single-con h4{font-size: 19px;}
}
</style> 
<?php        
}
/* POI END */

function mrylm_script(){
    
    global $wpdb;  
    $table_name = $wpdb->prefix . 'mrylm_setting';
    if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name ) {        

        $lm_sql = "SELECT * FROM {$wpdb->prefix}mrylm_setting";
        $lm_setting = $wpdb->get_row($lm_sql);     
    ?>
        <!--<script src="https://code.jquery.com/jquery-1.12.4.js"></script>-->
        <script type="text/javascript">

            function get_local_seo_by_city(url){   
                if(url){
                    window.location.href = url; 
                }
            }
            jQuery('.fn_lm_more_link').on('click', function(){
                jQuery('.fn_lm_more_link_block').slideToggle('slow');

                if ( jQuery(this).text() == 'Read More' ) {
                     jQuery(this).text('Read Less');
                 } else {            
                     jQuery(this).text('Read More');    
                 }
             });
             
            jQuery(document).ready(function () {
                jQuery(".lm-btn").on("click", function () {
                   var txt = jQuery(this).text();
                   if(txt == "Read more"){
                       jQuery(this).siblings(".lm-read-less").slideUp();
                       jQuery(this).siblings(".lm-read-more").slideDown("slow");
                       jQuery(this).text("Read less");
                   }else{
                       jQuery(this).siblings(".lm-read-more").slideUp("slow");
                       jQuery(this).siblings(".lm-read-less").slideDown();
                       jQuery(this).text("Read more");
                   }
                }),

                jQuery(".lm-accordion-title").on("click", function () {
                    jQuery(this).toggleClass("lm-active"), jQuery(this).siblings(".lm-accordion-content").slideToggle();
                });
            });

             <?php echo $lm_setting->lm_custom_js; ?>
        </script>
        <style type="text/css">
             /* Menu Area*/
            .lm-seo-footer{padding:6px 10px;border-radius:6px;width: auto;}
            .lm-near-me-menu ul li::before {content: '●';font-size: 25px;color: #fb6e39;float: left;line-height: 25px;padding-right: 4px;}
            .lm-dropdown-menu a{olor: #ffffff;text-decoration: none;font-size: 16px;font-weight: bold;}
            .lm-dropdown-menu a:hover{color: #bdb4eb;}

             /* Service Area Page*/
            .lm-community-title {font-size: 45px;color: #3661a1;font-weight: 700;margin: 0 0 20px 0;padding: 35px 0px 10px;line-height: normal;font-family: inherit;}             
            .lm-city-item:before{content: "\25AA";color: #3661a1;margin-right: 5px;line-height: .5;margin-top: 4px;font-size: 37px;float: left;}
            .lm-city-item{font-size: 20px;font-weight: 700;text-decoration: none;margin-top: 10px; float: left;}
            .lm-city-item a:hover{color: #d54300;text-decoration: none;} 
            .lm-city-item-block{padding-bottom: 50px;}
            .lm-community-map{margin: 80px 0px;}

            <?php echo $lm_setting->lm_custom_footer_css; ?>
        </style>
    <?php
    }
}