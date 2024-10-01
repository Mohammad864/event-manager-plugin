<?php
class Event_Filter {
    public static function init() {
        add_action('wp_ajax_filter_events', array(__CLASS__, 'filter_events'));
        add_action('wp_ajax_nopriv_filter_events', array(__CLASS__, 'filter_events'));
    }

    public static function filter_events() {
        // Input validation and sanitization
        $event_type = isset($_POST['event_type']) ? sanitize_text_field($_POST['event_type']) : '';
        $event_date = isset($_POST['event_date']) ? sanitize_text_field($_POST['event_date']) : '';

        $args = array(
            'post_type' => 'event',
            'posts_per_page' => 10,
        );

        if ($event_type) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'event_type',
                    'field'    => 'slug',
                    'terms'    => $event_type,
                ),
            );
        }

        if ($event_date) {
            $args['meta_query'] = array(
                array(
                    'key'     => '_event_date',
                    'value'   => $event_date,
                    'compare' => '=',
                ),
            );
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post(); ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php the_post_thumbnail_url('medium'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php the_title(); ?></h5>
                            <p class="card-text">
                                <?php
                                echo __('Date: ', 'event-plugin') . esc_html(get_post_meta(get_the_ID(), '_event_date', true)) . '<br>';
                                echo __('Location: ', 'event-plugin') . esc_html(get_post_meta(get_the_ID(), '_event_location', true));
                                ?>
                            </p>
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary"><?php _e('View Details', 'event-plugin'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endwhile;
        else : ?>
            <p class="text-center"><?php _e('No events found', 'event-plugin'); ?></p>
        <?php endif;

        wp_die();
    }
}
