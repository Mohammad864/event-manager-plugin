<?php
class User_Notifications {
    public static function init() {
        // Hook into event creation and updating to send notifications
        add_action('wp_insert_post', array(__CLASS__, 'send_event_notification'), 10, 2);
    }

    public static function send_event_notification($ID, $post) {
        // Only run for events
        if ($post->post_type !== 'event') {
            return;
        }

        // Ensure that the post is published
        if ($post->post_status !== 'publish') {
            return;
        }

        // Get the previous status of the post
        $previous_post = get_post($ID);
        if ($previous_post) {
            // Check if the previous post status was not 'publish'
            $was_previously_published = $previous_post->post_status === 'publish';
        } else {
            // If the post doesn't exist, assume it's a new post
            $was_previously_published = false;
        }

        // Get users who need to be notified
        $users = get_users(); // You may add conditions to target specific users.

        // Event details to include in the email
        $event_title = get_the_title($ID);
        $event_link = get_permalink($ID);
        $event_date = get_post_meta($ID, 'event_date', true); // Assuming 'event_date' is stored as metadata.

        if (!$was_previously_published) {
            // Customize the email content for new events
            $email_subject = __('New Event Published: ', 'event-plugin') . $event_title;
            $email_body = sprintf(
                __('Hello %s,' . "\n\n" . 'A new event has been published: %s.' . "\n" . 
                'Date: %s' . "\n" . 
                'You can view the event here: %s' . "\n\n" . 
                'Best Regards,' . "\n" . 'The Event Team', 'event-plugin'),
                $user->display_name,
                $event_title,
                $event_date,
                $event_link
            );
        } else {
            // Customize the email content for updated events
            $email_subject = __('Event Updated: ', 'event-plugin') . $event_title;
            $email_body = sprintf(
                __('Hello %s,' . "\n\n" . 'The event has been updated: %s.' . "\n" . 
                'Date: %s' . "\n" . 
                'You can view the updated event here: %s' . "\n\n" . 
                'Best Regards,' . "\n" . 'The Event Team', 'event-plugin'),
                $user->display_name,
                $event_title,
                $event_date,
                $event_link
            );
        }

        // Send the email
        foreach ($users as $user) {
            wp_mail($user->user_email, $email_subject, $email_body);
        }
    }
}

User_Notifications::init();