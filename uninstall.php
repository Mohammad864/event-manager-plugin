<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete custom post types and related metadata
$events = get_posts(array('post_type' => 'event', 'numberposts' => -1));
foreach ($events as $event) {
    wp_delete_post($event->ID, true);
}
