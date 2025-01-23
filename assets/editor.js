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

    // Initialize CodeMirror
    var codeEditor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
        mode: "javascript",
        lineNumbers: true,
        theme: "dracula",
        matchBrackets: true,
        autoCloseBrackets: true
    });

    $('#submit-code-btn').on('click', function(e) {
        e.preventDefault();
        
        var submittedCode = codeEditor.getValue().trim();
        var assignmentId = $('#assignment_id').val();

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
                    
                    // Enable the "Next Lesson" button
                    if (nextButton.length) {
                        nextButton.css({
                            'pointer-events': 'auto',
                            'opacity': '1',
                            'cursor': 'pointer'
                        }).removeAttr('disabled');
                    }
                } else {
                    $('#assignment-feedback').html('<span style="color:red;">' + response.data.message + '</span>');
                    
                    // Keep "Next Lesson" button disabled
                    if (nextButton.length) {
                        nextButton.css({
                            'pointer-events': 'none',
                            'opacity': '0.5',
                            'cursor': 'not-allowed'
                        }).attr('disabled', true);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#assignment-feedback').html('<span style="color:red;">An error occurred. Please try again.</span>');
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });
    });
});
