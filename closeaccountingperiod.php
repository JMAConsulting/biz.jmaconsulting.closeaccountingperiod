<?php

require_once 'closeaccountingperiod.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function closeaccountingperiod_civicrm_config(&$config) {
  _closeaccountingperiod_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array $files
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function closeaccountingperiod_civicrm_xmlMenu(&$files) {
  _closeaccountingperiod_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function closeaccountingperiod_civicrm_install() {
  _closeaccountingperiod_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function closeaccountingperiod_civicrm_uninstall() {
  _closeaccountingperiod_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function closeaccountingperiod_civicrm_enable() {
  _closeaccountingperiod_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function closeaccountingperiod_civicrm_disable() {
  _closeaccountingperiod_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function closeaccountingperiod_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _closeaccountingperiod_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function closeaccountingperiod_civicrm_managed(&$entities) {
  _closeaccountingperiod_civix_civicrm_managed($entities);
  $entities[] = array(
    'module' => 'biz.jmaconsulting.closeaccountingperiod',
    'name' => 'close_accounting_period',
    'entity' => 'OptionValue',
    'params' => array(
      'version' => 3,
      'label' => 'Close Accounting Period',
      'name' => 'Close Accounting Period',
      'description' => 'Close Accounting Period',
      'option_group_id' => 'activity_type',
      'component_id' => 'CiviContribute',
      'icon' => 'fa-file-pdf-o',
    ),
  );
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * @param array $caseTypes
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function closeaccountingperiod_civicrm_caseTypes(&$caseTypes) {
  _closeaccountingperiod_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function closeaccountingperiod_civicrm_angularModules(&$angularModules) {
_closeaccountingperiod_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function closeaccountingperiod_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _closeaccountingperiod_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function closeaccountingperiod_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
 */
function closeaccountingperiod_civicrm_navigationMenu(&$menu) {
  _closeaccountingperiod_civix_insert_navigation_menu($menu, 'Contributions', array(
    'label' => ts('Close Accounting Period', array('domain' => 'biz.jmaconsulting.closeaccountingperiod')),
    'name' => 'close_accounting_period',
    'url' => 'civicrm/admin/contribute/closeaccperiod?reset=1',
    'permission' => 'access CiviContribute,administer Accounting',
    'operator' => 'AND',
    'separator' => 0,
  ));
  _closeaccountingperiod_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_permission().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_permission
 *
 */
function closeaccountingperiod_civicrm_permission(&$permissions) {
  $prefix = ts('CiviContribute') . ': ';
  $permissions['administer Accounting'] = array(
    $prefix . ts('administer Accounting'),
    ts('Administer Accounting'),
  );
}

/**
 * Implements hook_civicrm_summaryActions().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_summaryActions
 *
 */
function closeaccountingperiod_civicrm_summaryActions(&$actions, $contactID) {
  if (CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getOrganizationNames($contactID)) {
    $actions['prior_financial_period'] = array(
      'title' => 'Set prior financial period',
      'weight' => 999,
      'ref' => 'priorfinancialperiod',
      'key' => 'priorfinancialperiod',
      'href' => "priorfinancialperiod?reset=1",
    );
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 *
 */
function closeaccountingperiod_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Preferences_Contribute') {
    $defaults['fiscalYearStart'] = Civi::settings()->get('fiscalYearStart');
    $defaults['financial_account_balance_enabled'] = Civi::settings()->get('financial_account_balance_enabled');
    $form->setDefaults($defaults);

    $period = array();
    $orgs = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getOrganizationNames();
    $dateFormat = Civi::settings()->get('dateformatFinancialBatch');
    if (!empty($orgs)) {
      foreach ($orgs as $cid => $name) {
        $priorFinancialPeriod = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($cid);
        $priorFinancialPeriod = date('Ymd', strtotime($priorFinancialPeriod));
        if ($priorFinancialPeriod) {
          $period[$cid] = array($name => CRM_Utils_Date::customFormat($priorFinancialPeriod, $dateFormat));
        }
      }
    }
    $form->assign('period', $period);
  }

  if (!CRM_Core_Permission::check('administer Accounting')) {
    if ("CRM_Activity_Form_Search" == $formName) {
      $info = civicrm_api3('Activity', 'getoptions', array('field' => 'activity_type_id'));
      $allowedActivities = array_diff($info['values'], array('Close Accounting Period'));
      $form->addSelect('activity_type_id', array(
        'entity' => 'activity',
        'label' => ts('Activity Type(s)'),
        'multiple' => 'multiple',
        'option_url' => NULL,
        'placeholder' => ts('- any -'),
        'options' => $allowedActivities)
      );
    }
    if ("CRM_Activity_Form_Activity" == $formName) {
      $allowedActivities = array_diff($form->_fields['followup_activity_type_id']['attributes'], array('Close Accounting Period'));

      $form->add('select', 'activity_type_id', ts('Activity Type'),
        array('' => '- ' . ts('select') . ' -') + $allowedActivities,
        FALSE, array(
          'onchange' => "CRM.buildCustomData( 'Activity', this.value );",
          'class' => 'crm-select2 required',
        )
      );

      if ($form->_action & CRM_Core_Action::VIEW) {
        CRM_Utils_System::permissionDenied();
        CRM_Utils_System::civiExit();
      }
    }
  }

  if ('CRM_Financial_Form_FinancialAccount' == $formName) {
    // foolish hack since CRM_Financial_Form_FinancialAccount is invoked twice
    static $alreadyInvoked = FALSE;
    if (!$alreadyInvoked) {
      $alreadyInvoked = TRUE;
      return FALSE;
    }
    if (Civi::settings()->get('financial_account_balance_enabled')) {
      $attributes = array(
        'size' => 6,
        'maxlength' => 14,
      );
      $form->add('text', 'opening_balance', ts('Opening Balance'), $attributes);
      $form->add('text', 'current_period_opening_balance', ts('Current Period Opening Balance'), $attributes);
      $financialAccountType = CRM_Core_PseudoConstant::get(
        'CRM_Financial_DAO_FinancialAccount',
        'financial_account_type_id',
        array('labelColumn' => 'name')
      );
      $filterAccounts = array(
        array_search('Asset', $financialAccountType),
        array_search('Liability', $financialAccountType),
      );
      $form->assign('filterAccounts', json_encode($filterAccounts));
      CRM_Core_Region::instance('page-body')->add(array(
        'template' => 'CRM/Financial/Form/FinancialAccountExtra.tpl',
      ));
      if ($form->_action & CRM_Core_Action::ADD) {
        $defaults['opening_balance'] = $defaults['current_period_opening_balance'] = '0.00';
      }
      else {
        $financialAccountID = $form->getVar('_id');
        $defaults = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getDefaultBalance($financialAccountID);
      }
      $form->setDefaults($defaults);
    }
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 *
 */
function closeaccountingperiod_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Preferences_Contribute') {
    $params = $form->_submitValues;
    if (!empty($params['fiscalYearStart'])) {
      Civi::settings()->set('fiscalYearStart', $params['fiscalYearStart']);
    }
    if (!empty($params['financial_account_balance_enabled'])) {
      Civi::settings()->set('financial_account_balance_enabled', $params['financial_account_balance_enabled']);
    }
  }

  if ('CRM_Financial_Form_FinancialAccount' == $formName) {
    $financialAccountId = $form->getVar('_id');
    if (!$financialAccountId) {
      $financialAccountId = CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialAccount', $form->_submitValues['name'], 'id', 'name');
    }
    $params = array(
      'financial_account_id' => $financialAccountId,
      'financial_account_type_id' => CRM_Utils_Array::value('financial_account_type_id', $form->_submitValues),
      'opening_balance' => CRM_Utils_Rule::cleanMoney(
        CRM_Utils_Array::value('opening_balance', $form->_submitValues)
      ),
      'current_period_opening_balance' => CRM_Utils_Rule::cleanMoney(
        CRM_Utils_Array::value('current_period_opening_balance', $form->_submitValues)
      ),
    );
    CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::createFinancialAccountBalance($params);
  }
}

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
 */
function closeaccountingperiod_civicrm_preProcess($formName, &$form) {
  if ($formName == 'CRM_Admin_Form_Preferences_Contribute') {
    $settings = $form->getVar('_settings');
    $contributeSettings = array();
    foreach ($settings as $key => $setting) {
      $contributeSettings[$key] = $setting;
      if ($key == 'default_invoice_page') {
        $contributeSettings['financial_account_balance_enabled'] = CRM_Core_BAO_Setting::CONTRIBUTE_PREFERENCES_NAME;
        $contributeSettings['fiscalYearStart'] = CRM_Core_BAO_Setting::LOCALIZATION_PREFERENCES_NAME;
        $contributeSettings['prior_financial_period'] = CRM_Core_BAO_Setting::CONTRIBUTE_PREFERENCES_NAME;
      }
    }
    $form->setVar('_settings', $contributeSettings);    
  }
}

/**
 * Implements hook_civicrm_alterSettingsMetaData().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsMetaData
 *
 */
function closeaccountingperiod_civicrm_alterSettingsMetaData(&$settingsMetadata, $domainID, $profile) {
  $settingsMetadata['financial_account_balance_enabled'] = array(
    'group_name' => 'Contribute Preferences',
    'group' => 'contribute',
    'name' => 'financial_account_balance_enabled',
    'type' => 'Integer',
    'html_type' => 'checkbox',
    'quick_form_type' => 'Element',
    'default' => 0,
    'add' => '4.7',
    'title' => 'Enable Financial Account Balances',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => '',
  );
  $settingsMetadata['prior_financial_period'] = array(
    'group_name' => 'Contribute Preferences',
    'group' => 'contribute',
    'name' => 'prior_financial_period',
    'type' => 'activityDate',
    'quick_form_type' => 'Date',
    'html_type' => 'Date',
    'default' => '',
    'add' => '4.7',
    'title' => 'Prior Financial Period',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'help_text' => '',
  );
}

/**
 * Implementation of hook_civicrm_alterReportVar
 */
function closeaccountingperiod_civicrm_alterReportVar($varType, &$var, &$object) {
  if (get_class($object) != 'CRM_CloseAccountingPeriod_Form_Report_TrialBalance') {
    return;
  }
  $instanceID = CRM_Report_Utils_Report::getInstanceID();
  if ($instanceID) {
    $params = array('id' => $instanceID);
    $instanceValues = array();
    CRM_Core_DAO::commonRetrieve('CRM_Report_DAO_ReportInstance',
      $params,
      $instanceValues
    );
    $formValues = CRM_Utils_Array::value('form_values', $instanceValues);
    if ($formValues) {
      $formValues = unserialize($formValues);
      if ($contactId = CRM_Utils_Array::value('contact_id_value', $formValues)) {
        $priorDate = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($contactId);
        if (!$priorDate) {
          $startDate = CRM_Core_DAO::singleValueQuery("SELECT MIN(trxn_date) FROM (SELECT trxn_date FROM civicrm_financial_trxn UNION SELECT transaction_date FROM civicrm_financial_item) AS S1");
          $startYear = date('Y', strtotime($startDate));
          $years = range($startYear, date('Y'));
          $years = array_combine($years, $years);
          for ($i=1; $i<=12; $i++) {
            $months[$i] = date("M", mktime(0, 0, 0, $i, 10));
          }
        }
        if ($varType == 'columns') {
          $var['civicrm_financial_trxn']['filters'] = array(
            'trxn_date_month' => array(
              'title' => ts('Financial Period End Month'),
              'operatorType' => CRM_Report_Form::OP_SELECT,
              'options' => $months,
              'type' => CRM_Utils_Type::T_INT,
              'pseudofield' => TRUE,
            ),
          );
          $var['civicrm_financial_trxn']['filters'] += array(
            'trxn_date_year' => array(
              'title' => ts('Financial Period End Year'),
              'operatorType' => CRM_Report_Form::OP_SELECT,
              'options' => $years,
              'type' => CRM_Utils_Type::T_INT,
              'pseudofield' => TRUE,
            ),
          );
        }
        if ($varType == 'sql') {
          $params = $var->getVar('_params');
          $endDate = date('Y-m-t', mktime(0, 0, 0, $params['trxn_date_month_value'], 1, $params['trxn_date_year_value']));
          $from = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getTrialBalanceQuery($var->getVar('_aliases'), TRUE, $contactId, $endDate);
          $var->setVar('_from', $from);
        }
      }
    }
  }
}