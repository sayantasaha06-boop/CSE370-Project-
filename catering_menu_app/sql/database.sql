-- Catering Management System Database Setup
-- Run these commands in your MySQL client (phpMyAdmin, MySQL Workbench, or command line)

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS catering 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. Use the database
USE catering;

-- 3. Menu Items Table (already handled by your db_connect.php)
-- This table is auto-created by your existing code, but here's the explicit version:
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('Veg','Non-Veg','Drinks','Desserts') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    delivery_time_minutes INT NOT NULL,
    description TEXT,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_available (is_available),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Meal Packages Table
CREATE TABLE IF NOT EXISTS meal_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    target_audience ENUM('Bachelor','Student','Office','Family','Premium') NOT NULL,
    daily_price DECIMAL(10,2) NOT NULL,
    description TEXT,
    features JSON,
    meals_included JSON,
    image_url VARCHAR(500),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_audience (target_audience),
    INDEX idx_active (is_active),
    INDEX idx_price (daily_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Package Orders Table
CREATE TABLE IF NOT EXISTS package_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    delivery_address TEXT NOT NULL,
    start_date DATE NOT NULL,
    duration_days INT NOT NULL,
    meals_per_day INT NOT NULL DEFAULT 3,
    total_amount DECIMAL(10,2) NOT NULL,
    special_instructions TEXT,
    payment_status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
    order_status ENUM('Active','Paused','Completed','Cancelled') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES meal_packages(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_name),
    INDEX idx_status (order_status),
    INDEX idx_payment (payment_status),
    INDEX idx_start_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Insert Sample Meal Packages
INSERT INTO meal_packages (name, target_audience, daily_price, description, features, meals_included, image_url) VALUES
('Basic Bachelor Pack', 'Bachelor', 120.00, 'Simple, nutritious meals perfect for working bachelors', 
 '["Home-cooked taste", "Balanced nutrition", "Quick delivery", "No cooking required"]',
 '["lunch", "dinner"]', 
 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400'),

('Student Special', 'Student', 80.00, 'Budget-friendly meals with good portions for students',
 '["Budget-friendly", "Large portions", "Healthy options", "Free delivery"]',
 '["lunch", "dinner"]',
 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=400'),

('Office Executive', 'Office', 180.00, 'Premium meals delivered to your office on time',
 '["Office delivery", "Premium quality", "Variety menu", "Professional packaging"]',
 '["breakfast", "lunch", "dinner"]',
 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400'),

('Family Feast', 'Family', 320.00, 'Complete family meals for 4 people with variety',
 '["Serves 4 people", "Traditional recipes", "Fresh ingredients", "Weekend specials"]',
 '["breakfast", "lunch", "dinner"]',
 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400'),

('Premium Gourmet', 'Premium', 450.00, 'Luxury dining experience with chef-curated meals',
 '["Chef-curated", "Premium ingredients", "Gourmet presentation", "Wine pairing suggestions"]',
 '["breakfast", "lunch", "dinner"]',
 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=400');

-- 7. Insert Sample Menu Items
INSERT INTO menu_items (name, category, price, delivery_time_minutes, description, is_available) VALUES
-- Veg Items
('Paneer Butter Masala', 'Veg', 180.00, 25, 'Creamy paneer curry with butter and spices', 1),
('Dal Tadka', 'Veg', 120.00, 20, 'Traditional lentil curry with tempering', 1),
('Vegetable Biryani', 'Veg', 200.00, 35, 'Fragrant basmati rice with mixed vegetables', 1),
('Aloo Gobi', 'Veg', 140.00, 22, 'Spiced potato and cauliflower curry', 1),
('Palak Paneer', 'Veg', 170.00, 25, 'Paneer in creamy spinach gravy', 1),

-- Non-Veg Items
('Chicken Curry', 'Non-Veg', 220.00, 30, 'Traditional chicken curry with aromatic spices', 1),
('Mutton Biryani', 'Non-Veg', 280.00, 40, 'Fragrant basmati rice with tender mutton pieces', 1),
('Fish Fry', 'Non-Veg', 200.00, 25, 'Crispy fried fish with spices', 1),
('Chicken Tikka', 'Non-Veg', 240.00, 35, 'Grilled chicken pieces with yogurt marinade', 1),
('Prawn Curry', 'Non-Veg', 260.00, 28, 'Spicy prawn curry in coconut gravy', 1),

-- Drinks
('Fresh Lime Soda', 'Drinks', 40.00, 5, 'Refreshing lime soda with mint', 1),
('Mango Lassi', 'Drinks', 60.00, 8, 'Creamy mango yogurt drink', 1),
('Masala Chai', 'Drinks', 25.00, 10, 'Traditional spiced tea', 1),
('Fresh Fruit Juice', 'Drinks', 50.00, 8, 'Seasonal fresh fruit juice', 1),
('Cold Coffee', 'Drinks', 70.00, 12, 'Iced coffee with milk and sugar', 1),

-- Desserts
('Gulab Jamun', 'Desserts', 80.00, 15, 'Sweet milk balls in sugar syrup', 1),
('Rasgulla', 'Desserts', 70.00, 12, 'Spongy cottage cheese balls in syrup', 1),
('Ice Cream', 'Desserts', 60.00, 8, 'Assorted flavors of ice cream', 1),
('Kheer', 'Desserts', 90.00, 20, 'Traditional rice pudding with nuts', 1),
('Chocolate Brownie', 'Desserts', 120.00, 18, 'Rich chocolate brownie with vanilla ice cream', 1);

-- 8. Create indexes for better performance
CREATE INDEX idx_menu_category_available ON menu_items (category, is_available);
CREATE INDEX idx_orders_customer_date ON package_orders (customer_name, start_date);
CREATE INDEX idx_packages_audience_active ON meal_packages (target_audience, is_active);

-- 9. Show table structure (optional - for verification)
SHOW TABLES;
DESCRIBE menu_items;
DESCRIBE meal_packages;  
DESCRIBE package_orders;