            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional JavaScript for specific pages -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Main JavaScript -->
    <script src="js/main.js"></script>
    
    <!-- CSRF Token for AJAX requests -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($pageScript)): ?>
        <script>
            <?= $pageScript ?>
        </script>
    <?php endif; ?>
    
    <!-- Performance monitoring -->
    <?php if (!IS_PRODUCTION): ?>
    <script>
        console.log('Page loaded in:', performance.now(), 'ms');
    </script>
    <?php endif; ?>
</body>
</html> 