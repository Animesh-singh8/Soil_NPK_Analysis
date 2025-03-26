-- Create database
CREATE DATABASE IF NOT EXISTS soil_npk_analysis;
USE soil_npk_analysis;

-- Create states table
CREATE TABLE IF NOT EXISTS states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Create districts table
CREATE TABLE IF NOT EXISTS districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    FOREIGN KEY (state_id) REFERENCES states(id)
);

-- Create crops table
CREATE TABLE IF NOT EXISTS crops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- Create crop requirements table
CREATE TABLE IF NOT EXISTS crop_requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    state_id INT NOT NULL,
    district_id INT NOT NULL,
    required_n FLOAT NOT NULL,
    required_p FLOAT NOT NULL,
    required_k FLOAT NOT NULL,
    min_ph FLOAT NOT NULL,
    max_ph FLOAT NOT NULL,
    FOREIGN KEY (crop_id) REFERENCES crops(id),
    FOREIGN KEY (state_id) REFERENCES states(id),
    FOREIGN KEY (district_id) REFERENCES districts(id)
);

-- Create fertilizers table
CREATE TABLE IF NOT EXISTS fertilizers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    nitrogen_percent FLOAT NOT NULL,
    phosphorus_percent FLOAT NOT NULL,
    potassium_percent FLOAT NOT NULL,
    ph_effect VARCHAR(50) NOT NULL
);

-- Insert sample states
INSERT INTO states (name) VALUES 
('Punjab'), ('Bihar'), ('Maharashtra'), ('Gujarat'), ('Uttar Pradesh'),
('Rajasthan'), ('Madhya Pradesh'), ('Karnataka'), ('Tamil Nadu'), ('Andhra Pradesh');

-- Insert sample districts (for each state)
INSERT INTO districts (state_id, name) VALUES
(1, 'Ludhiana'), (1, 'Amritsar'), (1, 'Patiala'),
(2, 'Patna'), (2, 'Gaya'), (2, 'Muzaffarpur'),
(3, 'Pune'), (3, 'Mumbai'), (3, 'Nagpur'),
(4, 'Surat'), (4, 'Ahmedabad'), (4, 'Vadodara'),
(5, 'Varanasi'), (5, 'Lucknow'), (5, 'Agra'),
(6, 'Jaipur'), (6, 'Jodhpur'), (6, 'Udaipur'),
(7, 'Bhopal'), (7, 'Indore'), (7, 'Jabalpur'),
(8, 'Bangalore'), (8, 'Mysore'), (8, 'Hubli'),
(9, 'Chennai'), (9, 'Coimbatore'), (9, 'Madurai'),
(10, 'Hyderabad'), (10, 'Visakhapatnam'), (10, 'Vijayawada');

-- Insert sample crops
INSERT INTO crops (name) VALUES
('Wheat'), ('Rice'), ('Maize'), ('Cotton'), ('Sugarcane'),
('Soybean'), ('Groundnut'), ('Potato'), ('Tomato'), ('Onion');

-- Insert sample crop requirements
-- For each crop in each district, add NPK requirements and pH range
INSERT INTO crop_requirements (crop_id, state_id, district_id, required_n, required_p, required_k, min_ph, max_ph) VALUES
-- Wheat
(1, 1, 1, 120, 60, 40, 6.0, 7.5), -- Punjab, Ludhiana
(1, 2, 4, 110, 55, 35, 6.0, 7.5), -- Bihar, Patna
(1, 3, 7, 130, 65, 45, 6.0, 7.5), -- Maharashtra, Pune

-- Rice
(2, 1, 1, 100, 50, 35, 5.5, 7.0), -- Punjab, Ludhiana
(2, 2, 4, 90, 45, 30, 5.5, 7.0),  -- Bihar, Patna
(2, 3, 7, 110, 55, 40, 5.5, 7.0), -- Maharashtra, Pune

-- Maize
(3, 1, 1, 150, 70, 50, 5.8, 7.2), -- Punjab, Ludhiana
(3, 2, 4, 140, 65, 45, 5.8, 7.2), -- Bihar, Patna
(3, 3, 7, 160, 75, 55, 5.8, 7.2), -- Maharashtra, Pune

-- Cotton
(4, 4, 10, 160, 80, 60, 6.2, 7.8), -- Gujarat, Surat
(4, 3, 7, 150, 75, 55, 6.2, 7.8),  -- Maharashtra, Pune
(4, 10, 28, 170, 85, 65, 6.2, 7.8), -- Andhra Pradesh, Hyderabad

-- Sugarcane
(5, 5, 13, 180, 90, 80, 5.5, 7.5), -- UP, Varanasi
(5, 9, 25, 170, 85, 75, 5.5, 7.5), -- Tamil Nadu, Chennai
(5, 3, 7, 190, 95, 85, 5.5, 7.5);  -- Maharashtra, Pune

-- Insert fertilizer data
INSERT INTO fertilizers (name, nitrogen_percent, phosphorus_percent, potassium_percent, ph_effect) VALUES
('Urea', 46, 0, 0, 'Increases soil acidity'),
('DAP (Diammonium Phosphate)', 18, 46, 0, 'Slightly decreases pH'),
('MOP (Muriate of Potash)', 0, 0, 60, 'Neutral effect'),
('NPK 10-26-26', 10, 26, 26, 'Neutral effect'),
('Organic Compost', 2, 1, 1.5, 'Increases soil alkalinity');