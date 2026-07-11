<div class="position-sticky pt-3">
    <ul class="nav flex-column">
        <?php if (get_user_role() == 'student'): ?>
            <!-- Student Sidebar -->
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>" href="rooms.php">
                    <i class="fas fa-door-open me-2"></i>View Rooms
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                    <i class="fas fa-book me-2"></i>My Bookings
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>" href="appointments.php">
                    <i class="fas fa-calendar-check me-2"></i>Appointments
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                    <i class="fas fa-credit-card me-2"></i>Payments
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'repair_requests.php' ? 'active' : ''; ?>" href="repair_requests.php">
                    <i class="fas fa-tools me-2"></i>Repair Requests
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user me-2"></i>My Profile
                </a>
            </li>
        <?php elseif (get_user_role() == 'admin'): ?>
            <!-- Admin Sidebar -->
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start  <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>" href="students.php">
                    <i class="fas fa-users me-2"></i>Manage Students
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>" href="rooms.php">
                    <i class="fas fa-door-open me-2"></i>Manage Rooms
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                    <i class="fas fa-book me-2"></i>Bookings
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>" href="appointments.php">
                    <i class="fas fa-calendar-check me-2"></i>Appointments
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>" href="payments.php">
                    <i class="fas fa-credit-card me-2"></i>Payments
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>" href="staff.php">
                    <i class="fas fa-user-cog me-2"></i>Manage Staff
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user me-2"></i>My Profile
                </a>
            </li>
        <?php elseif (get_user_role() == 'staff'): ?>
            <!-- Maintenance Staff Sidebar -->
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'repair_requests.php' ? 'active' : ''; ?>" href="repair_requests.php">
                    <i class="fas fa-tools me-2"></i>Repair Requests
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>" href="rooms.php">
                    <i class="fas fa-door-open me-2"></i>View Rooms
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link btn btn-outline-primary w-100 text-start <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user me-2"></i>My Profile
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item mb-2">
            <a class="nav-link btn btn-outline-danger w-100 text-start" href="../logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </li>
    </ul>
</div>