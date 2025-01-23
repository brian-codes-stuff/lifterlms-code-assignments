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
