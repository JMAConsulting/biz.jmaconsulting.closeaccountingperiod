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

  public static $_batchFinancialTrxnDate = NULL;

  public static $_batchFinancialTrxnOrg = NULL;

  public static $_contactsFinancialAccount = NULL;

  public static $_setStatus = NULL;

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
  public static function getTrialBalanceQuery($alias, $onlyFromClause = FALSE, $contactId = NULL, $endDate = NULL) {
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
    $priorDate = NULL;
    if ($contactId) {
      $priorDate = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($contactId);
    }
    if (empty($priorDate)) {
      $where = " <= DATE('$closingDate') ";
      $financialBalanceField = 'opening_balance';
      $statistics = ts('upto') . ' ' . CRM_Utils_Date::customFormat($closingDate);
    }
    else {
      $priorDate = date('Y-m-d', strtotime($priorDate . '+1 Day'));
      $where = " BETWEEN DATE('$priorDate') AND DATE('$closingDate') ";
      $financialBalanceField = 'current_period_opening_balance';
      $statistics = CRM_Utils_Date::customFormat($priorDate) . ' - ' . CRM_Utils_Date::customFormat($closingDate);
    }
    if ($endDate) {
      $where = " <= DATE('$endDate') ";
      $statistics = ts('upto') . ' ' . CRM_Utils_Date::customFormat($endDate);
    }
    CRM_Core_Session::singleton()->set('statisticsPriorPeriodDate', $statistics);
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
   * Function to create Closing date based on Month and Year.
   *
   * @param array $closingDate
   *
   */
  public static function buildClosingDate(&$closingDate) {
    $priorFinPeriod = date('Ymt', mktime(0, 0, 0, $closingDate['M'], 1, $closingDate['Y']));
    $priorFinPeriod = strtotime($priorFinPeriod);
    return $priorFinPeriod;
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

    // invoke hook to alter Close accounting period params
    // or to perform any action before close accounting period is processed
    CRM_CloseAccountingPeriod_Hook::preCloseAccountingPeriod($params);

    // Set closing date
    $priorFinPeriod = self::buildClosingDate($params['closing_date']);
    Civi::settings()->set('closing_date', $params['closing_date']);
    $priorFinPeriod = date('m/d/Y', $priorFinPeriod);
    // Create activity
    $activityType = CRM_Core_PseudoConstant::getKey(
      'CRM_Activity_BAO_Activity',
      'activity_type_id',
      'Close Accounting Period'
    );
    $previousPriorFinPeriod = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($params['contact_id']);
    $closingDate =  date('Y-m-d', strtotime($priorFinPeriod));
    $activityParams = array(
      'source_contact_id' => CRM_Core_Session::singleton()->get('userID'),
      'assignee_contact_id' => $params['contact_id'],
      'activity_type_id' => $activityType,
      'subject' => ts('Close Accounting Period : ') . $closingDate,
      'status_id' => CRM_Core_PseudoConstant::getKey(
        'CRM_Activity_BAO_Activity',
        'status_id',
        'Completed'
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
    $priorDate = array();
    $priorDate['M'] = date('n', strtotime($priorFinPeriod));
    $priorDate['Y'] = date('Y', strtotime($priorFinPeriod));
    CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::setPriorFinancialPeriod(
      $priorDate,
      $params['contact_id']
    );
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

  /**
   * Get Prior Financial Period for Organization
   *
   * @param int $contactID
   *
   */
  public static function getPriorFinancialPeriod($contactID) {
    $period = civicrm_api3('Setting', 'get', array(
      'contact_id' => $contactID,
      'name' => 'prior_financial_period',
    ));
    $priorFinancialPeriod = CRM_Utils_Array::value('prior_financial_period', $period['values'][$period['id']]);
    if ($priorFinancialPeriod) {
      $priorFinancialPeriod = self::buildClosingDate($priorFinancialPeriod);
      $priorFinancialPeriod = date('Y-m-t', $priorFinancialPeriod);
    }
    return $priorFinancialPeriod;
  }

  /**
   * Set Prior Financial Period for Organization
   *
   * @param int $contactID
   *
   */
  public static function setPriorFinancialPeriod($priorFinancialPeriod, $contactID) {
    $priorFinancialPeriod = date('Y-m-d', strtotime($priorFinancialPeriod));
    CRM_Core_BAO_Setting::setItem(
      $priorFinancialPeriod,
      CRM_Core_BAO_Setting::CONTRIBUTE_PREFERENCES_NAME,
      'prior_financial_period',
      CRM_Core_Component::getComponentID('CiviContribute'),
      $contactID
    );
  }

  /**
   * Return dates for filter
   *
   * @param int $contactId
   * return array
   */
  public static function getDates($contactId = NULL) {
    $months = $years = $priorDate = array();
    if ($contactId) {
      $priorDate = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($contactId);
    }
    if (!$priorDate) {
      $startDate = CRM_Core_DAO::singleValueQuery("SELECT MIN(trxn_date) FROM (SELECT trxn_date FROM civicrm_financial_trxn UNION SELECT transaction_date FROM civicrm_financial_item) AS S1");
      $startYear = date('Y', strtotime($startDate));
      $years = range($startYear, date('Y'));
      $years = array_combine($years, $years);
      $months[''] = $years[''] = '-any-';
      for ($i=1; $i<=12; $i++) {
        $months[$i] = date("M", mktime(0, 0, 0, $i, 10));
      }
    }
    else {
      $month = date('n', strtotime("+1 month", strtotime($priorDate)));
      $months[$month] = date('M', strtotime("+1 month", strtotime($priorDate)));
      $year = date('Y', strtotime($priorDate));
      $years[$year] = $year;
    }
    return array($months, $years);
  }

  /**
   * Return dates for filter
   *
   * @param datetime $receiveDate
   * @param int $financialTypeId
   */
  public static function checkReceiveDate($receiveDate, $financialTypeId) {
    if (!Civi::settings()->get('prevent_recording_trxn_closed_month') || !$financialTypeId) {
      return NULL;
    }
    $result = civicrm_api3('EntityFinancialAccount', 'getsingle', array(
      'return' => array("financial_account_id.contact_id"),
      'entity_table' => "civicrm_financial_type",
      'entity_id' => $financialTypeId,
      'options' => array('limit' => 1),
    ));
    $contactId = $result['financial_account_id.contact_id'];
    $priorFinancialPeriod = self::getPriorFinancialPeriod($contactId);
    if ($priorFinancialPeriod && strtotime($receiveDate) < strtotime($priorFinancialPeriod)) {
      throw new CRM_Core_Exception(ts("Recording of payment for closed month is not allowed."));
    }
  }

  /**
   * Limit adding of a financial trxn to same month by checking the month and year of its transaction date.
   *   Financial trxn is identified by $entityBatch->entity_id. If the month doesn't match
   *   with month of financial trxn which are already added in batch then delete the $entityBatch record
   *   and throw a error message.
   *
   * @param obj $params CRM_Batch_DAO_EntityBatch
   */
  public static function checkFinancialTrxnForBatch($entityBatch) {
    if (!Civi::settings()->get('financial_batch_same_month') || ($entityBatch->entity_table != 'civicrm_financial_trxn')) {
      return NULL;
    }
    $batchId = $entityBatch->batch_id;
    if (empty(self::$_batchFinancialTrxnDate[$batchId])) {
      self::$_batchFinancialTrxnDate[$batchId] = CRM_Core_DAO::singleValueQuery("
        SELECT DATE_FORMAT(cft.trxn_date, '%Y%m') FROM `civicrm_entity_batch` ceb
        INNER JOIN civicrm_financial_trxn cft ON cft.id = ceb.entity_id AND ceb.batch_id = {$batchId}
          AND ceb.entity_table = 'civicrm_financial_trxn'
        LIMIT 1
      ");
    }
    if (!empty(self::$_batchFinancialTrxnDate[$batchId])) {
      if (self::$_batchFinancialTrxnDate[$batchId] != date('Ym', strtotime(CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialTrxn', $entityBatch->entity_id, "trxn_date")))) {
        // Unassign the financial trxn which doesn't belong to same month of transactions which are already added in batch,
        //  by deleting the $entityBatch entry and throwing a error message.
        $entityBatch->delete();
        if (empty(self::$_setStatus['same_month'])) {
	  self::$_setStatus['same_month'] = TRUE;
	  $month = date('F', mktime(0, 0, 0, substr(self::$_batchFinancialTrxnDate[$batchId], -2), 10));
	  $year = substr(self::$_batchFinancialTrxnDate[$batchId], 0, 4);
          // set the error message in session variable $batchTransactionStatus, later used in self::checkFinancialTrxnForBatchStatus(..)
	  CRM_Core_Session::singleton()->set('batchTransactionStatus', ts("CiviCRM has been configured to require all transactions added to a batch to have their Transaction Date in the same calendar month. Please do not attempt to assign transactions that are not in {$month}, {$year} to this batch"));
        }
      }
    }
  }

  /**
   * Limit adding of a financial trxn to same company.
   *
   * @param obj $params CRM_Batch_DAO_EntityBatch
   */
  public static function checkFinancialTrxnForSameOrg($entityBatch) {
   $batchId = $entityBatch->batch_id;
    if (empty(self::$_batchFinancialTrxnOrg[$batchId])) {
      self::$_batchFinancialTrxnOrg[$batchId] = CRM_Core_DAO::getFieldValue('CRM_EasyBatch_DAO_EasyBatchEntity', $batchId, 'contact_id', 'batch_id');
      if (empty(self::$_batchFinancialTrxnOrg[$batchId])) {
        self::$_batchFinancialTrxnOrg[$batchId] = CRM_Core_DAO::singleValueQuery("
          SELECT cfa.contact_id FROM civicrm_financial_trxn cft
          INNER JOIN civicrm_financial_account cfa ON cfa.id = cft.to_financial_account_id
          INNER JOIN civicrm_entity_batch ceb ON cft.id = ceb.entity_id
            AND ceb.entity_table = 'civicrm_financial_trxn' AND ceb.batch_id = {$batchId}
          LIMIT 1
        ");
      }
    }
    if (!empty(self::$_batchFinancialTrxnOrg[$batchId])) {
      $contactId = self::$_batchFinancialTrxnOrg[$batchId];
      $query = "SELECT cfa.contact_id FROM civicrm_financial_trxn cft
          INNER JOIN civicrm_financial_account cfa ON cfa.id = cft.to_financial_account_id
           AND cfa.contact_id = {$contactId} AND cft.id = {$entityBatch->entity_id}";
      if (!CRM_Core_DAO::singleValueQuery($query)) {
        // Unassign the financial trxn which doesn't belong to same company,
        //  by deleting the $entityBatch entry and throwing a error message.
        $entityBatch->delete();
        if (empty(self::$_setStatus['same_company'])) {
	  self::$_setStatus['same_company'] = TRUE;
          // set the error message in session variable $batchTransactionOrgStatus, later used in self::checkFinancialTrxnForBatchStatus(..)
	  $companyName = CRM_Core_DAO::getFieldValue('CRM_Contact_BAO_Contact', self::$_batchFinancialTrxnOrg[$batchId], 'organization_name');
	  CRM_Core_Session::singleton()->set('batchTransactionOrgStatus', ts("Selected financial transaction(s) doesn't belong to '{$companyName}'."));
        }
      }
    }
  }

  /**
   * Display Status
   */
  public static function checkFinancialTrxnForBatchStatus() {
    $msg = CRM_Core_Session::singleton()->get('batchTransactionStatus');
    $message = array();
    if ($msg) {
      $message[] = $msg;
      CRM_Core_Session::singleton()->set('batchTransactionStatus', '');
    }
    $msg = CRM_Core_Session::singleton()->get('batchTransactionOrgStatus');
    if ($msg) {
      $message[] = $msg;
      CRM_Core_Session::singleton()->set('batchTransactionOrgStatus', '');
    }
    CRM_Utils_JSON::output($message);
  }

}
