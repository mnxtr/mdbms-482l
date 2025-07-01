$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        var username = $('#username').val().trim();
        var password = $('#password').val().trim();
        var submitBtn = $('button[type="submit"]');
        var originalText = submitBtn.text();
        
        // Clear previous messages
        $('#msg').text('').removeClass('text-danger text-success');
        
        // Validate fields
        if (username === '' || password === '') {
            $('#msg').text('Both fields are required.').addClass('text-danger');
            return false;
        }
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Logging in...');
        
        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: { username: username, password: password, ajax_login: 1 },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#msg').text('Login successful! Redirecting...').addClass('text-success');
                    submitBtn.html('<i class="fas fa-check"></i> Success!');
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    $('#msg').text(response.message).addClass('text-danger');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                $('#msg').text('An error occurred. Please try again.').addClass('text-danger');
                submitBtn.prop('disabled', false).text(originalText);
                console.error('Login error:', status, error);
            }
        });
    });
    
    // Enable Enter key submission
    $('#username, #password').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $('#loginForm').submit();
        }
    });
    
    // Clear error message when user starts typing
    $('#username, #password').on('input', function() {
        if ($('#msg').text()) {
            $('#msg').text('').removeClass('text-danger text-success');
        }
    });
}); 