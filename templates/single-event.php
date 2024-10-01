<?php get_header(); ?>

<div class="container mt-5">
    <div class="event-header text-center mb-5">
        <h1 class="event-title"><?php the_title(); ?></h1>
        <p class="event-details">
            <?php
            echo __('Date: ', 'event-plugin') . '<span>' . esc_html(get_post_meta(get_the_ID(), '_event_date', true)) . '</span><br>';
            echo __('Location: ', 'event-plugin') . '<span>' . esc_html(get_post_meta(get_the_ID(), '_event_location', true)) . '</span>';
            ?>
        </p>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="event-content card">
                <div class="card-body">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rsvp-form-wrapper card">
                <div class="card-body">
                    <h4 class="card-title text-center"><?php _e('RSVP for this Event', 'event-plugin'); ?></h4>
                    <?php echo do_shortcode('[rsvp_form]'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var rsvpForm = document.getElementById('rsvp-form');
        if (rsvpForm) {
            rsvpForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var formData = new FormData(rsvpForm);
                formData.append('action', 'handle_rsvp');
                formData.append('security', '<?php echo wp_create_nonce('rsvp_nonce'); ?>');

                // Prepare the request
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo esc_url(admin_url('admin-ajax.php')); ?>', true);

                // Set up a callback to handle the response
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                alert(response.data.message);
                                rsvpForm.reset();
                            } else {
                                alert(response.data.message);
                            }
                        } else {
                            alert('<?php _e("There was an error processing your request. Please try again.", "event-plugin"); ?>');
                        }
                    }
                };

                xhr.send(formData);
            });
        }
    });
</script>

<?php get_footer(); ?>
