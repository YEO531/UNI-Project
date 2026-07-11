  -- Users (students, admins, staff)
  CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student','admin','staff') NOT NULL,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(180) UNIQUE NOT NULL,
    phone VARCHAR(14),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  
  -- Rooms
  CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(180) NOT NULL,
    capacity INT NOT NULL,
    current_occupancy INT DEFAULT 0,
    status ENUM('available','booked','occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  
  -- Bookings
  CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(id),
    room_id INT NOT NULL REFERENCES rooms(id),
    date DATE NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  
  -- Appointments
  CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(id),
    room_id INT NOT NULL REFERENCES rooms(id),
    date DATETIME NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
  
  -- Maintenance Requests
  CREATE TABLE repair_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(id),
    room_id INT NOT NULL REFERENCES rooms(id),
    description VARCHAR(255),
    request_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    scheduled_date DATETIME,
    status ENUM('pending','in_progress','done') DEFAULT 'pending'
  );
  
  -- Payments
  CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL REFERENCES users(id),
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(180),
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','completed') DEFAULT 'pending'
  );