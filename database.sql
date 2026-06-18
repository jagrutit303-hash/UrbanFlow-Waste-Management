-- ============================================================
-- UrbanFlow Database Import Script
-- For FreeSQLDatabase.com / Aiven / any shared cloud MySQL
-- 
-- NOTE: Do NOT run DROP/CREATE/USE commands on shared hosting.
-- Your database is already created. Just import this file.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables in reverse dependency order (safe re-import)
DROP TABLE IF EXISTS fleet_assignments;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS driver_locations;
DROP TABLE IF EXISTS illegal_dumps;
DROP TABLE IF EXISTS disposal_requests;
DROP TABLE IF EXISTS zones;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('citizen', 'admin', 'collector') NOT NULL DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. zones table
CREATE TABLE IF NOT EXISTS zones (
    zone_id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    lat DECIMAL(10, 6) DEFAULT 40.7128,
    lng DECIMAL(10, 6) DEFAULT -74.0060,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. disposal_requests table
CREATE TABLE IF NOT EXISTS disposal_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    zone_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    comment TEXT,
    urgency_level ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    status VARCHAR(50) DEFAULT 'Logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id) ON DELETE CASCADE
);

-- 4. illegal_dumps table
-- (MUST be before fleet_assignments which references it)
CREATE TABLE IF NOT EXISTS illegal_dumps (
    dump_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    zone_id INT NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    voice_note_path VARCHAR(255) DEFAULT NULL,
    volume VARCHAR(50) DEFAULT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    status ENUM('Reported', 'Under Review', 'Cleanup Dispatched', 'Resolved') DEFAULT 'Reported',
    citizen_lat DECIMAL(10, 6) DEFAULT NULL,
    citizen_lng DECIMAL(10, 6) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id) ON DELETE CASCADE
);

-- 5. fleet_assignments table
-- (AFTER illegal_dumps — foreign key dependency)
CREATE TABLE IF NOT EXISTS fleet_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT DEFAULT NULL,
    dump_id INT DEFAULT NULL,
    collector_id INT NOT NULL,
    vehicle_no VARCHAR(50) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES disposal_requests(request_id) ON DELETE CASCADE,
    FOREIGN KEY (dump_id) REFERENCES illegal_dumps(dump_id) ON DELETE CASCADE,
    FOREIGN KEY (collector_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 6. feedback table
CREATE TABLE IF NOT EXISTS feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    rating INT CHECK(rating BETWEEN 1 AND 5),
    comments TEXT,
    voice_feedback_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES disposal_requests(request_id) ON DELETE CASCADE
);

-- 7. driver_locations table
CREATE TABLE IF NOT EXISTS driver_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL UNIQUE,
    lat DECIMAL(10, 6) NOT NULL,
    lng DECIMAL(10, 6) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(user_id) ON DELETE CASCADE
);


-- ============================================================
-- Mock Data
-- Password for ALL accounts below is: pass1234
-- ============================================================

INSERT INTO users (user_id, full_name, email, password, role) VALUES
(1, 'Jagruti (Citizen)', 'citizen@urbanflow.com', '$2y$10$rSBawCIiJCY8ZlD4SEZ4u.Iao.hteQIZqwiiCGAbhfEOWC6q7qoiC', 'citizen'),
(2, 'Driver Dave',       'driver@urbanflow.com',  '$2y$10$rSBawCIiJCY8ZlD4SEZ4u.Iao.hteQIZqwiiCGAbhfEOWC6q7qoiC', 'collector'),
(3, 'Admin User',        'admin@urbanflow.com',   '$2y$10$rSBawCIiJCY8ZlD4SEZ4u.Iao.hteQIZqwiiCGAbhfEOWC6q7qoiC', 'admin');

-- Zones (Davanagere Area)
INSERT INTO zones (zone_id, zone_name, lat, lng) VALUES
(1, 'North District, Sector 4', 14.4677, 75.9218),
(2, 'South District, Sector 9', 14.4700, 75.9100),
(3, 'Downtown Commercial',      14.4600, 75.9300),
(4, 'West End Residential',     14.4500, 75.9000);

-- Disposal Requests
INSERT INTO disposal_requests (request_id, citizen_id, zone_id, category, comment, urgency_level, status) VALUES
(1, 1, 1, 'Organic',    'Pick up from main gate.',         'Medium', 'Logged'),
(2, 1, 2, 'Hazardous',  'Chemical waste in bin.',          'High',   'Dispatched'),
(3, 1, 3, 'Recyclable', 'Old newspapers and boxes.',       'Low',    'Resolved');

-- Illegal Dump Reports
INSERT INTO illegal_dumps (citizen_id, zone_id, description, severity, status, citizen_lat, citizen_lng) VALUES
(1, 4, 'Large pile of construction debris dumped on sidewalk near the park entrance.', 'High',   'Reported',     14.4510, 75.9010),
(1, 1, 'Bags of household trash left outside dumpster area.',                          'Medium', 'Under Review', 14.4680, 75.9220);

-- Fleet Assignment for the Dispatched request
INSERT INTO fleet_assignments (request_id, collector_id, vehicle_no) VALUES
(2, 2, 'KA-17-EV-2026');

-- Driver location
INSERT INTO driver_locations (driver_id, lat, lng) VALUES
(2, 14.4700, 75.9100);
