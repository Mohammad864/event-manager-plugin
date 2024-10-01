<?php
class Admin_Interface {
    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'add_event_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_event_meta'));
        add_filter('manage_event_posts_columns', array(__CLASS__, 'set_custom_event_columns'));
        add_action('manage_event_posts_custom_column', array(__CLASS__, 'custom_event_column'), 10, 2);
        add_filter('manage_edit-event_sortable_columns', array(__CLASS__, 'sortable_columns'));
        add_action('pre_get_posts', array(__CLASS__, 'handle_sorting'));
    }

    public static function add_event_meta_boxes() {
        add_meta_box('event_details', __('Event Details', 'event-plugin'), array(__CLASS__, 'render_event_meta_box'), 'event', 'side', 'default');
    }

    public static function render_event_meta_box($post) {
        $event_date = get_post_meta($post->ID, '_event_date', true);
        $event_location = get_post_meta($post->ID, '_event_location', true);

        wp_nonce_field('event_nonce_action', 'event_nonce');

        echo '<label for="event_date">' . __('Event Date', 'event-plugin') . '</label>';
        echo '<input type="date" id="event_date" name="event_date" value="' . esc_attr($event_date) . '" />';
        echo '<label for="event_location">' . __('Event Location', 'event-plugin') . '</label>';
        echo '<input type="text" id="event_location" name="event_location" value="' . esc_attr($event_location) . '" />';
    }

    public static function save_event_meta($post_id) {
        if (!isset($_POST['event_nonce']) || !wp_verify_nonce($_POST['event_nonce'], 'event_nonce_action')) {
            return;
        }

        if (isset($_POST['event_date'])) {
            update_post_meta($post_id, '_event_date', sanitize_text_field($_POST['event_date']));
        }

        if (isset($_POST['event_location'])) {
            update_post_meta($post_id, '_event_location', sanitize_text_field($_POST['event_location']));
        }
    }

    public static function set_custom_event_columns($columns) {
        $columns['event_date'] = __('Event Date', 'event-plugin');
        $columns['event_location'] = __('Location', 'event-plugin');
        $columns['rsvp_count'] = __('RSVP Count', 'event-plugin');
        return $columns;
    }

    public static function custom_event_column($column, $post_id) {
        switch ($column) {
            case 'event_date':
                echo esc_html(get_post_meta($post_id, '_event_date', true));
                break;
            case 'event_location':
                echo esc_html(get_post_meta($post_id, '_event_location', true));
                break;
            case 'rsvp_count':
                global $wpdb;
                $table_name = $wpdb->prefix . 'event_rsvps';
                $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE event_id = %d", $post_id));
                echo $count ? intval($count) : __('No RSVPs', 'event-plugin');
                break;
        }
    }

    // Make custom columns sortable
    public static function sortable_columns($columns) {
        $columns['event_date'] = 'event_date';
        return $columns;
    }

    // Handle sorting by event date
    public static function handle_sorting($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        if ('event_date' === $query->get('orderby')) {
            $query->set('meta_key', '_event_date');
            $query->set('orderby', 'meta_value');
        }
    }
}

Admin_Interface::init();
?>
