<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Hostel Management System'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
         body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        
        .content {
            padding: 20px;
        }
        
        /* Make the main content area flex-grow to push footer down */
        .main-container {
            flex: 1;
        }
        
        .footer {
            padding: 10px 0;
            text-align: center;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            margin-top: auto; /* This ensures footer stays at bottom */
        }
        
        .login-form, .register-form {
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .card-dashboard {
            transition: transform 0.3s;
        }
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
        .room-card {
            height: 100%;
            transition: transform 0.3s;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .appointment-card {
            border-left: 5px solid #0d6efd;
        }
        .repair-card {
            border-left: 5px solid #dc3545;
        }
        .booking-card {
            border-left: 5px solid #198754;
        }
        .payment-card {
            border-left: 5px solid #ffc107;
        }
        .status-available {
            color: #198754;
        }
        .status-occupied {
            color: #dc3545;
        }
        .status-maintenance {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <?php include 'includes/sidebar.php'; ?>
            </div>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                <?php display_message(); ?>
                <!-- Page content will go here -->
                <?php echo $content ?? ''; ?>
            </main>
            <?php else: ?>
            <main class="col-12 content">
                <?php display_message(); ?>
                <!-- Page content will go here -->
                <?php echo $content ?? ''; ?>
            </main>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer mt-auto">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Hostel Management System. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (needed for some Bootstrap components) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Enable Bootstrap popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });
    </script>
</body>
</html>