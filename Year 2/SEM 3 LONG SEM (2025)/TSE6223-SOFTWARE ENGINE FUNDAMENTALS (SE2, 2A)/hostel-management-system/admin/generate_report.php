<?php
require_once '../db_connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] != "admin") {
    header("location: ../login.php");
    exit();
}

function generateReport($type) {
    global $conn;
    $report_data = [];
    
    switch($type) {
        case 'room_occupancy':
            // Room occupancy report
            $sql = "SELECT 
                    r.Room_ID,
                    r.Room_Type,
                    r.Status,
                    COUNT(s.Student_ID) as Occupied_Beds,
                    r.Capacity,
                    (r.Capacity - COUNT(s.Student_ID)) as Available_Beds
                FROM Room r
                LEFT JOIN Student s ON r.Room_ID = s.Room_ID
                GROUP BY r.Room_ID
                ORDER BY r.Room_ID";
            break;
            
        case 'student_distribution':
            // Student distribution by room type
            $sql = "SELECT 
                    r.Room_Type,
                    COUNT(s.Student_ID) as Student_Count,
                    COUNT(s.Student_ID) * 100.0 / (SELECT COUNT(*) FROM Student) as Percentage
                FROM Room r
                LEFT JOIN Student s ON r.Room_ID = s.Room_ID
                GROUP BY r.Room_Type";
            break;
            
        case 'maintenance_status':
            // Maintenance request status report
            $sql = "SELECT 
                    Status,
                    COUNT(*) as Request_Count,
                    COUNT(*) * 100.0 / (SELECT COUNT(*) FROM RepairRequest) as Percentage
                FROM RepairRequest
                GROUP BY Status";
            break;
            
        case 'booking_summary':
            // Booking summary report
            $sql = "SELECT 
                    Status,
                    COUNT(*) as Booking_Count,
                    COUNT(*) * 100.0 / (SELECT COUNT(*) FROM Booking) as Percentage
                FROM Booking
                GROUP BY Status";
            break;
            
        default:
            return false;
    }
    
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
    }
    
    return $report_data;
}

// Handle report generation request
if (isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'];
    $report_data = generateReport($report_type);
    
    if ($report_data) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $report_type . '_report_' . date('Y-m-d') . '.csv"');
        
        // Create CSV file
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array_keys($report_data[0]));
        
        // Add data
        foreach ($report_data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
    }
}

$page_title = "Generate Reports - Hostel Management System";

// Build content for template
ob_start();
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Generate Reports</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Reports</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-1"></i>
            Available Reports
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Select Report Type</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">Choose a report...</option>
                                <option value="room_occupancy">Room Occupancy Report</option>
                                <option value="student_distribution">Student Distribution Report</option>
                                <option value="maintenance_status">Maintenance Status Report</option>
                                <option value="booking_summary">Booking Summary Report</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" name="generate_report" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i> Generate Report
                </button>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i>
            Report Descriptions
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Room Occupancy Report</h5>
                    <p>Shows detailed information about room occupancy, including room type, status, and available beds.</p>
                </div>
                <div class="col-md-6">
                    <h5>Student Distribution Report</h5>
                    <p>Displays the distribution of students across different room types with percentages.</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>Maintenance Status Report</h5>
                    <p>Provides an overview of maintenance requests and their current status.</p>
                </div>
                <div class="col-md-6">
                    <h5>Booking Summary Report</h5>
                    <p>Shows the status distribution of all bookings in the system.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
?> 