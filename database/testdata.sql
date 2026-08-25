
USE crewsync;

DELETE FROM reports;
DELETE FROM notifications;
DELETE FROM feedback;
DELETE FROM project_comments;
DELETE FROM reviews;
DELETE FROM material_orders;
DELETE FROM service_requests;
DELETE FROM task_assignments;
DELETE FROM tasks;
DELETE FROM projects;
DELETE FROM supplier_materials;
DELETE FROM hardware_stores;
DELETE FROM provider_skills;
DELETE FROM supplier_profiles;
DELETE FROM service_providers;
DELETE FROM property_owners;
DELETE FROM users;

ALTER TABLE reports AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE feedback AUTO_INCREMENT = 1;
ALTER TABLE project_comments AUTO_INCREMENT = 1;
ALTER TABLE reviews AUTO_INCREMENT = 1;
ALTER TABLE material_orders AUTO_INCREMENT = 1;
ALTER TABLE service_requests AUTO_INCREMENT = 1;
ALTER TABLE task_assignments AUTO_INCREMENT = 1;
ALTER TABLE tasks AUTO_INCREMENT = 1;
ALTER TABLE projects AUTO_INCREMENT = 1;
ALTER TABLE supplier_materials AUTO_INCREMENT = 1;
ALTER TABLE hardware_stores AUTO_INCREMENT = 1;
ALTER TABLE provider_skills AUTO_INCREMENT = 1;
ALTER TABLE supplier_profiles AUTO_INCREMENT = 1;
ALTER TABLE service_providers AUTO_INCREMENT = 1;
ALTER TABLE property_owners AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;



-- ============================
-- 1. USERS (1-3 owners, 4-6 providers, 7-9 suppliers)
--    Passwords: password01 ... password09 (bcrypt)
-- ============================
INSERT INTO users (fname, lname, email, password_hash, contact_no, district, role) VALUES
('Nimal',  'Perera',    'nimal@gmail.com',   '$2b$12$F1TIrrzIqPJ3e/M5.B3V3usvq2/6fiPRrU.LCVfr3V5MbtB3Q4Rm6', '0771234501', 'Colombo',  'property_owner'),
('Kamala', 'Fernando',  'kamala@gmail.com',  '$2b$12$cryQwbx0UHZn/.6dL3EkouezQwz2LNZxgMPl1cfnHnnz4txoycWUK', '0771234502', 'Gampaha',  'property_owner'),
('Ruwan',  'Silva',     'ruwan@gmail.com',   '$2b$12$/WNsbOcK6YeLAa66kH8saeS/EhGLEJ/0zUUYO7c8fQiRhqPfJC76C', '0771234503', 'Kandy',    'property_owner'),

('Sunil',  'Jayasinghe','sunil@gmail.com',   '$2b$12$b5W1PIZNeJErmVTCUMKPpOT4Z2aAFQKaEECchPGBm02N9kKDjaibS', '0771234504', 'Colombo',  'service_provider'),
('Ajith',  'Bandara',   'ajith@gmail.com',   '$2b$12$FORMrik65LrWNV5BNBWS3Oxd8sASIyU89fimjnQG4GJ64XQ8jmMJa', '0771234505', 'Galle',    'service_provider'),
('Chamara','Wijesuriya','chamara@gmail.com', '$2b$12$B75EB4p7.505lHrQlriD6uN5q25Li3s75bZI2zP22Bh92WMC.fh4.', '0771234506', 'Kandy',    'service_provider'),

('Saman',    'Gunawardena','saman@gmail.com',    '$2b$12$/ijLoE1owvreHlHN12wUGu.t0zySbVa.ZHZFx7K8cfJQv8kUgsC3y', '0771234507', 'Colombo',    'material_supplier'),
('Priyantha','Dias',       'priyantha@gmail.com','$2b$12$a43XCERgmgxuwZ9RtquLqu.SXHvh9D1l8p85EjiGpbaTBexOLPypK', '0771234508', 'Gampaha',    'material_supplier'),
('Lasith',   'Kumara',     'lasith@gmail.com',   '$2b$12$JBKqi6fLeE3Hdtv8bT9AmuZtRTdkZmRgBgOUpxFwBr0zHjS5NS6ou', '0771234509', 'Kurunegala','material_supplier');


INSERT INTO users (fname, lname, email, password_hash, contact_no, district, role)
VALUES ('System', 'Admin', 'admin@crewsync.com', '$2y$10$EnkkPFBnw/qa50F/q4AXO.4Zj/o4YD3mRy7uyFyI7LiMl8/mXBcDe', '0770000000', 'Colombo', 'admin');

INSERT INTO admins (user_id)
SELECT user_id FROM users WHERE email = 'admin@crewsync.com';

-- ============================
-- 2. PROPERTY OWNERS (user_id 1-3)
-- ============================
INSERT INTO property_owners (user_id, address) VALUES
(1, '25/1 Galle Road, Dehiwala'),
(2, '112 Negombo Road, Ja-Ela'),
(3, '48 Peradeniya Road, Kandy');

-- ============================
-- 3. SERVICE PROVIDERS (user_id 4-6)
-- ============================
INSERT INTO service_providers (user_id, bio, experience_yr, charge_per_day, avg_rating, is_available, willing_outside_region) VALUES
(4, 'Experienced mason specializing in brickwork and plastering.', 10, 4500.00, 4.50, TRUE,  TRUE),
(5, 'Licensed electrician for household and commercial wiring.',    6, 5000.00, 4.20, TRUE,  FALSE),
(6, 'Carpenter skilled in doors, windows, and roofing work.',       8, 4000.00, 4.80, FALSE, TRUE);

-- ============================
-- 4. SUPPLIER PROFILES (user_id 7-9)
-- ============================
INSERT INTO supplier_profiles (user_id, business_name, business_address, is_hardware_shop, avg_rating, is_delivery) VALUES
(7, 'Saman Hardware & Building Materials', '10 High Level Road, Nugegoda', TRUE,  4.60, TRUE),
(8, 'Dias Cement Suppliers',               '55 Kandy Road, Kadawatha',     FALSE, 4.10, TRUE),
(9, 'Kumara Timber Depot',                 '8 Puttalam Road, Kurunegala',  TRUE,  4.30, FALSE);

-- ============================
-- 5. PROVIDER SKILLS (your IDs: 1=Masonry, 2=Carpentry, 3=Electrical, 4=Plumbing, 5=Painting)
-- ============================
INSERT INTO provider_skills (provider_id, skill_id, experience_yr) VALUES
(1, 1, 10),  -- Sunil: Masonry
(1, 5, 4),   -- Sunil: Painting
(2, 3, 6),   -- Ajith: Electrical
(3, 2, 8),   -- Chamara: Carpentry
(3, 4, 3);   -- Chamara: Plumbing

-- ============================
-- 6. HARDWARE STORES (suppliers 1 & 3 are hardware shops)
-- ============================
INSERT INTO hardware_stores (supplier_id, store_name, br_number, address) VALUES
(1, 'Saman Hardware', 'BR-2024-1001', '10 High Level Road, Nugegoda'),
(3, 'Kumara Timber Depot', 'BR-2024-1003', '8 Puttalam Road, Kurunegala');

-- ============================
-- 7. SUPPLIER MATERIALS (your IDs: 1=Sand, 2=Cement, 3=Gravel/Metal, 5=Cement Blocks, 6=Timber, 7=Bricks, 8=Glass)
-- ============================
INSERT INTO supplier_materials (supplier_id, material_id, unit_price, stock_qty, is_available, description) VALUES
(1, 2, 2350.00,  500,  TRUE, 'Tokyo Super cement 50kg bags'),
(1, 7, 45.00,    10000,TRUE, 'Engineering bricks'),
(1, 5, 180.00,   3000, TRUE, 'Standard cement blocks 4 inch'),
(2, 2, 2300.00,  800,  TRUE, 'Bulk cement orders welcome'),
(2, 1, 25000.00, 20,   TRUE, 'Clean river sand per cube'),
(2, 3, 22000.00, 15,   TRUE, 'Crushed metal 3/4 inch per cube'),
(3, 6, 350.00,   2000, TRUE, 'Treated mahogany planks per ft'),
(3, 8, 1200.00,  150,  TRUE, 'Clear glass sheets 5mm');

-- ============================
-- 8. PROJECTS (owner_id 1-3)
-- ============================
INSERT INTO projects (owner_id, project_name, district, address, p_budget, p_cost, start_date, end_date, is_finished) VALUES
(1, 'Two Storey House Build', 'Colombo', '25/1 Galle Road, Dehiwala', 8500000.00, 1200000.00, '2026-06-01', '2027-02-28', FALSE),
(2, 'Kitchen Renovation',     'Gampaha', '112 Negombo Road, Ja-Ela',   750000.00,  300000.00, '2026-07-01', '2026-08-15', FALSE),
(3, 'Boundary Wall',          'Kandy',   '48 Peradeniya Road, Kandy',  450000.00,  450000.00, '2026-05-01', '2026-06-10', TRUE);

-- ============================
-- 9. TASKS
-- ============================
INSERT INTO tasks (project_id, task_name, start_date, end_date, task_budget, t_cost, is_finished) VALUES
(1, 'Foundation Work',   '2026-06-01', '2026-07-15', 1500000.00, 900000.00, FALSE),
(1, 'Brick Laying',      '2026-07-16', '2026-09-30', 2000000.00, 0.00,      FALSE),
(2, 'Electrical Wiring', '2026-07-05', '2026-07-20',  120000.00, 0.00,      FALSE);

-- ============================
-- 10. TASK ASSIGNMENTS
-- ============================
INSERT INTO task_assignments (task_id, provider_id) VALUES
(1, 1),   -- Sunil on Foundation Work
(3, 2);   -- Ajith on Electrical Wiring

-- ============================
-- 11. SERVICE REQUESTS
-- ============================
INSERT INTO service_requests (owner_id, provider_id, task_id, expires_at, request_status) VALUES
(1, 1, 1,    '2026-07-12 23:59:59', 'accepted'),
(2, 2, 3,    '2026-07-11 23:59:59', 'pending'),
(3, 3, NULL, '2026-07-01 23:59:59', 'expired');

-- ============================
-- 12. MATERIAL ORDERS (supplier_material_id = ids from step 7, i.e. 1-8)
-- ============================
INSERT INTO material_orders (owner_id, supplier_material_id, quantity, total_cost, order_status) VALUES
(1, 1, 100, 235000.00, 'accepted'),   -- Nimal: 100 cement bags from Saman
(1, 7, 200, 70000.00,  'pending'),    -- Nimal: 200ft timber from Kumara
(2, 4, 50,  115000.00, 'delivered');  -- Kamala: 50 cement bags from Dias

-- ============================
-- 13. REVIEWS
-- ============================
INSERT INTO reviews (owner_id, provider_id, rating, comment) VALUES
(1, 1, 5, 'Excellent masonry work, finished on time.'),
(3, 3, 4, 'Good carpentry but slightly delayed.');

-- ============================
-- 14. FOUREMS (project comments - mixed users)
-- ============================
INSERT INTO project_comments (project_id, user_id, comment) VALUES
(1, 1, 'Foundation work starts Monday, all welcome to discuss.'),
(1, 4, 'I will arrive at 8am with my team.'),
(1, 7, 'Cement delivery scheduled for Tuesday morning.'),
(2, 2, 'Any electricians available for a quick quote?'),
(2, 5, 'Yes, I can visit the site this weekend.');