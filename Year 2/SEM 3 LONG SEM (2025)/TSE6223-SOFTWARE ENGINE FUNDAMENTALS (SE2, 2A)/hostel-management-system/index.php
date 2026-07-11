<?php
require_once 'db_connection.php';

$page_title = "Home - Hostel Management System";

// Get total count of rooms
$room_sql = "SELECT COUNT(*) as room_count FROM Room";
$room_result = $conn->query($room_sql);
$room_row = $room_result->fetch_assoc();
$total_rooms = $room_row['room_count'];

// Get available rooms count
$available_sql = "SELECT COUNT(*) as available_count FROM Room WHERE Status = 'Available'";
$available_result = $conn->query($available_sql);
$available_row = $available_result->fetch_assoc();
$available_rooms = $available_row['available_count'];

// Build content for template
ob_start();
?>

<!-- Hero Section -->

<div class="bg-dark  text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to Hostel Management System</h1>
                <p class="lead mb-4 text-warning" style="text-align: justify; text-justify: inter-word;">Framed by swaying palms and bathed in the golden glow of dusk, the hotel's elegant arches, terracotta-tiled terraces, and lush courtyard gardens unfold into a serene sanctuary of timeless beauty.</p>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
            <img src="hostel-picture.webp" alt="Hostel Illustration" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="container my-4">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100 border-0 bg-info bg-opacity-10">
                <div class="card-body text-center p-3">
                    <i class="fas fa-door-open fa-2x text-info mb-2"></i>
                    <h5 class="card-title text-info mb-2">Total Rooms</h5>
                    <p class="display-6 fw-bold text-info mb-0"><?php echo $total_rooms; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border-0 bg-success bg-opacity-10">
                <div class="card-body text-center p-3">
                    <i class="fas fa-key fa-2x text-success mb-2"></i>
                    <h5 class="card-title text-success mb-2">Available Rooms</h5>
                    <p class="display-6 fw-bold text-success mb-0"><?php echo $available_rooms; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Call to Action -->
<div class="bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4 text-primary">Ready to Get Started?</h2>
        <p class="lead mb-4">Join our hostel management system today and experience the difference.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>