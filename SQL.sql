--  Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) CHECK (role IN ('user', 'admin', 'kitchen', 'reception')) NOT NULL,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

--  Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

-- ️ Menu Items Table
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    category_id INT,
    available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

--  Menu Variants Table
CREATE TABLE menu_variants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT NOT NULL,
    variant_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (item_id) REFERENCES menu_items(id)
);

--  Menu Add-ons Table
CREATE TABLE menu_add_ons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT NOT NULL,
    addon_name VARCHAR(100) NOT NULL,
    addon_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (item_id) REFERENCES menu_items(id)
);

--  Orders Table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    table_number VARCHAR(20),
    total_amount DECIMAL(10, 2) NOT NULL,
    special_request TEXT,
    order_status VARCHAR(50) CHECK (order_status IN ('pending', 'preparing', 'served', 'cancelled')) NOT NULL DEFAULT 'pending',
    status_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

--  Cart Item Table
CREATE TABLE cart_item (
    cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
    cart_id INT,
    item_id INT NOT NULL,
    variant_id INT,
    special_request TEXT,
    quantity INT NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (item_id) REFERENCES menu_items(id),
    FOREIGN KEY (variant_id) REFERENCES menu_variants(id)
    -- Optional: FOREIGN KEY (cart_id) REFERENCES cart(id)
);

--  Cart Item Addon Table
CREATE TABLE cart_item_addon (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cart_item_id INT NOT NULL,
    menu_addon_id INT NOT NULL,
    FOREIGN KEY (cart_item_id) REFERENCES cart_item(cart_item_id),
    FOREIGN KEY (menu_addon_id) REFERENCES menu_add_ons(id)
);

--  Table Bookings
CREATE TABLE table_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    number_of_people INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    special_request TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- User Feedback
CREATE TABLE user_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_email VARCHAR(255)
);

-- Contact Info
CREATE TABLE contact_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    value VARCHAR(255) NOT NULL
);
