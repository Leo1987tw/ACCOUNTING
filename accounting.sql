-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2026-05-24 21:19:37
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `accounting`
--

-- --------------------------------------------------------

--
-- 資料表結構 `accounting_periods`
--

CREATE TABLE `accounting_periods` (
  `id` int(11) NOT NULL,
  `ledger_id` int(11) NOT NULL,
  `period_name` varchar(30) NOT NULL,
  `type` enum('standard','adjustment') NOT NULL DEFAULT 'standard',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `ledger_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('asset','liability','equity','revenue','expense') NOT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `is_reconciled` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `account_balances`
--

CREATE TABLE `account_balances` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `accounting_period_id` int(11) NOT NULL,
  `beginning_balance` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `debit_total` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `credit_total` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `ending_balance` decimal(18,6) NOT NULL DEFAULT 0.000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `transaction_line_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `ledger_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(30) NOT NULL COMMENT '''insert'',''import'',''update'',''delete'',''post'',''void''',
  `table_name` varchar(50) NOT NULL,
  `row_id` int(11) NOT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('personal','business','shared') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `current_rate` decimal(18,6) NOT NULL DEFAULT 1.000000,
  `is_base` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `ledgers`
--

CREATE TABLE `ledgers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('personal','business','shared') NOT NULL,
  `currency_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `ledger_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('self','family','employee','vendor','customer','both_vendor_and_customer','subcontractor','bank','government','other') NOT NULL,
  `tax_id` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `ledger_id` int(11) NOT NULL,
  `accounting_period_id` int(11) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `voucher_number` varchar(50) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `total_amount` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('draft','posted','voided') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `transaction_lines`
--

CREATE TABLE `transaction_lines` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `currency_id` int(11) NOT NULL,
  `exchange_rate` decimal(18,6) NOT NULL DEFAULT 1.000000,
  `debit_amount` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `credit_amount` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `foreign_debit_amount` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `foreign_credit_amount` decimal(18,6) NOT NULL DEFAULT 0.000000,
  `memo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `real_name` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `user_ledger_permissions`
--

CREATE TABLE `user_ledger_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ledger_id` int(11) NOT NULL,
  `can_edit` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `accounting_periods`
--
ALTER TABLE `accounting_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ledger_id_period_name` (`ledger_id`,`period_name`),
  ADD KEY `fk_accounting_periods_ledger` (`ledger_id`) USING BTREE,
  ADD KEY `fk_accounting_periods_closed_by_users_id` (`closed_by`);

--
-- 資料表索引 `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ledger_id_code` (`ledger_id`,`code`),
  ADD KEY `fk_accounts_ledger_id_ledgers_id` (`ledger_id`) USING BTREE,
  ADD KEY `fk_accounts_partner_id_partners_id` (`partner_id`);

--
-- 資料表索引 `account_balances`
--
ALTER TABLE `account_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_account_id_accounting_period_id` (`account_id`,`accounting_period_id`),
  ADD KEY `fk_account_balances_accounting_period_id_accounting_periods_id` (`accounting_period_id`),
  ADD KEY `fk_account_balances_account_id_accounts_id` (`account_id`) USING BTREE;

--
-- 資料表索引 `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attachments_transaction_id_transactions_id` (`transaction_id`) USING BTREE,
  ADD KEY `fk_attachments_transaction_line_id_transaction_lines_id` (`transaction_line_id`) USING BTREE;

--
-- 資料表索引 `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fk_audit_logs_user_id_users_id` (`user_id`) USING BTREE,
  ADD KEY `idx_audit_logs_table_name` (`table_name`) USING BTREE,
  ADD KEY `idx_audit_logs_created_at` (`created_at`) USING BTREE;

--
-- 資料表索引 `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_categories_account_id_parent_id_name_type` (`account_id`,`parent_id`,`name`(20),`type`),
  ADD KEY `fk_categories_account_id_accounts_id` (`account_id`) USING BTREE,
  ADD KEY `fk_categories_parent_id_categores_id` (`parent_id`) USING BTREE;

--
-- 資料表索引 `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_currencies_code` (`code`) USING BTREE;

--
-- 資料表索引 `ledgers`
--
ALTER TABLE `ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ledgers_currency_id_currencies_id` (`currency_id`) USING BTREE;

--
-- 資料表索引 `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_partners_tax_id` (`tax_id`) USING BTREE,
  ADD KEY `fk_partners_ledger_id_ledgers_id` (`ledger_id`) USING BTREE;

--
-- 資料表索引 `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_transactions_ledger_id_voucher_number` (`ledger_id`,`voucher_number`),
  ADD KEY `fk_transactions_partner_id_partners_id` (`partner_id`) USING BTREE,
  ADD KEY `fk_transactions_accounting_period_id_accounting_periods_id` (`accounting_period_id`) USING BTREE,
  ADD KEY `fk_transactions_created_by_users_id` (`created_by`) USING BTREE,
  ADD KEY `fk_transactions_ledger_id_ledgers_id` (`ledger_id`) USING BTREE;

--
-- 資料表索引 `transaction_lines`
--
ALTER TABLE `transaction_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaction_lines_account_id_accounts_id` (`account_id`) USING BTREE,
  ADD KEY `fk_transaction_lines_category_id_categories_id` (`category_id`) USING BTREE,
  ADD KEY `fk_transaction_lines_currency_id_currencies_id` (`currency_id`) USING BTREE,
  ADD KEY `fk_transaction_lines_partner_id_partners_id` (`partner_id`) USING BTREE,
  ADD KEY `fk_transaction_lines_transaction_id_transactions_id` (`transaction_id`) USING BTREE;

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_user_name` (`user_name`) USING BTREE,
  ADD UNIQUE KEY `uk_users_email` (`email`) USING BTREE;

--
-- 資料表索引 `user_ledger_permissions`
--
ALTER TABLE `user_ledger_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_id_ledger_id` (`user_id`,`ledger_id`),
  ADD KEY `fk_user_ledger_permissions_ledger_id_ledgers_id` (`ledger_id`) USING BTREE,
  ADD KEY `fk_user_ledger_permissions_user_id_users_id` (`user_id`) USING BTREE;

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `accounting_periods`
--
ALTER TABLE `accounting_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `account_balances`
--
ALTER TABLE `account_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `ledgers`
--
ALTER TABLE `ledgers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `transaction_lines`
--
ALTER TABLE `transaction_lines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_ledger_permissions`
--
ALTER TABLE `user_ledger_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `accounting_periods`
--
ALTER TABLE `accounting_periods`
  ADD CONSTRAINT `fk_accounting_periods_closed_by_users_id` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accounting_periods_ledger_id_ledger_id` FOREIGN KEY (`ledger_id`) REFERENCES `ledgers` (`id`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_accounts_ledger_id_ledger_id` FOREIGN KEY (`ledger_id`) REFERENCES `ledgers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_accounts_partner_id_partners_id` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL;

--
-- 資料表的限制式 `account_balances`
--
ALTER TABLE `account_balances`
  ADD CONSTRAINT `fk_account_balances_account_id_accounts_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `fk_account_balances_accounting_period_id_accounting_periods_id` FOREIGN KEY (`accounting_period_id`) REFERENCES `accounting_periods` (`id`);

--
-- 資料表的限制式 `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `fk_attachments_transaction_id_transaction_id` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_attachments_transaction_line_id_transaction_line_id` FOREIGN KEY (`transaction_line_id`) REFERENCES `transaction_lines` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_user_id_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 資料表的限制式 `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_account_id_accounts_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_categories_parent_id_categories_id` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `ledgers`
--
ALTER TABLE `ledgers`
  ADD CONSTRAINT `fk_ledger_currency_id_currencies_id` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `partners`
--
ALTER TABLE `partners`
  ADD CONSTRAINT `fk_partners_ledger_id_ledger_id` FOREIGN KEY (`ledger_id`) REFERENCES `ledgers` (`id`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_accounting_period_id_accounting_periods_id` FOREIGN KEY (`accounting_period_id`) REFERENCES `accounting_periods` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_created_by_users_id` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_ledger_id_ledgers_id` FOREIGN KEY (`ledger_id`) REFERENCES `ledgers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_partner_id_partners_id` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- 資料表的限制式 `transaction_lines`
--
ALTER TABLE `transaction_lines`
  ADD CONSTRAINT `fk_transaction_lines_account_id_accounts_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_lines_category_id_categories_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_lines_currency_id_currencies_id` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_lines_partner_id_partners_id` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaction_lines_transaction_id_transactions_id` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `user_ledger_permissions`
--
ALTER TABLE `user_ledger_permissions`
  ADD CONSTRAINT `fk_user_ledger_permissions_ledger_id_ledgers_id` FOREIGN KEY (`ledger_id`) REFERENCES `ledgers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_ledger_permissions_user_id_users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
