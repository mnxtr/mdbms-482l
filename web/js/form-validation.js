$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        var username = $('#username').val().trim();
        var password = $('#password').val().trim();
        if (username === '' || password === '') {
            $('#msg').text('Both fields are required.');
            return false;
        }
        $.ajax({
            url: 'login.php',
            type: 'POST',
            data: { username: username, password: password },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'Index.html';
                } else {
                    $('#msg').text(response.message);
                }
            },
            error: function() {
                $('#msg').text('An error occurred. Please try again.');
            }
        });
    });
}); 