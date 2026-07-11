<?php
if (basename($_SERVER['PHP_SELF']) === 'login.php' || basename($_SERVER['PHP_SELF']) === 'register.php') return;
?>
<style>
.taskbar {
    width: 100vw;
    background: #3b5bdb;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    height: 60px;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.taskbar .nav {
    display: flex;
    gap: 16px;
    align-items: center;
}
.taskbar .nav a {
    color: #3b5bdb;
    background: #fff;
    text-decoration: none;
    font-weight: 500;
    font-size: 1.05em;
    padding: 7px 22px;
    border-radius: 999px;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    box-shadow: 0 1px 4px rgba(59,91,219,0.08);
    border: none;
    display: inline-block;
}
.taskbar .nav a:hover {
    background: #ffd43b;
    color: #3b5bdb;
}
.taskbar .nav a.logout {
    background: #ff6b6b;
    color: #fff;
}
.taskbar .nav a.logout:hover {
    background: #fff;
    color: #ff6b6b;
    border: 1px solid #ff6b6b;
}
.taskbar .nav a.admin {
    background: #fff;
    color: #3b5bdb;
}
.taskbar .nav a.admin:hover {
    background: #ffd43b;
    color: #3b5bdb;
}
.taskbar .logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.3em;
    font-weight: bold;
}
.taskbar .logo-icon {
    background: #fff;
    color: #3b5bdb;
    border-radius: 8px;
    padding: 6px 8px;
    font-size: 1.2em;
}
body { padding-top: 60px; }
</style>
<div class="taskbar">
    <div class="logo">
        <a href="index.php" style="display: flex; align-items: center; gap: 10px; color: inherit; text-decoration: none;">
            <span class="logo-icon"><i class="fas fa-calendar-alt"></i></span>
            LeaveTrack
        </a>
    </div>
    <div class="nav">
        <!-- <a href="index.php">Home</a> -->
        <a href="calendar.php">Calendar</a>
        <?php if (is_admin() || get_user_role() === 'super_admin'): ?>
            <a href="admin.php" class="admin"><i class="fas fa-user-shield"></i> Admin</a>
            <a href="admin_calendar.php" class="admin"><i class="fas fa-calendar-check"></i> Leave Overview</a>
        <?php endif; ?>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>
<!-- End Taskbar --> 