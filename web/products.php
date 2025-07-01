<?php
define('SECURE_ACCESS', true);
require_once 'config/config.php';

// Page configuration
$currentPage = 'products';
$pageTitle = 'Products - ' . APP_NAME;
$pageDescription = 'Manage products in the manufacturing database system';
$require_auth = true;

// Get search and pagination parameters
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;

// Build query with search
$whereClause = '';
$params = [];

if (!empty($search)) {
    $whereClause = "WHERE name LIKE ? OR product_code LIKE ? OR category LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}

// Get paginated products
$query = "SELECT * FROM products {$whereClause} ORDER BY name ASC";
$result = $db->getPaginated($query, $params, $page, $limit);

$products = $result['data'];
$totalPages = $result['pages'];
$totalRecords = $result['total'];

// Page header and actions
$pageHeader = 'Products';
$pageActions = '<button class="btn btn-primary" onclick="window.location.href=\'add-product.php\'">
                    <i class="fas fa-plus"></i> Add Product
                </button>';

// Include header
include 'includes/header.php';
?>

<!-- Search and Filter Section -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Search products..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php
                    $categories = $db->getAll("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category");
                    foreach ($categories as $cat):
                    ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                <?= ($_GET['category'] ?? '') === $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Product List</h5>
        <small class="text-muted">
            Showing <?= count($products) ?> of <?= $totalRecords ?> products
        </small>
    </div>
    <div class="card-body">
        <?php if ($products): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Product Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php 
                            $stockStatus = get_stock_level_status($product['current_stock'], $product['min_stock_level']);
                            $statusClass = [
                                'out_of_stock' => 'danger',
                                'low_stock' => 'warning',
                                'in_stock' => 'success'
                            ][$stockStatus] ?? 'secondary';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($product['product_code']) ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <?php if (!empty($product['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($product['category']) ?></span>
                                </td>
                                <td>
                                    <strong>$<?= number_format($product['unit_price'], 2) ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2"><?= (int)$product['current_stock'] ?></span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <?php 
                                            $stockPercentage = $product['min_stock_level'] > 0 
                                                ? min(100, ($product['current_stock'] / $product['min_stock_level']) * 100)
                                                : 100;
                                            ?>
                                            <div class="progress-bar bg-<?= $statusClass ?>" 
                                                 style="width: <?= $stockPercentage ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= ucwords(str_replace('_', ' ', $stockStatus)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit-product.php?id=<?= $product['product_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete-product.php?id=<?= $product['product_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this product?');"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info"
                                                onclick="viewProductDetails(<?= $product['product_id'] ?>)"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Products pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">
                    <?= !empty($search) ? 'Try adjusting your search criteria.' : 'Get started by adding your first product.' ?>
                </p>
                <?php if (empty($search)): ?>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add First Product
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="productModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php
// Page-specific JavaScript
$pageScript = "
function viewProductDetails(productId) {
    $.get('api/product-details.php', {id: productId})
        .done(function(response) {
            if (response.success) {
                $('#productModalBody').html(response.html);
                $('#productModal').modal('show');
            } else {
                alert('Failed to load product details: ' + response.message);
            }
        })
        .fail(function() {
            alert('Failed to load product details');
        });
}

// Auto-refresh stock levels every 30 seconds
setInterval(function() {
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 30000);
";

include 'includes/footer.php';
?> 