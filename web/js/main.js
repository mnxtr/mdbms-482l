/**
 * Manufacturing Database System - Main JavaScript
 * Optimized for performance and user experience
 */

// Global configuration
const CONFIG = {
    API_BASE_URL: window.location.origin + '/web/api/',
    REFRESH_INTERVAL: 30000, // 30 seconds
    DEBOUNCE_DELAY: 300,
    ANIMATION_DURATION: 300
};

// Utility functions
const Utils = {
    // Debounce function for search inputs
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Format currency
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    // Format date
    formatDate(date, format = 'short') {
        const d = new Date(date);
        if (format === 'short') {
            return d.toLocaleDateString();
        } else if (format === 'long') {
            return d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
        }
        return d.toISOString();
    },

    // Show notification
    showNotification(message, type = 'info', duration = 5000) {
        const alertClass = `alert-${type}`;
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const alertElement = $(alertHtml);
        $('body').append(alertElement);
        
        setTimeout(() => {
            alertElement.alert('close');
        }, duration);
    },

    // AJAX wrapper with error handling
    ajax(options) {
        const defaultOptions = {
            url: '',
            method: 'GET',
            data: {},
            dataType: 'json',
            timeout: 10000,
            beforeSend: function() {
                // Show loading indicator
                if (options.showLoading !== false) {
                    Utils.showLoading();
                }
            },
            success: function(response) {
                if (response.success === false) {
                    Utils.showNotification(response.message || 'Operation failed', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                let message = 'An error occurred';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    message = 'Resource not found';
                } else if (xhr.status === 500) {
                    message = 'Server error occurred';
                }
                
                Utils.showNotification(message, 'danger');
            },
            complete: function() {
                // Hide loading indicator
                if (options.showLoading !== false) {
                    Utils.hideLoading();
                }
            }
        };

        return $.ajax({ ...defaultOptions, ...options });
    },

    // Show/hide loading indicator
    showLoading() {
        if ($('#loadingOverlay').length === 0) {
            const overlay = `
                <div id="loadingOverlay" class="position-fixed w-100 h-100 d-flex justify-content-center align-items-center" 
                     style="background: rgba(0,0,0,0.5); z-index: 9999; top: 0; left: 0;">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            $('body').append(overlay);
        }
    },

    hideLoading() {
        $('#loadingOverlay').fadeOut(CONFIG.ANIMATION_DURATION, function() {
            $(this).remove();
        });
    },

    // Validate form
    validateForm(formElement) {
        const form = $(formElement);
        let isValid = true;
        
        // Clear previous validation messages
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
        
        // Required fields
        form.find('[required]').each(function() {
            const field = $(this);
            const value = field.val().trim();
            
            if (!value) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">This field is required.</div>');
                isValid = false;
            }
        });
        
        // Email validation
        form.find('[type="email"]').each(function() {
            const field = $(this);
            const value = field.val().trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value && !emailRegex.test(value)) {
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
                isValid = false;
            }
        });
        
        // Number validation
        form.find('[type="number"]').each(function() {
            const field = $(this);
            const value = field.val();
            const min = field.attr('min');
            const max = field.attr('max');
            
            if (value) {
                const numValue = parseFloat(value);
                if (isNaN(numValue)) {
                    field.addClass('is-invalid');
                    field.after('<div class="invalid-feedback">Please enter a valid number.</div>');
                    isValid = false;
                } else if (min && numValue < parseFloat(min)) {
                    field.addClass('is-invalid');
                    field.after(`<div class="invalid-feedback">Value must be at least ${min}.</div>`);
                    isValid = false;
                } else if (max && numValue > parseFloat(max)) {
                    field.addClass('is-invalid');
                    field.after(`<div class="invalid-feedback">Value must be at most ${max}.</div>`);
                    isValid = false;
                }
            }
        });
        
        return isValid;
    }
};

// Sidebar functionality
const Sidebar = {
    init() {
        this.bindEvents();
        this.setActiveMenuItem();
    },

    bindEvents() {
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('active');
        });

        // Close sidebar on mobile when clicking outside
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                if (!$(e.target).closest('#sidebar, #sidebarCollapse').length) {
                    $('#sidebar').removeClass('active');
                }
            }
        });
    },

    setActiveMenuItem() {
        const currentPath = window.location.pathname;
        const pageName = currentPath.split('/').pop().replace('.php', '');
        
        // Remove all active classes
        $('#sidebar .components li').removeClass('active');
        $('#sidebar .components a').removeClass('active');
        
        // Set active based on current page
        if (pageName === 'dashboard' || pageName === 'index') {
            $('#sidebar .components li:first-child').addClass('active');
        } else {
            $(`#sidebar a[href="${pageName}.php"]`).addClass('active').parent().addClass('active');
        }
    }
};

// Table functionality
const TableManager = {
    init() {
        this.bindEvents();
        this.initSortable();
        this.initSearch();
    },

    bindEvents() {
        // Row selection
        $('.table tbody tr').on('click', function(e) {
            if (!$(e.target).closest('.btn, a').length) {
                $(this).toggleClass('table-active');
            }
        });

        // Bulk actions
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.table tbody input[type="checkbox"]').prop('checked', isChecked);
            $('.table tbody tr').toggleClass('table-active', isChecked);
        });
    },

    initSortable() {
        $('.table th[data-sort]').on('click', function() {
            const column = $(this).data('sort');
            const direction = $(this).hasClass('sort-asc') ? 'desc' : 'asc';
            
            // Update sort indicators
            $('.table th').removeClass('sort-asc sort-desc');
            $(this).addClass(`sort-${direction}`);
            
            // Trigger sort (implement based on your needs)
            TableManager.sortTable(column, direction);
        });
    },

    initSearch() {
        const searchInput = $('.table-search');
        if (searchInput.length) {
            const debouncedSearch = Utils.debounce(function() {
                const query = $(this).val().toLowerCase();
                TableManager.filterTable(query);
            }, CONFIG.DEBOUNCE_DELAY);

            searchInput.on('input', debouncedSearch);
        }
    },

    sortTable(column, direction) {
        // Implement table sorting logic
        console.log(`Sorting by ${column} in ${direction} order`);
    },

    filterTable(query) {
        $('.table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    }
};

// Form handling
const FormHandler = {
    init() {
        this.bindEvents();
        this.initAutoSave();
    },

    bindEvents() {
        // Form submission
        $('form').on('submit', function(e) {
            if (!Utils.validateForm(this)) {
                e.preventDefault();
                return false;
            }
        });

        // Auto-save on input change
        $('form input, form textarea, form select').on('change', function() {
            FormHandler.autoSave($(this).closest('form'));
        });
    },

    initAutoSave() {
        // Auto-save every 30 seconds
        setInterval(() => {
            $('form[data-autosave]').each(function() {
                FormHandler.autoSave($(this));
            });
        }, 30000);
    },

    autoSave(form) {
        const formData = new FormData(form[0]);
        const url = form.attr('action') || window.location.href;
        
        Utils.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            showLoading: false,
            success: function(response) {
                if (response.success) {
                    console.log('Form auto-saved');
                }
            }
        });
    }
};

// Charts and data visualization
const ChartManager = {
    charts: {},

    init() {
        this.initCharts();
    },

    initCharts() {
        // Initialize any charts on the page
        $('[data-chart]').each(function() {
            const chartType = $(this).data('chart');
            const chartId = $(this).attr('id');
            
            if (chartId && window.Chart) {
                ChartManager.createChart(chartId, chartType, $(this).data('chart-data'));
            }
        });
    },

    createChart(canvasId, type, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        this.charts[canvasId] = new Chart(ctx, {
            type: type,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    },

    updateChart(chartId, newData) {
        if (this.charts[chartId]) {
            this.charts[chartId].data = newData;
            this.charts[chartId].update();
        }
    }
};

// Real-time updates
const RealTimeUpdates = {
    interval: null,

    init() {
        if (this.shouldEnableUpdates()) {
            this.startUpdates();
        }
    },

    shouldEnableUpdates() {
        // Only enable on dashboard and list pages
        const currentPage = window.location.pathname.split('/').pop();
        return ['dashboard.php', 'products.php', 'production.php', 'quality.php'].includes(currentPage);
    },

    startUpdates() {
        this.interval = setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.updateData();
            }
        }, CONFIG.REFRESH_INTERVAL);
    },

    stopUpdates() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
        }
    },

    updateData() {
        const currentPage = window.location.pathname.split('/').pop();
        
        switch (currentPage) {
            case 'dashboard.php':
                this.updateDashboard();
                break;
            case 'products.php':
                this.updateProductCounts();
                break;
            // Add more cases as needed
        }
    },

    updateDashboard() {
        Utils.ajax({
            url: CONFIG.API_BASE_URL + 'dashboard-stats.php',
            method: 'GET',
            showLoading: false,
            success: function(response) {
                if (response.success) {
                    // Update dashboard statistics
                    Object.keys(response.data).forEach(key => {
                        $(`#${key}`).text(response.data[key]);
                    });
                }
            }
        });
    },

    updateProductCounts() {
        Utils.ajax({
            url: CONFIG.API_BASE_URL + 'product-counts.php',
            method: 'GET',
            showLoading: false,
            success: function(response) {
                if (response.success) {
                    // Update product counts in the UI
                    $('#totalProducts').text(response.data.total);
                    $('#lowStockProducts').text(response.data.lowStock);
                    $('#outOfStockProducts').text(response.data.outOfStock);
                }
            }
        });
    }
};

// Initialize everything when DOM is ready
$(document).ready(function() {
    // Initialize all modules
    Sidebar.init();
    TableManager.init();
    FormHandler.init();
    ChartManager.init();
    RealTimeUpdates.init();

    // Global error handling
    $(document).ajaxError(function(event, xhr, settings, error) {
        console.error('Global AJAX Error:', error);
        Utils.showNotification('A network error occurred. Please try again.', 'danger');
    });

    // Performance monitoring
    if (window.performance && window.performance.timing) {
        window.addEventListener('load', function() {
            const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
            console.log(`Page loaded in ${loadTime}ms`);
        });
    }

    // Service Worker registration (for PWA features)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/web/sw.js')
            .then(function(registration) {
                console.log('ServiceWorker registration successful');
            })
            .catch(function(err) {
                console.log('ServiceWorker registration failed');
            });
    }
});

// Export for use in other scripts
window.MDS = {
    Utils,
    Sidebar,
    TableManager,
    FormHandler,
    ChartManager,
    RealTimeUpdates,
    CONFIG
}; 