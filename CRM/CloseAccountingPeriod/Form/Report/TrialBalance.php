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
class CRM_CloseAccountingPeriod_Form_Report_TrialBalance extends CRM_Report_Form {

  /**
   */
  public function __construct() {
    list($months, $years) = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getDates();
    $priorFinancialMonth = date('n', strtotime(Civi::settings()->get('prior_financial_period') . ' + 1 month'));
    $priorFinancialYear = date('Y', strtotime(Civi::settings()->get('prior_financial_period')));
    $this->_columns = array(
      'civicrm_financial_account' => array(
        'dao' => 'CRM_Financial_DAO_FinancialAccount',
        'fields' => array(
          'accounting_code' => array(
            'title' => ts('Accounting Code'),
            'required' => TRUE,
          ),
          'name' => array(
            'title' => ts('Account'),
            'required' => TRUE,
          ),
        ),
        'filters' => array(
          'contact_id' => array(
            'title' => ts('Organization Name'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getOrganizationNames(),
            'type' => CRM_Utils_Type::T_INT,
          ),
        ),
      ),
      'civicrm_financial_trxn' => array(
        'dao' => 'CRM_Financial_DAO_FinancialTrxn',
        'fields' => array(
          'debit' => array(
            'title' => ts('Debit'),
            'required' => TRUE,
            'dbAlias' => 'SUM(debit)',
          ),
          'credit' => array(
            'title' => ts('Credit'),
            'required' => TRUE,
            'dbAlias' => 'SUM(credit)',
          ),
        ),
        'filters' => array(
          'trxn_date_month' => array(
            'title' => ts('Financial Period End Month'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $months,
            'type' => CRM_Utils_Type::T_INT,
            'pseudofield' => TRUE,
            'default' => $priorFinancialMonth,
          ),
          'trxn_date_year' => array(
            'title' => ts('Financial Period End Year'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $years,
            'type' => CRM_Utils_Type::T_INT,
            'pseudofield' => TRUE,
            'default' => $priorFinancialYear,
          ),
          'prior_financial_month' => array(
            'title' => ts('Prior Financial Period End Month'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $months,
            'type' => CRM_Utils_Type::T_INT,
            'pseudofield' => TRUE,
            'required' => TRUE,
            'default' => date('n', strtotime(Civi::settings()->get('prior_financial_period'))),
          ),
          'prior_financial_year' => array(
            'title' => ts('Prior Financial Period End Year'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $years,
            'type' => CRM_Utils_Type::T_INT,
            'pseudofield' => TRUE,
            'required' => TRUE,
            'default' => date('Y', strtotime(Civi::settings()->get('prior_financial_period'))),
          ),
        ),
      ),
      'civicrm_chapter_entity' => array(
        'dao' => 'CRM_EFT_DAO_EFT',
        'fields' => array(
          'chapter_code_to' => array(
            'title' => ts('Chapter Code Debit'),
            'required' => TRUE,
            'dbAlias' => 'financial_trxn_civireport.chapter_to',
          ),
          'chapter_code_from' => array(
            'title' => ts('Chapter Code Credit'),
            'required' => TRUE,
            'dbAlias' => 'financial_trxn_civireport.chapter_from',
          ),
          'fund_code_to' => array(
            'title' => ts('Fund Debit'),
            'required' => TRUE,
            'dbAlias' => 'financial_trxn_civireport.fund_to',
          ),
          'fund_code_from' => array(
            'title' => ts('Fund Credit'),
            'required' => TRUE,
            'dbAlias' => 'financial_trxn_civireport.fund_from',
          ),
        ),
      ),
    );
    parent::__construct();
  }

  public function preProcess() {
    parent::preProcess();
  }


  public function from() {
    $endDate = NULL;
    $contactID = $this->_params['contact_id_value'];
    if (!empty($this->_params['trxn_date_month_value']) && !empty($this->_params['trxn_date_year_value'])) {
      $endDate = date('Y-m-t', mktime(0, 0, 0, $this->_params['trxn_date_month_value'], 1, $this->_params['trxn_date_year_value']));
    }
    $this->_from = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getTrialBalanceQuery($this->_aliases, TRUE, $contactID, $endDate);
  }

  public function orderBy() {
    $this->_orderBy = " ORDER BY financial_account_civireport.accounting_code, financial_trxn_civireport.chapter_to, financial_trxn_civireport.chapter_from, financial_trxn_civireport.fund_to, financial_trxn_civireport.fund_from ";
  }

  /**
   * Post process function.
   */
  public function postProcess() {
    parent::postProcess();
  }

  public function groupBy() {
    $this->_groupBy = " GROUP BY financial_account_civireport.accounting_code, financial_trxn_civireport.chapter_to, financial_trxn_civireport.chapter_from, financial_trxn_civireport.fund_to, financial_trxn_civireport.fund_from ";
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {
    if (empty($rows)) {
      return NULL;
    }
    $creditAmount = $debitAmount = 0;
    $chapterCodes = CRM_EFT_BAO_EFT::getCodes('chapter_codes');
    $fundCodes = CRM_EFT_BAO_EFT::getCodes('fund_codes');
    $financialAccountType = CRM_Core_OptionGroup::values('financial_account_type');
    foreach ($rows as &$row) {
      $creditAmount += $row['civicrm_financial_trxn_credit'];
      $debitAmount += $row['civicrm_financial_trxn_debit'];
      $row['civicrm_financial_trxn_credit'] = CRM_Utils_Money::format($row['civicrm_financial_trxn_credit']);
      $row['civicrm_financial_trxn_debit'] = CRM_Utils_Money::format($row['civicrm_financial_trxn_debit']);
      if (CRM_Utils_Array::value('civicrm_chapter_entity_chapter_code_from', $row)) {
        $row['civicrm_chapter_entity_chapter_code_from'] = $chapterCodes[$row['civicrm_chapter_entity_chapter_code_from']];
      }
      if (CRM_Utils_Array::value('civicrm_chapter_entity_chapter_code_to', $row)) {
        $row['civicrm_chapter_entity_chapter_code_to'] = $chapterCodes[$row['civicrm_chapter_entity_chapter_code_to']];
      }
      if (CRM_Utils_Array::value('civicrm_chapter_entity_fund_code_from', $row)) {
        $row['civicrm_chapter_entity_fund_code_from'] = $fundCodes[$row['civicrm_chapter_entity_fund_code_from']];
      }
      if (CRM_Utils_Array::value('civicrm_chapter_entity_fund_code_to', $row)) {
        $row['civicrm_chapter_entity_fund_code_to'] = $fundCodes[$row['civicrm_chapter_entity_fund_code_to']];
      }
      if (CRM_Utils_Array::value('civicrm_financial_account_financial_account_type_id', $row)) {
        $row['civicrm_financial_account_financial_account_type_id'] = $financialAccountType[$row['civicrm_financial_account_financial_account_type_id']];
      }
    }
    $rows[] = array(
      'civicrm_financial_account_accounting_code' => ts('<b>Total Amount</b>'),
      'civicrm_financial_trxn_debit' => '<b>' . CRM_Utils_Money::format($debitAmount) . '</b>',
      'civicrm_financial_trxn_credit' => '<b>' . CRM_Utils_Money::format($creditAmount) . '</b>',
    );
  }

  /**
   * Filter statistics.
   *
   * @param array $statistics
   */
  public function filterStat(&$statistics) {
    parent::filterStat($statistics);
    $statisticsPriorPeriodDate = CRM_Core_Session::singleton()->get('statisticsPriorPeriodDate');
    if ($statisticsPriorPeriodDate) {
      $statistics['filters'][] = array(
        'title' => ts('Trial Balance report for the period'),
        'value' => $statisticsPriorPeriodDate,
      );
    }
  }

}
