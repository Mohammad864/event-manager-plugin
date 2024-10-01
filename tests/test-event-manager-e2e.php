<?php

use PHPUnit\Framework\TestCase;

class Test_Event_Manager_E2E extends TestCase {

    // E2E Test: Create an Event, Add RSVP, and Validate Output
    public function test_create_event_and_rsvp_flow() {
        // Create Event
        $event_id = wp_insert_post([
            'post_title' => 'E2E Test Event',
            'post_content' => 'Event description for E2E testing.',
            'post_type' => 'event',
            'post_status' => 'publish'
        ]);

        $this->assertNotEmpty($event_id, "Event should be successfully created.");
        
        // Set Taxonomy
        wp_set_object_terms($event_id, 'Webinar', 'event_type');
        $terms = wp_get_post_terms($event_id, 'event_type');
        $this->assertEquals('Webinar', $terms[0]->name, "The taxonomy 'Webinar' should be set for the event.");

        // RSVP Submission
        $_POST = [
            'event_id'  => $event_id,
            'rsvp_name' => 'Jane Doe',
            'rsvp_email' => 'jane.doe@example.com',
        ];
        ob_start();
        RSVP_Handler::handle_rsvp();
        $rsvp_output = ob_get_clean();

        // Check Response and Database Entry
        $this->assertStringContainsString('Thank you for your RSVP!', $rsvp_output, "RSVP should be acknowledged on the page.");
        $rsvps = get_post_meta($event_id, '_event_rsvps', true);
        $this->assertNotEmpty($rsvps, "RSVP should be saved in the database.");
    }

    // E2E Test: Filtering Events
    public function test_filter_events_by_taxonomy() {
        // Create Events
        wp_insert_post([
            'post_title' => 'Tech Talk',
            'post_type' => 'event',
            'post_status' => 'publish',
            'tax_input' => ['event_type' => ['Technology']]
        ]);
        wp_insert_post([
            'post_title' => 'Art Show',
            'post_type' => 'event',
            'post_status' => 'publish',
            'tax_input' => ['event_type' => ['Art']]
        ]);

        // Filter by "Technology"
        $_GET['event_type'] = 'Technology';
        ob_start();
        Event_Filter::render_filtered_events();
        $filtered_output = ob_get_clean();

        $this->assertStringContainsString('Tech Talk', $filtered_output, "Filtered results should display the event 'Tech Talk'.");
        $this->assertStringNotContainsString('Art Show', $filtered_output, "'Art Show' should not appear in filtered results for 'Technology'.");
    }
}
?>
