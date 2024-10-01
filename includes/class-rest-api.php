<?php
class REST_API {
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_rest_fields'));
    }

    public static function register_rest_fields() {
        register_rest_field('event', 'event_meta', array(
            'get_callback' => function ($object) {
                return array(
                    'date' => get_post_meta($object['id'], '_event_date', true),
                    'location' => get_post_meta($object['id'], '_event_location', true),
                );
            },
        ));
    }
}
