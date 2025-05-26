<?php
/*
Plugin Name: Autoposting for WordPress
Description: Posts generator with AI tools.
Version: 1.0
Author: George Kapanadze
*/
require_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/includes/class-autoposting-generator.php';
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Autoposting Generator class
// This class is responsible for generating posts using AI tools

if ( function_exists( 'plugin_dir_path' ) ) {
       require_once plugin_dir_path(__FILE__) . '/includes/class-autoposting-installer.php';
    require_once plugin_dir_path( __FILE__ ) . '/admin/class-autoposting-admin-page.php';
    require_once plugin_dir_path( __FILE__ ) . '/includes/class-autoposting-generator.php';
 
} else {
    require_once dirname(__FILE__) . '/includes/class-autoposting-installer.php';
    require_once dirname( __FILE__ ) . '/admin/class-autoposting-admin-page.php';
    require_once dirname( __FILE__ ) . '/includes/class-autoposting-generator.php';
}

register_activation_hook(__FILE__, ['Autoposting_Installer', 'install']);

new Autoposting_Admin_Page();

add_action('autoposting_generate', function() {
    $generator = new Autoposting_Generator();
    $generator->run();
});