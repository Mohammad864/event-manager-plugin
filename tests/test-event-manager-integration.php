<?php

use PHPUnit\Framework\TestCase;

class Test_Event_Manager_Integration extends TestCase {

    // Test Custom Post Type and Taxonomy Connection
    public function test_event_to_taxonomy_relationship() {
        $event_id = wp_insert_post([
            'post_title' => 'Test Event',
            'post_type' => 'event',
            'post_status' => 'publish'
        ]);

        $term = wp_insert_term('Conference', 'event_type');
        wp_set_object_terms($event_id, $term['term_id'], 'event_type');

        $terms = wp_get_post_terms($event_id, 'event_type');
        $this->assertEquals('Conference', $terms[0]->name, "The event should have an 'Conference' taxonomy term.");
    }

    // Test RSVP Workflow Integration
    public function test_rsvp_workflow() {
        // Insert Event
        $event_id = wp_insert_post([
            'post_title' => 'Integration Test Event',
            'post_type' => 'event',
            'post_status' => 'publish'
        ]);

        // Submit RSVP
        $_POST = [
            'event_id'  => $event_id,
            'rsvp_name' => 'John Doe',
            'rsvp_email' => 'john.doe@example.com',
        ];

        ob_start();
        RSVP_Handler::handle_rsvp();
        $output = ob_get_clean();

        $this->assertStringContainsString('Thank you for your RSVP!', $output, "RSVP should return success message.");

        // Check RSVP Saved in Database
        $rsvps = get_post_meta($event_id, '_event_rsvps', true);
        $this->assertNotEmpty($rsvps, "RSVP should be saved in the event meta.");
    }

    // Test Email Notifications Integration
    public function test_email_notification_sent() {
        $this->expectOutputRegex('/Email notification sent to john.doe@example.com/');
        
        // Simulate publish event which triggers notification
        do_action('publish_event', 1);
    }
}
?>
