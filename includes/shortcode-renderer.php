<?php
class Shortcode_Renderer {
    public static function render_assignment($atts) {
        // Extract shortcode attributes and ensure 'id' is provided
        $atts = shortcode_atts(['id' => ''], $atts);
        $assignment_id = intval($atts['id']);

        if (!$assignment_id) {
            return '<p style="color:red;">Error: Assignment ID is missing or invalid.</p>';
        }

        // Fetch correct answer to verify it's pulling the right data
        $correct_answer = get_post_meta($assignment_id, 'correct_answer', true);

        if (empty($correct_answer)) {
            return '<p style="color:red;">Error: No correct answer set for this assignment.</p>';
        }

        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/coding-assignment-template.php';
        return ob_get_clean();
    }
}
?>
