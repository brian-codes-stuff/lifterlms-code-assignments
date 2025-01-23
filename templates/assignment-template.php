

<?php 
$assignment_id = isset($atts['id']) ? intval($atts['id']) : get_the_ID();
$correct_answer = get_post_meta($assignment_id, 'correct_answer', true);
$saved_progress = get_assignment_progress(get_current_user_id(), $assignment_id);

?>




<div class="coding-assignment">
    <h3><?php echo esc_html(get_the_title($assignment_id)); ?></h3>
    
    <textarea id="code-editor" name="submitted_code" rows="10" cols="50" style="display: none;"><?php echo esc_textarea($saved_progress); ?></textarea>
    
    <input type="hidden" id="assignment_id" value="<?php echo esc_attr($assignment_id); ?>">
    <input type="hidden" id="correct-answer" value="<?php echo esc_attr(get_post_meta($assignment_id, 'correct_answer', true)); ?>">


    <button id="submit-code-btn">Submit Code</button>
    <button id="save-progress-btn" style="margin-left: 10px;">Save Progress</button>
    
    <div id="assignment-feedback" style="margin-top: 10px; font-weight: bold;"></div>
</div>

<!-- Load CodeMirror scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.14/mode/javascript/javascript.min.js"></script>

<script src="<?php echo plugin_dir_url(__FILE__); ?>../assets/js/editor-init.js"></script>
