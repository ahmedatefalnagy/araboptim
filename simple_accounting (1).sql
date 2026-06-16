-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 16 يونيو 2026 الساعة 12:51
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simple_accounting`
--

-- --------------------------------------------------------

--
-- بنية الجدول `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `account_type_id` bigint(20) UNSIGNED NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `is_postable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `depreciation_rate` decimal(5,2) DEFAULT NULL COMMENT 'نسبة الإهلاك للأصول الثابتة',
  `report_group` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `accounts`
--

INSERT INTO `accounts` (`id`, `parent_id`, `code`, `name`, `account_type_id`, `level`, `is_postable`, `is_active`, `depreciation_rate`, `report_group`, `created_at`, `updated_at`) VALUES
(1, NULL, '1', 'الأصول', 1, 1, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(2, 1, '11', 'الأصول المتداولة', 1, 2, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(3, 2, '1101', 'الصندوق', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(4, 2, '1102', 'البنك', 1, 3, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(5, 4, '110201', 'بنك الراجحي الرئيسي', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(6, 4, '110202', 'بنك الراجحي الإدارة', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(7, 4, '110203', 'بنك الراجحي المشتريات', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(8, 2, '1103', 'العملاء (ذمم مدينة)', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(9, 2, '1104', 'المخزون', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(10, 2, '1105', 'مصروفات مدفوعة مقدماً', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(11, 2, '1106', 'عهد وسلف الموظفين', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(12, 2, '1107', 'ضريبة القيمة المضافة مدينة (مدخلات)', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(13, 2, '1108', 'أوراق القبض', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(14, 1, '12', 'الأصول غير المتداولة', 1, 2, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(15, 14, '1201', 'الأصول الثابتة', 1, 3, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(16, 15, '120101', 'سيارات', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(17, 15, '120102', 'أجهزة ومعدات', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(18, 15, '120103', 'أثاث', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(19, 14, '1202', 'مجمع الإهلاك', 1, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(20, 14, '1203', 'أصول غير ملموسة', 1, 3, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(21, 20, '120301', 'برامج وأنظمة', 1, 4, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(22, NULL, '2', 'الخصوم', 2, 1, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(23, 22, '21', 'الخصوم المتداولة', 2, 2, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(24, 23, '2101', 'الموردون', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(25, 23, '2102', 'دائنون متنوعون', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(26, 23, '2103', 'ضريبة القيمة المضافة مستحقة (مخرجات)', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(27, 23, '2104', 'رواتب وأجور مستحقة', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(28, 23, '2105', 'مصروفات مستحقة', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(29, 23, '2106', 'إيرادات مقدمة (سلف العملاء)', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(30, 23, '2107', 'أوراق الدفع', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(31, 23, '2108', 'الجزء المتداول من القروض', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(32, 22, '22', 'الخصوم غير المتداولة', 2, 2, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(33, 32, '2201', 'قروض طويلة الأجل', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(34, 32, '2202', 'التزامات طويلة الأجل أخرى', 2, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(35, NULL, '3', 'حقوق الملكية', 3, 1, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(36, 35, '3101', 'رأس المال', 3, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(37, 35, '3201', 'الأرباح المبقاة', 3, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(38, 35, '3301', 'مسحوبات الشركاء', 3, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(39, 35, '3401', 'احتياطي نظامي', 3, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(40, 35, '3402', 'احتياطي اختياري', 3, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(41, NULL, '4', 'الإيرادات', 4, 1, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(42, 41, '4101', 'المبيعات', 4, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(43, 41, '4102', 'مبيعات خدمات', 4, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(44, 41, '4103', 'خصومات المبيعات', 4, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(45, 41, '4201', 'إيرادات أخرى', 4, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(46, 41, '4202', 'فروقات أسعار', 4, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(47, NULL, '5', 'المصروفات', 5, 1, 0, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(48, 47, '5101', 'تكلفة البضاعة المباعة', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(49, 47, '5102', 'رواتب وأجور', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(50, 47, '5103', 'إيجارات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(51, 47, '5104', 'كهرباء ومياه', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(52, 47, '5105', 'إنترنت واتصالات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(53, 47, '5106', 'محروقات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(54, 47, '5107', 'صيانة وإصلاحات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(55, 47, '5108', 'نقل وشحن', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(56, 47, '5109', 'ضيافة وسفر', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(57, 47, '5201', 'مصاريف مكتبية', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(58, 47, '5202', 'رسوم حكومية', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(59, 47, '5203', 'رسوم بنكية', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(60, 47, '5204', 'استشارات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(61, 47, '5301', 'إهلاك سيارات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59'),
(62, 47, '5302', 'إهلاك أجهزة ومعدات', 5, 3, 1, 1, NULL, NULL, '2026-06-12 07:40:59', '2026-06-12 07:40:59');

-- --------------------------------------------------------

--
-- بنية الجدول `account_types`
--

CREATE TABLE `account_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `normal_balance` enum('debit','credit') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `account_types`
--

INSERT INTO `account_types` (`id`, `code`, `name`, `normal_balance`, `created_at`, `updated_at`) VALUES
(1, 'asset', 'الأصول', 'debit', '2026-04-09 11:38:42', '2026-04-09 11:38:42'),
(2, 'liability', 'الخصوم', 'credit', '2026-04-09 11:38:42', '2026-04-09 11:38:42'),
(3, 'equity', 'حقوق الملكية', 'credit', '2026-04-09 11:38:42', '2026-04-09 11:38:42'),
(4, 'revenue', 'الإيرادات', 'credit', '2026-04-09 11:38:42', '2026-04-09 11:38:42'),
(5, 'expense', 'المصروفات', 'debit', '2026-04-09 11:38:42', '2026-04-09 11:38:42');

-- --------------------------------------------------------

--
-- بنية الجدول `advances`
--

CREATE TABLE `advances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `spent` decimal(14,2) NOT NULL DEFAULT 0.00,
  `remaining` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','settled','closed') NOT NULL DEFAULT 'active',
  `issue_date` date NOT NULL,
  `settlement_date` date DEFAULT NULL,
  `settled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `advance_expenses`
--

CREATE TABLE `advance_expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `advance_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `expense_date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 0,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL,
  `expense_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tax_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('purchase','expense','voucher') NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `advance_settlements`
--

CREATE TABLE `advance_settlements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `advance_id` bigint(20) UNSIGNED NOT NULL,
  `settlement_no` varchar(255) NOT NULL,
  `settlement_date` date NOT NULL,
  `status` enum('draft','approved') NOT NULL DEFAULT 'draft',
  `total_expenses` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'إجمالي المصروفات قبل الضريبة',
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'إجمالي الضريبة',
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'الإجمالي شامل الضريبة',
  `refund_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'المبلغ المرتجع للشركة',
  `refund_type` varchar(255) DEFAULT NULL,
  `rolled_over_to_advance_id` bigint(20) UNSIGNED DEFAULT NULL,
  `additional_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'المبلغ المطلوب دفعه إضافياً للموظف',
  `journal_entry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `advance_settlement_lines`
--

CREATE TABLE `advance_settlement_lines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `settlement_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('expense','purchase') NOT NULL COMMENT 'مصروفات أو مشتريات',
  `invoice_no` varchar(255) DEFAULT NULL COMMENT 'رقم الفاتورة',
  `invoice_date` date NOT NULL,
  `vendor_name` varchar(255) DEFAULT NULL COMMENT 'اسم المورد',
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL COMMENT 'المبلغ قبل الضريبة',
  `is_taxable` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'هل خاضعة للضريبة',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'نسبة الضريبة',
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'مبلغ الضريبة',
  `total_amount` decimal(15,2) NOT NULL COMMENT 'المبلغ شامل الضريبة',
  `expense_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `advance_transactions`
--

CREATE TABLE `advance_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `advance_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('expense','voucher','invoice','recharge','adjustment') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_taxable` tinyint(1) NOT NULL DEFAULT 0,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `is_customer` tinyint(1) NOT NULL DEFAULT 0,
  `is_supplier` tinyint(1) NOT NULL DEFAULT 0,
  `is_main_company` tinyint(1) NOT NULL DEFAULT 0,
  `is_sub_client` tinyint(1) NOT NULL DEFAULT 0,
  `main_company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_related_party` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receivable_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payable_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `contacts`
--

INSERT INTO `contacts` (`id`, `type`, `is_customer`, `is_supplier`, `is_main_company`, `is_sub_client`, `main_company_id`, `is_related_party`, `name`, `email`, `phone`, `tax_number`, `account_id`, `receivable_account_id`, `payable_account_id`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'supplier', 1, 0, 0, 0, NULL, 0, 'شركة التفاؤل للمقاولات', NULL, '0512345678', NULL, NULL, NULL, NULL, NULL, 1, '2026-06-12 13:27:42', '2026-06-12 13:27:42');

-- --------------------------------------------------------

--
-- بنية الجدول `cost_centers`
--

CREATE TABLE `cost_centers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `driver_locations`
--

CREATE TABLE `driver_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `speed` double NOT NULL DEFAULT 0,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `driver_locations`
--

INSERT INTO `driver_locations` (`id`, `trip_id`, `latitude`, `longitude`, `speed`, `recorded_at`, `created_at`, `updated_at`) VALUES
(1, 1, 37.4219983, -122.084, 0, '2026-06-13 04:53:09', '2026-06-13 04:53:09', '2026-06-13 04:53:09'),
(2, 1, 24.7135983, 46.6753, 0, '2026-06-13 05:20:50', '2026-06-13 05:20:50', '2026-06-13 05:20:50'),
(3, 1, 24.7135983, 46.6753, 0, '2026-06-13 05:21:14', '2026-06-13 05:21:14', '2026-06-13 05:21:14'),
(4, 1, 24.7135983, 46.6753, 0, '2026-06-13 05:23:33', '2026-06-13 05:23:33', '2026-06-13 05:23:33'),
(5, 1, 24.7135983, 46.6753, 0, '2026-06-14 01:58:01', '2026-06-14 01:58:01', '2026-06-14 01:58:01'),
(6, 1, 24.7135983, 46.6753, 0, '2026-06-14 01:58:01', '2026-06-14 01:58:01', '2026-06-14 01:58:01'),
(7, 1, 24.7135983, 46.6753, 0, '2026-06-14 02:00:51', '2026-06-14 02:00:51', '2026-06-14 02:00:51'),
(8, 1, 24.7135983, 46.6753, 0, '2026-06-14 02:00:55', '2026-06-14 02:00:55', '2026-06-14 02:00:55'),
(9, 1, 24.7135983, 46.6753, 0, '2026-06-14 02:03:28', '2026-06-14 02:03:28', '2026-06-14 02:03:28'),
(10, 1, 24.7135983, 46.6753, 0, '2026-06-14 02:03:28', '2026-06-14 02:03:28', '2026-06-14 02:03:28'),
(11, 1, 24.7135983, 46.6753, 0, '2026-06-14 02:04:27', '2026-06-14 02:04:27', '2026-06-14 02:04:27'),
(12, 1, 24.7135983, 46.6753, 0, '2026-06-14 02:04:27', '2026-06-14 02:04:27', '2026-06-14 02:04:27');

-- --------------------------------------------------------

--
-- بنية الجدول `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_no` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `iqama_no` varchar(255) DEFAULT NULL COMMENT 'رقم الإقامة',
  `operation_card_no` varchar(255) DEFAULT NULL,
  `driver_card_no` varchar(255) DEFAULT NULL,
  `transport_license_no` varchar(255) DEFAULT NULL,
  `iqama_expiry` date DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `authorization_expiry` date DEFAULT NULL,
  `work_card_expiry` date DEFAULT NULL,
  `driver_card_expiry` date DEFAULT NULL,
  `transport_license_expiry` date DEFAULT NULL,
  `national_id` varchar(255) DEFAULT NULL,
  `passport_no` varchar(255) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `is_driver` tinyint(1) NOT NULL DEFAULT 0,
  `department` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `basic_salary` decimal(15,2) DEFAULT 0.00,
  `commission` decimal(15,2) NOT NULL DEFAULT 0.00,
  `housing_allowance` decimal(15,2) DEFAULT 0.00,
  `transport_allowance` decimal(15,2) DEFAULT 0.00,
  `other_allowances` decimal(15,2) DEFAULT 0.00,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_no` varchar(255) DEFAULT NULL,
  `iban` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `license_copy` varchar(255) DEFAULT NULL,
  `iqama_copy` varchar(255) DEFAULT NULL,
  `document_file` varchar(255) DEFAULT NULL,
  `authorization_copy` varchar(255) DEFAULT NULL,
  `operation_card_copy` varchar(255) DEFAULT NULL,
  `driver_card_copy` varchar(255) DEFAULT NULL,
  `combined_documents_pdf` varchar(255) DEFAULT NULL,
  `vehicle_license_copy` varchar(255) DEFAULT NULL,
  `work_card_copy` varchar(255) DEFAULT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `employees`
--

INSERT INTO `employees` (`id`, `employee_no`, `name`, `name_en`, `nationality`, `birth_date`, `iqama_no`, `operation_card_no`, `driver_card_no`, `transport_license_no`, `iqama_expiry`, `license_expiry`, `authorization_expiry`, `work_card_expiry`, `driver_card_expiry`, `transport_license_expiry`, `national_id`, `passport_no`, `passport_expiry`, `job_title`, `is_driver`, `department`, `hire_date`, `end_date`, `basic_salary`, `commission`, `housing_allowance`, `transport_allowance`, `other_allowances`, `bank_name`, `account_no`, `iban`, `phone`, `address`, `email`, `status`, `notes`, `created_at`, `updated_at`, `license_copy`, `iqama_copy`, `document_file`, `authorization_copy`, `operation_card_copy`, `driver_card_copy`, `combined_documents_pdf`, `vehicle_license_copy`, `work_card_copy`, `account_id`) VALUES
(17, 'EMP-1002', 'أحمد السائق', NULL, NULL, NULL, '2345678901', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-06-12', NULL, 4500.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, '0555555555', NULL, 'driver@driver.com', 'active', NULL, '2026-06-12 13:27:42', '2026-06-13 04:32:16', 'drivers/licenses/test_license.png', 'drivers/iqamas/test_iqama.png', NULL, NULL, NULL, NULL, NULL, 'drivers/vehicle_licenses/test_vehicle_license.png', NULL, NULL),
(18, 'EMP-01003', 'Ahmed Driver Test', NULL, 'Saudi', '1990-05-15', '2345678901', '3456789012', '4567890123', '5678901234', '2026-12-31', '2027-01-15', '2027-02-28', '2027-03-31', '2027-04-30', '2027-05-31', NULL, NULL, NULL, 'Truck Driver', 1, NULL, '2004-02-20', NULL, 5000.00, 1500.00, NULL, NULL, NULL, 'Al Rajhi Bank', '123456789012', 'SA8080000000123456789012', '0512345678', 'Riyadh, Al-Malaz', 'ahmed.driver@test.com', 'active', NULL, '2026-06-14 12:03:46', '2026-06-14 17:38:15', 'employees/j2MAJSIyYyZZbjoJQNRAGC5pzoc1AVYsKM4awBv6.png', 'employees/2K3D6IASGRg411DkwldzRTmyKusoz78H6GRL0AUV.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'EMP-01004', 'Ahmed Driver Test', NULL, 'Saudi', '1990-05-15', '2345678901', 'OP-998877', 'DC-112233', 'TL-554433', '2030-12-31', '2030-05-15', '2027-06-14', '2027-06-14', '2027-06-14', '2027-06-14', NULL, NULL, NULL, 'Truck Driver', 1, NULL, '2025-01-01', NULL, 5000.00, 1500.00, NULL, NULL, NULL, 'Al Rajhi Bank', '123456789012', 'SA8080000000123456789012', '0512345678', 'Riyadh, Al-Malaz', 'ahmed.driver@test.com', 'active', NULL, '2026-06-14 12:12:07', '2026-06-14 17:40:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 'EMP-01005', 'Test Driver', NULL, 'Saudi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Driver', 0, NULL, '2004-02-20', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, '2026-06-14 17:42:23', '2026-06-16 04:34:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `employee_advances`
--

CREATE TABLE `employee_advances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('advance','custody','bonus') NOT NULL,
  `reference_no` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `deducted_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'المبلغ المسترد / المخصوم',
  `remaining_amount` decimal(15,2) GENERATED ALWAYS AS (`amount` - `deducted_amount`) STORED,
  `status` enum('open','partially_settled','settled') NOT NULL DEFAULT 'open',
  `purpose` varchar(255) DEFAULT NULL COMMENT 'غرض السلفة أو العهدة',
  `debit_description` varchar(255) DEFAULT NULL,
  `credit_description` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_account_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `fiscal_years`
--

CREATE TABLE `fiscal_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `fiscal_years`
--

INSERT INTO `fiscal_years` (`id`, `name`, `start_date`, `end_date`, `is_closed`, `created_at`, `updated_at`) VALUES
(1, '2025', '2025-01-01', '2025-12-31', 0, '2026-04-09 11:38:42', '2026-04-20 16:13:22'),
(2, '2026', '2026-01-01', '2026-12-31', 0, '2026-04-26 07:12:25', '2026-04-26 07:12:25'),
(3, '2027', '2027-01-01', '2027-12-31', 0, '2026-04-26 07:12:25', '2026-04-26 07:12:25'),
(4, '2024', '2024-01-01', '2024-12-31', 1, '2026-04-26 07:12:25', '2026-04-26 07:12:25');

-- --------------------------------------------------------

--
-- بنية الجدول `government_expenses`
--

CREATE TABLE `government_expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('iqama_renewal','work_permit','insurance','exit_reentry','other') NOT NULL COMMENT 'نوع المصروف: تجديد إقامة، تصريح عمل، تأمين، تأشيرة خروج وعودة، أخرى',
  `reference_no` varchar(255) DEFAULT NULL,
  `expense_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL COMMENT 'تاريخ انتهاء الصلاحية',
  `amount` decimal(15,2) NOT NULL,
  `provider` varchar(255) DEFAULT NULL COMMENT 'الجهة / الحكومة / شركة التأمين',
  `status` enum('pending','paid') NOT NULL DEFAULT 'paid',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `expense_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `journal_entry_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `inventory_stocks`
--

CREATE TABLE `inventory_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `inventory_stocks`
--

INSERT INTO `inventory_stocks` (`id`, `item_id`, `warehouse_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 31.00, '2026-04-18 18:26:39', '2026-04-25 10:26:24');

-- --------------------------------------------------------

--
-- بنية الجدول `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `type` enum('sale','sale_return','purchase','purchase_return','sale_quotation','sale_order','purchase_quotation','purchase_order','work_order','goods_receipt','goods_issue') NOT NULL,
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'credit',
  `payment_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `total_base` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `base_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tax_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `journal_entry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL COMMENT 'Path to uploaded document (Original Invoice or Signed Delivery Note)',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `parent_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cost_center_id` bigint(20) UNSIGNED DEFAULT NULL,
  `xml_uuid` char(36) DEFAULT NULL COMMENT 'ZATCA specific Universally Unique Identifier',
  `zatca_hash` varchar(255) DEFAULT NULL COMMENT 'SHA256 Hash of the XML Base64',
  `previous_hash` varchar(255) DEFAULT NULL COMMENT 'PIH: Previous Invoice Hash',
  `qr_code_base64` text DEFAULT NULL COMMENT 'TLV Base64 QR Code string',
  `xml_content` longtext DEFAULT NULL COMMENT 'Generated UBL 2.1 XML',
  `zatca_status` enum('pending','reported','cleared','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `invoice_lines`
--

CREATE TABLE `invoice_lines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(15,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `subtotal` decimal(15,2) NOT NULL,
  `tax_amount` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cost_center_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'product',
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 15.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `track_inventory` tinyint(1) NOT NULL DEFAULT 1,
  `alert_quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `items`
--

INSERT INTO `items` (`id`, `name`, `sku`, `barcode`, `type`, `price`, `cost_price`, `tax_rate`, `is_active`, `track_inventory`, `alert_quantity`, `description`, `created_at`, `updated_at`, `unit_id`, `category_id`) VALUES
(1, 'صنف 1', NULL, NULL, 'product', 0.00, 0.00, 15.00, 1, 1, 0.00, NULL, '2026-04-18 18:24:05', '2026-04-18 18:24:05', 1, 1);

-- --------------------------------------------------------

--
-- بنية الجدول `item_categories`
--

CREATE TABLE `item_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `item_categories`
--

INSERT INTO `item_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'عام', 'التصنيف العام للأصناف', '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(2, 'منتجات تامة الصنع', 'المنتجات الجاهزة للبيع', '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(3, 'مواد خام', 'مواد تستخدم في التصنيع', '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(4, 'خدمات', 'أصناف غير ملموسة كالتركيب والصيانة', '2026-04-10 23:50:20', '2026-04-10 23:50:20');

-- --------------------------------------------------------

--
-- بنية الجدول `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entry_no` varchar(255) NOT NULL,
  `entry_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `fiscal_year_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('draft','posted') NOT NULL DEFAULT 'draft',
  `transaction_type` varchar(255) DEFAULT NULL COMMENT 'e.g., salary, advance, custody',
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'related entity ID, e.g., employee_id',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `posted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `journal_entries`
--

INSERT INTO `journal_entries` (`id`, `entry_no`, `entry_date`, `description`, `fiscal_year_id`, `status`, `transaction_type`, `reference_id`, `created_by`, `posted_by`, `posted_at`, `created_at`, `updated_at`) VALUES
(1, 'JV-000001', '2025-01-01', 'سند قبض رقم RV-3980 - Payment from Ahmed', 1, 'posted', 'voucher', 1, 4, 4, '2026-06-12 09:58:51', '2026-06-12 09:58:51', '2026-06-12 09:58:51');

-- --------------------------------------------------------

--
-- بنية الجدول `journal_entry_lines`
--

CREATE TABLE `journal_entry_lines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `journal_entry_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `debit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `line_order` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cost_center_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `journal_entry_lines`
--

INSERT INTO `journal_entry_lines` (`id`, `journal_entry_id`, `account_id`, `contact_id`, `description`, `debit`, `credit`, `line_order`, `created_at`, `updated_at`, `cost_center_id`) VALUES
(1, 1, 3, NULL, 'سند قبض رقم RV-3980 - Payment from Ahmed', 150.00, 0.00, 1, '2026-06-12 09:58:51', '2026-06-12 09:58:51', NULL),
(2, 1, 8, NULL, 'سند قبض رقم RV-3980 - Payment from Ahmed', 0.00, 150.00, 2, '2026-06-12 09:58:51', '2026-06-12 09:58:51', NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `maintenance_orders`
--

CREATE TABLE `maintenance_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(255) NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('draft','pending_parts','in_progress','completed','cancelled') NOT NULL DEFAULT 'draft',
  `type` enum('routine','emergency','preventive') NOT NULL DEFAULT 'routine',
  `current_odometer` int(11) NOT NULL,
  `issue_description` text DEFAULT NULL,
  `total_parts_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `labor_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `maintenance_order_items`
--

CREATE TABLE `maintenance_order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_order_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` enum('oil_filter','tires','mechanical','electrical','others') NOT NULL,
  `issue_description` text NOT NULL,
  `status` enum('pending','approved','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
  `stock_status` enum('available','needed','ordered','issued') NOT NULL DEFAULT 'available',
  `estimated_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_04_09_143004_create_account_types_table', 1),
(6, '2026_04_09_143005_create_accounts_table', 1),
(7, '2026_04_09_143005_create_fiscal_years_table', 1),
(8, '2026_04_09_143006_create_journal_entries_table', 1),
(9, '2026_04_09_143006_create_opening_balances_table', 1),
(10, '2026_04_09_143007_create_journal_entry_lines_table', 1),
(11, '2026_04_10_200743_create_contacts_table', 2),
(12, '2026_04_10_200743_create_invoices_table', 2),
(13, '2026_04_10_200744_create_vouchers_table', 2),
(14, '2026_04_10_234041_create_items_table', 3),
(15, '2026_04_10_234048_create_invoice_lines_table', 3),
(16, '2026_04_11_024638_create_item_categories_table', 4),
(17, '2026_04_11_024638_create_units_table', 4),
(18, '2026_04_11_024638_create_warehouses_table', 4),
(19, '2026_04_11_024639_create_inventory_stocks_table', 4),
(20, '2026_04_11_024639_create_stock_movements_table', 4),
(21, '2026_04_11_024640_add_erp_fields_to_items_table', 4),
(22, '2026_04_11_030646_update_invoices_type_enum', 5),
(23, '2026_04_11_033136_create_cost_centers_table', 6),
(24, '2026_04_11_033144_add_erp_features_to_invoices_and_journals', 6),
(25, '2026_04_11_034719_create_employees_table', 7),
(26, '2026_04_11_034726_create_employee_advances_table', 7),
(27, '2026_04_11_034726_create_payrolls_table', 7),
(28, '2026_04_11_034727_create_government_expenses_table', 7),
(29, '2026_04_11_103858_create_logistics_tables', 8),
(30, '2026_04_11_105746_update_logistic_driver_financials', 9),
(31, '2026_04_11_122049_add_is_driver_to_employees_table', 10),
(32, '2026_04_11_123615_update_trips_for_commission_logic', 11),
(33, '2026_04_11_124727_create_trip_routes_table', 12),
(34, '2026_04_11_135318_create_maintenance_tables', 13),
(35, '2026_04_11_144526_add_oil_km_to_vehicles_table', 14),
(36, '2026_04_11_145506_add_employee_id_to_users_table', 15),
(37, '2026_04_11_173414_create_trip_diesels_table', 16),
(38, '2026_04_19_141458_add_transaction_fields_to_journal_entries', 17),
(39, '2026_04_19_145247_add_account_id_to_employees_table', 18),
(40, '2026_04_19_145301_add_payment_account_id_to_hr_tables', 18),
(41, '2026_04_19_151254_update_vouchers_table_types', 19),
(42, '2026_04_19_161340_add_payment_fields_to_invoices_table', 20),
(43, '2026_04_19_183700_add_line_descriptions_to_vouchers_table', 21),
(44, '2026_04_19_184551_add_line_descriptions_to_employee_advances_table', 22),
(45, '2026_04_20_112417_add_bonus_type_to_employee_advances', 23),
(46, '2026_04_20_112447_add_bonus_account_to_coa', 24),
(47, '2026_04_20_185707_add_contact_id_to_journal_entry_lines_table', 25),
(48, '2026_04_25_220635_create_settings_table', 26),
(49, '2026_04_26_000001_create_advances_table', 27),
(50, '2026_04_26_000002_create_advance_transactions_table', 27),
(51, '2026_04_26_000003_create_advance_expenses_table', 27),
(52, '2026_04_26_125000_create_advance_settlements_table', 28),
(53, '2026_04_26_222847_add_account_fields_to_government_expenses_table', 29),
(54, '2026_04_27_001706_upgrade_contacts_to_partner_system', 30),
(55, '2026_04_27_002358_clean_up_contact_accounts_migration', 31),
(56, '2026_04_27_003732_consolidate_contacts_to_single_account_migration', 32),
(57, '2026_04_27_041721_add_cargo_details_to_trips_table', 33),
(58, '2026_04_26_042605_enhance_fleet_management_tables', 34),
(59, '2026_04_27_042605_enhance_fleet_management_tables', 34),
(60, '2026_04_27_044547_add_advanced_fields_to_trip_routes_table', 35),
(61, '2026_04_28_175658_add_depreciation_rate_to_accounts_table', 36),
(62, '2026_04_29_004002_add_indexes_to_journal_entry_lines_table', 37),
(63, '2026_06_12_000000_add_attachment_path_to_invoices_table', 38),
(64, '2026_06_12_010000_update_invoices_type_enum_for_goods', 39),
(65, '2026_06_12_020000_add_role_to_users_table', 40),
(66, '2026_06_12_030000_add_address_and_document_to_employees_table', 41),
(67, '2026_06_12_040000_add_attachment_path_to_employee_advances_table', 42),
(68, '2026_06_12_050000_add_refund_type_to_advance_settlements_table', 43),
(69, '2026_06_12_060000_add_attachment_path_to_vouchers_table', 44),
(70, '2026_06_12_070000_add_driver_app_fields_and_locations_table', 45),
(71, '2026_06_13_065333_add_extra_driver_documents_to_employees_table', 46),
(72, '2026_06_14_145906_add_new_fields_to_employees_table', 47),
(73, '2026_06_16_000001_add_flags_to_contacts', 48),
(74, '2026_06_16_000002_add_main_company_id_to_trips', 48),
(75, '2026_06_16_000003_create_trip_sub_clients_table', 48),
(76, '2026_06_16_000004_add_main_company_id_to_contacts_table', 49);

-- --------------------------------------------------------

--
-- بنية الجدول `opening_balances`
--

CREATE TABLE `opening_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fiscal_year_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `debit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `month` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `housing_allowance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transport_allowance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `other_allowances` decimal(15,2) NOT NULL DEFAULT 0.00,
  `trip_allowance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `overtime_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `gross_salary` decimal(15,2) NOT NULL COMMENT 'الإجمالي قبل الخصومات',
  `gosi_employee` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'حصة الموظف في التأمينات',
  `gosi_employer` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'حصة صاحب العمل في التأمينات',
  `advance_deduction` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'خصم السلفة',
  `other_deductions` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(15,2) NOT NULL COMMENT 'صافي الراتب',
  `status` enum('draft','approved','paid') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payment_account_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 5, 'driver-app-token', '13be9bf022b41b032c89573c8c04abd8ccba9728109d88881fabef88a084fcc6', '[\"*\"]', NULL, NULL, '2026-06-13 03:45:56', '2026-06-13 03:45:56'),
(2, 'App\\Models\\User', 5, 'driver-app-token', 'bc3d5fabc6a5f24e9260c8d0157676d17106fc0855a18ce93e3aad32e883a866', '[\"*\"]', '2026-06-13 03:57:09', NULL, '2026-06-13 03:46:04', '2026-06-13 03:57:09'),
(3, 'App\\Models\\User', 5, 'driver-app-token', '2735b77ea0c469ef6c1025abd39bdc56afe419a49159508cfbdf2f1c5a56f259', '[\"*\"]', '2026-06-13 03:59:46', NULL, '2026-06-13 03:59:42', '2026-06-13 03:59:46'),
(4, 'App\\Models\\User', 5, 'driver-app-token', '80ce7bbcaa267d2d69ffabe7ad01ef8cb56781744d68573564dfe6429b994ca4', '[\"*\"]', '2026-06-13 04:47:32', NULL, '2026-06-13 04:07:50', '2026-06-13 04:47:32'),
(5, 'App\\Models\\User', 5, 'driver-app-token', '6c4ddb11f3606aaed9125673978239d6c04e12bed37dbb87d9b40ae48707c396', '[\"*\"]', '2026-06-13 04:56:33', NULL, '2026-06-13 04:52:36', '2026-06-13 04:56:33'),
(6, 'App\\Models\\User', 5, 'driver-app-token', '1f23b4729f1e8656aa6d6efb2ea4b3b213e38cef3a28663eb3547ecb6bdc0943', '[\"*\"]', NULL, NULL, '2026-06-13 05:17:30', '2026-06-13 05:17:30'),
(7, 'App\\Models\\User', 5, 'driver-app-token', '29d19f2b8a115d8940eda8c271da26ac6d2ccb6c000d58d7317ea68dfaba4abe', '[\"*\"]', '2026-06-13 05:58:59', NULL, '2026-06-13 05:19:38', '2026-06-13 05:58:59'),
(8, 'App\\Models\\User', 5, 'driver-app-token', '3fa15ce12ff7b18821fb2dc8729c2b7f48000f13a9a60fdb8eb48152341f72e2', '[\"*\"]', NULL, NULL, '2026-06-14 01:52:49', '2026-06-14 01:52:49'),
(9, 'App\\Models\\User', 5, 'driver-app-token', '9d587314117238cf441f833a8d773f891c4d6e45c2d7cd2909d5b010b8e72d92', '[\"*\"]', '2026-06-14 02:01:39', NULL, '2026-06-14 01:53:40', '2026-06-14 02:01:39'),
(10, 'App\\Models\\User', 5, 'driver-app-token', '2c9875d1fc67b574e9419324321be302f523f1f2f2426a3d01835776e1e26eb2', '[\"*\"]', '2026-06-14 02:03:34', NULL, '2026-06-14 02:03:22', '2026-06-14 02:03:34'),
(11, 'App\\Models\\User', 5, 'driver-app-token', 'a7af2546e9c3433e02bbe81d856e1c9b7dbd964b7550afb5eaa54ee8ff5c8175', '[\"*\"]', '2026-06-14 02:03:48', NULL, '2026-06-14 02:03:41', '2026-06-14 02:03:48'),
(12, 'App\\Models\\User', 5, 'driver-app-token', '3c6e1d1e0c5fc8fb697b8ca4042dbf8707711e45a5b7e32bc5038c47d3654b97', '[\"*\"]', '2026-06-14 05:33:28', NULL, '2026-06-14 02:04:22', '2026-06-14 05:33:28'),
(13, 'App\\Models\\User', 4, 'driver-app-token', '04d65fb16ce77ffd37790e74b5a7f9aaff300f52b1489a7c726db31299300bad', '[\"*\"]', '2026-06-14 08:34:29', NULL, '2026-06-14 05:45:12', '2026-06-14 08:34:29'),
(14, 'App\\Models\\User', 5, 'driver-app-token', '22d3f8f53537fc6aa1db9a0a45610d5aa623d1c55cc301481b56ec606f19a7a3', '[\"*\"]', '2026-06-14 08:40:54', NULL, '2026-06-14 08:35:10', '2026-06-14 08:40:54');

-- --------------------------------------------------------

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'شركة التفاؤل العربية للخدمات اللوجستية', '2026-04-25 19:09:41', '2026-06-15 16:23:45'),
(2, 'company_address', NULL, '2026-04-25 19:09:41', '2026-04-25 19:09:41'),
(3, 'company_phone', NULL, '2026-04-25 19:09:41', '2026-04-25 19:09:41'),
(4, 'company_vat_no', '312253166440003', '2026-04-25 19:09:41', '2026-06-15 16:23:45'),
(5, 'default_fiscal_year_id', '1', '2026-04-25 19:09:41', '2026-04-25 19:09:41'),
(6, 'company_fax', NULL, '2026-04-25 20:07:38', '2026-04-25 20:07:38'),
(7, 'company_commercial_record', '1009037942', '2026-04-25 20:07:38', '2026-06-15 16:23:45'),
(8, 'bank_name', NULL, '2026-04-25 20:07:38', '2026-04-25 20:07:38'),
(9, 'account_number', NULL, '2026-04-25 20:07:38', '2026-04-25 20:07:38'),
(10, 'iban', NULL, '2026-04-25 20:07:38', '2026-04-25 20:07:38'),
(11, 'company_name_en', 'ARAB OPTIMISM for Logistic services Co.', '2026-06-15 16:23:45', '2026-06-15 16:23:45'),
(12, 'company_email', 'accounts@araboptim.com', '2026-06-15 16:23:45', '2026-06-15 16:23:45');

-- --------------------------------------------------------

--
-- بنية الجدول `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out','transfer') NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `movement_date` date NOT NULL,
  `cost_per_unit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `item_id`, `warehouse_id`, `type`, `quantity`, `reference_type`, `reference_id`, `movement_date`, `cost_per_unit`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 1, '2026-04-18', 0.00, 'Invoice Ref: PR-946967', '2026-04-18 18:26:39', '2026-04-18 18:26:39'),
(5, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 5, '2025-01-14', 1500.00, 'Invoice Ref: PUR-257267', '2026-04-24 21:04:48', '2026-04-24 21:04:48'),
(6, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 6, '2025-01-09', 300.00, 'Invoice Ref: PUR-282501', '2026-04-24 21:09:10', '2026-04-24 21:09:10'),
(7, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 7, '2025-01-14', 434.78, 'Invoice Ref: PUR-575864', '2026-04-24 21:09:49', '2026-04-24 21:09:49'),
(8, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 8, '2025-02-01', 22110.00, 'Invoice Ref: PUR-692460', '2026-04-24 21:11:09', '2026-04-24 21:11:09'),
(9, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 9, '2025-01-19', 5050.00, 'Invoice Ref: PUR-162352', '2026-04-24 21:11:59', '2026-04-24 21:11:59'),
(10, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 10, '2025-02-09', 4920.00, 'Invoice Ref: PUR-238816', '2026-04-24 21:12:56', '2026-04-24 21:12:56'),
(11, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 11, '2025-03-23', 43350.00, 'Invoice Ref: PUR-826849', '2026-04-24 21:14:02', '2026-04-24 21:14:02'),
(12, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 12, '2025-03-25', 550.00, 'Invoice Ref: PUR-507385', '2026-04-24 21:14:53', '2026-04-24 21:14:53'),
(13, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 13, '2025-01-01', 12859.00, 'Invoice Ref: PUR-387066', '2026-04-24 21:17:18', '2026-04-24 21:17:18'),
(14, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 14, '2025-01-29', 2900.00, 'Invoice Ref: PUR-801875', '2026-04-24 21:18:00', '2026-04-24 21:18:00'),
(15, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 15, '2025-02-12', 1380.00, 'Invoice Ref: PUR-921249', '2026-04-24 21:18:42', '2026-04-24 21:18:42'),
(16, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 16, '2025-01-16', 350.00, 'Invoice Ref: PUR-292827', '2026-04-24 21:19:42', '2026-04-24 21:19:42'),
(17, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 17, '2025-01-14', 350.00, 'Invoice Ref: PUR-595579', '2026-04-24 21:20:36', '2026-04-24 21:20:36'),
(18, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 18, '2025-01-30', 240.00, 'Invoice Ref: PUR-313938', '2026-04-24 21:21:40', '2026-04-24 21:21:40'),
(19, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 19, '2025-01-30', 480.00, 'Invoice Ref: PUR-347854', '2026-04-24 21:22:21', '2026-04-24 21:22:21'),
(20, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 20, '2025-02-15', 915.50, 'Invoice Ref: PUR-968146', '2026-04-24 21:24:48', '2026-04-24 21:24:48'),
(21, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 21, '2025-01-28', 107900.00, 'Invoice Ref: PUR-646107', '2026-04-24 21:25:30', '2026-04-24 21:25:30'),
(22, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 22, '2025-01-08', 68478.27, 'Invoice Ref: PUR-491087', '2026-04-24 21:26:06', '2026-04-24 21:26:06'),
(23, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 23, '2025-01-05', 8786.00, 'Invoice Ref: PUR-982955', '2026-04-24 21:28:05', '2026-04-24 21:28:05'),
(24, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 24, '2025-01-14', 600.00, 'Invoice Ref: PUR-702365', '2026-04-24 21:29:20', '2026-04-24 21:29:20'),
(25, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 25, '2025-03-09', 53471.00, 'Invoice Ref: PUR-642241', '2026-04-24 21:30:03', '2026-04-24 21:30:03'),
(29, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 29, '2025-01-16', 0.00, 'Invoice Ref: SAL-951916', '2026-04-24 21:50:11', '2026-04-24 21:50:11'),
(30, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 30, '2025-01-02', 0.00, 'Invoice Ref: SAL-263886', '2026-04-24 21:57:32', '2026-04-24 21:57:32'),
(31, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 31, '2025-01-19', 0.00, 'Invoice Ref: SAL-417895', '2026-04-24 23:19:21', '2026-04-24 23:19:21'),
(32, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 32, '2026-02-09', 0.00, 'Invoice Ref: SAL-702432', '2026-04-24 23:22:07', '2026-04-24 23:22:07'),
(33, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 33, '2025-03-23', 0.00, 'Invoice Ref: SAL-826791', '2026-04-24 23:24:53', '2026-04-24 23:24:53'),
(34, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 34, '2025-03-23', 0.00, 'Invoice Ref: SAL-924070', '2026-04-24 23:27:30', '2026-04-24 23:27:30'),
(35, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 35, '2025-03-26', 0.00, 'Invoice Ref: SAL-583069', '2026-04-24 23:35:43', '2026-04-24 23:35:43'),
(36, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 36, '2025-01-23', 0.00, 'Invoice Ref: SAL-608520', '2026-04-24 23:47:13', '2026-04-24 23:47:13'),
(37, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 37, '2025-01-29', 0.00, 'Invoice Ref: SAL-675443', '2026-04-24 23:48:44', '2026-04-24 23:48:44'),
(38, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 38, '2025-02-12', 0.00, 'Invoice Ref: SAL-228853', '2026-04-24 23:49:18', '2026-04-24 23:49:18'),
(39, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 39, '2025-01-16', 0.00, 'Invoice Ref: SAL-901684', '2026-04-24 23:50:18', '2026-04-24 23:50:18'),
(40, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 40, '2025-01-30', 0.00, 'Invoice Ref: SAL-780514', '2026-04-24 23:50:52', '2026-04-24 23:50:52'),
(41, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 41, '2025-01-21', 0.00, 'Invoice Ref: SAL-676664', '2026-04-24 23:51:28', '2026-04-24 23:51:28'),
(42, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 42, '2025-01-29', 0.00, 'Invoice Ref: SAL-502072', '2026-04-24 23:52:04', '2026-04-24 23:52:04'),
(43, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 43, '2025-01-09', 0.00, 'Invoice Ref: SAL-713138', '2026-04-24 23:53:13', '2026-04-24 23:53:13'),
(44, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 44, '2025-01-06', 0.00, 'Invoice Ref: SAL-924530', '2026-04-24 23:53:51', '2026-04-24 23:53:51'),
(45, 1, 1, 'out', 1.00, 'App\\Models\\Invoice', 45, '2025-01-07', 0.00, 'Invoice Ref: SAL-911216', '2026-04-24 23:54:36', '2026-04-24 23:54:36'),
(46, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 46, '2025-06-04', 67480.00, 'Invoice Ref: PUR-378071', '2026-04-25 01:51:26', '2026-04-25 01:51:26'),
(47, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 47, '2025-04-06', 30580.00, 'Invoice Ref: PUR-543444', '2026-04-25 01:53:20', '2026-04-25 01:53:20'),
(48, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 48, '2025-04-09', 16850.00, 'Invoice Ref: PUR-137375', '2026-04-25 01:54:26', '2026-04-25 01:54:26'),
(49, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 49, '2025-05-12', 20.00, 'Invoice Ref: PUR-850701', '2026-04-25 01:55:15', '2026-04-25 01:55:15'),
(50, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 50, '2025-08-12', 156.52, 'Invoice Ref: PUR-893894', '2026-04-25 07:57:17', '2026-04-25 07:57:17'),
(51, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 51, '2025-05-10', 33.91, 'Invoice Ref: PUR-673213', '2026-04-25 08:31:39', '2026-04-25 08:31:39'),
(52, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 52, '2025-05-14', 434.78, 'Invoice Ref: PUR-223225', '2026-04-25 08:32:22', '2026-04-25 08:32:22'),
(53, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 53, '2025-05-14', 156.52, 'Invoice Ref: PUR-605268', '2026-04-25 08:33:07', '2026-04-25 08:33:07'),
(54, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 54, '2025-05-16', 33.91, 'Invoice Ref: PUR-321997', '2026-04-25 08:33:52', '2026-04-25 08:33:52'),
(55, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 55, '2025-05-20', 57.38, 'Invoice Ref: PUR-572248', '2026-04-25 08:34:43', '2026-04-25 08:34:43'),
(56, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 56, '2025-05-20', 13.05, 'Invoice Ref: PUR-854148', '2026-04-25 08:35:15', '2026-04-25 08:35:15'),
(57, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 57, '2025-05-21', 15.65, 'Invoice Ref: PUR-521701', '2026-04-25 08:36:41', '2026-04-25 08:36:41'),
(58, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 58, '2025-05-21', 40.87, 'Invoice Ref: PUR-232681', '2026-04-25 08:37:26', '2026-04-25 08:37:26'),
(59, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 59, '2025-05-24', 47.83, 'Invoice Ref: PUR-529004', '2026-04-25 08:38:05', '2026-04-25 08:38:05'),
(60, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 60, '2025-05-24', 434.78, 'Invoice Ref: PUR-566304', '2026-04-25 08:38:38', '2026-04-25 08:38:38'),
(61, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 61, '2025-02-25', 77.39, 'Invoice Ref: PUR-142718', '2026-04-25 08:39:38', '2026-04-25 08:39:38'),
(62, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 62, '2025-05-25', 45.15, 'Invoice Ref: PUR-175937', '2026-04-25 08:40:15', '2026-04-25 08:40:15'),
(63, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 63, '2025-05-25', 165.00, 'Invoice Ref: PUR-186915', '2026-04-25 08:41:21', '2026-04-25 08:41:21'),
(64, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 64, '2025-05-26', 933.00, 'Invoice Ref: PUR-369214', '2026-04-25 08:41:55', '2026-04-25 08:41:55'),
(65, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 65, '2025-05-26', 52.17, 'Invoice Ref: PUR-958255', '2026-04-25 08:42:35', '2026-04-25 08:42:35'),
(66, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 66, '2025-05-26', 490.00, 'Invoice Ref: PUR-818846', '2026-04-25 08:43:07', '2026-04-25 08:43:07'),
(67, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 67, '2025-05-27', 42.17, 'Invoice Ref: PUR-959757', '2026-04-25 08:43:37', '2026-04-25 08:43:37'),
(68, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 68, '2025-05-27', 930.85, 'Invoice Ref: PUR-455566', '2026-04-25 08:44:15', '2026-04-25 08:44:15'),
(69, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 69, '2025-05-27', 65.13, 'Invoice Ref: PUR-286329', '2026-04-25 08:44:51', '2026-04-25 08:44:51'),
(70, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 70, '2025-05-28', 3500.00, 'Invoice Ref: PUR-570409', '2026-04-25 08:50:26', '2026-04-25 08:50:26'),
(71, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 71, '2025-05-28', 39.57, 'Invoice Ref: PUR-736595', '2026-04-25 08:51:55', '2026-04-25 08:51:55'),
(72, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 72, '2025-05-29', 1820.99, 'Invoice Ref: PUR-805500', '2026-04-25 10:25:29', '2026-04-25 10:25:29'),
(73, 1, 1, 'in', 1.00, 'App\\Models\\Invoice', 73, '2025-05-29', 36.52, 'Invoice Ref: PUR-427935', '2026-04-25 10:26:24', '2026-04-25 10:26:24');

-- --------------------------------------------------------

--
-- بنية الجدول `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_no` varchar(255) NOT NULL,
  `route_id` bigint(20) UNSIGNED DEFAULT NULL,
  `waybill_no` varchar(255) DEFAULT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `driver_id` bigint(20) UNSIGNED NOT NULL,
  `broker_id` bigint(20) UNSIGNED NOT NULL,
  `main_company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `end_customer_name` varchar(255) DEFAULT NULL,
  `cargo_type` varchar(255) DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `container_no` varchar(255) DEFAULT NULL,
  `origin` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `loading_site` varchar(255) DEFAULT NULL,
  `discharge_site` varchar(255) DEFAULT NULL,
  `doc_no` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'planned',
  `etd` datetime DEFAULT NULL,
  `eta` datetime DEFAULT NULL,
  `eta_unloading` datetime DEFAULT NULL,
  `actual_arrival` datetime DEFAULT NULL,
  `actual_loading_start` datetime DEFAULT NULL,
  `actual_loading_end` datetime DEFAULT NULL,
  `actual_unloading_start` datetime DEFAULT NULL,
  `actual_unloading_end` datetime DEFAULT NULL,
  `start_km` decimal(15,2) DEFAULT NULL,
  `end_km` decimal(15,2) DEFAULT NULL,
  `fuel_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `diesel_liters` decimal(10,2) DEFAULT NULL,
  `fuel_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `broker_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `driver_commission` decimal(15,2) NOT NULL DEFAULT 0.00,
  `loading_invoice_path` varchar(255) DEFAULT NULL,
  `delivery_invoice_path` varchar(255) DEFAULT NULL,
  `is_commission_paid` tinyint(1) NOT NULL DEFAULT 0,
  `total_trip_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `initial_diesel_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `invoice_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `stop_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `trips`
--

INSERT INTO `trips` (`id`, `trip_no`, `route_id`, `waybill_no`, `vehicle_id`, `driver_id`, `broker_id`, `main_company_id`, `end_customer_name`, `cargo_type`, `weight`, `container_no`, `origin`, `destination`, `loading_site`, `discharge_site`, `doc_no`, `status`, `etd`, `eta`, `eta_unloading`, `actual_arrival`, `actual_loading_start`, `actual_loading_end`, `actual_unloading_start`, `actual_unloading_end`, `start_km`, `end_km`, `fuel_amount`, `diesel_liters`, `fuel_cost`, `broker_price`, `driver_commission`, `loading_invoice_path`, `delivery_invoice_path`, `is_commission_paid`, `total_trip_budget`, `initial_diesel_amount`, `invoice_id`, `notes`, `stop_count`, `created_at`, `updated_at`) VALUES
(1, 'TRIP-999', NULL, 'WB-888', 1, 17, 1, NULL, 'مصنع الخليج للخرسانة', NULL, NULL, NULL, 'الدمام - ميناء الملك عبد العزيز', 'الدمام', 'رصيف رقم 5', 'موقع الإنشاء الرئيسي', NULL, 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 0.00, 0.00, 250.00, NULL, NULL, 0, 1500.00, 0.00, NULL, 'يرجى تحميل شحنة الحديد وتأكيد التحميل برفع الفاتورة.', 0, '2026-06-12 13:27:42', '2026-06-15 08:56:06');

-- --------------------------------------------------------

--
-- بنية الجدول `trip_diesels`
--

CREATE TABLE `trip_diesels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `trip_diesels`
--

INSERT INTO `trip_diesels` (`id`, `trip_id`, `amount`, `location`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1300.00, NULL, NULL, '2026-06-15 08:55:03', '2026-06-15 08:55:03'),
(2, 1, 100.00, NULL, NULL, '2026-06-15 08:55:12', '2026-06-15 08:55:12');

-- --------------------------------------------------------

--
-- بنية الجدول `trip_events`
--

CREATE TABLE `trip_events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `event_time` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `trip_routes`
--

CREATE TABLE `trip_routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `origin` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `distance_km` int(11) DEFAULT NULL,
  `standard_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `standard_diesel_budget` decimal(12,2) DEFAULT NULL,
  `standard_driver_commission` decimal(12,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `trip_routes`
--

INSERT INTO `trip_routes` (`id`, `name`, `origin`, `destination`, `distance_km`, `standard_budget`, `standard_diesel_budget`, `standard_driver_commission`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'من الدمام الي جده', 'الدمام', 'جده', NULL, 1100.00, 900.00, 200.00, 1, '2026-06-16 06:09:42', '2026-06-16 06:09:42');

-- --------------------------------------------------------

--
-- بنية الجدول `trip_stops`
--

CREATE TABLE `trip_stops` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `reason` varchar(255) NOT NULL COMMENT 'rest, saher, breakdown, fuel, other',
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `trip_sub_clients`
--

CREATE TABLE `trip_sub_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `contact_id` bigint(20) UNSIGNED NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `units`
--

INSERT INTO `units` (`id`, `name`, `short_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'قطعة', 'حبة', 1, '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(2, 'كرتون', 'كرتون', 1, '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(3, 'كيلوجرام', 'كجم', 1, '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(4, 'متر', 'م', 1, '2026-04-10 23:50:20', '2026-04-10 23:50:20'),
(5, 'ساعة', 'ساعة', 1, '2026-04-10 23:50:20', '2026-04-10 23:50:20');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `employee_id`) VALUES
(1, 'ahmed', 'ahmedatefnaji10@gmail.com', 'admin', NULL, '$2y$12$xKTF1qRJgCwq1OYATSa44O.xvzHVzSjYsGcWlNvYszIBrxijEsFDW', 'x3uFCf7PhboRoIk1O4DlUXF6tbF9flj08Yxwm8GOVUiPYLBRwFVbpddPQQCo', '2026-04-09 15:14:02', '2026-04-09 15:14:02', NULL),
(2, 'Test User', 'test@example.com', 'admin', NULL, '$2y$12$bD5Ck3yFJ7bttcl/SCXTzOWg9/pCay0QV/gFM2AnpmPc7g3Iwmgju', NULL, '2026-04-10 23:56:57', '2026-04-10 23:56:57', NULL),
(3, 'Audit Admin', 'audit@test.com', 'admin', NULL, '$2y$12$GkC0r7BFOwTWNFyYoid06.hBDHRCxb.MxEfA2aVNY5gjhth9uJuQG', NULL, '2026-04-19 13:50:33', '2026-04-19 13:50:33', NULL),
(4, 'Admin', 'admin@admin.com', 'admin', NULL, '$2y$12$ifJrKXLnuUqndkiRdvIP4e3IidggJ1Scna.CrtyEZOiR6zLOqtkii', '4Z39JhYEjTYfyUG7g0yYExuwtdTxQFdnurPuC6OfbGbMDDrnCSIU2JSuSCxT', '2026-06-12 07:36:42', '2026-06-12 07:36:42', NULL),
(5, 'أحمد السائق', 'driver@driver.com', 'employee', NULL, '$2y$12$yELo34o0.mgs4LvnQ57EYe7.86A7oj6fOa8pM/QZmkLwIWNSFwZMq', NULL, '2026-06-12 13:27:42', '2026-06-12 13:27:42', 17);

-- --------------------------------------------------------

--
-- بنية الجدول `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plate_no` varchar(255) NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'trailer',
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'available',
  `odometer` decimal(15,2) NOT NULL DEFAULT 0.00,
  `oil_change_interval_km` int(11) NOT NULL DEFAULT 10000,
  `tire_change_interval_km` int(11) NOT NULL DEFAULT 50000,
  `insurance_expiry` date DEFAULT NULL,
  `registration_expiry` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `registration_copy` varchar(255) DEFAULT NULL,
  `insurance_copy` varchar(255) DEFAULT NULL,
  `last_oil_change_km` int(11) DEFAULT NULL,
  `next_oil_change_km` int(11) DEFAULT NULL,
  `last_tire_change_km` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_no`, `model`, `type`, `driver_id`, `status`, `odometer`, `oil_change_interval_km`, `tire_change_interval_km`, `insurance_expiry`, `registration_expiry`, `is_active`, `created_at`, `updated_at`, `registration_copy`, `insurance_copy`, `last_oil_change_km`, `next_oil_change_km`, `last_tire_change_km`) VALUES
(1, 'أ ب ج 1234', 'Actros 2024', 'رأس تريلا', 17, 'available', 0.00, 10000, 50000, NULL, NULL, 1, '2026-06-12 13:27:42', '2026-06-15 08:56:06', NULL, NULL, NULL, NULL, 0),
(2, '1234 ABC', 'Actros 2024', 'head', 18, 'available', 0.00, 10000, 50000, NULL, NULL, 1, '2026-06-14 12:22:52', '2026-06-14 12:22:52', NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- بنية الجدول `vehicle_maintenance_logs`
--

CREATE TABLE `vehicle_maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_type` varchar(255) NOT NULL,
  `maintenance_date` date NOT NULL,
  `odometer_reading` decimal(15,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `vehicle_tires`
--

CREATE TABLE `vehicle_tires` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `position` varchar(255) NOT NULL,
  `unit_type` enum('head','trailer') NOT NULL,
  `serial_no` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `warranty_months` int(11) DEFAULT NULL,
  `expected_life_km` int(11) DEFAULT NULL,
  `installation_km` int(11) DEFAULT NULL,
  `status` enum('active','replaced','scrap') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_no` varchar(255) NOT NULL,
  `type` enum('expense','advance','petty_cash_issue','petty_cash_receipt','receipt','payment') NOT NULL,
  `contact_id` bigint(20) UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `debit_account_id` bigint(20) UNSIGNED NOT NULL,
  `credit_account_id` bigint(20) UNSIGNED NOT NULL,
  `journal_entry_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `debit_description` varchar(255) DEFAULT NULL,
  `credit_description` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher_no`, `type`, `contact_id`, `date`, `amount`, `debit_account_id`, `credit_account_id`, `journal_entry_id`, `description`, `attachment_path`, `debit_description`, `credit_description`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RV-3980', 'receipt', NULL, '2025-01-01', 150.00, 3, 8, NULL, 'Payment from Ahmed', NULL, NULL, NULL, 4, '2026-06-12 09:58:51', '2026-06-12 09:58:51');

-- --------------------------------------------------------

--
-- بنية الجدول `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `location`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'المستودع الرئيسي', 'MAIN', 'المركز الرئيسي', 1, '2026-04-10 23:50:20', '2026-04-10 23:50:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounts_code_unique` (`code`),
  ADD KEY `accounts_parent_id_foreign` (`parent_id`),
  ADD KEY `accounts_account_type_id_foreign` (`account_type_id`);

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_types_code_unique` (`code`);

--
-- Indexes for table `advances`
--
ALTER TABLE `advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advances_employee_id_foreign` (`employee_id`),
  ADD KEY `advances_settled_by_foreign` (`settled_by`);

--
-- Indexes for table `advance_expenses`
--
ALTER TABLE `advance_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advance_expenses_advance_id_foreign` (`advance_id`),
  ADD KEY `advance_expenses_expense_account_id_foreign` (`expense_account_id`),
  ADD KEY `advance_expenses_tax_account_id_foreign` (`tax_account_id`);

--
-- Indexes for table `advance_settlements`
--
ALTER TABLE `advance_settlements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `advance_settlements_settlement_no_unique` (`settlement_no`),
  ADD KEY `advance_settlements_advance_id_foreign` (`advance_id`),
  ADD KEY `advance_settlements_journal_entry_id_foreign` (`journal_entry_id`),
  ADD KEY `advance_settlements_created_by_foreign` (`created_by`),
  ADD KEY `advance_settlements_rolled_over_to_advance_id_foreign` (`rolled_over_to_advance_id`);

--
-- Indexes for table `advance_settlement_lines`
--
ALTER TABLE `advance_settlement_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advance_settlement_lines_settlement_id_foreign` (`settlement_id`),
  ADD KEY `advance_settlement_lines_expense_account_id_foreign` (`expense_account_id`);

--
-- Indexes for table `advance_transactions`
--
ALTER TABLE `advance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advance_transactions_advance_id_foreign` (`advance_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_account_id_foreign` (`account_id`),
  ADD KEY `contacts_receivable_account_id_foreign` (`receivable_account_id`),
  ADD KEY `contacts_payable_account_id_foreign` (`payable_account_id`),
  ADD KEY `contacts_main_company_id_foreign` (`main_company_id`);

--
-- Indexes for table `cost_centers`
--
ALTER TABLE `cost_centers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cost_centers_code_unique` (`code`),
  ADD KEY `cost_centers_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_locations_trip_id_foreign` (`trip_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_employee_no_unique` (`employee_no`),
  ADD KEY `employees_account_id_foreign` (`account_id`);

--
-- Indexes for table `employee_advances`
--
ALTER TABLE `employee_advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_advances_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_advances_payment_account_id_foreign` (`payment_account_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `government_expenses`
--
ALTER TABLE `government_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `government_expenses_employee_id_foreign` (`employee_id`),
  ADD KEY `government_expenses_payment_account_id_foreign` (`payment_account_id`),
  ADD KEY `government_expenses_expense_account_id_foreign` (`expense_account_id`),
  ADD KEY `government_expenses_journal_entry_id_foreign` (`journal_entry_id`);

--
-- Indexes for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_stocks_item_id_warehouse_id_unique` (`item_id`,`warehouse_id`),
  ADD KEY `inventory_stocks_warehouse_id_foreign` (`warehouse_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_no_unique` (`invoice_no`),
  ADD UNIQUE KEY `invoices_xml_uuid_unique` (`xml_uuid`),
  ADD KEY `invoices_contact_id_foreign` (`contact_id`),
  ADD KEY `invoices_base_account_id_foreign` (`base_account_id`),
  ADD KEY `invoices_tax_account_id_foreign` (`tax_account_id`),
  ADD KEY `invoices_journal_entry_id_foreign` (`journal_entry_id`),
  ADD KEY `invoices_created_by_foreign` (`created_by`),
  ADD KEY `invoices_parent_document_id_foreign` (`parent_document_id`),
  ADD KEY `invoices_cost_center_id_foreign` (`cost_center_id`),
  ADD KEY `invoices_payment_account_id_foreign` (`payment_account_id`);

--
-- Indexes for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_lines_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_lines_item_id_foreign` (`item_id`),
  ADD KEY `invoice_lines_cost_center_id_foreign` (`cost_center_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_sku_unique` (`sku`),
  ADD UNIQUE KEY `items_barcode_unique` (`barcode`),
  ADD KEY `items_unit_id_foreign` (`unit_id`),
  ADD KEY `items_category_id_foreign` (`category_id`);

--
-- Indexes for table `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `journal_entries_entry_no_unique` (`entry_no`),
  ADD KEY `journal_entries_fiscal_year_id_foreign` (`fiscal_year_id`),
  ADD KEY `journal_entries_created_by_foreign` (`created_by`),
  ADD KEY `journal_entries_posted_by_foreign` (`posted_by`),
  ADD KEY `journal_entries_entry_date_index` (`entry_date`),
  ADD KEY `journal_entries_status_index` (`status`);

--
-- Indexes for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `journal_entry_lines_journal_entry_id_foreign` (`journal_entry_id`),
  ADD KEY `journal_entry_lines_account_id_foreign` (`account_id`),
  ADD KEY `journal_entry_lines_cost_center_id_foreign` (`cost_center_id`),
  ADD KEY `journal_entry_lines_contact_id_foreign` (`contact_id`);

--
-- Indexes for table `maintenance_orders`
--
ALTER TABLE `maintenance_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `maintenance_orders_order_no_unique` (`order_no`),
  ADD KEY `maintenance_orders_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `maintenance_orders_driver_id_foreign` (`driver_id`);

--
-- Indexes for table `maintenance_order_items`
--
ALTER TABLE `maintenance_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_order_items_maintenance_order_id_foreign` (`maintenance_order_id`),
  ADD KEY `maintenance_order_items_item_id_foreign` (`item_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `maintenance_requests_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `maintenance_requests_driver_id_foreign` (`driver_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `opening_balances`
--
ALTER TABLE `opening_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `opening_balances_fiscal_year_id_account_id_unique` (`fiscal_year_id`,`account_id`),
  ADD KEY `opening_balances_account_id_foreign` (`account_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`),
  ADD KEY `payrolls_payment_account_id_foreign` (`payment_account_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_movements_item_id_foreign` (`item_id`),
  ADD KEY `stock_movements_warehouse_id_foreign` (`warehouse_id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trips_trip_no_unique` (`trip_no`),
  ADD KEY `trips_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `trips_driver_id_foreign` (`driver_id`),
  ADD KEY `trips_broker_id_foreign` (`broker_id`),
  ADD KEY `trips_invoice_id_foreign` (`invoice_id`),
  ADD KEY `trips_route_id_foreign` (`route_id`),
  ADD KEY `trips_main_company_id_foreign` (`main_company_id`);

--
-- Indexes for table `trip_diesels`
--
ALTER TABLE `trip_diesels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_diesels_trip_id_foreign` (`trip_id`);

--
-- Indexes for table `trip_events`
--
ALTER TABLE `trip_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_events_trip_id_foreign` (`trip_id`);

--
-- Indexes for table `trip_routes`
--
ALTER TABLE `trip_routes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_stops`
--
ALTER TABLE `trip_stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_stops_trip_id_foreign` (`trip_id`);

--
-- Indexes for table `trip_sub_clients`
--
ALTER TABLE `trip_sub_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_sub_clients_trip_id_foreign` (`trip_id`),
  ADD KEY `trip_sub_clients_contact_id_foreign` (`contact_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicles_plate_no_unique` (`plate_no`),
  ADD KEY `vehicles_driver_id_foreign` (`driver_id`);

--
-- Indexes for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_maintenance_logs_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `vehicle_maintenance_logs_voucher_id_foreign` (`voucher_id`);

--
-- Indexes for table `vehicle_tires`
--
ALTER TABLE `vehicle_tires`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicle_tires_serial_no_unique` (`serial_no`),
  ADD KEY `vehicle_tires_vehicle_id_foreign` (`vehicle_id`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_voucher_no_unique` (`voucher_no`),
  ADD KEY `vouchers_contact_id_foreign` (`contact_id`),
  ADD KEY `vouchers_debit_account_id_foreign` (`debit_account_id`),
  ADD KEY `vouchers_credit_account_id_foreign` (`credit_account_id`),
  ADD KEY `vouchers_journal_entry_id_foreign` (`journal_entry_id`),
  ADD KEY `vouchers_created_by_foreign` (`created_by`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouses_code_unique` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `advances`
--
ALTER TABLE `advances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `advance_expenses`
--
ALTER TABLE `advance_expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `advance_settlements`
--
ALTER TABLE `advance_settlements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `advance_settlement_lines`
--
ALTER TABLE `advance_settlement_lines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `advance_transactions`
--
ALTER TABLE `advance_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cost_centers`
--
ALTER TABLE `cost_centers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `employee_advances`
--
ALTER TABLE `employee_advances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `government_expenses`
--
ALTER TABLE `government_expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_lines`
--
ALTER TABLE `invoice_lines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `item_categories`
--
ALTER TABLE `item_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `maintenance_orders`
--
ALTER TABLE `maintenance_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_order_items`
--
ALTER TABLE `maintenance_order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `opening_balances`
--
ALTER TABLE `opening_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trip_diesels`
--
ALTER TABLE `trip_diesels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trip_events`
--
ALTER TABLE `trip_events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trip_routes`
--
ALTER TABLE `trip_routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trip_stops`
--
ALTER TABLE `trip_stops`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trip_sub_clients`
--
ALTER TABLE `trip_sub_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_tires`
--
ALTER TABLE `vehicle_tires`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_account_type_id_foreign` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `advances`
--
ALTER TABLE `advances`
  ADD CONSTRAINT `advances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advances_settled_by_foreign` FOREIGN KEY (`settled_by`) REFERENCES `contacts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `advance_expenses`
--
ALTER TABLE `advance_expenses`
  ADD CONSTRAINT `advance_expenses_advance_id_foreign` FOREIGN KEY (`advance_id`) REFERENCES `employee_advances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advance_expenses_expense_account_id_foreign` FOREIGN KEY (`expense_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `advance_expenses_tax_account_id_foreign` FOREIGN KEY (`tax_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `advance_settlements`
--
ALTER TABLE `advance_settlements`
  ADD CONSTRAINT `advance_settlements_advance_id_foreign` FOREIGN KEY (`advance_id`) REFERENCES `employee_advances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `advance_settlements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `advance_settlements_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `advance_settlements_rolled_over_to_advance_id_foreign` FOREIGN KEY (`rolled_over_to_advance_id`) REFERENCES `employee_advances` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `advance_settlement_lines`
--
ALTER TABLE `advance_settlement_lines`
  ADD CONSTRAINT `advance_settlement_lines_expense_account_id_foreign` FOREIGN KEY (`expense_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `advance_settlement_lines_settlement_id_foreign` FOREIGN KEY (`settlement_id`) REFERENCES `advance_settlements` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `advance_transactions`
--
ALTER TABLE `advance_transactions`
  ADD CONSTRAINT `advance_transactions_advance_id_foreign` FOREIGN KEY (`advance_id`) REFERENCES `advances` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_main_company_id_foreign` FOREIGN KEY (`main_company_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_payable_account_id_foreign` FOREIGN KEY (`payable_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contacts_receivable_account_id_foreign` FOREIGN KEY (`receivable_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `cost_centers`
--
ALTER TABLE `cost_centers`
  ADD CONSTRAINT `cost_centers_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `cost_centers` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `employee_advances`
--
ALTER TABLE `employee_advances`
  ADD CONSTRAINT `employee_advances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_advances_payment_account_id_foreign` FOREIGN KEY (`payment_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `government_expenses`
--
ALTER TABLE `government_expenses`
  ADD CONSTRAINT `government_expenses_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `government_expenses_expense_account_id_foreign` FOREIGN KEY (`expense_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `government_expenses_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `government_expenses_payment_account_id_foreign` FOREIGN KEY (`payment_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  ADD CONSTRAINT `inventory_stocks_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_base_account_id_foreign` FOREIGN KEY (`base_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `invoices_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `invoices_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_parent_document_id_foreign` FOREIGN KEY (`parent_document_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_payment_account_id_foreign` FOREIGN KEY (`payment_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `invoices_tax_account_id_foreign` FOREIGN KEY (`tax_account_id`) REFERENCES `accounts` (`id`);

--
-- قيود الجداول `invoice_lines`
--
ALTER TABLE `invoice_lines`
  ADD CONSTRAINT `invoice_lines_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_lines_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_lines_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `journal_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `journal_entries_fiscal_year_id_foreign` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entries_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD CONSTRAINT `journal_entry_lines_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entry_lines_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `journal_entry_lines_cost_center_id_foreign` FOREIGN KEY (`cost_center_id`) REFERENCES `cost_centers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `journal_entry_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `maintenance_orders`
--
ALTER TABLE `maintenance_orders`
  ADD CONSTRAINT `maintenance_orders_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_orders_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- قيود الجداول `maintenance_order_items`
--
ALTER TABLE `maintenance_order_items`
  ADD CONSTRAINT `maintenance_order_items_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `maintenance_order_items_maintenance_order_id_foreign` FOREIGN KEY (`maintenance_order_id`) REFERENCES `maintenance_orders` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `maintenance_requests_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `maintenance_requests_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `opening_balances`
--
ALTER TABLE `opening_balances`
  ADD CONSTRAINT `opening_balances_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opening_balances_fiscal_year_id_foreign` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payrolls_payment_account_id_foreign` FOREIGN KEY (`payment_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_broker_id_foreign` FOREIGN KEY (`broker_id`) REFERENCES `contacts` (`id`),
  ADD CONSTRAINT `trips_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `trips_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `trips_main_company_id_foreign` FOREIGN KEY (`main_company_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trips_route_id_foreign` FOREIGN KEY (`route_id`) REFERENCES `trip_routes` (`id`),
  ADD CONSTRAINT `trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- قيود الجداول `trip_diesels`
--
ALTER TABLE `trip_diesels`
  ADD CONSTRAINT `trip_diesels_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `trip_events`
--
ALTER TABLE `trip_events`
  ADD CONSTRAINT `trip_events_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `trip_stops`
--
ALTER TABLE `trip_stops`
  ADD CONSTRAINT `trip_stops_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `trip_sub_clients`
--
ALTER TABLE `trip_sub_clients`
  ADD CONSTRAINT `trip_sub_clients_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_sub_clients_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

--
-- قيود الجداول `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `employees` (`id`);

--
-- قيود الجداول `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  ADD CONSTRAINT `vehicle_maintenance_logs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `vehicle_maintenance_logs_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`);

--
-- قيود الجداول `vehicle_tires`
--
ALTER TABLE `vehicle_tires`
  ADD CONSTRAINT `vehicle_tires_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_contact_id_foreign` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vouchers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vouchers_credit_account_id_foreign` FOREIGN KEY (`credit_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `vouchers_debit_account_id_foreign` FOREIGN KEY (`debit_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `vouchers_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
