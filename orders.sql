create database restorant;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES produck(id)
);

CREATE TABLE IF NOT EXISTS produck (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    image_url VARCHAR(255),
    category ENUM('MakananBerat', 'MakananRingan') NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO produck (name, description, price, image_url, category, stock) VALUES 
('Nasi Gudeg Yogya', 'Nasi gudeg khas Yogyakarta dengan ayam dan telur', 25000.00, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', 'MakananBerat', 50),
('Soto Ayam', 'Soto ayam hangat dengan bumbu rempah pilihan', 18000.00, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400', 'MakananBerat', 30),
('Gado-gado', 'Gado-gado segar dengan bumbu kacang spesial', 15000.00, 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=400', 'MakananBerat', 25),
('Rendang Daging', 'Rendang daging sapi empuk dengan bumbu tradisional', 35000.00, 'https://images.unsplash.com/photo-1555126634-323283e090fa?w=400', 'MakananBerat', 20),
('Nasi Liwet', 'Nasi liwet dengan lauk pauk lengkap', 22000.00, 'https://images.unsplash.com/photo-1536304447766-da0ed4ce1b73?w=400', 'MakananBerat', 40),

('Keripik Singkong', 'Keripik singkong renyah dengan berbagai rasa', 12000.00, 'https://images.unsplash.com/photo-1621939514649-280e2ee25f60?w=400', 'MakananRingan', 100),
('Pisang Goreng', 'Pisang goreng crispy dengan topping pilihan', 10000.00, 'https://images.unsplash.com/photo-1541745537411-b8046dc6d66c?w=400', 'MakananRingan', 75),
('Tahu Crispy', 'Tahu goreng crispy dengan saus sambal', 8000.00, 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400', 'MakananRingan', 60),
('Cireng Isi', 'Cireng isi dengan berbagai pilihan isian', 7000.00, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400', 'MakananRingan', 80),
('Es Cendol', 'Es cendol segar dengan santan dan gula merah', 12000.00, 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400', 'MakananRingan', 45);