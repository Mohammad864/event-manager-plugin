<?php
/**
 * Plugin Name: Event Plugin
 * Description: Custom Plugin for managing events.
 * Version: 1.0
 * Author: Mohammad Taghipoor
 * Text Domain: event-plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define constants
define('EVENT_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include dependencies
require_once(EVENT_PLUGIN_DIR . 'includes/class-register-post-types.php');
require_once(EVENT_PLUGIN_DIR . 'includes/class-admin-interface.php');
require_once(EVENT_PLUGIN_DIR . 'includes/class-shortcodes.php');
require_once(EVENT_PLUGIN_DIR . 'includes/class-user-notifications.php');
require_once(EVENT_PLUGIN_DIR . 'includes/class-rest-api.php');
require_once(EVENT_PLUGIN_DIR . 'includes/class-event-filter.php');
require_once(EVENT_PLUGIN_DIR . 'includes/class-rsvp-handler.php');

// Initialize Plugin
add_action('plugins_loaded', 'event_plugin_init');
function event_plugin_init() {
    Register_Post_Types::init();
    Admin_Interface::init();
    Shortcodes::init();
    User_Notifications::init();
    REST_API::init();
    Event_Filter::init();
}

// Load templates for single and archive event pages
function event_manager_template_include($template) {
    if (is_singular('event')) {
        // Use the single-event.php template from the plugin
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-event.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    if (is_post_type_archive('event')) {
        // Use the archive-event.php template from the plugin
        $plugin_template = plugin_dir_path(__FILE__) . 'templates/archive-event.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('template_include', 'event_manager_template_include');

// Enqueue the custom CSS and JS files
function event_manager_enqueue_assets() {
    // Enqueue CSS file
    wp_enqueue_style('event-manager-styles', plugin_dir_url(__FILE__) . 'assets/css/event-styles.css');

    // Enqueue JavaScript file
    wp_enqueue_script('event-manager-script', plugin_dir_url(__FILE__) . 'assets/js/event-script.js', array('jquery'), null, true);

    // Localize the script for AJAX
    // Localize script to pass PHP data to JavaScript
    wp_localize_script('event-manager-script', 'eventManagerAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('event_filter_nonce_action')
    ));
}
add_action('wp_enqueue_scripts', 'event_manager_enqueue_assets');


register_activation_hook(__FILE__, 'create_rsvp_table');

function create_rsvp_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'event_rsvps'; // Name of the table

    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        event_id mediumint(9) NOT NULL,
        name tinytext NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}





