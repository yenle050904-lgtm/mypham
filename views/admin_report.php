<?php 
include 'layout/header.php'; 

// Kiểm tra quyền admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('<div class="container py-5"><div class="alert alert-danger text-center">Bạn không có quyền truy cập!</div></div>');
}

// ==================== THỐNG KÊ CHUNG (PDO) ====================
$total_orders   = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch()['total'];
$total_revenue  = $conn->query("SELECT SUM(total) as revenue FROM orders")->fetch()['revenue'] ?? 0;
$total_users    = $conn->query("SELECT COUNT(*) as total FROM users WHERE username != 'admin'")->fetch()['total'];
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch()['total'];

// Đơn hàng theo trạng thái
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$status_data = [];
while($row = $status_query->fetch()){
    $status_data[$row['status']] = $row['count'];
}

// Top 5 sản phẩm bán chạy
$top_products = $conn->query("
    SELECT oi.product_name, 
           SUM(oi.quantity) as total_qty,
           SUM(oi.price * oi.quantity) as total_revenue
    FROM order_items oi
    GROUP BY oi.product_name
    ORDER BY total_revenue DESC
    LIMIT 5
")->fetchAll();

// Doanh thu theo tháng (Dữ liệu cho Chart)
$monthly_res = $conn->query("
    SELECT DATE_FORMAT(created_at, '%m/%Y') as month, 
           SUM(total) as revenue
    FROM orders 
    GROUP BY month 
    ORDER BY created_at ASC 
    LIMIT 12
")->fetchAll();

$chart_labels = [];
$chart_data = [];
foreach($monthly_res as $m) {
    $chart_labels[] = $m['month'];
    $chart_data[]   = (int)$m['revenue'];
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container py-5">
    <h2 class="text-center mb-5 text-pink fw-bold font-elegant">📊 Hệ thống Báo cáo & Thống kê</h2>

    <!-- Thẻ tóm tắt -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="stats-icon mb-3 text-pink"><i class="fa-solid fa-cart-shopping fs-3"></i></div>
                    <h6 class="text-muted text-uppercase mb-1">Tổng đơn hàng</h6>
                    <h1 class="text-dark fw-bold mb-0"><?= number_format($total_orders) ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="stats-icon mb-3 text-success"><i class="fa-solid fa-sack-dollar fs-3"></i></div>
                    <h6 class="text-muted text-uppercase mb-1">Doanh thu</h6>
                    <h2 class="text-dark fw-bold mb-0"><?= number_format($total_revenue) ?> đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100 stats-card border-start border-pink border-4">
                <div class="card-body p-4">
                    <div class="stats-icon mb-3 text-primary"><i class="fa-solid fa-users fs-3"></i></div>
                    <h6 class="text-muted text-uppercase mb-1">Khách hàng</h6>
                    <h1 class="text-dark fw-bold mb-0"><?= number_format($total_users) ?></h1>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body p-4">
                    <div class="stats-icon mb-3 text-warning"><i class="fa-solid fa-boxes-stacked fs-3"></i></div>
                    <h6 class="text-muted text-uppercase mb-1">Sản phẩm</h6>
                    <h1 class="text-dark fw-bold mb-0"><?= number_format($total_products) ?></h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-chart-line me-2 text-pink"></i>Biểu đồ doanh thu theo tháng</h5>
                </div>
                <div class="card-body p-4">
                    <div style="height: 400px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top sản phẩm bán chạy -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-fire me-2 text-pink"></i>Top 5 sản phẩm bán chạy nhất</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-end pe-4">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_products as $p): ?>
                                <tr>
                                    <td class="ps-4 fw-medium"><?= htmlspecialchars($p['product_name']) ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= $p['total_qty'] ?></span></td>
                                    <td class="text-end pe-4 fw-bold text-danger"><?= number_format($p['total_revenue']) ?> đ</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trạng thái đơn hàng -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-pie-chart me-2 text-pink"></i>Tỷ lệ đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div style="height: 250px;" class="mb-4 d-flex justify-content-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <ul class="list-group list-group-flush border-top">
                        <?php foreach($status_data as $status => $count): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                            <span><i class="fa-solid fa-circle me-2 small <?= $status=='Hoàn thành'?'text-success':($status=='Đang xử lý'?'text-warning':'text-primary') ?>"></i><?= $status ?></span>
                            <span class="fw-bold"><?= $count ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// BIỂU ĐỒ DOANH THU
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($chart_data) ?>,
            borderColor: '#ff4d8d',
            backgroundColor: 'rgba(255, 77, 141, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 5,
            pointBackgroundColor: '#ff4d8d'
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { drawBorder: false },
                ticks: {
                    callback: function(value) { return value.toLocaleString() + ' đ'; }
                }
            },
            x: { grid: { display: false } }
        }
    }
});

// BIỂU ĐỒ TRẠNG THÁI (PIE CHART)
const ctxStatus = document.getElementById('statusChart').getContext('2d');
new Chart(ctxStatus, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($status_data)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($status_data)) ?>,
            backgroundColor: ['#ffc107', '#0d6efd', '#198754', '#17a2b8', '#6c757d'],
            borderWidth: 0
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        },
        cutout: '70%'
    }
});
</script>

<?php include 'layout/footer.php'; ?>