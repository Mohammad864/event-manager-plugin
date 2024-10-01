<?php
class RSVP_Handler {
    public static function init() {
        // Register REST API route for RSVP submission
        add_action('rest_api_init', array(__CLASS__, 'register_rest_routes'));
        
        // Register the RSVP shortcode
        add_shortcode('rsvp_form', array(__CLASS__, 'render_rsvp_form'));

        // Add admin menu to view RSVPs
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
    }

    public static function register_rest_routes() {
        register_rest_route('event-plugin/v1', '/rsvp', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_rsvp_submission'),
            'permission_callback' => '__return_true', // Can add capability checks if needed
        ));
    }

    public static function render_rsvp_form() {
        ob_start();
        ?>
        <form id="rsvp-form">
            <input type="text" name="rsvp_name" placeholder="Your Name" required>
            <input type="email" name="rsvp_email" placeholder="Your Email" required>
            <input type="hidden" name="event_id" value="<?php echo get_the_ID(); ?>">
            <button type="submit">RSVP Now</button>
        </form>
        <div id="rsvp-message"></div>

        <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('rsvp-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    rsvp_name: form.rsvp_name.value,
                    rsvp_email: form.rsvp_email.value,
                    event_id: form.event_id.value
                };
                
                fetch('<?php echo esc_url(rest_url('event-plugin/v1/rsvp')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData),
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('rsvp-message').textContent = data.message;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('rsvp-message').textContent = 'An error occurred. Please try again.';
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public static function handle_rsvp_submission($request) {
        // Get parameters from the request
        $event_id = $request->get_param('event_id');
        $rsvp_name = sanitize_text_field($request->get_param('rsvp_name'));
        $rsvp_email = sanitize_email($request->get_param('rsvp_email'));

        if ($event_id && $rsvp_name && $rsvp_email) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'event_rsvps';

            // Insert the RSVP into the database
            $result = $wpdb->insert($table_name, array(
                'event_id' => $event_id,
                'name' => $rsvp_name,
                'email' => $rsvp_email,
                'created_at' => current_time('mysql'),
            ));

            if ($result) {
                return new WP_REST_Response(array('message' => __('Thank you for your RSVP!', 'event-plugin')), 200);
            } else {
                return new WP_REST_Response(array('message' => __('Database error: Unable to save RSVP.', 'event-plugin')), 500);
            }
        } else {
            return new WP_REST_Response(array('message' => __('Invalid data. Please try again.', 'event-plugin')), 400);
        }
    }

    public static function add_admin_menu() {
        add_menu_page('Event RSVPs', 'Event RSVPs', 'manage_options', 'event_rsvps', array(__CLASS__, 'display_rsvps'), 'dashicons-tickets', 6);
    }

    public static function display_rsvps() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'event_rsvps';

        $rsvps = $wpdb->get_results("SELECT * FROM $table_name");

        echo '<div class="wrap"><h1>' . __('Event RSVPs', 'event-plugin') . '</h1>';
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>' . __('Event ID', 'event-plugin') . '</th><th>' . __('Name', 'event-plugin') . '</th><th>' . __('Email', 'event-plugin') . '</th><th>' . __('Date', 'event-plugin') . '</th></tr></thead>';
        echo '<tbody>';

        if ($rsvps) {
            foreach ($rsvps as $rsvp) {
                echo '<tr>';
                echo '<td>' . esc_html($rsvp->event_id) . '</td>';
                echo '<td>' . esc_html($rsvp->name) . '</td>';
                echo '<td>' . esc_html($rsvp->email) . '</td>';
                echo '<td>' . esc_html($rsvp->created_at) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="4">' . __('No RSVPs found.', 'event-plugin') . '</td></tr>';
        }

        echo '</tbody></table></div>';
    }
}

RSVP_Handler::init();
?>
