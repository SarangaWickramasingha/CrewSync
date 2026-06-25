CREATE DATABASE IF NOT EXISTS crewsync;
USE crewsync;

-- =====================================================
-- 1. USERS (BASE TABLE)
-- =====================================================
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    fname         VARCHAR(100) NOT NULL,
    lname         VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    contact_no    VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('property_owner','service_provider','material_supplier','admin') NOT NULL,
    district      VARCHAR(100),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. SKILLS (no dependency)
-- =====================================================
CREATE TABLE skills (
    skill_id   INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE
);

-- =====================================================
-- 3. SERVICE PROVIDER PROFILE
-- =====================================================
CREATE TABLE service_provider (
    profile_id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id                INT NOT NULL UNIQUE,
    bio                    TEXT,
    work_address           VARCHAR(255),
    willing_outside_region BOOLEAN NOT NULL DEFAULT FALSE,
    charge_per_day         DECIMAL(10,2),
    avg_rating             DECIMAL(3,2) DEFAULT 0.00,
    is_available           BOOLEAN NOT NULL DEFAULT TRUE,

    CONSTRAINT fk_sp_user FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 4. PROVIDER SKILLS (MANY-TO-MANY)
-- =====================================================
CREATE TABLE provider_skills (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    profile_id  INT NOT NULL,
    skill_id    INT NOT NULL,

    CONSTRAINT fk_psk_profile FOREIGN KEY (profile_id)
        REFERENCES service_provider(profile_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_psk_skill FOREIGN KEY (skill_id)
        REFERENCES skills(skill_id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_provider_skill (profile_id, skill_id)
);
-- Fix provider_skills table to include experience_yr
ALTER TABLE provider_skills 
ADD COLUMN experience_yr INT DEFAULT 0;
-- =====================================================
-- 5. PROPERTY OWNERS
-- =====================================================
CREATE TABLE property_owners (
    owner_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL UNIQUE,
    address  TEXT,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
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
    is_hardware_shop BOOLEAN DEFAULT FALSE,
    avg_rating DECIMAL(3,2) DEFAULT 0.00,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 7. HARDWARE STORE DETAILS
-- =====================================================
CREATE TABLE hardware_store_details (
    hardware_id    INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id    INT NOT NULL UNIQUE,
    store_name     VARCHAR(150) NOT NULL,
    br_number      VARCHAR(100) NOT NULL,
    address        VARCHAR(255) NOT NULL,

    CONSTRAINT fk_hw_supplier FOREIGN KEY (supplier_id)
        REFERENCES supplier_profiles(supplier_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 8. MATERIALS
-- =====================================================
CREATE TABLE materials (
    material_id  INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    unit         VARCHAR(50) NOT NULL
);

-- =====================================================
-- 9. SUPPLIER MATERIALS
-- =====================================================
CREATE TABLE supplier_materials (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id  INT NOT NULL,
    material_id  INT NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    is_available BOOLEAN NOT NULL DEFAULT TRUE,
    stock_qty    INT DEFAULT 0,
    description  TEXT,

    CONSTRAINT fk_sm_supplier FOREIGN KEY (supplier_id)
        REFERENCES supplier_profiles(supplier_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_sm_material FOREIGN KEY (material_id)
        REFERENCES materials(material_id)
        ON DELETE CASCADE,

    UNIQUE KEY uq_supplier_material (supplier_id, material_id)
);

-- =====================================================
-- 10. MATERIAL ORDERS
-- =====================================================
CREATE TABLE material_orders (
    order_id             INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id             INT NOT NULL,
    supplier_material_id INT NOT NULL,
    quantity             INT NOT NULL,
    status               ENUM('pending','accepted','rejected','delivered')
                         NOT NULL DEFAULT 'pending',
    total_cost           DECIMAL(12,2) DEFAULT 0.00,
    ordered_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mo_buyer FOREIGN KEY (buyer_id)
        REFERENCES property_owners(owner_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_mo_sm FOREIGN KEY (supplier_material_id)
        REFERENCES supplier_materials(id)
        ON DELETE RESTRICT
);