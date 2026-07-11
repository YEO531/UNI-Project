<?php
require_once '../db_connection.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "staff") {
    header("location: ../login.php");
    exit();
}

$staff_id = $_SESSION["user_id"];
$staff_name = $_SESSION["user_name"];

// Get staff information
$sql = "SELECT * FROM MaintenanceStaff WHERE Staff_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff_data = $result->fetch_assoc();

// Get repair request statistics
$sql_stats = "SELECT 
    COUNT(*) as total_requests,
    SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) as pending_requests,
    SUM(CASE WHEN Status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled_requests,
    SUM(CASE WHEN Status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_requests,
    SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) as completed_requests
    FROM RepairRequest";
$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Get rooms under maintenance
$sql_rooms = "SELECT COUNT(*) as maintenance_rooms FROM Room WHERE Status = 'Maintenance'";
$result_rooms = $conn->query($sql_rooms);
$maintenance_rooms = $result_rooms->fetch_assoc()['maintenance_rooms'];

// Get monthly repair requests data for trend chart
$sql_monthly = "SELECT 
    DATE_FORMAT(Request_Date, '%Y-%m') as month,
    COUNT(*) as request_count,
    Status
    FROM RepairRequest 
    WHERE Request_Date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(Request_Date, '%Y-%m'), Status
    ORDER BY month";
$result_monthly = $conn->query($sql_monthly);
$monthly_data = [];
while ($row = $result_monthly->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Get repair requests by room type
$sql_room_type = "SELECT 
    r.Room_Type,
    COUNT(*) as request_count
    FROM RepairRequest rr
    JOIN Room r ON rr.Room_ID = r.Room_ID
    GROUP BY r.Room_Type
    ORDER BY request_count DESC";
$result_room_type = $conn->query($sql_room_type);
$room_type_data = [];
while ($row = $result_room_type->fetch_assoc()) {
    $room_type_data[] = $row;
}

$page_title = "Staff Dashboard - Hostel Management System";

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4 bg-black text-info p-3 rounded d-inline-block">Maintenance Staff Dashboard</h1>
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Pending Requests</h4>
                    <p class="fs-2"><?= $stats['pending_requests'] ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="repair_requests.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4>Scheduled</h4>
                    <p class="fs-2"><?= $stats['scheduled_requests'] ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="repair_requests.php?filter=scheduled">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>In Progress</h4>
                    <p class="fs-2"><?= $stats['in_progress_requests'] ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="repair_requests.php?filter=in_progress">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h4>Rooms in Maintenance</h4>
                    <p class="fs-2"><?= $maintenance_rooms ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="rooms.php?filter=maintenance">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Repair Requests Status Distribution
                </div>
                <div class="card-body">
                    <canvas id="statusChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Requests by Room Type
                </div>
                <div class="card-body">
                    <canvas id="roomTypeChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Monthly Repair Requests Trend (Last 6 Months)
                </div>
                <div class="card-body">
                    <canvas id="trendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Status Distribution Pie Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: ['Pending', 'Scheduled', 'In Progress', 'Completed'],
        datasets: [{
            data: [
                <?= $stats['pending_requests'] ?>,
                <?= $stats['scheduled_requests'] ?>,
                <?= $stats['in_progress_requests'] ?>,
                <?= $stats['completed_requests'] ?>
            ],
            backgroundColor: [
                '#007bff',
                '#ffc107',
                '#28a745',
                '#6c757d'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.raw / total) * 100).toFixed(1);
                        return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Room Type Bar Chart
const roomTypeCtx = document.getElementById('roomTypeChart').getContext('2d');
const roomTypeChart = new Chart(roomTypeCtx, {
    type: 'bar',
    data: {
        labels: [<?php 
            $labels = [];
            foreach ($room_type_data as $data) {
                $labels[] = "'" . $data['Room_Type'] . "'";
            }
            echo implode(', ', $labels);
        ?>],
        datasets: [{
            label: 'Number of Requests',
            data: [<?php 
                $values = [];
                foreach ($room_type_data as $data) {
                    $values[] = $data['request_count'];
                }
                echo implode(', ', $values);
            ?>],
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6f42c1',
                '#20c997'
            ],
            borderColor: [
                '#0056b3',
                '#1e7e34',
                '#e0a800',
                '#c82333',
                '#5a32a3',
                '#198754'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Monthly Trend Line Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');

// Process monthly data for the chart
const monthlyLabels = [];
const monthlyValues = [];
const monthlyData = <?= json_encode($monthly_data) ?>;

// Group data by month
const groupedData = {};
monthlyData.forEach(item => {
    if (!groupedData[item.month]) {
        groupedData[item.month] = 0;
    }
    groupedData[item.month] += parseInt(item.request_count);
});

// Convert to arrays for Chart.js
Object.keys(groupedData).sort().forEach(month => {
    monthlyLabels.push(month);
    monthlyValues.push(groupedData[month]);
});

const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: monthlyLabels,
        datasets: [{
            label: 'Total Requests',
            data: monthlyValues,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#007bff',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>