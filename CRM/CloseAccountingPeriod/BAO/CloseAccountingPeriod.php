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
class CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod extends CRM_Core_DAO {

  public function __construct() {
    parent::__construct();
  }

  /**
   * Create trial balance sql query
   *
   * @param string $alias
   * @param bool $onlyFromClause
   *
   * @return string
   */
  public static function getTrialBalanceQuery($alias, $onlyFromClause = FALSE) {
    $closingDate = NULL;
    if (!$onlyFromClause && Civi::settings()->get('closing_date')) {
      $closingDate = Civi::settings()->get('closing_date');
      $closingDate = date('Y-m-t', mktime(0, 0, 0, $closingDate['M'], 1, $closingDate['Y']));
    }
    if (empty($closingDate)) {
      $date = new DateTime();
      $date->modify("last day of previous month");
      $closingDate = $date->format("Y-m-d");
    }
    $priorDate = Civi::settings()->get('prior_financial_period');
    if (empty($priorDate)) {
      $where = " <= DATE('$closingDate') ";
      $financialBalanceField = 'opening_balance';
    }
    else {
      $priorDate = date('Y-m-d', strtotime($priorDate . '+1 Day'));
      $where = " BETWEEN DATE('$priorDate') AND DATE('$closingDate') ";
      $financialBalanceField = 'current_period_opening_balance';
    }
    $params['labelColumn'] = 'name';
    $financialAccountType = CRM_Core_PseudoConstant::get('CRM_Financial_DAO_FinancialAccount', 'financial_account_type_id', $params);
    $financialAccountTypes = array(
      array_search('Liability', $financialAccountType),
      array_search('Asset', $financialAccountType)
    );
    $from = "
      FROM (
        SELECT cft1.id, 0 as fid, 0 AS credit, cft1.total_amount AS debit,
          cft1.to_financial_account_id AS financial_account_id
          FROM civicrm_financial_trxn cft1
          WHERE cft1.trxn_date {$where}
        UNION
        SELECT cft2.id, 0 as fid, cft2.total_amount AS credit, 0 AS debit, cft2.from_financial_account_id
          FROM civicrm_financial_trxn cft2
          WHERE cft2.trxn_date {$where}
        UNION
        SELECT cft3.id, cfi3.id, 0 AS credit, cfi3.amount AS debit, cfi3.financial_account_id
          FROM civicrm_financial_item cfi3
            INNER JOIN civicrm_entity_financial_trxn ceft3 ON cfi3.id = ceft3.entity_id
              AND ceft3.entity_table = 'civicrm_financial_item'
            INNER JOIN civicrm_financial_trxn cft3 ON ceft3.financial_trxn_id = cft3.id 
              AND cft3.to_financial_account_id IS NULL
          WHERE cfi3.transaction_date {$where}
        UNION
        SELECT cft4.id, cfi4.id,  cfi4.amount AS credit, 0 AS debit, cfi4.financial_account_id
          FROM civicrm_financial_item cfi4
          INNER JOIN civicrm_entity_financial_trxn ceft4 ON cfi4.id=ceft4.entity_id
            AND ceft4.entity_table='civicrm_financial_item'
          INNER JOIN civicrm_financial_trxn cft4 ON ceft4.financial_trxn_id=cft4.id
            AND cft4.from_financial_account_id IS NULL
          WHERE cfi4.transaction_date {$where}
        UNION
        SELECT 0 as tid, 0 as fid, IF (financial_account_type_id = " . array_search('Liability', $financialAccountType) . ", {$financialBalanceField}, 0) AS credit, IF (financial_account_type_id = " . array_search('Asset', $financialAccountType) . ", {$financialBalanceField}, 0) AS debit, cfa5.id
          FROM civicrm_financial_account cfa5
          INNER JOIN civicrm_financial_accounts_balance cfab ON cfab.financial_account_id = cfa5.id
          WHERE cfa5.financial_account_type_id IN (" . implode(', ', $financialAccountTypes) . ") AND {$financialBalanceField} <> 0

      ) AS {$alias['civicrm_financial_trxn']}
      INNER JOIN civicrm_financial_account {$alias['civicrm_financial_account']} ON {$alias['civicrm_financial_trxn']}.financial_account_id = {$alias['civicrm_financial_account']}.id
";
    if ($onlyFromClause) {
      return $from;
    }
    $query = "
SELECT financial_account_civireport.id as civicrm_financial_account_id,
financial_account_civireport.name as civicrm_financial_account_name,
financial_account_civireport.financial_account_type_id as civicrm_financial_account_financial_account_type_id,
financial_account_civireport.accounting_code as civicrm_financial_account_accounting_code,
SUM(debit) as civicrm_financial_trxn_debit,
SUM(credit) as civicrm_financial_trxn_credit  
  {$from}
  WHERE {$alias['civicrm_financial_account']}.contact_id = %1
  GROUP BY financial_account_civireport.id
  ORDER BY financial_account_civireport.name  
";
    return $query;
  }

  /**
   * Create trial balance export file
   * and update civicrm_financial_account.current_period_opening_balance field 
   *
   * @param int $orgId
   *
   * @return string
   */
  public static function exportTrialBalanceAndClosePeriod($orgId) {
    $alias = array(
      'civicrm_financial_trxn' => 'financial_trxn_civireport',
      'civicrm_financial_account' => 'financial_account_civireport',
    );
    $query = self::getTrialBalanceQuery($alias);
    $queryParams = array(1 => array($orgId, 'Integer'));
    $result = CRM_Core_DAO::executeQuery($query, $queryParams);
    $rows = array();
    $credit = $debit = 0;
    $params['labelColumn'] = 'name';
    $financialAccountType = CRM_Core_PseudoConstant::get('CRM_Financial_DAO_FinancialAccount', 'financial_account_type_id', $params);
    while ($result->fetch()) {
      $rows[] = array(
        $result->civicrm_financial_account_name,
        $result->civicrm_financial_account_accounting_code,
        $result->civicrm_financial_trxn_debit,
        $result->civicrm_financial_trxn_credit,
      );
      $debit += $result->civicrm_financial_trxn_debit;
      $credit += $result->civicrm_financial_trxn_credit;
      if (!in_array($financialAccountType[$result->civicrm_financial_account_financial_account_type_id], array('Liability', 'Asset'))) {
        continue;
      }
      // Update current_period_opening_balance
      $financialAccountParams = array(
        'id' => $result->civicrm_financial_account_id,
        'financial_account_type_id' => $result->civicrm_financial_account_financial_account_type_id,
      );
      if (array_search('Asset', $financialAccountType) == $result->civicrm_financial_account_financial_account_type_id) {
        $financialAccountParams['current_period_opening_balance'] = $result->civicrm_financial_trxn_debit - $result->civicrm_financial_trxn_credit;
      }
      else {
        $financialAccountParams['current_period_opening_balance'] = $result->civicrm_financial_trxn_credit - $result->civicrm_financial_trxn_debit;
      }
      self::createFinancialAccountBalance($financialAccountParams);
    }
    if (empty($rows)) {
      return NULL;
    }
    $rows[] = array(
      NULL,
      NULL,
      CRM_Utils_Money::format($debit),
      CRM_Utils_Money::format($credit),
    );
    $config = CRM_Core_Config::singleton();
    $fileName = $config->customFileUploadDir . CRM_Utils_File::makeFileName('TrialBalanceReport.csv');
    $header = array(
      'Account',
      'Accounting Code',
      'Debit',
      'Credit',
    );
    CRM_Contact_Import_Parser::exportCSV($fileName, $header, $rows);

    return $fileName;
  }

  /**
   * Create trial balance export file
   * and update civicrm_financial_account.current_period_opening_balance field 
   *
   * @param int $orgId
   *
   * @return string
   */
  public static function createClosePeriodActivity($params) {
    // Set closing date
    $priorFinPeriod = self::buildClosingDate($params['closing_date']);
    Civi::settings()->set('closing_date', $params['closing_date']);
    $priorFinPeriod = date('m/d/Y', $priorFinPeriod);
    // Create activity
    $activityType = CRM_Core_OptionGroup::getValue('activity_type',
      'Close Accounting Period',
      'name'
    );
    $previousPriorFinPeriod = Civi::settings()->get('prior_financial_period');
    $closingDate =  date('Y-m-d', strtotime($priorFinPeriod));
    $activityParams = array(
      'source_contact_id' => CRM_Core_Session::singleton()->get('userID'),
      'assignee_contact_id' => $params['contact_id'],
      'activity_type_id' => $activityType,
      'subject' => ts('Close Accounting Period : ') . $closingDate,
      'status_id' => CRM_Core_OptionGroup::getValue('activity_status',
        'Completed',
        'name'
      ),
      'activity_date_time' => date('YmdHis'),
      'details' => ts('Trial Balance Report ' . (empty($previousPriorFinPeriod) ? 'for All Time Prior' : "From " . date('m/d/Y', strtotime($previousPriorFinPeriod . '+1 Day'))) . " To {$priorFinPeriod}."),
    );
    $fileName = self::exportTrialBalanceAndClosePeriod($params['contact_id']);
    if ($fileName) {
      $activityParams['attachFile_1'] = array(
        'uri' => $fileName,
        'type' => 'text/csv',
        'upload_date' => date('YmdHis'),
        'location' => $fileName,
        'cleanName' => 'TrialBalanceReport_' . $closingDate . '.csv',
      );
    }
    $activity = CRM_Activity_BAO_Activity::create($activityParams);
    // Set Prior Financial Period
    Civi::settings()->set('prior_financial_period', $priorFinPeriod);
    $redirectURL = CRM_Utils_System::url('civicrm/activity',
      "action=view&reset=1&id={$activity->id}&atype={$activityType}&cid={$activityParams['source_contact_id']}"
    );
    return $redirectURL;
  }

  /**
   * get financial account balances
   *
   * @param int $financialAccountID
   *
   * @return string
   */
  public static function getDefaultBalance($financialAccountID) {
    $financialAccountBalance = new CRM_Financial_DAO_FinancialAccountBalance();
    $financialAccountBalance->financial_account_id = $financialAccountID;
    $openingBal = $currentPeriodOpeningBal = '0.00';
    if ($financialAccountBalance->find(TRUE)) {
      $openingBal = $financialAccountBalance->opening_balance;
      $currentPeriodOpeningBal = $financialAccountBalance->current_period_opening_balance;
    }
    $defaults = array(
      'opening_balance' => $openingBal,
      'current_period_opening_balance' => $currentPeriodOpeningBal,
    );
    return $defaults;
  }

  /**
   * Get Organization Name associated with Financial Account.
   *
   * @param int|string $cid
   *
   * @return array
   *
   */
  public static function getOrganizationNames($cid = NULL) {
    $where = " WHERE (1)";
    if ($cid) {
      $where = " AND cc.id IN ({$cid}) ";
    }
    $sql = "SELECT cc.id, cc.organization_name FROM civicrm_contact cc 
      INNER JOIN civicrm_financial_account cfa ON cfa.contact_id = cc.id AND cc.is_deleted = 0
      {$where}
      GROUP BY cc.id";
    $result = CRM_Core_DAO::executeQuery($sql);
    $organizationNames = array();
    while ($result->fetch()) {
      $organizationNames[$result->id] = $result->organization_name;
    }
    return $organizationNames;
  }

  /**
   * Create entry in civicrm_financial_accounts_balance to store
   * opening_balance and current_period_opening_balance for financial account.
   *
   * @param array $params
   *
   */
  public static function createFinancialAccountBalance($params) {
    $financialAccountBalance = new CRM_Financial_DAO_FinancialAccountBalance();
    if (!empty($params['financial_account_id'])) {
      $financialAccountBalance->financial_account_id = $params['financial_account_id'];
      $financialAccountBalance->find(TRUE);
    }  
    $financialAccountBalance->copyValues($params);

    $accountType = CRM_Core_PseudoConstant::accountOptionValues(
      'financial_account_type',
      NULL,
      " AND v.name IN ('Liability', 'Asset') "
    );
    if (!empty($params['financial_account_id'])
      && !CRM_Utils_Array::value($params['financial_account_type_id'], $accountType)
    ) {
      $financialAccountBalance->opening_balance = $financialAccountBalance->current_period_opening_balance = '0.00';
    }
    $financialAccountBalance->save();
  }

}
