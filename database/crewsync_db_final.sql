-- ============================================================
--  CrewSync Database Schema (Final)
-- ============================================================

CREATE DATABASE IF NOT EXISTS crewsync;
USE crewsync;

-- ============================================================
-- 1. USERS (base entity for all roles)
-- ============================================================
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    fname         VARCHAR(100) NOT NULL,
    lname         VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    contact_no    VARCHAR(20),
    district      VARCHAR(100),
    role          ENUM('admin','property_owner','service_provider','material_supplier') NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. ADMIN
-- ============================================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL UNIQUE,
    CONSTRAINT fk_admin_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 3. PROPERTY OWNERS
-- ============================================================
CREATE TABLE property_owners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL UNIQUE,
    address  TEXT,
    CONSTRAINT fk_po_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 4. SERVICE PROVIDERS
-- ============================================================
CREATE TABLE service_providers (
    provider_id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id                INT NOT NULL UNIQUE,
    bio                    TEXT,
    experience_yr          INT DEFAULT 0,
    charge_per_day         DECIMAL(10,2),
    avg_rating             DECIMAL(3,2) DEFAULT 0.00,
    is_available           BOOLEAN NOT NULL DEFAULT TRUE,
    willing_outside_region BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_sp_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 5. MATERIAL SUPPLIERS
-- ============================================================
CREATE TABLE supplier_profiles (
    supplier_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id          INT NOT NULL UNIQUE,
    business_name    VARCHAR(200),
    business_address TEXT,
    is_hardware_shop BOOLEAN DEFAULT FALSE,
    avg_rating       DECIMAL(3,2) DEFAULT 0.00,
    CONSTRAINT fk_sup_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 6. SKILLS
-- ============================================================
CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL UNIQUE
);

-- ============================================================
-- 7. PROVIDER SKILLS (M:N)
-- ============================================================
CREATE TABLE provider_skills (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    skill_id    INT NOT NULL,
    experience_yr INT DEFAULT 0,
    CONSTRAINT fk_psk_provider FOREIGN KEY (provider_id)
        REFERENCES service_providers(provider_id) ON DELETE CASCADE,
    CONSTRAINT fk_psk_skill FOREIGN KEY (skill_id)
        REFERENCES skills(skill_id) ON DELETE CASCADE,
    UNIQUE KEY uq_provider_skill (provider_id, skill_id)
);

-- ============================================================
-- 8. HARDWARE STORE DETAILS
-- ============================================================
CREATE TABLE hardware_stores (
    hardware_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL UNIQUE,
    store_name  VARCHAR(150) NOT NULL,
    br_number   VARCHAR(100) NOT NULL,
    address     VARCHAR(255) NOT NULL,
    CONSTRAINT fk_hw_supplier FOREIGN KEY (supplier_id)
        REFERENCES supplier_profiles(supplier_id) ON DELETE CASCADE
);
ALTER TABLE supplier_profiles 
ADD COLUMN is_delivery BOOLEAN DEFAULT FALSE;
-- ============================================================
-- 9. MATERIALS
-- ============================================================
CREATE TABLE materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150) NOT NULL,
    unit        VARCHAR(50)  NOT NULL
);

-- ============================================================
-- 10. SUPPLIER MATERIALS (what each supplier stocks)
-- ============================================================


CREATE TABLE supplier_materials (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id  INT NOT NULL,
    material_id  INT NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    stock_qty    INT DEFAULT 0,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    description  TEXT,
    CONSTRAINT fk_sm_supplier FOREIGN KEY (supplier_id)
        REFERENCES supplier_profiles(supplier_id) ON DELETE CASCADE,
    CONSTRAINT fk_sm_material FOREIGN KEY (material_id)
        REFERENCES materials(material_id) ON DELETE CASCADE,
    UNIQUE KEY uq_supplier_material (supplier_id, material_id)
);



-- ============================================================
-- 11. PROJECTS
-- ============================================================


CREATE TABLE projects (
    project_id   INT AUTO_INCREMENT PRIMARY KEY,
    owner_id     INT NOT NULL,
    project_name VARCHAR(150) NOT NULL,
    district     VARCHAR(100),
    address      VARCHAR(255),
    p_budget     DECIMAL(15,2),
    p_cost       DECIMAL(15,2),
    start_date   DATE,
    end_date     DATE,
    is_finished  BOOLEAN DEFAULT FALSE,

    CONSTRAINT fk_proj_owner
        FOREIGN KEY (owner_id)
        REFERENCES property_owners(owner_id)
        ON DELETE CASCADE
);

ALTER TABLE projects ADD COLUMN target_end_date DATE NULL AFTER end_date;

-- ============================================================
-- 12. TASKS
-- ============================================================



CREATE TABLE tasks (
    task_id      INT AUTO_INCREMENT PRIMARY KEY,
    project_id   INT NOT NULL,
    task_name    VARCHAR(150) NOT NULL,
    start_date   DATE,
    end_date     DATE,
    task_budget  DECIMAL(10,2),
    t_cost       DECIMAL(10,2),
    is_finished  BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_task_project
        FOREIGN KEY (project_id)
        REFERENCES projects(project_id)
        ON DELETE CASCADE
);


-- ============================================================
-- 13. TASK ASSIGNMENTS (provider assigned to task)
-- ============================================================


CREATE TABLE task_assignments (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    task_id     INT NOT NULL,
    provider_id INT NOT NULL,
    CONSTRAINT fk_ta_task FOREIGN KEY (task_id)
        REFERENCES tasks(task_id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_provider FOREIGN KEY (provider_id)
        REFERENCES service_providers(provider_id) ON DELETE CASCADE,
    UNIQUE KEY uq_task_provider (task_id, provider_id)
);

-- ============================================================
-- 14. SERVICE REQUESTS
-- ============================================================
CREATE TABLE service_requests (
    request_id     INT AUTO_INCREMENT PRIMARY KEY,
    owner_id       INT NOT NULL,
    provider_id    INT NOT NULL,
    task_id        INT,
    request_date   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at     DATETIME,
    request_status ENUM('pending','accepted','rejected','expired') DEFAULT 'pending',
    CONSTRAINT fk_sr_owner    FOREIGN KEY (owner_id)
        REFERENCES property_owners(owner_id)    ON DELETE CASCADE,
    CONSTRAINT fk_sr_provider FOREIGN KEY (provider_id)
        REFERENCES service_providers(provider_id) ON DELETE CASCADE,
    CONSTRAINT fk_sr_task     FOREIGN KEY (task_id)
        REFERENCES tasks(task_id)               ON DELETE SET NULL
);

-- ============================================================
-- 15. MATERIAL ORDERS
-- ============================================================
CREATE TABLE material_orders (
    order_id             INT AUTO_INCREMENT PRIMARY KEY,
    owner_id             INT NOT NULL,
    supplier_material_id INT NOT NULL,
    quantity             INT NOT NULL,
    total_cost           DECIMAL(12,2) DEFAULT 0.00,
    order_status         ENUM('pending','accepted','rejected','delivered') NOT NULL DEFAULT 'pending',
    ordered_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mo_owner FOREIGN KEY (owner_id)
        REFERENCES property_owners(owner_id)    ON DELETE CASCADE,
    CONSTRAINT fk_mo_sm   FOREIGN KEY (supplier_material_id)
        REFERENCES supplier_materials(id)       ON DELETE RESTRICT
);

-- ============================================================
-- 16. REVIEWS (owner reviews a provider)
-- ============================================================
CREATE TABLE reviews (
    review_id   INT AUTO_INCREMENT PRIMARY KEY,
    owner_id    INT NOT NULL,
    provider_id INT NOT NULL,
    rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rev_owner    FOREIGN KEY (owner_id)
        REFERENCES property_owners(owner_id)      ON DELETE CASCADE,
    CONSTRAINT fk_rev_provider FOREIGN KEY (provider_id)
        REFERENCES service_providers(provider_id) ON DELETE CASCADE
);
-- New table for review photos
CREATE TABLE review_photos (
    photo_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(review_id)
);

-- ============================================================
-- 17. FEEDBACK (user sends to admin)
-- ============================================================


CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(150),
    email VARCHAR(255),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_handled BOOLEAN
        NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fb_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE SET NULL
);



-- ============================================================
-- 18. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    title           VARCHAR(255) NOT NULL,
    message         TEXT,
    is_read         BOOLEAN DEFAULT FALSE,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 19. REPORTS
-- ============================================================
CREATE TABLE reports (
    report_id      INT AUTO_INCREMENT PRIMARY KEY,
    project_id     INT,
    task_id        INT,
    report_type    ENUM('project','task','financial','general') DEFAULT 'general',
    file_path      VARCHAR(500),
    generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rep_project FOREIGN KEY (project_id)
        REFERENCES projects(project_id) ON DELETE SET NULL,
    CONSTRAINT fk_rep_task    FOREIGN KEY (task_id)
        REFERENCES tasks(task_id)       ON DELETE SET NULL
);

ALTER TABLE reports 
MODIFY COLUMN report_type ENUM('task', 'project') DEFAULT 'project';
-- ==============================================================
-- 19. forum   --not added to ER 
-- ==============================================================
CREATE TABLE project_comments (
    comment_id  INT AUTO_INCREMENT PRIMARY KEY,
    project_id  INT NOT NULL,
    user_id     INT NOT NULL,
    comment     TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pc_project FOREIGN KEY (project_id)
        REFERENCES projects(project_id) ON DELETE CASCADE,
    CONSTRAINT fk_pc_user FOREIGN KEY (user_id)
        REFERENCES users(user_id) ON DELETE CASCADE
);
INSERT INTO skills (skill_id, name) VALUES
(1,  'Masonry'),
(2,  'Carpentry'),
(3,  'Electrical'),
(4,  'Plumbing'),
(5,  'Painting'),
(6,  'Tiling'),
(7,  'Welding'),
(8,  'Roofing'),
(9,  'Waterproofing'),
(10, 'Landscaping'),
(11, 'Aluminium Work'),
(12, 'Interior Design');

INSERT INTO materials (material_id, name, unit) VALUES
(1, 'Sand', 'unit'),
(2, 'Cement', 'unit'),
(3, 'Gravel / Metal', 'unit'),
(4, 'Stone / Rubble', 'unit'),
(5, 'Cement Blocks', 'unit'),
(6, 'Timber', 'unit'),
(7, 'Bricks', 'unit'),
(8, 'Glass', 'unit'),
(9, 'Other', 'unit');