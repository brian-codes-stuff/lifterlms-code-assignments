document.addEventListener("DOMContentLoaded", function() {
    if (typeof CodeMirror === "undefined") {
        console.error("CodeMirror is not loaded correctly.");
        return;
    }

    var textarea = document.getElementById("code-editor");

    if (!textarea) {
        console.error("Textarea element not found.");
        return;
    }

    // Check for and remove any existing CodeMirror instance within the parent container
    var existingEditor = textarea.parentElement.querySelector(".CodeMirror");
    if (existingEditor) {
        existingEditor.remove();
    }

    // Initialize CodeMirror only if it hasn't been initialized already
    if (!textarea.classList.contains("codemirror-initialized")) {
        var editor = CodeMirror.fromTextArea(textarea, {
            lineNumbers: true,
            mode: "javascript",
            theme: "dracula"
        });

        // Add a marker to prevent multiple initializations
        textarea.classList.add("codemirror-initialized");

        // Load saved progress into the editor
        editor.setValue(`<?php echo addslashes($saved_progress); ?>`);

        jQuery(document).ready(function($) {
            // Save Progress Button Click
            $('#save-progress-btn').on('click', function() {
                let progressText = editor.getValue().trim();
                let assignmentId = $('#assignment_id').val();

                if (progressText === "") {
                    $('#assignment-feedback').html('<span style="color:red;">No content to save.</span>');
                    return;
                }

                $.ajax({
                    url: ajax_object.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_assignment_progress',
                        assignment_id: assignmentId,
                        progress_text: progressText
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#assignment-feedback').html('<span style="color:green;">Progress saved successfully!</span>');
                        } else {
                            $('#assignment-feedback').html('<span style="color:red;">Error saving progress.</span>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.responseText);
                        $('#assignment-feedback').html('<span style="color:red;">Failed to save progress.</span>');
                    }
                });
            });

            // Auto-save every 5 minutes
            setInterval(function() {
                $('#save-progress-btn').click();
            }, 300000);
        });
    }
});
