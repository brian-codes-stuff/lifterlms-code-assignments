jQuery(document).ready(function($) {
    $('#save-progress').on('click', function() {
        let progressText = $('#assignment-code').val();
        let assignmentId = $('#assignment-id').val();

        $.ajax({
            url: ajaxurl.ajax_url,
            type: 'POST',
            data: {
                action: 'save_assignment_progress',
                assignment_id: assignmentId,
                progress_text: progressText
            },
            success: function(response) {
                alert('Progress saved!');
            },
            error: function() {
                alert('Failed to save progress.');
            }
        });
    });

    // Auto-save every 5 minutes
    setInterval(function() {
        $('#save-progress').click();
    }, 300000);
});
