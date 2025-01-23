<?php
class Submission_Handler {
    public static function handle_submission() {
        if (isset($_POST['submitted_code']) && isset($_POST['assignment_id'])) {
            $user_id = get_current_user_id();
            $assignment_id = intval($_POST['assignment_id']);
            $submitted_code = trim(sanitize_textarea_field($_POST['submitted_code']));
            $correct_code = trim(get_post_meta($assignment_id, 'correct_answer', true));

            if ($submitted_code === $correct_code) {
                update_user_meta($user_id, 'assignment_' . $assignment_id . '_status', 'completed');
                wp_send_json_success(['message' => 'Correct! Assignment completed.']);
            } else {
                wp_send_json_error(['message' => 'Incorrect answer, try again.']);
            }
        } else {
            wp_send_json_error(['message' => 'Invalid request. Please try again.']);
        }
    }
}
add_action('wp_ajax_submit_code', ['Submission_Handler', 'handle_submission']);
add_action('wp_ajax_nopriv_submit_code', ['Submission_Handler', 'handle_submission']);
?>
