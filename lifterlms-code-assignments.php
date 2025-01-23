<?php
/*
Plugin Name: LifterLMS Code Assignments
Plugin URI: https://brianalonzo.com
Description: Adds coding assignments to LifterLMS courses with an on-page code editor and auto-grading functionality.
Version: 1.0.1
Author: Brian Alonzo
Author URI: https://brianalonzo.com
License: GPL2
*/

// Plugin activation hook to create database table
function create_assignment_progress_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lifterlms_assignment_progress';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        assignment_id BIGINT(20) UNSIGNED NOT NULL,
        progress_text LONGTEXT NOT NULL,
        last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY user_assignment (user_id, assignment_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'create_assignment_progress_table');

// AJAX handler for saving progress
function save_assignment_progress() {
    global $wpdb;

    $user_id = get_current_user_id();
    $assignment_id = intval($_POST['assignment_id']);
    $progress_text = sanitize_textarea_field($_POST['progress_text']);
    
    $table_name = $wpdb->prefix . 'lifterlms_assignment_progress';

    $existing_entry = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND assignment_id = %d",
        $user_id, $assignment_id
    ));

    if ($existing_entry) {
        $wpdb->update(
            $table_name,
            array('progress_text' => $progress_text, 'last_updated' => current_time('mysql')),
            array('user_id' => $user_id, 'assignment_id' => $assignment_id),
            array('%s', '%s'),
            array('%d', '%d')
        );
    } else {
        $wpdb->insert(
            $table_name,
            array('user_id' => $user_id, 'assignment_id' => $assignment_id, 'progress_text' => $progress_text),
            array('%d', '%d', '%s')
        );
    }

    wp_send_json_success('Progress saved successfully!');
}
add_action('wp_ajax_save_assignment_progress', 'save_assignment_progress');

// Function to retrieve progress
function get_assignment_progress($user_id, $assignment_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lifterlms_assignment_progress';

    $progress = $wpdb->get_var($wpdb->prepare(
        "SELECT progress_text FROM $table_name WHERE user_id = %d AND assignment_id = %d",
        $user_id, $assignment_id
    ));

    return $progress ?: '';
}


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-coding-assignment.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-submission-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode-renderer.php';


// Register shortcode for displaying assignments
function llms_code_assignments_init() {
    add_shortcode('llms_code_assignment', ['Shortcode_Renderer', 'render_assignment']);
}
add_action('init', 'llms_code_assignments_init');

// Load assets and localize script
function llms_code_assignments_assets() {
    // CodeMirror CSS & JS (CDN)
    wp_enqueue_style('codemirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.css');
    wp_enqueue_style('codemirror-theme', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/theme/dracula.min.css');
    wp_enqueue_script('codemirror-js', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.js', [], null, true);
    wp_enqueue_script('codemirror-mode-js', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/javascript/javascript.min.js', [], null, true);

    // Load custom styles and scripts
    wp_enqueue_style('llms-code-assignments-style', plugin_dir_url(__FILE__) . 'assets/styles.css');
    wp_enqueue_script('llms-code-assignments-script', plugin_dir_url(__FILE__) . 'assets/editor.js', ['jquery', 'codemirror-js'], null, true);

    wp_localize_script('llms-code-assignments-script', 'ajax_object', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'llms_code_assignments_assets');


// Activation Hook
function llms_code_assignments_activation() {
    if (!is_plugin_active('lifterlms/lifterlms.php')) {
        wp_die('LifterLMS must be installed and activated to use this plugin.');
    }
}
register_activation_hook(__FILE__, 'llms_code_assignments_activation');

?>
