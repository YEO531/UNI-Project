#!/bin/bash

# Validation Script for Hostel Management System
# This script performs various tests to validate the enhanced system

echo "Starting validation of Hostel Management System..."
echo "=================================================="

# Check if project structure exists
echo -e "\n1. Checking project structure..."
if [ -d "/home/ubuntu/enhanced_hostel_system" ]; then
    echo "✓ Project structure exists"
    
    # Check for key directories
    for dir in config src public; do
        if [ -d "/home/ubuntu/enhanced_hostel_system/$dir" ]; then
            echo "  ✓ $dir directory exists"
        else
            echo "  ✗ $dir directory missing"
        fi
    done
else
    echo "✗ Project structure not found"
fi

# Check for key files
echo -e "\n2. Checking key files..."
key_files=(
    "/home/ubuntu/enhanced_hostel_system/config/config.php"
    "/home/ubuntu/enhanced_hostel_system/config/schema.sql"
    "/home/ubuntu/enhanced_hostel_system/public/index.php"
    "/home/ubuntu/enhanced_hostel_system/public/css/style.css"
    "/home/ubuntu/enhanced_hostel_system/src/views/layouts/default.php"
)

for file in "${key_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $(basename "$file") exists"
    else
        echo "  ✗ $(basename "$file") missing"
    fi
done

# Check for models
echo -e "\n3. Checking models..."
model_files=(
    "/home/ubuntu/enhanced_hostel_system/src/models/Database.php"
    "/home/ubuntu/enhanced_hostel_system/src/models/Auth.php"
    "/home/ubuntu/enhanced_hostel_system/src/models/User.php"
    "/home/ubuntu/enhanced_hostel_system/src/models/Room.php"
    "/home/ubuntu/enhanced_hostel_system/src/models/Booking.php"
    "/home/ubuntu/enhanced_hostel_system/src/models/Appointment.php"
    "/home/ubuntu/enhanced_hostel_system/src/models/Payment.php"
)

for file in "${model_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $(basename "$file") exists"
    else
        echo "  ✗ $(basename "$file") missing"
    fi
done

# Check for controllers
echo -e "\n4. Checking controllers..."
controller_files=(
    "/home/ubuntu/enhanced_hostel_system/src/controllers/Controller.php"
    "/home/ubuntu/enhanced_hostel_system/src/controllers/AuthController.php"
    "/home/ubuntu/enhanced_hostel_system/src/controllers/RoomController.php"
    "/home/ubuntu/enhanced_hostel_system/src/controllers/BookingController.php"
    "/home/ubuntu/enhanced_hostel_system/src/controllers/DashboardController.php"
    "/home/ubuntu/enhanced_hostel_system/src/controllers/ApiController.php"
)

for file in "${controller_files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $(basename "$file") exists"
    else
        echo "  ✗ $(basename "$file") missing"
    fi
done

# Check for security features
echo -e "\n5. Checking security features..."
security_features=(
    "CSRF protection" "/home/ubuntu/enhanced_hostel_system/src/utils/Util.php" "csrfField"
    "Password hashing" "/home/ubuntu/enhanced_hostel_system/src/models/Auth.php" "password_hash"
    "Input validation" "/home/ubuntu/enhanced_hostel_system/src/utils/Util.php" "validate"
    "SQL injection prevention" "/home/ubuntu/enhanced_hostel_system/src/models/Database.php" "prepare"
    "XSS prevention" "/home/ubuntu/enhanced_hostel_system/src/utils/Util.php" "escape"
)

for ((i=0; i<${#security_features[@]}; i+=3)); do
    feature="${security_features[i]}"
    file="${security_features[i+1]}"
    pattern="${security_features[i+2]}"
    
    if [ -f "$file" ] && grep -q "$pattern" "$file"; then
        echo "  ✓ $feature implemented"
    else
        echo "  ✗ $feature not found"
    fi
done

# Check for UI improvements
echo -e "\n6. Checking UI improvements..."
ui_features=(
    "Bootstrap integration" "/home/ubuntu/enhanced_hostel_system/src/views/layouts/default.php" "bootstrap"
    "Responsive design" "/home/ubuntu/enhanced_hostel_system/public/css/style.css" "media"
    "Notification system" "/home/ubuntu/enhanced_hostel_system/src/views/layouts/default.php" "notification"
    "Form validation" "/home/ubuntu/enhanced_hostel_system/src/controllers/AuthController.php" "validate"
)

for ((i=0; i<${#ui_features[@]}; i+=3)); do
    feature="${ui_features[i]}"
    file="${ui_features[i+1]}"
    pattern="${ui_features[i+2]}"
    
    if [ -f "$file" ] && grep -q "$pattern" "$file"; then
        echo "  ✓ $feature implemented"
    else
        echo "  ✗ $feature not found"
    fi
done

# Check for API endpoints
echo -e "\n7. Checking API endpoints..."
if [ -f "/home/ubuntu/enhanced_hostel_system/src/controllers/ApiController.php" ]; then
    api_endpoints=(
        "Get rooms" "getRooms"
        "Get available rooms" "getAvailableRooms"
        "Get room details" "getRoom"
        "Get bookings" "getBookings"
        "Create booking" "createBooking"
        "Cancel booking" "cancelBooking"
        "Get user profile" "getUserProfile"
        "Update user profile" "updateUserProfile"
    )
    
    for ((i=0; i<${#api_endpoints[@]}; i+=2)); do
        endpoint="${api_endpoints[i]}"
        method="${api_endpoints[i+1]}"
        
        if grep -q "$method" "/home/ubuntu/enhanced_hostel_system/src/controllers/ApiController.php"; then
            echo "  ✓ $endpoint API endpoint implemented"
        else
            echo "  ✗ $endpoint API endpoint not found"
        fi
    done
else
    echo "  ✗ API Controller not found"
fi

# Check for database improvements
echo -e "\n8. Checking database improvements..."
db_features=(
    "Foreign key constraints" "/home/ubuntu/enhanced_hostel_system/config/schema.sql" "FOREIGN KEY"
    "Indexes" "/home/ubuntu/enhanced_hostel_system/config/schema.sql" "CREATE INDEX"
    "Additional tables" "/home/ubuntu/enhanced_hostel_system/config/schema.sql" "room_categories"
    "Timestamps" "/home/ubuntu/enhanced_hostel_system/config/schema.sql" "created_at"
)

for ((i=0; i<${#db_features[@]}; i+=3)); do
    feature="${db_features[i]}"
    file="${db_features[i+1]}"
    pattern="${db_features[i+2]}"
    
    if [ -f "$file" ] && grep -q "$pattern" "$file"; then
        echo "  ✓ $feature implemented"
    else
        echo "  ✗ $feature not found"
    fi
done

echo -e "\nValidation completed!"
echo "=================================================="
