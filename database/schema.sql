CREATE DATABASE IF NOT EXISTS crewsync;
USE crewsync;

-- =====================================================
-- USERS
-- =====================================================

CREATE TABLE users (
user_id INT AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(255),
password_hash VARCHAR(255),
role ENUM('admin','owner','provider','supplier','guest'),
full_name VARCHAR(255),
phone VARCHAR(50),
district VARCHAR(100),
city VARCHAR(100),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- SERVICE PROVIDERS
-- =====================================================

CREATE TABLE service_provider_profiles (
profile_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
bio TEXT,
work_address VARCHAR(255),
willing_outside_region BOOLEAN,
charge_per_day DECIMAL(10,2),
avg_rating DECIMAL(3,2),
is_available BOOLEAN,
FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE provider_services (
service_id INT AUTO_INCREMENT PRIMARY KEY,
profile_id INT,
category ENUM(
'mason',
'carpenter',
'electrician',
'plumber'
),
description TEXT,
years_experience INT,
FOREIGN KEY (profile_id) REFERENCES service_provider_profiles(profile_id)
);

-- =====================================================
-- SUPPLIERS
-- =====================================================

CREATE TABLE supplier_profiles (
supplier_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
shop_name VARCHAR(255),
shop_address VARCHAR(255),
is_hardware_shop BOOLEAN,
avg_rating DECIMAL(3,2),
district VARCHAR(100),
FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =====================================================
-- PROJECT TEMPLATES
-- =====================================================

CREATE TABLE project_templates (
template_id INT AUTO_INCREMENT PRIMARY KEY,
template_name VARCHAR(255),
description TEXT
);

CREATE TABLE template_tasks (
template_task_id INT AUTO_INCREMENT PRIMARY KEY,
template_id INT,
task_name VARCHAR(255),
suggested_duration_days INT,
sequence_order INT,
FOREIGN KEY (template_id) REFERENCES project_templates(template_id)
);

-- =====================================================
-- PROJECTS
-- =====================================================

CREATE TABLE projects (
project_id INT AUTO_INCREMENT PRIMARY KEY,
owner_id INT,
title VARCHAR(255),
description TEXT,
status ENUM(
'planning',
'ongoing',
'completed',
'paused'
),
total_budget DECIMAL(12,2),
actual_cost DECIMAL(12,2),
start_date DATE,
end_date DATE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (owner_id) REFERENCES users(user_id)
);

-- =====================================================
-- TASKS
-- =====================================================

CREATE TABLE tasks (
task_id INT AUTO_INCREMENT PRIMARY KEY,
project_id INT,
task_name VARCHAR(255),
description TEXT,
status ENUM(
'pending',
'in_progress',
'completed'
),
priority ENUM(
'low',
'medium',
'high'
),
start_date DATE,
end_date DATE,
estimated_cost DECIMAL(12,2),
actual_cost DECIMAL(12,2),
sequence_order INT,
FOREIGN KEY (project_id) REFERENCES projects(project_id)
);

-- =====================================================
-- SERVICE REQUESTS
-- =====================================================

CREATE TABLE service_requests (
request_id INT AUTO_INCREMENT PRIMARY KEY,
task_id INT,
owner_id INT,
provider_id INT,
status ENUM(
'pending',
'accepted',
'rejected',
'completed'
),
message TEXT,
requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
responded_at TIMESTAMP NULL,
FOREIGN KEY (task_id) REFERENCES tasks(task_id),
FOREIGN KEY (owner_id) REFERENCES users(user_id),
FOREIGN KEY (provider_id) REFERENCES users(user_id)
);

CREATE TABLE task_providers (
id INT AUTO_INCREMENT PRIMARY KEY,
task_id INT,
provider_id INT,
assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (task_id) REFERENCES tasks(task_id),
FOREIGN KEY (provider_id) REFERENCES users(user_id)
);

-- =====================================================
-- MATERIALS
-- =====================================================

CREATE TABLE materials (
material_id INT AUTO_INCREMENT PRIMARY KEY,
supplier_id INT,
name VARCHAR(255),
category VARCHAR(100),
description TEXT,
unit_price DECIMAL(12,2),
unit VARCHAR(50),
is_available BOOLEAN,
stock_qty INT,
FOREIGN KEY (supplier_id)
REFERENCES supplier_profiles(supplier_id)
);

CREATE TABLE material_orders (
order_id INT AUTO_INCREMENT PRIMARY KEY,
task_id INT,
buyer_id INT,
supplier_id INT,
status ENUM(
'pending',
'accepted',
'rejected',
'delivered'
),
total_cost DECIMAL(12,2),
ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (task_id) REFERENCES tasks(task_id),
FOREIGN KEY (buyer_id) REFERENCES users(user_id),
FOREIGN KEY (supplier_id)
REFERENCES supplier_profiles(supplier_id)
);

CREATE TABLE order_items (
item_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT,
material_id INT,
quantity INT,
unit_price_at_order DECIMAL(12,2),
FOREIGN KEY (order_id)
REFERENCES material_orders(order_id),
FOREIGN KEY (material_id)
REFERENCES materials(material_id)
);

CREATE TABLE task_materials (
id INT AUTO_INCREMENT PRIMARY KEY,
task_id INT,
material_id INT,
quantity_used DECIMAL(12,2),
cost DECIMAL(12,2),
FOREIGN KEY (task_id) REFERENCES tasks(task_id),
FOREIGN KEY (material_id) REFERENCES materials(material_id)
);

-- =====================================================
-- REVIEWS
-- =====================================================

CREATE TABLE reviews (
review_id INT AUTO_INCREMENT PRIMARY KEY,
reviewer_id INT,
reviewee_id INT,
task_id INT NULL,
rating TINYINT,
comment TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (reviewer_id) REFERENCES users(user_id),
FOREIGN KEY (reviewee_id) REFERENCES users(user_id),
FOREIGN KEY (task_id) REFERENCES tasks(task_id)
);

CREATE TABLE review_photos (
photo_id INT AUTO_INCREMENT PRIMARY KEY,
review_id INT,
photo_url VARCHAR(500),
uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (review_id)
REFERENCES reviews(review_id)
);

CREATE TABLE platform_feedback (
feedback_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NULL,
message TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =====================================================
-- FORUM
-- =====================================================

CREATE TABLE forum_messages (
message_id INT AUTO_INCREMENT PRIMARY KEY,
project_id INT,
sender_id INT,
message TEXT,
sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
is_cleared BOOLEAN,
cleared_at TIMESTAMP NULL,
FOREIGN KEY (project_id) REFERENCES projects(project_id),
FOREIGN KEY (sender_id) REFERENCES users(user_id)
);

-- =====================================================
-- NOTIFICATIONS
-- =====================================================

CREATE TABLE notifications (
notif_id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT,
type ENUM(
'request',
'order',
'review',
'system'
),
message VARCHAR(500),
is_read BOOLEAN,
related_id INT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- =====================================================
-- REPORTS
-- =====================================================

CREATE TABLE reports (
report_id INT AUTO_INCREMENT PRIMARY KEY,
project_id INT,
task_id INT NULL,
report_type ENUM(
'task',
'final'
),
file_path VARCHAR(500),
generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES projects(project_id),
FOREIGN KEY (task_id) REFERENCES tasks(task_id)
);
