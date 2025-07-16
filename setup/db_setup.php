<?php
/**
 * NIT Delhi Crowdfunding Platform
 * Database Setup Script
 * 
 * This script creates the necessary database and tables for the platform.
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL without database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS nit_delhi_crowdfunding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    echo "Database created successfully<br>";
    
    // Connect to the new database
    $pdo = new PDO("mysql:host=$host;dbname=nit_delhi_crowdfunding", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student', 'alumni', 'faculty', 'staff') NOT NULL,
        department VARCHAR(50) NOT NULL,
        phone VARCHAR(20),
        bio TEXT,
        is_verified BOOLEAN DEFAULT FALSE,
        is_nit_delhi BOOLEAN DEFAULT FALSE,
        email_verified BOOLEAN DEFAULT FALSE,
        verification_token VARCHAR(64),
        reset_token VARCHAR(64),
        reset_expires DATETIME,
        is_active BOOLEAN DEFAULT TRUE,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Users table created successfully<br>";
    
    // Create campaigns table
    $sql = "CREATE TABLE IF NOT EXISTS campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        short_description VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(50) NOT NULL,
        goal_amount DECIMAL(10, 2) NOT NULL,  /* Changed 'goal' to 'goal_amount' to match code */
        amount_raised DECIMAL(10, 2) DEFAULT 0,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        featured_image VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected', 'completed', 'draft') NOT NULL,
        is_featured BOOLEAN DEFAULT FALSE,
        rejection_reason TEXT,
        department VARCHAR(50),
        location VARCHAR(100),
        team_members TEXT,
        budget_breakdown TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Campaigns table created successfully<br>";
    
    // Create campaign_images table
    $sql = "CREATE TABLE IF NOT EXISTS campaign_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Campaign images table created successfully<br>";
    
    // Create donations table
    $sql = "CREATE TABLE IF NOT EXISTS donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        user_id INT,
        amount DECIMAL(10, 2) NOT NULL,
        donor_name VARCHAR(100),
        donor_email VARCHAR(100),
        is_anonymous BOOLEAN DEFAULT FALSE,
        status ENUM('pending', 'completed', 'failed') NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100),
        order_id VARCHAR(100), /* Added missing field used in code */
        payment_id VARCHAR(100), /* Added missing field used in code */
        is_recurring BOOLEAN DEFAULT FALSE,
        recurring_frequency VARCHAR(20),
        subscription_id VARCHAR(100),
        subscription_status VARCHAR(20),
        tribute_name VARCHAR(100),
        tribute_type VARCHAR(20),
        tribute_message TEXT,
        receipt_id VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Donations table created successfully<br>";
    
    // Create comments table
    $sql = "CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        user_id INT,
        author_name VARCHAR(100),
        comment TEXT NOT NULL,
        is_approved BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Comments table created successfully<br>";
    
    // Create saved_campaigns table
    $sql = "CREATE TABLE IF NOT EXISTS saved_campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        campaign_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY (user_id, campaign_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Saved campaigns table created successfully<br>";
    
    // Create campaign_updates table
    $sql = "CREATE TABLE IF NOT EXISTS campaign_updates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        user_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB";
    
    $pdo->exec($sql);
    echo "Campaign updates table created successfully<br>";
    
    // Create admin user if not exists
    $check_admin = $pdo->query("SELECT id FROM users WHERE email = 'admin@nitdelhi.ac.in' LIMIT 1");
    
    if ($check_admin->rowCount() == 0) {
        // Create admin account
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users 
            (name, email, password, role, department, is_verified, is_nit_delhi, email_verified, is_active) 
            VALUES 
            ('Administrator', 'admin@nitdelhi.ac.in', :password, 'admin', 'admin', TRUE, TRUE, TRUE, TRUE)");
        
        $stmt->bindParam(':password', $admin_password);
        $stmt->execute();
        
        echo "Admin account created successfully<br>";
    } else {
        echo "Admin account already exists<br>";
    }
    
    echo "<br>Database setup completed successfully!";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>