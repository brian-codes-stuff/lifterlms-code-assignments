<?php
/*
Plugin Name: LifterLMS Code Assignments
Plugin URI: https://brianalonzo.com
Description: Adds coding assignments to LifterLMS courses with an on-page code editor and auto-grading functionality.
Version: 1.0.2
Author: Brian Alonzo
Author URI: https://brianalonzo.com
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

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

// Function to retrieve saved progress
function get_assignment_progress($user_id, $assignment_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'lifterlms_assignment_progress';

    $progress = $wpdb->get_var($wpdb->prepare(
        "SELECT progress_text FROM $table_name WHERE user_id = %d AND assignment_id = %d",
        $user_id, $assignment_id
    ));

    return $progress ?: '';
}

// AJAX handler to save assignment progress
function save_assignment_progress() {
    global $wpdb;

    $user_id = get_current_user_id();
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : 0;
    $progress_text = isset($_POST['progress_text']) ? wp_unslash(sanitize_textarea_field($_POST['progress_text'])) : '';

    if (empty($assignment_id) || empty($user_id)) {
        wp_send_json_error(['message' => 'Invalid request data.']);
        wp_die();
    }

    $table_name = $wpdb->prefix . 'lifterlms_assignment_progress';

    $existing_entry = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND assignment_id = %d",
        $user_id, $assignment_id
    ));

    if ($existing_entry) {
        $updated = $wpdb->update(
            $table_name,
            ['progress_text' => $progress_text, 'last_updated' => current_time('mysql')],
            ['user_id' => $user_id, 'assignment_id' => $assignment_id],
            ['%s', '%s'],
            ['%d', '%d']
        );

        if ($updated !== false) {
            wp_send_json_success(['message' => 'Progress updated successfully!']);
        } else {
            wp_send_json_error(['message' => 'Error updating progress.']);
        }
    } else {
        $inserted = $wpdb->insert(
            $table_name,
            ['user_id' => $user_id, 'assignment_id' => $assignment_id, 'progress_text' => $progress_text],
            ['%d', '%d', '%s']
        );

        if ($inserted) {
            wp_send_json_success(['message' => 'Progress saved successfully!']);
        } else {
            wp_send_json_error(['message' => 'Error saving progress.']);
        }
    }
    wp_die();
}
add_action('wp_ajax_save_assignment_progress', 'save_assignment_progress');
add_action('wp_ajax_nopriv_save_assignment_progress', 'save_assignment_progress'); // Allow non-logged-in users (if needed)

// AJAX handler to submit code and check against the correct answer
function submit_code() {
    // Verify the required parameters are set
    if (!isset($_POST['assignment_id']) || !isset($_POST['submitted_code'])) {
        wp_send_json_error(['message' => 'Missing assignment ID or code.']);
        wp_die();
    }

    $assignment_id = intval($_POST['assignment_id']);
    $submitted_code = trim(sanitize_textarea_field(wp_unslash($_POST['submitted_code'])));

    if (empty($assignment_id) || empty($submitted_code)) {
        wp_send_json_error(['message' => 'Invalid input. Please try again.']);
        wp_die();
    }

    // Retrieve the correct answer from the assignment post meta
    $correct_answer = get_post_meta($assignment_id, 'correct_answer', true);

    // Check if the submitted code matches the correct answer
    if (strcasecmp($submitted_code, trim($correct_answer)) === 0) {
        wp_send_json_success(['message' => 'Your answer is correct!']);
    } else {
        wp_send_json_error(['message' => 'Incorrect code. Please try again.']);
    }

    wp_die();
}
add_action('wp_ajax_submit_code', 'submit_code');
add_action('wp_ajax_nopriv_submit_code', 'submit_code'); // Allow non-logged-in users



// Enqueue necessary scripts and styles
function enqueue_assignment_scripts() {
    wp_enqueue_style('codemirror-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.css');
    wp_enqueue_style('codemirror-theme', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/theme/dracula.min.css');
    wp_enqueue_script('codemirror-js', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.js', [], null, true);
    wp_enqueue_script('codemirror-mode-js', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/javascript/javascript.min.js', ['codemirror-js'], null, true);

    wp_enqueue_script('editor-init', plugin_dir_url(__FILE__) . 'assets/js/editor-init.js', ['jquery'], null, true);

    // Correct AJAX URL localization
    wp_localize_script('editor-init', 'ajax_object', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);


}
add_action('wp_enqueue_scripts', 'enqueue_assignment_scripts');



// Register Custom Post Type for Code Assignments
function register_code_assignment_post_type() {
    $labels = array(
        'name'               => 'Code Assignments',
        'singular_name'      => 'Code Assignment',
        'menu_name'          => 'Code Assignments',
        'name_admin_bar'     => 'Code Assignment',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Code Assignment',
        'new_item'           => 'New Code Assignment',
        'edit_item'          => 'Edit Code Assignment',
        'view_item'          => 'View Code Assignment',
        'all_items'          => 'All Code Assignments',
        'search_items'       => 'Search Code Assignments',
        'not_found'          => 'No code assignments found.',
        'not_found_in_trash' => 'No code assignments found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-editor-code',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'rewrite'            => array('slug' => 'code-assignments')
    );

    register_post_type('code_assignment', $args);
}
add_action('init', 'register_code_assignment_post_type');

// Shortcode to display coding assignment

function render_assignment_shortcode($atts) {
    $atts = shortcode_atts(['id' => get_the_ID()], $atts, 'llms_code_assignment');
    $assignment_id = intval($atts['id']);

    if (!$assignment_id) {
        return '<p style="color:red;">Invalid assignment ID.</p>';
    }

    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/assignment-template.php';
    return ob_get_clean();
}
add_shortcode('llms_code_assignment', 'render_assignment_shortcode');
