<?php
class Shortcodes {
    public static function init() {
        add_shortcode('event_list', array(__CLASS__, 'event_list_shortcode'));
    }

    public static function event_list_shortcode($atts) {
        // Process filter form submission
        $search_query = isset($_GET['event_search']) ? sanitize_text_field($_GET['event_search']) : '';
        $type = isset($_GET['event_type']) ? sanitize_text_field($_GET['event_type']) : '';
        $start_date = isset($_GET['event_start_date']) ? sanitize_text_field($_GET['event_start_date']) : '';
        $end_date = isset($_GET['event_end_date']) ? sanitize_text_field($_GET['event_end_date']) : '';

        // Query events based on filtering criteria
        $args = array(
            'post_type' => 'event',
            'posts_per_page' => 10,
            's' => $search_query,
        );

        // Taxonomy filtering
        if ($type) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'event_type',
                    'field'    => 'slug',
                    'terms'    => $type,
                ),
            );
        }

        // Date range filtering
        if ($start_date || $end_date) {
            $meta_query = array('relation' => 'AND');

            if ($start_date) {
                $meta_query[] = array(
                    'key'     => '_event_date',
                    'value'   => $start_date,
                    'compare' => '>=',
                    'type'    => 'DATE',
                );
            }

            if ($end_date) {
                $meta_query[] = array(
                    'key'     => '_event_date',
                    'value'   => $end_date,
                    'compare' => '<=',
                    'type'    => 'DATE',
                );
            }

            $args['meta_query'] = $meta_query;
        }

        // Debugging
        error_log(print_r($args, true));

        $events = new WP_Query($args);
        ob_start();

        // Render filter form
        ?>
        <form id="event-filter" method="GET" action="<?php echo esc_url(get_permalink()); ?>">
            <input type="hidden" name="post_type" value="event" />

            <label for="event_search"><?php _e('Search:', 'event-plugin'); ?></label>
            <input type="text" id="event_search" name="event_search" placeholder="<?php _e('Search Events...', 'event-plugin'); ?>" value="<?php echo esc_attr($search_query); ?>" />

            <label for="event_type"><?php _e('type:', 'event-plugin'); ?></label>
            <select name="event_type" id="event_type">
                <option value=""><?php _e('Select type', 'event-plugin'); ?></option>
                <?php
                $categories = get_terms(array('taxonomy' => 'event_type', 'hide_empty' => true));
                foreach ($categories as $cat) {
                    echo '<option value="' . esc_attr($cat->slug) . '"' . selected($type, $cat->slug, false) . '>' . esc_html($cat->name) . '</option>';
                }
                ?>
            </select>

            <label for="event_start_date"><?php _e('Start Date:', 'event-plugin'); ?></label>
            <input type="date" id="event_start_date" name="event_start_date" value="<?php echo esc_attr($start_date); ?>" />

            <label for="event_end_date"><?php _e('End Date:', 'event-plugin'); ?></label>
            <input type="date" id="event_end_date" name="event_end_date" value="<?php echo esc_attr($end_date); ?>" />

            <input type="submit" value="<?php _e('Filter', 'event-plugin'); ?>" />
        </form>
        <?php

        // Render event list
        if ($events->have_posts()) {
            echo '<ul>';
            while ($events->have_posts()) {
                $events->the_post();
                echo '<li><a href="' . get_the_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No events found.', 'event-plugin') . '</p>';
        }

        wp_reset_postdata();
        return ob_get_clean();
    }
}

Shortcodes::init();









class Event_Filtering {
    public static function init() {
        add_shortcode('event_search_filter', array(__CLASS__, 'event_search_filter_shortcode'));
    }

    public static function event_search_filter_shortcode($atts) {
        // Handle form inputs securely
        $search_query = isset($_GET['event_search']) ? sanitize_text_field($_GET['event_search']) : '';
        $type = isset($_GET['event_type']) ? sanitize_text_field($_GET['event_type']) : '';
        $start_date = isset($_GET['event_start_date']) ? sanitize_text_field($_GET['event_start_date']) : '';
        $end_date = isset($_GET['event_end_date']) ? sanitize_text_field($_GET['event_end_date']) : '';

        // Transient caching to reduce database load
        $cache_key = 'event_search_' . md5(serialize($_GET));
        $events = get_transient($cache_key);

        if (false === $events) {
            // Query Arguments
            $args = array(
                'post_type' => 'event',
                'posts_per_page' => 10,
                's' => $search_query,
                'no_found_rows' => true, // Disables pagination count for better performance
                'fields' => 'ids', // Only select IDs for better memory use
            );

            // Taxonomy filtering (by event type)
            if ($type) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'event_type',
                        'field'    => 'slug',
                        'terms'    => $type,
                    ),
                );
            }

            // Meta query for date range filtering
            if ($start_date || $end_date) {
                $meta_query = array('relation' => 'AND');

                if ($start_date) {
                    $meta_query[] = array(
                        'key'     => '_event_date',
                        'value'   => $start_date,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    );
                }

                if ($end_date) {
                    $meta_query[] = array(
                        'key'     => '_event_date',
                        'value'   => $end_date,
                        'compare' => '<=',
                        'type'    => 'DATE',
                    );
                }

                $args['meta_query'] = $meta_query;
            }

            $events_query = new WP_Query($args);
            $events = $events_query->posts;

            // Set transient for caching (30 minutes)
            set_transient($cache_key, $events, 30 * MINUTE_IN_SECONDS);
        }

        // Security Nonce for form
        $nonce_field = wp_nonce_field('event_filter_nonce_action', 'event_filter_nonce', true, false);

        ob_start();

        // Render filter form
        ?>
        <div class="event-filter-container">
            <form id="event-filter" method="GET" action="<?php echo esc_url(get_permalink()); ?>">
                <?php echo $nonce_field; ?>
                <div class="form-group">
                    <label for="event_search"><?php _e('Search:', 'event-plugin'); ?></label>
                    <input type="text" id="event_search" name="event_search" placeholder="<?php _e('Search Events...', 'event-plugin'); ?>" value="<?php echo esc_attr($search_query); ?>" />
                </div>
                <div class="form-group">
                    <label for="event_type"><?php _e('type:', 'event-plugin'); ?></label>
                    <select name="event_type" id="event_type">
                        <option value=""><?php _e('Select type', 'event-plugin'); ?></option>
                        <?php
                        $categories = get_terms(array('taxonomy' => 'event_type', 'hide_empty' => true));
                        foreach ($categories as $cat) {
                            echo '<option value="' . esc_attr($cat->slug) . '"' . selected($type, $cat->slug, false) . '>' . esc_html($cat->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="event_start_date"><?php _e('Start Date:', 'event-plugin'); ?></label>
                    <input type="date" id="event_start_date" name="event_start_date" value="<?php echo esc_attr($start_date); ?>" />
                </div>
                <div class="form-group">
                    <label for="event_end_date"><?php _e('End Date:', 'event-plugin'); ?></label>
                    <input type="date" id="event_end_date" name="event_end_date" value="<?php echo esc_attr($end_date); ?>" />
                </div>
                <div class="form-group">
                    <input type="submit" value="<?php _e('Filter', 'event-plugin'); ?>" class="btn btn-primary" />
                </div>
            </form>
        </div>

        <?php
        // Render filtered event list
        echo '<div class="event-list">';
        if (!empty($events)) {
            echo '<ul>';
            foreach ($events as $event_id) {
                $event_date = get_post_meta($event_id, '_event_date', true);
                echo '<li>';
                echo '<a href="' . get_permalink($event_id) . '">' . get_the_title($event_id) . '</a>';
                echo ' - ' . esc_html($event_date);
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No events found.', 'event-plugin') . '</p>';
        }
        echo '</div>';

        return ob_get_clean();
    }
}

Event_Filtering::init();