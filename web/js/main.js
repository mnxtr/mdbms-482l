$(document).ready(function () {
    // Sidebar toggle
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle dropdown menus
    $('.dropdown-toggle').dropdown();

    // Add active class to current page in sidebar
    var currentPage = window.location.pathname.split('/').pop();
    $('nav a[href="' + currentPage + '"]').addClass('active');
    $('nav a[href="' + currentPage + '"]').parents('li').addClass('active');

    // Handle form submissions with AJAX
    $('form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success: function(response) {
                // Handle success
                showNotification('Success', 'Operation completed successfully', 'success');
            },
            error: function(xhr, status, error) {
                // Handle error
                showNotification('Error', 'An error occurred', 'error');
            }
        });
    });

    // Notification function
    function showNotification(title, message, type) {
        // You can implement a toast or alert system here
        alert(title + ': ' + message);
    }

    // Data table initialization (if using DataTables)
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries"
            }
        });
    }

    // Chart initialization (if using Charts.js)
    if (typeof Chart !== 'undefined') {
        // Example chart initialization
        var ctx = document.getElementById('productionChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Production Output',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                }
            });
        }
    }

    // Handle file uploads
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Handle dynamic form fields
    $('.add-field').on('click', function() {
        var template = $(this).data('template');
        var container = $(this).data('container');
        $(container).append(template);
    });

    // Remove dynamic form fields
    $(document).on('click', '.remove-field', function() {
        $(this).closest('.dynamic-field').remove();
    });
}); 