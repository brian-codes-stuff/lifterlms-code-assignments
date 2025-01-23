<?php
class Coding_Assignment {
    public static function create_post_type() {
        register_post_type('coding_assignment', [
            'labels' => [
                'name' => 'Coding Assignments',
                'singular_name' => 'Coding Assignment',
            ],
            'public' => true,
            'has_archive' => false,
            'supports' => ['title', 'editor', 'custom-fields'],
            'show_in_menu' => true,
        ]);
    }
}
add_action('init', ['Coding_Assignment', 'create_post_type']);
?>
