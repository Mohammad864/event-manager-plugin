<?php
class Register_Post_Types {
    public static function init() {
        add_action('init', array(__CLASS__, 'register_event_post_type'));
    }

    public static function register_event_post_type() {
        $labels = array(
            'name' => __('Events', 'event-plugin'),
            'singular_name' => __('Event', 'event-plugin'),
            // Other labels...
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        );

        register_post_type('event', $args);

        // Register custom taxonomy
        $tax_labels = array(
            'name' => __('Event Types', 'event-plugin'),
            'singular_name' => __('Event Type', 'event-plugin'),
        );

        $tax_args = array(
            'labels' => $tax_labels,
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        );

        register_taxonomy('event_type', 'event', $tax_args);
    }
}
