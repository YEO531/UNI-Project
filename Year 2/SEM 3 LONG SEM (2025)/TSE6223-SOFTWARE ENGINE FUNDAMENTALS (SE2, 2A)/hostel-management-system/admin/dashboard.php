<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
//session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

$admin_id = $_SESSION["user_id"];
$admin_name = $_SESSION["user_name"];

// Get system statistics
// Total students
$sql_students = "SELECT COUNT(*) as total_students FROM Student";
$result_students = $conn->query($sql_students);
$total_students = $result_students->fetch_assoc()['total_students'];

// Total rooms
$sql_rooms = "SELECT COUNT(*) as total_rooms FROM Room";
$result_rooms = $conn->query($sql_rooms);
$total_rooms = $result_rooms->fetch_assoc()['total_rooms'];

// Available rooms
$sql_available = "SELECT COUNT(*) as available_rooms FROM Room WHERE Status = 'Available'";
$result_available = $conn->query($sql_available);
$available_rooms = $result_available->fetch_assoc()['available_rooms'];

// Pending bookings
$sql_bookings = "SELECT COUNT(*) as pending_bookings FROM Booking WHERE Status = 'Pending'";
$result_bookings = $conn->query($sql_bookings);
$pending_bookings = $result_bookings->fetch_assoc()['pending_bookings'];

// Pending repair requests
$sql_repairs = "SELECT COUNT(*) as pending_repairs FROM RepairRequest WHERE Status = 'Pending'";
$result_repairs = $conn->query($sql_repairs);
$pending_repairs = $result_repairs->fetch_assoc()['pending_repairs'];

// Maintenance staff count
$sql_staff = "SELECT COUNT(*) as total_staff FROM MaintenanceStaff";
$result_staff = $conn->query($sql_staff);
$total_staff = $result_staff->fetch_assoc()['total_staff'];

// Students per room type data for pie chart
$sql_students_per_room_type = "
    SELECT r.Room_Type, COUNT(s.Student_ID) as student_count 
    FROM Room r 
    LEFT JOIN Student s ON r.Room_ID = s.Room_ID 
    GROUP BY r.Room_Type
    ORDER BY student_count DESC
";
$result_students_per_room_type = $conn->query($sql_students_per_room_type);
$students_per_room_type_data = [];
while ($row = $result_students_per_room_type->fetch_assoc()) {
    $students_per_room_type_data[] = $row;
}

// Repair requests data for chart
$sql_repair_status = "SELECT Status, COUNT(*) as count FROM RepairRequest GROUP BY Status";
$result_repair_status = $conn->query($sql_repair_status);
$repair_status_data = [];
while ($row = $result_repair_status->fetch_assoc()) {
    $repair_status_data[] = $row;
}

// Bookings data for chart
$sql_booking_status = "SELECT Status, COUNT(*) as count FROM Booking GROUP BY Status";
$result_booking_status = $conn->query($sql_booking_status);
$booking_status_data = [];
while ($row = $result_booking_status->fetch_assoc()) {
    $booking_status_data[] = $row;
}

$page_title = "Admin Dashboard - Hostel Management System";

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><span class="bg-black text-info p-3 rounded d-inline-block">Admin Dashboard</span></h1>
    <!-- Add Reports Button -->
    <div class="mb-4">
        <a href="generate_report.php" class="btn btn-primary">
            <i class="fas fa-file-alt me-1"></i> Generate Reports
        </a>
    </div>
    
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Students</h4>
                    <p class="fs-2"><?= $total_students ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="students.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4>Rooms</h4>
                    <p class="fs-2"><?= $total_rooms ?> (<?= $available_rooms ?> available)</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="rooms.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Pending Bookings</h4>
                    <p class="fs-2"><?= $pending_bookings ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="bookings.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h4>Repair Requests</h4>
                    <p class="fs-2"><?= $pending_repairs ?></p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="staff.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Number of Students in each Room Type 
                </div>
                <div class="card-body" style="height: 400px;">
                    <canvas id="studentsPerRoomTypeChart" width="100%" height="100%"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Repair Requests Status
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="repairRequestsChart" width="100%" height="100%"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-1"></i>
                    Bookings Status
                </div>
                <div class="card-body" style="height: 350px;">
                    <canvas id="bookingsChart" width="100%" height="100%"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Students per Room Type Pie Chart
    const ctxStudents = document.getElementById('studentsPerRoomTypeChart').getContext('2d');
    
    const studentsPerRoomTypeData = <?= json_encode($students_per_room_type_data) ?>;
    
    const roomTypeLabels = studentsPerRoomTypeData.map(item => item.Room_Type);
    const studentCounts = studentsPerRoomTypeData.map(item => parseInt(item.student_count));
    
    const roomTypeColors = [
        '#FF6384',
        '#36A2EB', 
        '#FFCE56',
        '#4BC0C0',
        '#9966FF',
        '#FF9F40'
    ];
    
    new Chart(ctxStudents, {
        type: 'pie',
        data: {
            labels: roomTypeLabels,
            datasets: [{
                data: studentCounts,
                backgroundColor: roomTypeColors.slice(0, roomTypeLabels.length),
                borderColor: roomTypeColors.slice(0, roomTypeLabels.length).map(color => color + '80'),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed * 100) / total).toFixed(1) : '0.0';
                            return context.label + ': ' + context.parsed + ' students (' + percentage + '%)';
                        }
                    }
                }
            },
            layout: {
                padding: 15
            }
        }
    });

    // Repair Requests Doughnut Chart
    const ctxRepairs = document.getElementById('repairRequestsChart').getContext('2d');
    
    const repairStatusData = <?= json_encode($repair_status_data) ?>;
    
    const repairLabels = repairStatusData.map(item => item.Status);
    const repairData = repairStatusData.map(item => parseInt(item.count));
    
    const repairColors = [
        '#28a745', // Completed - Green
        '#007bff', // In Progress - Blue
        '#ffc107', // Pending - Yellow
        '#17a2b8', // Scheduled - Cyan
        '#dc3545'  // Cancelled - Red
    ];
    
    new Chart(ctxRepairs, {
        type: 'doughnut',
        data: {
            labels: repairLabels,
            datasets: [{
                data: repairData,
                backgroundColor: repairColors.slice(0, repairLabels.length),
                borderColor: repairColors.slice(0, repairLabels.length).map(color => color + '80'),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed * 100) / total).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            layout: {
                padding: 10
            }
        }
    });

    // Bookings Bar Chart
    const ctxBookings = document.getElementById('bookingsChart').getContext('2d');
    
    const bookingStatusData = <?= json_encode($booking_status_data) ?>;
    
    const bookingLabels = bookingStatusData.map(item => item.Status);
    const bookingData = bookingStatusData.map(item => parseInt(item.count));
    
    const bookingColors = [
        '#28a745', // Confirmed - Green
        '#17a2b8', // Completed - Cyan
        '#ffc107', // Pending - Yellow
        '#dc3545'  // Cancelled - Red
    ];
    
    new Chart(ctxBookings, {
        type: 'bar',
        data: {
            labels: bookingLabels,
            datasets: [{
                label: 'Number of Bookings',
                data: bookingData,
                backgroundColor: bookingColors.slice(0, bookingLabels.length),
                borderColor: bookingColors.slice(0, bookingLabels.length).map(color => color + '80'),
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed.y + ' bookings';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45
                    }
                }
            },
            layout: {
                padding: 10
            }
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include '../layout.php';
?>