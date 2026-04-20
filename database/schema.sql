CREATE TABLE IF NOT EXISTS requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    name VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(50) NOT NULL,
    service_type VARCHAR(255) DEFAULT NULL,
    message TEXT,
    file_path VARCHAR(500) DEFAULT NULL,
    estimated_price VARCHAR(50) DEFAULT NULL,
    source VARCHAR(50) DEFAULT 'site',
    status ENUM('new', 'in_progress', 'completed') NOT NULL DEFAULT 'new'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
