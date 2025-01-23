jQuery(document).ready(function($) {
    var nextButton = $('.llms-pre-text:contains("Next Lesson")').closest('a');

    // Disable the "Next Lesson" button on page load
    if (nextButton.length) {
        nextButton.css({
            'pointer-events': 'none',
            'opacity': '0.5',
            'cursor': 'not-allowed'
        }).attr('disabled', true);
    }

    // Check if CodeMirror is already initialized
    if ($('#code-editor').hasClass('codemirror-initialized')) {
        return;
    }

    // Initialize CodeMirror instance
    var codeEditor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
        mode: "javascript",
        lineNumbers: true,
        theme: "dracula",
        matchBrackets: true,
        autoCloseBrackets: true
    });

    // Add a class to avoid re-initialization
    $('#code-editor').addClass('codemirror-initialized');

    // Event listener for Submit Code button
    $('#submit-code-btn').on('click', function(e) {
        e.preventDefault();
        
        let submittedCode = codeEditor.getValue().trim();  // Get code from CodeMirror
        let assignmentId = $('#assignment_id').val();

        if (submittedCode === "") {
            $('#assignment-feedback').html('<span style="color:red;">Please enter some code.</span>');
            return;
        }

        $('#assignment-feedback').html('Checking your answer...');

        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'submit_code',
                assignment_id: assignmentId,
                submitted_code: submittedCode
            },
            success: function(response) {
                if (response.success) {
                    $('#assignment-feedback').html('<span style="color:green;">' + response.data.message + '</span>');
                    
                    if (nextButton.length) {
                        nextButton.css({
                            'pointer-events': 'auto',
                            'opacity': '1',
                            'cursor': 'pointer'
                        }).removeAttr('disabled');
                    }
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

    // Auto-save every 5 minutes
    setInterval(function() {
        let progressText = codeEditor.getValue().trim();
        let assignmentId = $('#assignment_id').val();

        $.ajax({
            url: ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'save_assignment_progress',
                assignment_id: assignmentId,
                progress_text: progressText
            },
            success: function(response) {
                $('#assignment-feedback').html('<span style="color:green;">Progress auto-saved.</span>');
            },
            error: function() {
                $('#assignment-feedback').html('<span style="color:red;">Auto-save failed.</span>');
            }
        });
    }, 300000);
});
