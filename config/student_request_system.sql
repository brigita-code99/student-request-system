CREATE DATABASE IF NOT EXISTS student_request_system;
USE student_request_system;

CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'student') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS request_types (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS requests (
    id INT NOT NULL AUTO_INCREMENT,
    student_id INT NOT NULL,
    type_id INT NOT NULL,
    subject VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved', 'Rejected') NOT NULL DEFAULT 'Pending',
    staff_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY student_id (student_id),
    KEY type_id (type_id),
    CONSTRAINT requests_ibfk_1 FOREIGN KEY (student_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT requests_ibfk_2 FOREIGN KEY (type_id) REFERENCES request_types (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS service_requests (
    id INT NOT NULL AUTO_INCREMENT,
    student_id INT NOT NULL,
    request_type_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Pending', 'Processing', 'Completed', 'Rejected') DEFAULT 'Pending',
    assigned_staff_id INT DEFAULT NULL,
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY student_id (student_id),
    KEY request_type_id (request_type_id),
    KEY assigned_staff_id (assigned_staff_id),
    CONSTRAINT service_requests_ibfk_1 FOREIGN KEY (student_id) REFERENCES users (id),
    CONSTRAINT service_requests_ibfk_2 FOREIGN KEY (request_type_id) REFERENCES request_types (id),
    CONSTRAINT service_requests_ibfk_3 FOREIGN KEY (assigned_staff_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS request_updates (
    id INT NOT NULL AUTO_INCREMENT,
    request_id INT NOT NULL,
    staff_id INT NOT NULL,
    old_status VARCHAR(30) DEFAULT NULL,
    new_status VARCHAR(30) DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY request_id (request_id),
    KEY staff_id (staff_id),
    CONSTRAINT request_updates_ibfk_1 FOREIGN KEY (request_id) REFERENCES service_requests (id) ON DELETE CASCADE,
    CONSTRAINT request_updates_ibfk_2 FOREIGN KEY (staff_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO request_types (name, description)
SELECT * FROM (
    SELECT 'Enrollment', 'Enrollment-related requests' AS description
    UNION ALL SELECT 'Scholarship', 'Scholarship requests' 
    UNION ALL SELECT 'Records', 'Student records requests' 
    UNION ALL SELECT 'Technical Support', 'IT support requests' 
    UNION ALL SELECT 'Other', 'General requests'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM request_types LIMIT 1);
