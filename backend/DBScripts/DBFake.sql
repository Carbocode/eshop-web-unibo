use soccer_tshirt_shop;
-- Teams data
INSERT INTO teams (name, image_url, type, country) VALUES
('Napoli', 'https://example.com/napoli.jpg', 'club', 'Italy'),
('Inter', 'https://example.com/inter.jpg', 'club', 'Italy'),
('Milan', 'https://example.com/milan.jpg', 'club', 'Italy'),
('Italy', 'https://example.com/italy.jpg', 'national', 'Italy'),
('Argentina', 'https://example.com/argentina.jpg', 'national', 'Argentina');

-- Editions data
INSERT INTO editions (name, year, description) VALUES
('Home 2024/25', 2024, 'Home kit for 2024/25 season'),
('Away 2024/25', 2024, 'Away kit for 2024/25 season'),
('Third 2024/25', 2024, 'Third kit for 2024/25 season'),
('Vintage 1987', 1987, 'Replica of the historic 1987 kit'),
('Special Edition', 2024, 'Limited Champions League Edition');

-- T-shirts data
INSERT INTO tshirts (team_id, edition_id, size, price, stock_quantity, image_url) VALUES
(1, 1, 'M', 89.99, 50, 'https://example.com/napoli-home.jpg'),
(1, 2, 'L', 89.99, 30, 'https://example.com/napoli-away.jpg'),
(2, 1, 'S', 89.99, 25, 'https://example.com/inter-home.jpg'),
(3, 1, 'XL', 89.99, 20, 'https://example.com/milan-home.jpg'),
(4, 1, 'M', 99.99, 40, 'https://example.com/italy-home.jpg');

-- Customers data
INSERT INTO customers (email, password_hash, first_name, last_name, phone, image_url) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', '+1234567890', 'https://example.com/john.jpg'),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', '+0987654321', 'https://example.com/jane.jpg');

-- Addresses data
INSERT INTO addresses (customer_id, street_address, city, state, postal_code, country, is_default) VALUES
(1, 'Via Roma 123', 'Rome', 'Lazio', '00100', 'Italy', true),
(2, 'Via Milano 456', 'Milan', 'Lombardy', '20100', 'Italy', true);

-- Admins data
INSERT INTO admins (email, password_hash, first_name, last_name, role, image_url) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'super_admin', 'https://example.com/admin.jpg');

-- Cart items data
INSERT INTO cart_items (customer_id, tshirt_id, quantity) VALUES
(1, 1, 2),
(2, 3, 1);

-- Orders data
INSERT INTO orders (customer_id, address_id, order_status, total_amount) VALUES
(1, 1, 'processing', 179.98),
(2, 2, 'delivered', 89.99);

-- Order items data
INSERT INTO order_items (order_id, tshirt_id, quantity, unit_price) VALUES
(1, 1, 2, 89.99),
(2, 3, 1, 89.99);