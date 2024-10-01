<?php

use PHPUnit\Framework\TestCase;

class Test_Event_Manager_Unit extends TestCase {

    // Testing Event CPT Registration
    public function test_register_event_post_type() {
        $this->assertTrue(post_type_exists('event'), "Event custom post type should be registered.");
    }

    // Testing Event Type Taxonomy Registration
    public function test_register_event_taxonomy() {
        $this->assertTrue(taxonomy_exists('event_type'), "Event taxonomy should be registered.");
    }

    // Testing Meta Boxes
    public function test_event_meta_boxes() {
        global $wp_meta_boxes;
        $this->assertArrayHasKey('event_meta_box', $wp_meta_boxes['event'], "Event meta box should be registered.");
    }

    // Test RSVP Submission Validity
    public function test_validate_rsvp_submission() {
        $handler = new RSVP_Handler();
        $_POST = [
            'event_id'  => 1,
            'rsvp_name' => 'Jane Doe',
            'rsvp_email' => 'jane.doe@example.com'
        ];
        
        $this->assertTrue($handler->validate_rsvp_submission($_POST), "RSVP should be valid with correct data.");
    }

    // Test Empty RSVP Submission (Edge Case)
    public function test_validate_empty_rsvp_submission() {
        $handler = new RSVP_Handler();
        $_POST = [
            'event_id'  => 1,
            'rsvp_name' => '',
            'rsvp_email' => ''
        ];
        
        $this->assertFalse($handler->validate_rsvp_submission($_POST), "RSVP submission should be invalid if the fields are empty.");
    }

    // Test Invalid Email in RSVP (Edge Case)
    public function test_validate_invalid_email_rsvp_submission() {
        $handler = new RSVP_Handler();
        $_POST = [
            'event_id'  => 1,
            'rsvp_name' => 'Jane Doe',
            'rsvp_email' => 'invalid-email'
        ];
        
        $this->assertFalse($handler->validate_rsvp_submission($_POST), "RSVP submission should be invalid if the email is incorrectly formatted.");
    }
}
?>
