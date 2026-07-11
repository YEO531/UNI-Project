<?php
require_once __DIR__ . '/../includes/config.php';

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Leave Management System - Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #007bff; color: white; padding: 15px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Leave Management System - Database Setup</h1>
            <p>Database initialization and configuration</p>
        </div>";

try {
    // Test database connection
    echo "<div class='section'>
        <h2>🔍 Database Connection Test</h2>";
    
    if ($conn->ping()) {
        echo "<p class='success'>✅ Database connection successful</p>";
        echo "<p class='info'>Connected to: " . $conn->host_info . "</p>";
    } else {
        throw new Exception("Database connection failed");
    }
    echo "</div>";

    // Get current year
    $year = date('Y');
    echo "<div class='section'>
        <h2>📅 Current Year: $year</h2>
    </div>";

    // Check and create missing tables
    echo "<div class='section'>
        <h2>🗄️ Database Schema Check</h2>";
    
    $required_tables = [
        'users', 'departments', 'positions', 'leave_types', 
        'leave_balances', 'leave_applications', 'notifications', 
        'system_settings', 'activity_logs', 'calendar_notes'
    ];
    
    $existing_tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        $existing_tables[] = $row[0];
    }
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    
    if (empty($missing_tables)) {
        echo "<p class='success'>✅ All required tables exist</p>";
    } else {
        echo "<p class='warning'>⚠️ Missing tables: " . implode(', ', $missing_tables) . "</p>";
        echo "<p class='info'>Please run the leave_management.sql file first to create the database schema.</p>";
        echo "<a href='leave_management.sql' class='btn' target='_blank'>View SQL File</a>";
    }
    echo "</div>";

    // Get all users
    echo "<div class='section'>
        <h2>👥 User Management</h2>";
    
    $user_result = $conn->query("SELECT id, employee_id, email, first_name, last_name, role, status FROM users ORDER BY created_at");
    $users = [];
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo "<p class='info'>Found " . count($users) . " users in the system</p>";
    
    if (count($users) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
            <thead>
                <tr style='background: #f8f9fa;'>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>ID</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Employee ID</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Name</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Email</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Role</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Status</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($users as $user) {
            $status_color = $user['status'] === 'active' ? 'success' : 'warning';
            echo "<tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$user['id']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$user['employee_id']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$user['first_name']} {$user['last_name']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$user['email']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$user['role']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'><span class='$status_color'>{$user['status']}</span></td>
            </tr>";
        }
        echo "</tbody></table>";
    }
    echo "</div>";

    // Get all leave types
    echo "<div class='section'>
        <h2>📋 Leave Types</h2>";
    
    $type_result = $conn->query("SELECT id, name, description, max_days, requires_approval, is_active, color FROM leave_types ORDER BY name");
    $leave_types = [];
    while ($row = $type_result->fetch_assoc()) {
        $leave_types[] = $row;
    }
    
    echo "<p class='info'>Found " . count($leave_types) . " leave types</p>";
    
    if (count($leave_types) > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
            <thead>
                <tr style='background: #f8f9fa;'>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>ID</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Name</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Max Days</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Approval Required</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Status</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($leave_types as $type) {
            $approval_text = $type['requires_approval'] ? 'Yes' : 'No';
            $status_text = $type['is_active'] ? 'Active' : 'Inactive';
            $status_color = $type['is_active'] ? 'success' : 'warning';
            echo "<tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$type['id']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$type['name']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$type['max_days']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$approval_text}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'><span class='$status_color'>{$status_text}</span></td>
            </tr>";
        }
        echo "</tbody></table>";
    }
    echo "</div>";

    // Initialize leave balances
    echo "<div class='section'>
        <h2>💰 Leave Balance Initialization</h2>";
    
    if (count($users) > 0 && count($leave_types) > 0) {
        $created_count = 0;
        $updated_count = 0;
        
        foreach ($users as $user) {
            if ($user['status'] !== 'active') {
                continue; // Skip inactive users
            }
            
            foreach ($leave_types as $type) {
                if (!$type['is_active']) {
                    continue; // Skip inactive leave types
                }
                
                $type_id = $type['id'];
                $max_days = $type['max_days'];
                
                // Check if balance record exists
                $stmt = $conn->prepare("SELECT id, total_days, used_days, balance FROM leave_balances WHERE user_id = ? AND leave_type_id = ? AND year = ?");
                $stmt->bind_param('iii', $user['id'], $type_id, $year);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 0) {
                    // Create new balance record
                    $insert = $conn->prepare("INSERT INTO leave_balances (user_id, leave_type_id, total_days, used_days, balance, year) VALUES (?, ?, ?, 0, ?, ?)");
                    $insert->bind_param('iiiii', $user['id'], $type_id, $max_days, $max_days, $year);
                    $insert->execute();
                    $created_count++;
                    echo "<p class='success'>✅ Created balance for {$user['first_name']} {$user['last_name']} - {$type['name']}: {$max_days} days</p>";
                } else {
                    // Update existing record if needed
                    $balance_row = $result->fetch_assoc();
                    if ($balance_row['total_days'] != $max_days || $balance_row['balance'] != ($balance_row['total_days'] - $balance_row['used_days'])) {
                        $new_balance = $max_days - $balance_row['used_days'];
                        $update = $conn->prepare("UPDATE leave_balances SET total_days = ?, balance = ? WHERE user_id = ? AND leave_type_id = ? AND year = ?");
                        $update->bind_param('iiiii', $max_days, $new_balance, $user['id'], $type_id, $year);
                        $update->execute();
                        $updated_count++;
                        echo "<p class='info'>🔄 Updated balance for {$user['first_name']} {$user['last_name']} - {$type['name']}: {$max_days} days (balance: {$new_balance})</p>";
                    }
                }
                $stmt->close();
            }
        }
        
        echo "<p class='success'>✅ Created $created_count new leave balance records</p>";
        if ($updated_count > 0) {
            echo "<p class='info'>🔄 Updated $updated_count existing leave balance records</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ No users or leave types found. Please add users and leave types first.</p>";
    }
    echo "</div>";

    // Show current balances summary
    echo "<div class='section'>
        <h2>📊 Current Leave Balances Summary</h2>";
    
    $balances_query = "
        SELECT 
            u.employee_id,
            u.first_name,
            u.last_name,
            u.email,
            d.name as department,
            lt.name as leave_type,
            lb.total_days,
            lb.used_days,
            lb.balance,
            lb.year
        FROM leave_balances lb
        JOIN users u ON lb.user_id = u.id
        LEFT JOIN departments d ON u.department_id = d.id
        JOIN leave_types lt ON lb.leave_type_id = lt.id
        WHERE lb.year = ? AND u.status = 'active'
        ORDER BY u.first_name, u.last_name, lt.name
        LIMIT 20
    ";
    
    $stmt = $conn->prepare($balances_query);
    $stmt->bind_param('i', $year);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<p class='info'>Showing first 20 active user balances for year $year:</p>";
        echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
            <thead>
                <tr style='background: #f8f9fa;'>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Employee</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Department</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Leave Type</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Total</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Used</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Balance</th>
                </tr>
            </thead>
            <tbody>";
        
        while ($row = $result->fetch_assoc()) {
            $balance_color = $row['balance'] > 0 ? 'success' : 'error';
            echo "<tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$row['first_name']} {$row['last_name']} ({$row['employee_id']})</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$row['department']}</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>{$row['leave_type']}</td>
                <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>{$row['total_days']}</td>
                <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>{$row['used_days']}</td>
                <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'><span class='$balance_color'>{$row['balance']}</span></td>
            </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p class='warning'>No leave balance records found for year $year</p>";
    }
    echo "</div>";

    // System health check
    echo "<div class='section'>
        <h2>🏥 System Health Check</h2>";
    
    // Check for orphaned records
    $orphaned_balances = $conn->query("SELECT COUNT(*) as count FROM leave_balances lb LEFT JOIN users u ON lb.user_id = u.id WHERE u.id IS NULL")->fetch_assoc()['count'];
    $orphaned_applications = $conn->query("SELECT COUNT(*) as count FROM leave_applications la LEFT JOIN users u ON la.user_id = u.id WHERE u.id IS NULL")->fetch_assoc()['count'];
    
    if ($orphaned_balances == 0 && $orphaned_applications == 0) {
        echo "<p class='success'>✅ No orphaned records found</p>";
    } else {
        echo "<p class='warning'>⚠️ Found $orphaned_balances orphaned balance records and $orphaned_applications orphaned application records</p>";
    }
    
    // Check for pending applications
    $pending_count = $conn->query("SELECT COUNT(*) as count FROM leave_applications WHERE status = 'pending'")->fetch_assoc()['count'];
    echo "<p class='info'>📋 Pending leave applications: $pending_count</p>";
    
    // Check for unread notifications
    $unread_notifications = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")->fetch_assoc()['count'];
    echo "<p class='info'>🔔 Unread notifications: $unread_notifications</p>";
    
    echo "</div>";

    echo "<div class='section'>
        <h2>✅ Setup Summary</h2>
        <p class='success'>Leave Management System database setup completed successfully!</p>
        <p class='info'>The system is now ready for use.</p>
        <a href='../index.php' class='btn'>Go to Dashboard</a>
        <a href='../admin.php' class='btn'>Admin Panel</a>
    </div>";

} catch (Exception $e) {
    echo "<div class='section'>
        <h2>❌ Setup Error</h2>
        <p class='error'>An error occurred during setup: " . $e->getMessage() . "</p>
        <p class='info'>Please check your database configuration and try again.</p>
    </div>";
}

echo "</div></body></html>";
?> 