USE catering_demo;

-- Admin user (demo)
INSERT INTO admin(email,password) VALUES
('admin@gmail.com','1234');

-- Customers
INSERT INTO customer(name,email,address,phone_no) VALUES
('Rahim','rahim@gmail.com','Mirpur, Dhaka','01711111111'),
('Karim','karim@gmail.com','Uttara, Dhaka','01822222222');

-- Delivery men
INSERT INTO delivery_man(name,contact_no,available,not_available,A_id) VALUES
('Hasan','01700000001',1,0,1),
('Sabbir','01700000002',1,0,1);

-- Menu
INSERT INTO menu(item_name,price,customer_id) VALUES
('Chicken Biryani',250.00,1),
('Beef Tehari',280.00,2);

INSERT INTO category(category,m_id) VALUES
('Rice',1),
('Rice',2);
