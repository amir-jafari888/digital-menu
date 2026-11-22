-- ============================================================================
--  منوچی (Monochi) — Digital Menu, Loyalty & In-app Ads
--  MySQL / MariaDB schema + demo data
--
--  Import:  phpMyAdmin > Import > choose this file (UTF-8)
--  The script creates the `digital_menu` database if it does not exist.
--  Safe to re-run: tables are dropped and recreated (DATA IS LOST).
--
--  Conventions
--    * Engine InnoDB, charset utf8mb4 / utf8mb4_unicode_ci
--    * Money is stored as an integer number of Toman (no decimals)
--    * Timestamps are DATETIME in Asia/Tehran local time
--    * Soft state is expressed with ENUM status columns
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+03:30';
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO,STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS `digital_menu`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `digital_menu`;

-- Drop in reverse dependency order so a re-import always starts clean.
DROP TABLE IF EXISTS `uploaded_files`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `subscriptions`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `ad_events`;
DROP TABLE IF EXISTS `ad_campaigns`;
DROP TABLE IF EXISTS `reward_redemptions`;
DROP TABLE IF EXISTS `rewards`;
DROP TABLE IF EXISTS `loyalty_transactions`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `dining_tables`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `menus`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `rate_limits`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tenants`;
DROP TABLE IF EXISTS `plans`;

-- ----------------------------------------------------------------------------
--  Plans
-- ----------------------------------------------------------------------------
CREATE TABLE `plans` (
    `id`            TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`          VARCHAR(40)  NOT NULL,
    `name`          VARCHAR(80)  NOT NULL,
    `description`   VARCHAR(255) NOT NULL DEFAULT '',
    `monthly_price` INT UNSIGNED NOT NULL DEFAULT 0,
    `yearly_price`  INT UNSIGNED NOT NULL DEFAULT 0,
    `features`      JSON NULL,
    `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_plans_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Tenants (businesses / cafes / restaurants)
-- ----------------------------------------------------------------------------
CREATE TABLE `tenants` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(120) NOT NULL,
    `slug`          VARCHAR(60)  NOT NULL,
    `category`      ENUM('cafe','restaurant','bakery','dessert','other') NOT NULL DEFAULT 'cafe',
    `description`   VARCHAR(500) NOT NULL DEFAULT '',
    `phone`         VARCHAR(20)  NULL,
    `address`       VARCHAR(255) NOT NULL DEFAULT '',
    `logo`          VARCHAR(255) NULL,
    `cover`         VARCHAR(255) NULL,
    `status`        ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
    `plan_id`       TINYINT UNSIGNED NULL,
    `trial_ends_at` DATETIME NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tenants_slug` (`slug`),
    KEY `idx_tenants_status` (`status`),
    KEY `fk_tenants_plan` (`plan_id`),
    CONSTRAINT `fk_tenants_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Users (platform admins, business owners, staff)
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`            INT UNSIGNED NULL,
    `name`                 VARCHAR(120) NOT NULL,
    `email`                VARCHAR(190) NULL,
    `phone`                VARCHAR(20)  NULL,
    `password_hash`        VARCHAR(255) NOT NULL,
    `role`                 ENUM('super_admin','owner','staff') NOT NULL DEFAULT 'owner',
    `status`               ENUM('active','disabled') NOT NULL DEFAULT 'active',
    `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
    `email_verified_at`    DATETIME NULL,
    `last_login_at`        DATETIME NULL,
    `last_login_ip_hash`   CHAR(64) NULL,
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`),
    UNIQUE KEY `uq_users_phone` (`phone`),
    KEY `idx_users_tenant_role` (`tenant_id`, `role`),
    KEY `idx_users_status` (`status`),
    CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Rate limiting (fixed window + temporary block), used for login,
--  registration, contact, order creation and the whole API.
-- ----------------------------------------------------------------------------
CREATE TABLE `rate_limits` (
    `bucket`        VARCHAR(190) NOT NULL,
    `hits`          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `window_start`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `blocked_until` DATETIME NULL,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`bucket`),
    KEY `idx_rate_limits_blocked` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Login attempts (forensics + per-identifier lockout)
-- ----------------------------------------------------------------------------
CREATE TABLE `login_attempts` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `identifier_hash` CHAR(64) NOT NULL,
    `ip_hash`         CHAR(64) NOT NULL,
    `success`         TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_attempts_identifier` (`identifier_hash`, `created_at`),
    KEY `idx_attempts_ip` (`ip_hash`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Menus / categories / products
-- ----------------------------------------------------------------------------
CREATE TABLE `menus` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`    INT UNSIGNED NOT NULL,
    `name`         VARCHAR(100) NOT NULL,
    `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `qr_token`     CHAR(32) NOT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_menus_qr` (`qr_token`),
    KEY `idx_menus_tenant` (`tenant_id`, `is_default`),
    CONSTRAINT `fk_menus_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`   INT UNSIGNED NOT NULL,
    `menu_id`     INT UNSIGNED NOT NULL,
    `name`        VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_categories_menu` (`menu_id`, `sort_order`),
    KEY `idx_categories_tenant` (`tenant_id`),
    CONSTRAINT `fk_categories_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_categories_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`        INT UNSIGNED NOT NULL,
    `category_id`      INT UNSIGNED NULL,
    `name`             VARCHAR(160) NOT NULL,
    `description`      TEXT NULL,
    `price`            INT UNSIGNED NOT NULL DEFAULT 0,
    `compare_at_price` INT UNSIGNED NULL,
    `image`            VARCHAR(255) NULL,
    `is_available`     TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured`      TINYINT(1) NOT NULL DEFAULT 0,
    `calories`         SMALLINT UNSIGNED NULL,
    `allergens`        VARCHAR(255) NOT NULL DEFAULT '',
    `sort_order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_products_tenant` (`tenant_id`, `is_available`),
    KEY `idx_products_category` (`category_id`, `sort_order`),
    CONSTRAINT `fk_products_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dining_tables` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`  INT UNSIGNED NOT NULL,
    `label`      VARCHAR(40) NOT NULL,
    `qr_token`   CHAR(32) NOT NULL,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tables_qr` (`qr_token`),
    UNIQUE KEY `uq_tables_label` (`tenant_id`, `label`),
    CONSTRAINT `fk_tables_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Customers & loyalty
-- ----------------------------------------------------------------------------
CREATE TABLE `customers` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`      INT UNSIGNED NOT NULL,
    `phone`          VARCHAR(20) NOT NULL,
    `name`           VARCHAR(120) NOT NULL DEFAULT '',
    `email`          VARCHAR(190) NULL,
    `birthday`       DATE NULL,
    `points_balance` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_spent`    BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `visits`         INT UNSIGNED NOT NULL DEFAULT 0,
    `last_visit_at`  DATETIME NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_customers_phone` (`tenant_id`, `phone`),
    KEY `idx_customers_tenant_points` (`tenant_id`, `points_balance`),
    CONSTRAINT `fk_customers_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`     INT UNSIGNED NOT NULL,
    `menu_id`       INT UNSIGNED NULL,
    `table_id`      INT UNSIGNED NULL,
    `customer_id`   BIGINT UNSIGNED NULL,
    `code`          VARCHAR(20) NOT NULL,
    `type`          ENUM('dine_in','takeaway','delivery') NOT NULL DEFAULT 'dine_in',
    `status`        ENUM('pending','confirmed','preparing','ready','completed','canceled') NOT NULL DEFAULT 'pending',
    `subtotal`      INT UNSIGNED NOT NULL DEFAULT 0,
    `discount`      INT UNSIGNED NOT NULL DEFAULT 0,
    `total`         INT UNSIGNED NOT NULL DEFAULT 0,
    `points_earned` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `note`          VARCHAR(500) NOT NULL DEFAULT '',
    `placed_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at`  DATETIME NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_orders_code` (`code`),
    KEY `idx_orders_tenant_status` (`tenant_id`, `status`, `placed_at`),
    KEY `idx_orders_customer` (`customer_id`),
    CONSTRAINT `fk_orders_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_menu` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_table` FOREIGN KEY (`table_id`) REFERENCES `dining_tables` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`   BIGINT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NULL,
    `name`       VARCHAR(160) NOT NULL,
    `unit_price` INT UNSIGNED NOT NULL DEFAULT 0,
    `quantity`   SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `total`      INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_order_items_order` (`order_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loyalty_transactions` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`     INT UNSIGNED NOT NULL,
    `customer_id`   BIGINT UNSIGNED NOT NULL,
    `order_id`      BIGINT UNSIGNED NULL,
    `points`        INT NOT NULL,
    `reason`        ENUM('signup','purchase','redemption','adjustment') NOT NULL,
    `balance_after` INT UNSIGNED NOT NULL,
    `note`          VARCHAR(255) NOT NULL DEFAULT '',
    `created_by`    INT UNSIGNED NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_loyalty_customer` (`customer_id`, `created_at`),
    KEY `idx_loyalty_tenant` (`tenant_id`, `created_at`),
    CONSTRAINT `fk_loyalty_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_loyalty_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_loyalty_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_loyalty_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rewards` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`   INT UNSIGNED NOT NULL,
    `title`       VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `points_cost` INT UNSIGNED NOT NULL,
    `stock`       INT UNSIGNED NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rewards_tenant` (`tenant_id`, `is_active`),
    CONSTRAINT `fk_rewards_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reward_redemptions` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`    INT UNSIGNED NOT NULL,
    `reward_id`    INT UNSIGNED NOT NULL,
    `customer_id`  BIGINT UNSIGNED NOT NULL,
    `points_spent` INT UNSIGNED NOT NULL,
    `code`         VARCHAR(20) NOT NULL,
    `status`       ENUM('pending','used','canceled') NOT NULL DEFAULT 'pending',
    `redeemed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `used_at`      DATETIME NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_redemptions_code` (`code`),
    KEY `idx_redemptions_customer` (`customer_id`, `redeemed_at`),
    CONSTRAINT `fk_redemptions_reward` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_redemptions_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_redemptions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  In-app advertising (اپ سراغ)
-- ----------------------------------------------------------------------------
CREATE TABLE `ad_campaigns` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`   INT UNSIGNED NOT NULL,
    `title`       VARCHAR(120) NOT NULL,
    `description` VARCHAR(500) NOT NULL DEFAULT '',
    `image`       VARCHAR(255) NULL,
    `cta_url`     VARCHAR(500) NULL,
    `placement`   ENUM('app_home','app_search','app_nearby') NOT NULL DEFAULT 'app_home',
    `budget`      INT UNSIGNED NOT NULL DEFAULT 0,
    `spent`       INT UNSIGNED NOT NULL DEFAULT 0,
    `starts_at`   DATETIME NOT NULL,
    `ends_at`     DATETIME NOT NULL,
    `status`      ENUM('draft','active','paused','ended') NOT NULL DEFAULT 'draft',
    `impressions` INT UNSIGNED NOT NULL DEFAULT 0,
    `clicks`      INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ads_status_placement` (`status`, `placement`, `starts_at`, `ends_at`),
    KEY `idx_ads_tenant` (`tenant_id`),
    CONSTRAINT `fk_ads_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ad_events` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT UNSIGNED NOT NULL,
    `type`        ENUM('impression','click') NOT NULL,
    `ip_hash`     CHAR(64) NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ad_events_campaign` (`campaign_id`, `type`, `created_at`),
    CONSTRAINT `fk_ad_events_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `ad_campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Contact messages
-- ----------------------------------------------------------------------------
CREATE TABLE `contact_messages` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `contact`    VARCHAR(190) NOT NULL,
    `subject`    VARCHAR(120) NOT NULL DEFAULT '',
    `message`    TEXT NOT NULL,
    `ip_hash`    CHAR(64) NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL DEFAULT '',
    `status`     ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_contact_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Subscriptions & payments (gateway is not connected yet)
-- ----------------------------------------------------------------------------
CREATE TABLE `subscriptions` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`  INT UNSIGNED NOT NULL,
    `plan_id`    TINYINT UNSIGNED NOT NULL,
    `billing`    ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
    `price`      INT UNSIGNED NOT NULL DEFAULT 0,
    `starts_at`  DATE NOT NULL,
    `ends_at`    DATE NOT NULL,
    `status`     ENUM('trial','active','expired','canceled') NOT NULL DEFAULT 'trial',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_subscriptions_tenant` (`tenant_id`, `status`),
    CONSTRAINT `fk_subscriptions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_subscriptions_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`       INT UNSIGNED NOT NULL,
    `subscription_id` INT UNSIGNED NULL,
    `amount`          INT UNSIGNED NOT NULL,
    `gateway`         VARCHAR(30) NOT NULL DEFAULT 'manual',
    `reference`       VARCHAR(100) NULL,
    `status`          ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `paid_at`         DATETIME NULL,
    `meta`            JSON NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payments_reference` (`gateway`, `reference`),
    KEY `idx_payments_tenant` (`tenant_id`, `status`),
    CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Audit log (security-relevant actions)
-- ----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NULL,
    `tenant_id`  INT UNSIGNED NULL,
    `action`     VARCHAR(80) NOT NULL,
    `entity`     VARCHAR(40) NULL,
    `entity_id`  BIGINT UNSIGNED NULL,
    `ip_hash`    CHAR(64) NULL,
    `meta`       JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_tenant` (`tenant_id`, `created_at`),
    KEY `idx_audit_user` (`user_id`, `created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_audit_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Uploaded files (stored outside the web root, served by media.php)
-- ----------------------------------------------------------------------------
CREATE TABLE `uploaded_files` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`     INT UNSIGNED NULL,
    `user_id`       INT UNSIGNED NULL,
    `path`          VARCHAR(255) NOT NULL,
    `mime`          VARCHAR(80) NOT NULL,
    `size`          INT UNSIGNED NOT NULL DEFAULT 0,
    `original_name` VARCHAR(255) NOT NULL DEFAULT '',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_files_tenant` (`tenant_id`),
    CONSTRAINT `fk_files_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_files_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
--  Settings (key/value)
-- ----------------------------------------------------------------------------
CREATE TABLE `settings` (
    `key`        VARCHAR(80) NOT NULL,
    `value`      TEXT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
--  Demo data
-- ============================================================================

INSERT INTO `plans` (`id`, `slug`, `name`, `description`, `monthly_price`, `yearly_price`, `features`, `sort_order`) VALUES
(1, 'basic', 'پایه', 'برای شروع و ساخت منوی دیجیتال بدون هزینه.', 0, 0,
 JSON_ARRAY('یک منوی دیجیتال', 'QR اختصاصی', 'ویرایش نامحدود محصول'), 1),
(2, 'pro', 'حرفه‌ای', 'برای کافه و رستورانی که می‌خواهد مشتری را نگه دارد.', 290000, 2900000,
 JSON_ARRAY('همه‌ی امکانات پایه', 'باشگاه مشتریان و امتیاز', 'سفارش‌گیری و پنل سفارش', 'گزارش فروش و مشتری'), 2),
(3, 'business', 'کسب‌وکار', 'برای چند شعبه و رشد جدی از طریق تبلیغات.', 590000, 5900000,
 JSON_ARRAY('همه‌ی امکانات حرفه‌ای', 'تبلیغات در اپ سراغ', 'چند شعبه و چند منو', 'گزارش پیشرفته و خروجی', 'پشتیبانی اولویت‌دار'), 3);

INSERT INTO `tenants` (`id`, `name`, `slug`, `category`, `description`, `phone`, `address`, `status`, `plan_id`, `trial_ends_at`) VALUES
(1, 'کافه منوچی', 'cafe-monochi', 'cafe',
 'کافه‌ای کوچک با قهوه‌ی تازه، دسر خانگی و منوی دیجیتال منوچی.',
 '021-88776655', 'تهران، خیابان ولیعصر، پلاک ۱۲۰', 'active', 2, NULL);

-- Demo passwords (change them after the first login):
--   admin@monochi.ir  Admin@1404   (super admin)
--   owner@monochi.ir  Owner@1404   (business owner)
--   staff@monochi.ir  Staff@1404   (staff, limited permissions)
INSERT INTO `users` (`id`, `tenant_id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `must_change_password`, `email_verified_at`) VALUES
(1, NULL, 'مدیر پلتفرم', 'admin@monochi.ir', '09120000001',
 '$2y$12$ilTekJKY9l4JJ8SyBXUzXeMAZlMzxCjA.JUIukbodyR/IDYle.3u.', 'super_admin', 'active', 1, NOW()),
(2, 1, 'امیر جعفری', 'owner@monochi.ir', '09120000002',
 '$2y$12$E.Kv5aXYy5jSRwGdz9f/QuHQbjML0bU1m5QrKEbHvRR6MtT2vRAla', 'owner', 'active', 1, NOW()),
(3, 1, 'کارمند کافه', 'staff@monochi.ir', '09120000003',
 '$2y$12$Lw09cLoESJ2B/LTEs88IF.OdDefYOrgxcoRgsECuC/ifid4HrDWHu', 'staff', 'active', 1, NOW());

INSERT INTO `menus` (`id`, `tenant_id`, `name`, `is_default`, `is_published`, `qr_token`) VALUES
(1, 1, 'منوی اصلی', 1, 1, 'a1b2c3d4e5f60718293a4b5c6d7e8f90');

INSERT INTO `categories` (`id`, `tenant_id`, `menu_id`, `name`, `description`, `sort_order`) VALUES
(1, 1, 1, 'قهوه', 'قهوه‌های تازه‌دم‌شده با دانه‌ی رست اختصاصی', 1),
(2, 1, 1, 'نوشیدنی سرد', 'برای روزهای گرم', 2),
(3, 1, 1, 'دسر', 'دسرهای خانگی روز', 3),
(4, 1, 1, 'صبحانه', 'تا ساعت ۱۱ صبح', 4);

INSERT INTO `products` (`id`, `tenant_id`, `category_id`, `name`, `description`, `price`, `compare_at_price`, `is_available`, `is_featured`, `calories`, `allergens`, `sort_order`) VALUES
(1, 1, 1, 'لاته کاراملی', 'اسپرسو، شیر و سس کارامل خانگی', 125000, 140000, 1, 1, 210, 'شیر', 1),
(2, 1, 1, 'اسپرسو دوبل', 'دو شات اسپرسو با کرما پررنگ', 85000, NULL, 1, 0, 5, '', 2),
(3, 1, 1, 'کاپوچینو', 'اسپرسو، شیر بخارپز و فوم غلیظ', 110000, NULL, 1, 0, 150, 'شیر', 3),
(4, 1, 2, 'آیس لاته', 'شیر سرد، اسپرسو و یخ', 130000, NULL, 1, 0, 180, 'شیر', 1),
(5, 1, 2, 'لیموناد نعناع', 'لیموی تازه، نعناع و سودا', 95000, NULL, 1, 0, 90, '', 2),
(6, 1, 3, 'چیزکیک روز', 'چیزکیک نیویورکی با سس تمشک', 150000, NULL, 1, 1, 380, 'شیر، تخم‌مرغ، گلوتن', 1),
(7, 1, 3, 'براونی شکلات', 'براونی گرم با مغز شکلات تلخ', 120000, NULL, 1, 0, 420, 'گلوتن، تخم‌مرغ', 2),
(8, 1, 4, 'املت قارچ', 'تخم‌مرغ، قارچ، پنیر و نان تست', 165000, NULL, 1, 0, 320, 'تخم‌مرغ، گلوتن، شیر', 1);

INSERT INTO `dining_tables` (`id`, `tenant_id`, `label`, `qr_token`, `is_active`) VALUES
(1, 1, 'میز ۱', '11112222333344445555666677778888', 1),
(2, 1, 'میز ۲', '99990000aaaabbbbccccddddeeeeffff', 1),
(3, 1, 'میز ۳', '12341234123412341234123412341234', 1),
(4, 1, 'میز ۴', 'abcdabcdabcdabcdabcdabcdabcdabcd', 1);

INSERT INTO `customers` (`id`, `tenant_id`, `phone`, `name`, `email`, `birthday`, `points_balance`, `total_spent`, `visits`, `last_visit_at`) VALUES
(1, 1, '09121234567', 'سارا محمدی', 'sara@example.com', '1996-04-12', 77, 550000, 3, NOW() - INTERVAL 2 DAY),
(2, 1, '09351112233', 'امیر رضایی', NULL, NULL, 120, 1250000, 6, NOW() - INTERVAL 9 DAY),
(3, 1, '09190001122', 'نگار کریمی', NULL, NULL, 0, 0, 0, NULL);

INSERT INTO `orders` (`id`, `tenant_id`, `menu_id`, `table_id`, `customer_id`, `code`, `type`, `status`, `subtotal`, `discount`, `total`, `points_earned`, `note`, `placed_at`, `completed_at`) VALUES
(1, 1, 1, 1, 1, 'MN-8F3K2Q1A', 'dine_in', 'completed', 275000, 0, 275000, 27, 'بدون شکر', NOW() - INTERVAL 2 DAY, NOW() - INTERVAL 2 DAY),
(2, 1, 1, 2, 2, 'MN-7B2L9Z4C', 'takeaway', 'preparing', 130000, 0, 130000, 0, '', NOW() - INTERVAL 25 MINUTE, NULL);

INSERT INTO `order_items` (`order_id`, `product_id`, `name`, `unit_price`, `quantity`, `total`) VALUES
(1, 1, 'لاته کاراملی', 125000, 1, 125000),
(1, 6, 'چیزکیک روز', 150000, 1, 150000),
(2, 4, 'آیس لاته', 130000, 1, 130000);

INSERT INTO `loyalty_transactions` (`tenant_id`, `customer_id`, `order_id`, `points`, `reason`, `balance_after`, `note`, `created_by`) VALUES
(1, 1, NULL, 50, 'signup', 50, 'هدیه‌ی خوش‌آمدگویی', NULL),
(1, 1, 1, 27, 'purchase', 77, 'امتیاز خرید سفارش MN-8F3K2Q1A', 2),
(1, 2, NULL, 50, 'signup', 50, 'هدیه‌ی خوش‌آمدگویی', NULL),
(1, 2, NULL, 70, 'adjustment', 120, 'امتیاز انتقالی از باشگاه قبلی', 2);

INSERT INTO `rewards` (`id`, `tenant_id`, `title`, `description`, `points_cost`, `stock`, `is_active`) VALUES
(1, 1, 'یک قهوه مهمان ما', 'هر قهوه‌ی سرد یا گرم از منو', 100, 50, 1),
(2, 1, '۱۵٪ تخفیف دسر', 'روی یکی از دسرهای روز', 60, NULL, 1),
(3, 1, '۵۰ هزار تومان اعتبار', 'اعتبار خرید برای سفارش بعدی', 250, 20, 1);

INSERT INTO `ad_campaigns` (`id`, `tenant_id`, `title`, `description`, `cta_url`, `placement`, `budget`, `spent`, `starts_at`, `ends_at`, `status`, `impressions`, `clicks`) VALUES
(1, 1, 'صبحانه تا ساعت ۱۱', 'املت قارچ و قهوه‌ی تازه، هر روز تا ۱۱ صبح.', 'https://monochi.ir/menu/cafe-monochi', 'app_home', 5000000, 1240000, NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 25 DAY, 'active', 18420, 642),
(2, 1, 'دسر روز، نصف قیمت', 'هر روز یک دسر متفاوت با ۵۰٪ تخفیف.', NULL, 'app_nearby', 2000000, 0, NOW() + INTERVAL 3 DAY, NOW() + INTERVAL 18 DAY, 'draft', 0, 0);

INSERT INTO `ad_events` (`campaign_id`, `type`, `ip_hash`, `created_at`) VALUES
(1, 'impression', REPEAT('a', 64), NOW() - INTERVAL 2 HOUR),
(1, 'impression', REPEAT('b', 64), NOW() - INTERVAL 90 MINUTE),
(1, 'click', REPEAT('b', 64), NOW() - INTERVAL 89 MINUTE);

INSERT INTO `contact_messages` (`name`, `contact`, `subject`, `message`, `ip_hash`, `status`) VALUES
('مریم احمدی', 'maryam@example.com', 'همکاری', 'سلام، برای راه‌اندازی منوی کافه‌مان راهنمایی می‌خواهم.', REPEAT('c', 64), 'new');

INSERT INTO `subscriptions` (`id`, `tenant_id`, `plan_id`, `billing`, `price`, `starts_at`, `ends_at`, `status`) VALUES
(1, 1, 2, 'monthly', 290000, CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 20 DAY, 'active');

INSERT INTO `payments` (`tenant_id`, `subscription_id`, `amount`, `gateway`, `reference`, `status`, `paid_at`, `meta`) VALUES
(1, 1, 290000, 'manual', 'DEMO-1001', 'paid', NOW() - INTERVAL 10 DAY, JSON_OBJECT('note', 'پرداخت آزمایشی'));

INSERT INTO `settings` (`key`, `value`) VALUES
('site_name', 'منوچی'),
('support_email', 'hello@monochi.ir'),
('support_phone', '021-88776655'),
('loyalty_points_per_10000', '1'),
('max_open_orders_per_table', '5'),
('ads_enabled', '1');

SET FOREIGN_KEY_CHECKS = 1;
