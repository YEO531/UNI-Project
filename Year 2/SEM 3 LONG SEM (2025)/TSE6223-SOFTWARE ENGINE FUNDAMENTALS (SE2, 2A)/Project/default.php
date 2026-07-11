<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title . ' - ' : '' ?>Hostel Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">Hostel Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if (isset($currentUser)): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">Dashboard</a>
                        </li>
                        
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="roomsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Rooms
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="roomsDropdown">
                                    <li><a class="dropdown-item" href="/admin/rooms/manage">Manage Rooms</a></li>
                                    <li><a class="dropdown-item" href="/admin/rooms/add">Add Room</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="bookingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Bookings
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="bookingsDropdown">
                                    <li><a class="dropdown-item" href="/admin/bookings/manage">Manage Bookings</a></li>
                                    <li><a class="dropdown-item" href="/admin/bookings/statistics">Booking Statistics</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/rooms/available">Available Rooms</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/bookings">My Bookings</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <?php 
                                    require_once __DIR__ . '/../../src/models/User.php';
                                    $userModel = new User();
                                    $unreadNotifications = $userModel->getNotifications($currentUser['id'], true);
                                    $count = count($unreadNotifications);
                                ?>
                                <?php if ($count > 0): ?>
                                    <span class="badge bg-danger"><?= $count ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                                <li class="dropdown-header">Notifications</li>
                                <?php if (empty($unreadNotifications)): ?>
                                    <li><span class="dropdown-item">No new notifications</span></li>
                                <?php else: ?>
                                    <?php foreach ($unreadNotifications as $notification): ?>
                                        <li>
                                            <a class="dropdown-item notification-item" href="/notifications/mark-read/<?= $notification['id'] ?>">
                                                <div class="notification-title"><?= Util::escape($notification['title']) ?></div>
                                                <div class="notification-message"><?= Util::escape($notification['message']) ?></div>
                                                <div class="notification-time"><?= Util::formatDate($notification['created_at'], 'M d, Y H:i') ?></div>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= Util::escape($currentUser['name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/profile">My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/register">Register</a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Flash Messages -->
        <?php if (Util::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= Util::getFlash('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (Util::hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= Util::getFlash('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (Util::hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= Util::getFlash('warning') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (Util::hasFlash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= Util::getFlash('info') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?= $content ?>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="container p-4">
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Hostel Management System</h5>
                    <p>
                        A comprehensive solution for managing hostel bookings, rooms, and student accommodations.
                    </p>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Links</h5>
                    <ul class="list-unstyled mb-0">
                        <li><a href="/" class="text-dark">Home</a></li>
                        <li><a href="/rooms/available" class="text-dark">Available Rooms</a></li>
                        <li><a href="/contact" class="text-dark">Contact Us</a></li>
                        <li><a href="/about" class="text-dark">About</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="text-uppercase">Contact</h5>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 University Ave, City</li>
                        <li><i class="fas fa-phone me-2"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope me-2"></i> info@hostel.com</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © <?= date('Y') ?> Hostel Management System
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="/js/script.js"></script>
</body>
</html>
