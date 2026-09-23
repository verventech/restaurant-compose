-- Create Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_order INT DEFAULT 0
);

-- Create Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id SERIAL PRIMARY KEY,
    category_id INT REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price NUMERIC(10, 2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Categories
INSERT INTO categories (name, display_order) VALUES
('Starters & Appetizers', 1),
('Main Courses', 2),
('Desserts', 3),
('Beverages', 4);

-- Seed Menu Items
INSERT INTO menu_items (category_id, name, description, price, is_available) VALUES
(1, 'Truffle Garlic Bread', 'Toasted artisan bread topped with garlic butter, herbs, and black truffle oil.', 8.50, TRUE),
(1, 'Crispy Calamari', 'Panko-crusted squid served with house-made smoked paprika aioli.', 12.00, TRUE),
(2, 'Wood-Fired Margherita Pizza', 'San Marzano tomatoes, fresh mozzarella, basil, and extra virgin olive oil.', 15.99, TRUE),
(2, 'Pan-Seared Salmon', 'Atlantic salmon served over wild mushroom risotto and lemon asparagus.', 22.50, TRUE),
(2, 'Classic Cheeseburger', 'Angus beef patty, aged cheddar, caramelized onions, and secret sauce on brioche.', 14.00, TRUE),
(3, 'Chocolate Lava Cake', 'Warm chocolate cake with a molten center, served with vanilla bean ice cream.', 7.50, TRUE),
(3, 'Classic Tiramisu', 'Espresso-soaked ladyfingers layered with mascarpone cream and cocoa dust.', 6.50, TRUE),
(4, 'Craft Iced Lemon Tea', 'Freshly brewed black tea infused with lemon and mint.', 4.00, TRUE),
(4, 'Sparkling Espresso', 'Double shot of espresso combined with tonic water and fresh orange slice.', 5.00, TRUE);