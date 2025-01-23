<?php
if (!isset($assignment_id) || empty($assignment_id)) {
    echo '<p style="color:red;">Assignment ID is not available.</p>';
    return;
}

$correct_answer = get_post_meta($assignment_id, 'correct_answer', true);
?>


<div class="coding-assignment">
    <h3><?php echo get_the_title($assignment_id); ?></h3>
    <textarea id="code-editor" name="submitted_code" rows="10" cols="50" placeholder="Write your code here..."></textarea>
    <input type="hidden" id="assignment_id" value="<?php echo esc_attr($assignment_id); ?>">
    <button id="submit-code-btn">Submit Code</button>
    <div id="assignment-feedback" style="margin-top: 10px; font-weight: bold;"></div>
</div>


<script>
jQuery(document).ready(function($) {
    $('#submit-code-btn').on('click', function() {
        var submittedCode = $.trim($('#code-editor').val());  // Trim input to avoid spacing issues
        var assignmentId = $('#assignment_id').val();

        if (submittedCode === "") {
            $('#assignment-feedback').html('<span style="color:red;">Please enter some code.</span>');
            return;
        }

        $('#assignment-feedback').html('Checking your answer...');

        $.ajax({
            url: ajax_object.ajaxurl,  // Use localized WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'submit_code',
                assignment_id: assignmentId,
                submitted_code: submittedCode
            },
            success: function(response) {
                if (response.success) {
                    $('#assignment-feedback').html('<span style="color:green;">' + response.data.message + '</span>');
                } else {
                    $('#assignment-feedback').html('<span style="color:red;">' + response.data.message + '</span>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#assignment-feedback').html('<span style="color:red;">An error occurred. Please try again.</span>');
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });
    });
});
</script>
