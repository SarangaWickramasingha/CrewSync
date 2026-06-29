sql
CREATE DATABASE IF NOT EXISTS crewsync;
USE crewsync;

-- =====================================================
-- 1. USERS
-- =====================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,

    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,

    email VARCHAR(255) NOT NULL UNIQUE,
    contact_no VARCHAR(20) UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    role ENUM(
        'property_owner',
        'service_provider',
        'material_supplier',
        'admin'
    ) NOT NULL,

    district VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. PROPERTY OWNERS
-- =====================================================
CREATE TABLE property_owners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    address TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_owner_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 3. SERVICE PROVIDER
-- =====================================================
CREATE TABLE service_provider (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    bio TEXT,

    work_address VARCHAR(255),

    willing_outside_region BOOLEAN
        NOT NULL DEFAULT FALSE,

    charge_per_day DECIMAL(10,2)
        CHECK (charge_per_day >= 0),

    avg_rating DECIMAL(3,2)
        DEFAULT 0.00,

    is_available BOOLEAN
        NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sp_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 4. SKILLS
-- =====================================================
CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100)
        NOT NULL UNIQUE
);

-- =====================================================
-- 5. PROVIDER SKILLS (M:N)
-- =====================================================
CREATE TABLE provider_skills (

    profile_id INT NOT NULL,

    skill_id INT NOT NULL,

    experience_yr INT
        DEFAULT 0
        CHECK (experience_yr >= 0),

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        profile_id,
        skill_id
    ),

    CONSTRAINT fk_provider_skill_profile
        FOREIGN KEY (profile_id)
        REFERENCES service_provider(profile_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_provider_skill_skill
        FOREIGN KEY (skill_id)
        REFERENCES skills(skill_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 6. SUPPLIER PROFILES
-- =====================================================
CREATE TABLE supplier_profiles (
    supplier_id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    business_name VARCHAR(200),

    business_address TEXT,

    is_hardware_shop BOOLEAN
        DEFAULT FALSE,

    avg_rating DECIMAL(3,2)
        DEFAULT 0.00,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_supplier_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 7. HARDWARE STORE DETAILS
-- =====================================================
CREATE TABLE hardware_store_details (
    hardware_id INT AUTO_INCREMENT PRIMARY KEY,

    supplier_id INT NOT NULL UNIQUE,

    store_name VARCHAR(150)
        NOT NULL,

    br_number VARCHAR(100)
        NOT NULL UNIQUE,

    address VARCHAR(255)
        NOT NULL,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_hardware_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES supplier_profiles(supplier_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 8. MATERIALS
-- =====================================================
CREATE TABLE materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150)
        NOT NULL UNIQUE,

    unit VARCHAR(50)
        NOT NULL
);

-- =====================================================
-- 9. SUPPLIER MATERIALS
-- =====================================================
CREATE TABLE supplier_materials (
    supplier_material_id
        INT AUTO_INCREMENT PRIMARY KEY,

    supplier_id INT NOT NULL,

    material_id INT NOT NULL,

    unit_price DECIMAL(10,2)
        NOT NULL
        CHECK (unit_price >= 0),

    stock_qty INT
        DEFAULT 0
        CHECK (stock_qty >= 0),

    is_available BOOLEAN
        NOT NULL DEFAULT TRUE,

    description TEXT,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sm_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES supplier_profiles(supplier_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_sm_material
        FOREIGN KEY (material_id)
        REFERENCES materials(material_id)
        ON DELETE CASCADE,

    UNIQUE (
        supplier_id,
        material_id
    )
);

-- =====================================================
-- 10. PROJECTS
-- =====================================================
CREATE TABLE projects (
    project_id INT AUTO_INCREMENT PRIMARY KEY,

    owner_id INT NOT NULL,

    title VARCHAR(200) NOT NULL,

    description TEXT,

    location VARCHAR(255),

    estimated_budget DECIMAL(12,2)
        CHECK (estimated_budget >= 0),

    start_date DATE,

    expected_end_date DATE,

    status ENUM(
        'planning',
        'active',
        'paused',
        'completed',
        'cancelled'
    ) DEFAULT 'planning',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_project_owner
        FOREIGN KEY (owner_id)
        REFERENCES property_owners(owner_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 11. PROJECT TASKS
-- =====================================================
CREATE TABLE project_tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,

    project_id INT NOT NULL,

    title VARCHAR(200) NOT NULL,

    description TEXT,

    priority ENUM(
        'low',
        'medium',
        'high'
    ) DEFAULT 'medium',

    status ENUM(
        'pending',
        'in_progress',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    start_date DATE,

    due_date DATE,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_task_project
        FOREIGN KEY (project_id)
        REFERENCES projects(project_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 12. SERVICE REQUESTS
-- =====================================================
CREATE TABLE service_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,

    task_id INT NOT NULL,

    owner_id INT NOT NULL,

    requested_skill_id INT NOT NULL,

    description TEXT,

    budget DECIMAL(10,2)
        CHECK (budget >= 0),

    preferred_date DATE,

    status ENUM(
        'open',
        'accepted',
        'cancelled',
        'completed'
    ) DEFAULT 'open',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_request_task
        FOREIGN KEY (task_id)
        REFERENCES project_tasks(task_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_request_owner
        FOREIGN KEY (owner_id)
        REFERENCES property_owners(owner_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_request_skill
        FOREIGN KEY (requested_skill_id)
        REFERENCES skills(skill_id)
        ON DELETE RESTRICT
);

-- =====================================================
-- 13. SERVICE REQUEST ASSIGNMENTS
-- =====================================================
CREATE TABLE service_request_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,

    request_id INT NOT NULL,

    profile_id INT NOT NULL,

    assigned_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    assignment_status ENUM(
        'pending',
        'accepted',
        'rejected',
        'completed'
    ) DEFAULT 'pending',

    agreed_daily_rate DECIMAL(10,2)
        CHECK (agreed_daily_rate >= 0),

    CONSTRAINT fk_assignment_request
        FOREIGN KEY (request_id)
        REFERENCES service_requests(request_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_assignment_provider
        FOREIGN KEY (profile_id)
        REFERENCES service_provider(profile_id)
        ON DELETE CASCADE,

    UNIQUE (
        request_id,
        profile_id
    )
);

-- =====================================================
-- 14. PROJECT MEMBERS
-- =====================================================
CREATE TABLE project_members (

    project_id INT NOT NULL,

    profile_id INT NOT NULL,

    joined_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        project_id,
        profile_id
    ),

    CONSTRAINT fk_pm_project
        FOREIGN KEY (project_id)
        REFERENCES projects(project_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_pm_provider
        FOREIGN KEY (profile_id)
        REFERENCES service_provider(profile_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 15. TASK UPDATES
-- =====================================================
CREATE TABLE task_updates (
    update_id INT AUTO_INCREMENT PRIMARY KEY,

    task_id INT NOT NULL,

    updated_by INT NOT NULL,

    update_note TEXT,

    progress_percent INT
        DEFAULT 0
        CHECK (
            progress_percent >= 0
            AND progress_percent <= 100
        ),

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_update_task
        FOREIGN KEY (task_id)
        REFERENCES project_tasks(task_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_update_user
        FOREIGN KEY (updated_by)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);


