</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <i class="fa-solid fa-house me-2"></i>Hostel Management
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (!is_logged_in()): ?>
                <li class="nav-item me-2">
                    <a class="btn btn-outline-light btn-sm shadow" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-light btn-sm shadow" href="register.php">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                </li>
                <?php else: ?>
                    <?php if (get_user_role() === 'admin'): ?>
                    <li class="nav-item me-3">
                        <a class="btn btn-gradient-primary shadow-lg" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
                        </a>
                    </li>
                    <?php elseif (get_user_role() === 'staff'): ?>
                    <li class="nav-item me-3">
                        <a class="btn btn-gradient-success shadow-lg" href="dashboard.php">
                            <i class="fas fa-clipboard-list me-2"></i>Staff Dashboard
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item me-3">
                        <a class="btn btn-gradient-info shadow-lg" href="dashboard.php">
                            <i class="fas fa-home me-2"></i>My Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['user_name'] ?? 'User'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-card me-2"></i>My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>