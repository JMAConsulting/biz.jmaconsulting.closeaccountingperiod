/**
 * Close Accounting Period Extension
 * 
 * Copyright (C) 2016 JMA Consulting
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
 * Contact: info@jmaconsulting.biz
 *          JMA Consulting
 *          215 Spadina Ave, Ste 400
 *          Toronto, ON  
 *          Canada   M5T 2C7
 */

CREATE TABLE IF NOT EXISTS `civicrm_financial_accounts_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `financial_account_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_financial_account',
  `opening_balance` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'Contains the opening balance for this financial account',
  `current_period_opening_balance` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'Contains the opening balance for the current period for this financial account',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`,`financial_account_id`),
  KEY `FK_civicrm_financial_accounts_balance_financial_account_id` (`financial_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=68 ;

--
-- Constraints for table `civicrm_financial_accounts_balance`
--

ALTER TABLE `civicrm_financial_accounts_balance`
  ADD CONSTRAINT `FK_civicrm_financial_accounts_balance_financial_account_id` FOREIGN KEY (`financial_account_id`) REFERENCES `civicrm_financial_account` (`id`)  ON DELETE CASCADE;