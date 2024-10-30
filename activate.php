<?php

function mrylm_create_tables() {

    global $wpdb;
    $mrylm_setting_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mrylm_setting(
                id int(11) unsigned NOT NULL auto_increment,
                lm_unique_id varchar(50) CHARACTER SET utf8 NOT NULL,                
                lm_url_type varchar(50) CHARACTER SET utf8 NULL,                
                lm_head_line varchar(150) CHARACTER SET utf8 NULL,                
                lm_display_type varchar(20) CHARACTER SET utf8 NULL,                
                lm_custom_css text CHARACTER SET utf8 NULL,    
                lm_custom_footer_css text CHARACTER SET utf8 NULL,
                lm_custom_js text CHARACTER SET utf8 NULL,
                lm_org_name varchar(150) CHARACTER SET utf8 NULL,                
                lm_org_type varchar(20) CHARACTER SET utf8 NULL,                
                lm_org_url varchar(150) CHARACTER SET utf8 NULL,                
                lm_page_url varchar(150) CHARACTER SET utf8 NULL,                
                lm_org_logo_url varchar(255) CHARACTER SET utf8 NULL,                
                lm_org_address varchar(100) CHARACTER SET utf8 NULL, 
                lm_org_address_line varchar(100) CHARACTER SET utf8 NULL, 
                lm_org_city varchar(50) CHARACTER SET utf8 NULL, 
                lm_org_state varchar(20) CHARACTER SET utf8 NULL, 
                lm_org_zipcode varchar(10) CHARACTER SET utf8 NULL,                
                lm_title_tag varchar(150) CHARACTER SET utf8 NULL,                
                lm_site_title varchar(150) CHARACTER SET utf8 NULL,                
                lm_meta_description text CHARACTER SET utf8, 
                lm_form_id varchar(100) CHARACTER SET utf8 NULL,
                lm_phone varchar(60) CHARACTER SET utf8 NULL,
                lm_keyword varchar(100) CHARACTER SET utf8 NULL,
                lm_keyword_separator varchar(50) CHARACTER SET utf8 NULL,
                lm_page_type varchar(50) CHARACTER SET utf8 NULL COMMENT '[base_page, sub_page]',
                lm_default_city varchar(50) CHARACTER SET utf8 NULL,
                lm_template_path varchar(100) CHARACTER SET utf8 NULL COMMENT '[template_directory, home_path, theme_file_path, other]',
                lm_template_other_path varchar(255) CHARACTER SET utf8 NULL,
                lm_api_url varchar(255) CHARACTER SET utf8 NULL,
                lm_is_replace_image varchar(255) CHARACTER SET utf8 NULL,
                lm_image_keyword text CHARACTER SET utf8 NULL,
                lm_is_job_post tinyint(1) DEFAULT '0',
                lm_latitude varchar(100) CHARACTER SET utf8 NULL,
                lm_longitude varchar(100) CHARACTER SET utf8 NULL,
                lm_job_headline varchar(100) CHARACTER SET utf8 NULL,
                lm_service_area_page_title varchar(150) CHARACTER SET utf8 NULL,
                lm_embed_code text CHARACTER SET utf8,
                lm_is_multi_location tinyint(1) DEFAULT '0',
                lm_is_service_area_page tinyint(1) DEFAULT '0',
                lm_is_point_of_interest tinyint(1) DEFAULT '0',
                lm_is_pause tinyint(1) DEFAULT '0',
                lm_is_rss_feed tinyint(1) DEFAULT '0',
                lm_is_multi_type tinyint(1) DEFAULT '0',   
                lm_multi_title text CHARACTER SET utf8 NULL,   
                lm_multi_meta_desc text CHARACTER SET utf8 NULL,   
                lm_multi_keyword text CHARACTER SET utf8 NULL,   
                lm_multi_head text CHARACTER SET utf8 NULL,   
                lm_multi_near_me text CHARACTER SET utf8 NULL,   
                PRIMARY KEY (id)
        );";

    $wpdb->query($mrylm_setting_sql);


    $mrylm_cities_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mrylm_cities(
                id int(11) unsigned NOT NULL auto_increment,
                lm_city_id int(11) NULL,
                lm_parent_id int(11) NULL,
                lm_city varchar(150) CHARACTER SET utf8 NULL,                
                lm_state varchar(100) CHARACTER SET utf8 NULL,                
                lm_slug varchar(100) CHARACTER SET utf8 NULL,  
                lm_url_slug varchar(150) CHARACTER SET utf8 NULL,      
                lm_google_map_id TEXT CHARACTER SET utf8 NULL,
                lm_form_id varchar(150) CHARACTER SET utf8 NULL,
                lm_city_keyword_separator varchar(100) CHARACTER SET utf8 NULL,
                lm_location varchar(100) CHARACTER SET utf8 NULL,
                lm_city_phone varchar(100) CHARACTER SET utf8 NULL,
                lm_city_mayor varchar(100) CHARACTER SET utf8 NULL,
                lm_population varchar(50) CHARACTER SET utf8 NULL,
                lm_avg_home_value varchar(50) CHARACTER SET utf8 NULL,
                lm_avg_household_income varchar(50) CHARACTER SET utf8 NULL,
                lm_avg_temperature varchar(50) CHARACTER SET utf8 NULL,
                lm_price_range varchar(50) CHARACTER SET utf8 NULL,
                lm_image varchar(255) CHARACTER SET utf8 NULL,
                lm_description TEXT CHARACTER SET utf8 NULL,
                lm_telephone varchar(50) CHARACTER SET utf8 NULL,
                lm_educational_institution TEXT CHARACTER SET utf8 NULL,
                lm_state_park TEXT CHARACTER SET utf8 NULL,
                lm_historic_landmark TEXT CHARACTER SET utf8 NULL,
                lm_assisted_living TEXT CHARACTER SET utf8 NULL,
                lm_restaurant TEXT CHARACTER SET utf8 NULL,
                PRIMARY KEY (id)
        );";

    $wpdb->query($mrylm_cities_sql);


    $mrylm_news_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mrylm_news(
                id int(11) unsigned NOT NULL auto_increment,
                lm_city_id int(11) NULL,
                lm_slug varchar(150) CHARACTER SET utf8 NULL,                 
                lm_city varchar(150) CHARACTER SET utf8 NULL,                
                lm_title varchar(255) CHARACTER SET utf8 NULL,                
                lm_news text CHARACTER SET utf8 NULL,              
                lm_detail_url varchar(255) CHARACTER SET utf8 NULL,              
                lm_author varchar(100) CHARACTER SET utf8 NULL,              
                lm_source varchar(100) CHARACTER SET utf8 NULL,              
                lm_created_at varchar(50) CHARACTER SET utf8 NULL,              
                lm_updated_at varchar(50) CHARACTER SET utf8 NULL,              
                PRIMARY KEY (id)
        );";

    $wpdb->query($mrylm_news_sql);

    // jobs_table
    $mrylm_jobs_sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mrylm_jobs(
                id int(11) unsigned NOT NULL auto_increment,
                title varchar(255) CHARACTER SET utf8 NOT NULL,                
                description text CHARACTER SET utf8,                
                post_date varchar(50) CHARACTER SET utf8 NOT NULL,                
                industry varchar(150) CHARACTER SET utf8 NOT NULL,                
                category varchar(150) CHARACTER SET utf8 NOT NULL,                
                employment_type varchar(150) CHARACTER SET utf8 NOT NULL,                
                work_hour varchar(150) CHARACTER SET utf8 NOT NULL,                
                hour_value varchar(100) CHARACTER SET utf8 NOT NULL,                
                responsibility text CHARACTER SET utf8,                
                edu_requirement text CHARACTER SET utf8,                
                work_experience text CHARACTER SET utf8,                
                skill text CHARACTER SET utf8,                
                benefit text CHARACTER SET utf8,                
                incentive text CHARACTER SET utf8,                
                job_url text CHARACTER SET utf8,                
                is_publish tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Publish, 0 = Unpublish',
                created_at datetime DEFAULT NULL,
                updated_at datetime DEFAULT NULL,            
                PRIMARY KEY (id)
        );";

    $wpdb->query($mrylm_jobs_sql);
}

function mrylm_update_tables() {

    global $wpdb;
    $mrylm_setting_table = $wpdb->prefix . 'mrylm_setting';
    $mrylm_cities_table = $wpdb->prefix . 'mrylm_cities';
    // v 1.7.0   
    // SETTING TABLE UPDATE
    // =========================================================================

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $mrylm_setting_table)) === $mrylm_setting_table) {
        $setting_table_exist_1 = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$mrylm_setting_table' AND column_name = 'lm_service_area_page_title'");
        if (empty($setting_table_exist_1)) {
            $setting_alter_sql_1 = "ALTER TABLE {$wpdb->prefix}mrylm_setting 
            ADD `lm_org_address_line` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_org_address`,
            ADD `lm_org_city` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_org_address_line`,
            ADD `lm_org_state` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_org_city`,
            ADD `lm_is_job_post` tinyint(1) NULL DEFAULT '0' AFTER `lm_image_keyword`,
            ADD `lm_latitude` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_is_job_post`,
            ADD `lm_longitude` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_latitude`,
            ADD `lm_job_headline` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_longitude`,
            ADD `lm_service_area_page_title` varchar(150) NULL  AFTER `lm_job_headline`,
            ADD `lm_embed_code` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `lm_service_area_page_title`, 
            ADD `lm_is_multi_location` tinyint(1) NULL DEFAULT '0' AFTER `lm_embed_code`,
            ADD `lm_is_service_area_page` tinyint(1) NULL DEFAULT '0'  AFTER `lm_is_multi_location`,
            ADD `lm_is_point_of_interest` TINYINT(1) NULL DEFAULT '0' AFTER `lm_is_service_area_page`
            ;";
            $wpdb->query($setting_alter_sql_1);
        }
    }

    // v:2.0   
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $mrylm_setting_table)) === $mrylm_setting_table) {
        $setting_table_exist_2 = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$mrylm_setting_table' AND column_name = 'lm_is_pause'");
        if (empty($setting_table_exist_2)) {
            $setting_alter_sql_2 = "ALTER TABLE {$wpdb->prefix}mrylm_setting 
            ADD `lm_is_pause` tinyint(1) NULL DEFAULT '0' AFTER `lm_is_point_of_interest`,           
            ADD `lm_is_rss_feed` tinyint(1) NULL DEFAULT '0' AFTER `lm_is_pause`           
            ;";
            $wpdb->query($setting_alter_sql_2);
        }
    }

    // v:2.2  
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $mrylm_setting_table)) === $mrylm_setting_table) {
        $setting_table_exist_3 = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$mrylm_setting_table' AND column_name = 'lm_is_multi_type'");
        if (empty($setting_table_exist_3)) {
            $setting_alter_sql_3 = "ALTER TABLE {$wpdb->prefix}mrylm_setting 
            ADD `lm_is_multi_type` tinyint(1) NULL DEFAULT '0' AFTER `lm_is_rss_feed`,           
            ADD `lm_multi_title` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_is_multi_type`,           
            ADD `lm_multi_meta_desc` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_multi_title`,           
            ADD `lm_multi_keyword` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_multi_meta_desc`,           
            ADD `lm_multi_head` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_multi_keyword`,           
            ADD `lm_multi_near_me` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_multi_head`        
            ;";
            $wpdb->query($setting_alter_sql_3);
        }
    }




    // v 1.7.0 
    // CITIES TABLE UPDATE
    // =========================================================================   
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $mrylm_cities_table)) === $mrylm_cities_table) {

        $cities_table_exist_1 = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$mrylm_cities_table' AND column_name = 'lm_city_mayor'");
        if (empty($cities_table_exist_1)) {
            $cities_alter_sql_1 = "ALTER TABLE {$wpdb->prefix}mrylm_cities 
            ADD `lm_city_mayor` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_city_phone`,
            ADD `lm_population` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_city_mayor`, 
            ADD `lm_avg_home_value` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_population`,
            ADD `lm_avg_household_income` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_avg_home_value`,
            ADD `lm_avg_temperature` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_avg_household_income`,
            ADD `lm_price_range` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_avg_temperature`,
            ADD `lm_image` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_price_range`,
            ADD `lm_description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_image`,
            ADD `lm_telephone` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_description`,
            ADD `lm_educational_institution` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_telephone`,
            ADD `lm_state_park` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_educational_institution`,
            ADD `lm_historic_landmark` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_state_park`,
            ADD `lm_city_keyword_separator` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_form_id`,
            ADD `lm_location` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_city_keyword_separator`
            ;";
            $wpdb->query($cities_alter_sql_1);

            $sql = 'ALTER TABLE ' . $mrylm_cities_table . ' CHANGE `lm_google_map_id` `lm_google_map_id` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;';
            $wpdb->query($sql);
        }
    }


    // V.2.3  
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $mrylm_cities_table)) === $mrylm_cities_table) {

        $cities_table_exist_2 = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$mrylm_cities_table' AND column_name = 'lm_assisted_living'");
        if (empty($cities_table_exist_2)) {
            $cities_alter_sql_2 = "ALTER TABLE {$wpdb->prefix}mrylm_cities 
            ADD `lm_assisted_living` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_historic_landmark`,
            ADD `lm_restaurant` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `lm_assisted_living`
            ;";
            $wpdb->query($cities_alter_sql_2);
        }
    }

    mrylm_create_tables();
}

function create_service_area_page($lm_setting) {

    $word = "alwaysbestcare.com";
    $mystring = $lm_setting->lm_org_url;

    $page_title = $lm_setting->lm_service_area_page_title ? $lm_setting->lm_service_area_page_title : 'Communities Served';
    $page_slug = $lm_setting->lm_service_area_page_title ? $lm_setting->lm_service_area_page_title : 'communities-served';

    $lm_post_id = url_to_postid($lm_setting->lm_org_url . $page_slug);

    $mrylm_post = array(
        'ID' => $lm_post_id,
        'post_title' => $page_title,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_parent' => 0,
        'post_category' => array()
    );

    $lm_insert_id = wp_insert_post($mrylm_post);

    // to set template for the page 
    if (strpos($mystring, $word) !== false) {
        if (!add_post_meta($lm_insert_id, '_wp_page_template', 'local-magic-area.php', true)) {
            update_post_meta($lm_insert_id, '_wp_page_template', 'local-magic-area.php');
        }
    } else {
        if (!add_post_meta($lm_insert_id, '_wp_page_template', 'local-magic.php', true)) {
            update_post_meta($lm_insert_id, '_wp_page_template', 'local-magic.php');
        }
    }
}