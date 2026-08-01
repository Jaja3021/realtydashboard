-- Realty Dashboard schema
-- Import this file via phpMyAdmin, or run: mysql -u root -p < db.sql

CREATE DATABASE IF NOT EXISTS realty_dashboard;
USE realty_dashboard;

CREATE TABLE IF NOT EXISTS sales (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    property_name  VARCHAR(150) NOT NULL,
    property_type  VARCHAR(50)  NOT NULL,
    location       VARCHAR(150) NULL,
    buyer_name     VARCHAR(120) NOT NULL,
    buyer_contact  VARCHAR(60)  NULL,
    price          DECIMAL(15,2) NOT NULL,
    agent_name     VARCHAR(100) NOT NULL,
    date_sold      DATE NOT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data so the dashboard has something to show on first load.
-- Feel free to delete these rows from the Sales list once you add real ones.
INSERT INTO sales (property_name, property_type, location, buyer_name, buyer_contact, price, agent_name, date_sold) VALUES
('Sunrise Villas Blk 3 Lot 12', 'House and Lot', 'Cavite',        'Maria Santos',   '0917-111-2222', 4500000.00, 'Jenny Cruz',    '2026-07-03'),
('Greenfield Residences #402', 'Condominium',   'Quezon City',   'Paolo Reyes',    '0918-222-3333', 3200000.00, 'Mark Villanueva','2026-07-08'),
('Lot 7 Palm Estates',         'Lot Only',      'Batangas',      'Ana Lim',        '0919-333-4444',  980000.00, 'Jenny Cruz',    '2026-07-15'),
('The Meadows Townhome C-1',   'Townhouse',     'Laguna',        'Carlo Dizon',    '0920-444-5555', 2750000.00, 'Rico Tan',      '2026-07-19'),
('Skyline Tower Unit 1801',    'Condominium',   'Makati',        'Grace Uy',       '0921-555-6666', 6100000.00, 'Mark Villanueva','2026-07-22'),
('Sunrise Villas Blk 1 Lot 5', 'House and Lot', 'Cavite',        'Ferdinand Chua', '0922-666-7777', 4300000.00, 'Jenny Cruz',    '2026-07-25'),
('Commercial Space Unit G-2',  'Commercial',    'Pasig',         'Liza Robles',    '0923-777-8888', 8900000.00, 'Rico Tan',      '2026-06-10'),
('Greenfield Residences #210', 'Condominium',   'Quezon City',   'Noel Aquino',    '0924-888-9999', 3100000.00, 'Mark Villanueva','2026-06-18');
