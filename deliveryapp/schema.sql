CREATE DATABASE IF NOT EXISTS catering_demo;
USE catering_demo;

-- CUSTOMER
CREATE TABLE IF NOT EXISTS customer (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  address VARCHAR(255) NOT NULL,
  phone_no VARCHAR(20) NOT NULL
);

-- MENU
CREATE TABLE IF NOT EXISTS menu (
  m_id INT AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(120) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  customer_id INT NULL,
  CONSTRAINT fk_menu_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
);

-- CATEGORY (category, m_id)
CREATE TABLE IF NOT EXISTS category (
  category VARCHAR(80) NOT NULL,
  m_id INT NOT NULL,
  PRIMARY KEY (category, m_id),
  CONSTRAINT fk_category_menu FOREIGN KEY (m_id) REFERENCES menu(m_id)
);

-- ADMIN
CREATE TABLE IF NOT EXISTS admin (
  A_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- DELIVERY MAN
CREATE TABLE IF NOT EXISTS delivery_man (
  dm_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact_no VARCHAR(20) NOT NULL,
  available TINYINT(1) NOT NULL DEFAULT 1,
  not_available TINYINT(1) NOT NULL DEFAULT 0,
  A_id INT NULL,
  CONSTRAINT fk_dm_admin FOREIGN KEY (A_id) REFERENCES admin(A_id)
);

-- DELIVERY
CREATE TABLE IF NOT EXISTS delivery (
  d_id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_no VARCHAR(30) NOT NULL,
  dm_name VARCHAR(100) NULL,
  delivery_time DATETIME NULL,
  delivered TINYINT(1) NOT NULL DEFAULT 0,
  schedule TINYINT(1) NOT NULL DEFAULT 0,
  out_of_delivery TINYINT(1) NOT NULL DEFAULT 0,
  otp_code VARCHAR(10) NULL
);

-- DELIVERS (customer <-> delivery)
CREATE TABLE IF NOT EXISTS delivers (
  customer_id INT NOT NULL,
  d_id INT NOT NULL,
  PRIMARY KEY (customer_id, d_id),
  CONSTRAINT fk_delivers_customer FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
  CONSTRAINT fk_delivers_delivery FOREIGN KEY (d_id) REFERENCES delivery(d_id)
);

-- MAKES (delivery_man <-> delivery)
CREATE TABLE IF NOT EXISTS makes (
  d_id INT NOT NULL,
  dm_id INT NOT NULL,
  PRIMARY KEY (d_id, dm_id),
  CONSTRAINT fk_makes_delivery FOREIGN KEY (d_id) REFERENCES delivery(d_id),
  CONSTRAINT fk_makes_dm FOREIGN KEY (dm_id) REFERENCES delivery_man(dm_id)
);

-- MANAGES (admin <-> delivery)
CREATE TABLE IF NOT EXISTS manages (
  A_id INT NOT NULL,
  d_id INT NOT NULL,
  PRIMARY KEY (A_id, d_id),
  CONSTRAINT fk_manages_admin FOREIGN KEY (A_id) REFERENCES admin(A_id),
  CONSTRAINT fk_manages_delivery FOREIGN KEY (d_id) REFERENCES delivery(d_id)
);


CREATE INDEX idx_delivery_schedule ON delivery(schedule, delivered);
