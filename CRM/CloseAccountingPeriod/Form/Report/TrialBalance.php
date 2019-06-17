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
    $this->_columns = array(
      'civicrm_financial_account' => array(
        'dao' => 'CRM_Financial_DAO_FinancialAccount',
        'fields' => array(
          'name' => array(
            'title' => ts('Account'),
            'required' => TRUE,
          ),
          'accounting_code' => array(
            'title' => ts('Accounting Code'),
            'required' => TRUE,
          ),
        ),
        'filters' => array(
          'contact_id' => array(
            'title' => ts('Organization Name'),
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => array('' => '- Select Organization -') + CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getOrganizationNames(),
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
          'trxn_date' => [
            'title' => ts('Financial Period End Month'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'options' => $months,
            'type' => CRM_Utils_Type::T_DATE,
            'pseudofield' => TRUE,
            'default' => '',
          ],
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
    $clauses = [];
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
        cft1.to_financial_account_id AS financial_account_id
        FROM civicrm_financial_trxn cft1
        WHERE $clauses
    ";
    $sql = str_replace('fieldName', 'cft1.trxn_date', $sql);

    $sql .= "
    UNION
    SELECT cft2.id, 0 as fid, cft2.total_amount AS credit, 0 AS debit, cft2.from_financial_account_id
      FROM civicrm_financial_trxn cft2
      WHERE $clauses
    ";
    $sql = str_replace('fieldName', 'cft2.trxn_date', $sql);

    $sql .= "
    UNION
    SELECT cft3.id, cfi3.id, 0 AS credit, cfi3.amount AS debit, cfi3.financial_account_id
      FROM civicrm_financial_item cfi3
        INNER JOIN civicrm_entity_financial_trxn ceft3 ON cfi3.id = ceft3.entity_id
          AND ceft3.entity_table = 'civicrm_financial_item'
        INNER JOIN civicrm_financial_trxn cft3 ON ceft3.financial_trxn_id = cft3.id
          AND cft3.to_financial_account_id IS NULL
      WHERE $clauses
    ";
    $sql = str_replace('fieldName', 'cft3.trxn_date', $sql);

    $sql .= "
    UNION
    SELECT cft4.id, cfi4.id,  cfi4.amount AS credit, 0 AS debit, cfi4.financial_account_id
      FROM civicrm_financial_item cfi4
      INNER JOIN civicrm_entity_financial_trxn ceft4 ON cfi4.id=ceft4.entity_id
        AND ceft4.entity_table='civicrm_financial_item'
      INNER JOIN civicrm_financial_trxn cft4 ON ceft4.financial_trxn_id=cft4.id
        AND cft4.from_financial_account_id IS NULL
      WHERE $clauses
    UNION
    SELECT 0 as tid, 0 as fid, IF (financial_account_type_id = " . array_search('Liability', $financialAccountType) . ", {$financialBalanceField}, 0) AS credit, IF (financial_account_type_id = " . array_search('Asset', $financialAccountType) . ", {$financialBalanceField}, 0) AS debit, cfa5.id
      FROM civicrm_financial_account cfa5
      INNER JOIN civicrm_financial_accounts_balance cfab ON cfab.financial_account_id = cfa5.id
      WHERE cfa5.financial_account_type_id IN (" . implode(', ', $financialAccountTypes) . ") AND {$financialBalanceField} <> 0

  ) AS {$this->_aliases['civicrm_financial_trxn']}
  INNER JOIN civicrm_financial_account {$this->_aliases['civicrm_financial_account']} ON {$this->_aliases['civicrm_financial_trxn']}.financial_account_id = {$this->_aliases['civicrm_financial_account']}.id
    ";
    $sql = str_replace('fieldName', 'cft4.trxn_date', $sql);
    $this->_from = $sql;
  }

  public function orderBy() {
    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_financial_account']}.name ";
  }

 /**
   * Set limit.
   *
   * @param int $rowCount
   *
   * @return array
   */
  public function limit($rowCount = self::ROW_COUNT_LIMIT) {
    $this->_limit = NULL;
  }

  /**
   * Post process function.
   */
  public function postProcess() {
    parent::postProcess();
  }

  public function groupBy() {
    $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_financial_account']}.id ";
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
    foreach ($rows as &$row) {
      $creditAmount += $row['civicrm_financial_trxn_credit'];
      $debitAmount += $row['civicrm_financial_trxn_debit'];
      $row['civicrm_financial_trxn_credit'] = CRM_Utils_Money::format($row['civicrm_financial_trxn_credit']);
      $row['civicrm_financial_trxn_debit'] = CRM_Utils_Money::format($row['civicrm_financial_trxn_debit']);
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
