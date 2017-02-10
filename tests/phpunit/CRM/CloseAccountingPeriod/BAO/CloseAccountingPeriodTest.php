<?php
/*
 +--------------------------------------------------------------------+
 | Close Accounting Period Extension                                  |
 +--------------------------------------------------------------------+
 | Copyright (C) 2016-2017 JMA Consulting                             |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 * $Id$
 *
 */
class CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriodTest extends CiviUnitTestCase {
  public function setUp() {
    $this->quickCleanUpFinancialEntities();
    parent::setUp();
  }

  /**
   * Test for exportTrialBalanceAndClosePeriod().
   */
  public function testExportTrialBalanceAndClosePeriod() {
    $cid = $this->individualCreate();
    $params = array(
      'contact_id' => $cid,
      'receive_date' => '2016-01-20',
      'total_amount' => 622,
      'financial_type_id' => 4,
    );
    $contribution = CRM_Contribute_BAO_Contribution::create($params);
    $params = array(
      'total_amount' => 100,
      'id' => $contribution->id,
    );
    $contribution = CRM_Contribute_BAO_Contribution::create($params);
    $fileName = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::exportTrialBalanceAndClosePeriod();
    require_once 'CiviTest/CiviReportTestCase.php';
    $obj = new CiviReportTestCase();
    $csvArray = $obj->getArrayFromCsv($fileName);
    $expectedOutputCsvArray = $obj->getArrayFromCsv(dirname(__FILE__) . "/fixtures/TrialBalance.csv");
    $obj->assertCsvArraysEqual($expectedOutputCsvArray, $csvArray);
  }

  /**
   * Test for getTrialBalanceQuery().
   */
  public function testgetTrialBalanceQuery() {
    $cid = $this->individualCreate();
    $params = array(
      'contact_id' => $cid,
      'receive_date' => '2016-01-20',
      'total_amount' => 622,
      'financial_type_id' => 4,
    );
    $contribution = CRM_Contribute_BAO_Contribution::create($params);
    $params = array(
      'total_amount' => 100,
      'id' => $contribution->id,
    );
    $contribution = CRM_Contribute_BAO_Contribution::create($params);
    $alias = array(
      'civicrm_financial_trxn' => 'financial_trxn_civireport',
      'civicrm_financial_account' => 'financial_account_civireport',
    );
    $query = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getTrialBalanceQuery($alias);
    $dao = CRM_Core_DAO::executeQuery($query);
    $this->assertEquals(2, $dao->N);
  }
}
