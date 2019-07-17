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
    list($pmonths, $pyears) = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getDates(1);
    $priorDate = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod(1);
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
          'trxn_date' => array(
            'title' => ts('Financial Period End Month'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'options' => $months,
            'type' => CRM_Utils_Type::T_DATE,
            'pseudofield' => TRUE,
            'default' => date('n', strtotime($priorDate . "+ 1 month")),
          ),
          'prior_financial_month' => array(
            'title' => ts('Prior Financial Period End Month'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $months,
            'type' => CRM_Utils_Type::T_INT,
            'pseudofield' => TRUE,
            'required' => TRUE,
            'default' => date('n', strtotime($priorDate)),
          ),
          'prior_financial_year' => array(
            'title' => ts('Prior Financial Period End Year'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => $years,
            'type' => CRM_Utils_Type::T_INT,
            'pseudofield' => TRUE,
            'required' => TRUE,
            'default' => date('Y', strtotime($priorDate)),
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
    $fieldName = 'trxn_date';
    $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
    $from = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
    $to = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);
    list($from, $to) = $this->getFromTo($relative, $from, $to, NULL, NULL);
    $clauses = ['(1)'];
    if ($from) {
      $clauses[] = "( fieldName >= $from )";
    }
    if ($to) {
      $clauses[] = "( fieldName <= {$to} )";
    }
    if (!empty($clauses)) {
      $clauses =  implode(' AND ', $clauses);
    }
    $params['labelColumn'] = 'name';
    $financialAccountType = CRM_Core_PseudoConstant::get('CRM_Financial_DAO_FinancialAccount', 'financial_account_type_id', $params);
    $financialAccountTypes = array(
      array_search('Liability', $financialAccountType),
      array_search('Asset', $financialAccountType)
    );
    $priorDate = NULL;
    if ($contactID) {
      $priorDate = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($contactID);
    }
    if (empty($priorDate)) {
      $financialBalanceField = 'opening_balance';
    }
    else {
      $financialBalanceField = 'current_period_opening_balance';
    }
    $sql = "
    FROM (
      SELECT cft1.id, 0 as fid, 0 AS credit, cft1.total_amount AS debit,
        cft1.to_financial_account_id AS financial_account_id,
        '' AS chapter_from, ce.chapter_code AS chapter_to, '' AS fund_from, ce.fund_code AS fund_to
        FROM civicrm_financial_trxn cft1
        LEFT JOIN civicrm_chapter_entity ce ON ce.entity_id = cft1.id AND ce.entity_table = 'civicrm_financial_trxn'
        WHERE $clauses
    ";
    $sql = str_replace('fieldName', 'cft1.trxn_date', $sql);
    $sql .= "
    UNION
    SELECT cft2.id, 0 as fid, cft2.total_amount AS credit, 0 AS debit, cft2.from_financial_account_id,
    ce1.chapter_code AS chapter_from, '' AS chapter_to, ce1.fund_code AS fund_from, '' AS fund_to
      FROM civicrm_financial_trxn cft2
      LEFT JOIN civicrm_entity_financial_trxn ceft1 ON ceft1.financial_trxn_id = cft2.id AND entity_table = 'civicrm_contribution'
      LEFT JOIN civicrm_line_item li ON li.contribution_id = ceft1.entity_id
      LEFT JOIN civicrm_chapter_entity ce1 ON ce1.entity_id = li.id AND ce1.entity_table = 'civicrm_line_item'
      WHERE $clauses
    ";
    $sql = str_replace('fieldName', 'cft2.trxn_date', $sql);
    $sql .= "
    UNION
    SELECT cft3.id, cfi3.id, 0 AS credit, cfi3.amount AS debit, cfi3.financial_account_id,
    '' AS chapter_from, ce2.chapter_code AS chapter_to, '' AS fund_from, ce2.fund_code AS fund_to
      FROM civicrm_financial_item cfi3
        INNER JOIN civicrm_entity_financial_trxn ceft3 ON cfi3.id = ceft3.entity_id
          AND ceft3.entity_table = 'civicrm_financial_item'
        INNER JOIN civicrm_financial_trxn cft3 ON ceft3.financial_trxn_id = cft3.id
          AND cft3.to_financial_account_id IS NULL
          LEFT JOIN civicrm_chapter_entity ce2 ON ce2.entity_id = cft3.id AND ce2.entity_table = 'civicrm_financial_trxn'
      WHERE $clauses
    ";
    $sql = str_replace('fieldName', 'cft3.trxn_date', $sql);
    $sql .= "
    UNION
    SELECT cft4.id, cfi4.id,  cfi4.amount AS credit, 0 AS debit, cfi4.financial_account_id,
    ce3.chapter_code AS chapter_from, '' AS chapter_to, ce3.fund_code AS fund_from, '' AS fund_to
      FROM civicrm_financial_item cfi4
      INNER JOIN civicrm_entity_financial_trxn ceft4 ON cfi4.id=ceft4.entity_id
        AND ceft4.entity_table='civicrm_financial_item'
      INNER JOIN civicrm_financial_trxn cft4 ON ceft4.financial_trxn_id=cft4.id
        AND cft4.from_financial_account_id IS NULL
        LEFT JOIN civicrm_chapter_entity ce3 ON ce3.entity_id = cfi4.id AND ce3.entity_table = 'civicrm_financial_item'
      WHERE $clauses
    UNION
    SELECT 0 as tid, 0 as fid, IF (financial_account_type_id = " . array_search('Liability', $financialAccountType) . ", {$financialBalanceField}, 0) AS credit, IF (financial_account_type_id = " . array_search('Asset', $financialAccountType) . ", {$financialBalanceField}, 0) AS debit, cfa5.id,
              IF (financial_account_type_id = " . array_search('Liability', $financialAccountType) . ", cec.chapter_code, '') AS chapter_from,
              IF (financial_account_type_id = " . array_search('Asset', $financialAccountType) . ", ced.chapter_code, '') AS chapter_to,
              IF (financial_account_type_id = " . array_search('Liability', $financialAccountType) . ", cec.fund_code, '') AS fund_from,
              IF (financial_account_type_id = " . array_search('Asset', $financialAccountType) . ", ced.fund_code, '') AS fund_to
      FROM civicrm_financial_account cfa5
      INNER JOIN civicrm_financial_accounts_balance cfab ON cfab.financial_account_id = cfa5.id
      LEFT JOIN civicrm_chapter_entity cec ON cec.entity_id = cfa5.id AND cec.entity_table = 'civicrm_financial_item'
      INNER JOIN civicrm_entity_financial_trxn ceft5 ON cfa5.id = ceft5.entity_id AND ceft5.entity_table = 'civicrm_financial_item'
      LEFT JOIN civicrm_chapter_entity ced ON ced.entity_id = ceft5.financial_trxn_id AND ced.entity_table = 'civicrm_financial_trxn'
      WHERE cfa5.financial_account_type_id IN (" . implode(', ', $financialAccountTypes) . ") AND {$financialBalanceField} <> 0
      ) AS financial_trxn_civireport
        INNER JOIN civicrm_financial_account financial_account_civireport ON financial_trxn_civireport.financial_account_id = financial_account_civireport.id

    ";
    $sql = str_replace('fieldName', 'cft4.trxn_date', $sql);
    $this->_from = $sql;
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
