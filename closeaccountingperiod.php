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
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 *
 */
function closeaccountingperiod_civicrm_buildForm($formName, &$form) {
  if ('CRM_Financial_Form_FinancialAccount' == $formName) {
    // foolish hack since CRM_Financial_Form_FinancialAccount is invoked twice
    static $alreadyInvoked = FALSE;
    if (!$alreadyInvoked) {
      $alreadyInvoked = TRUE;
      return FALSE;
    }
    if (CRM_Contribute_BAO_Contribution::checkContributeSettings('financial_account_bal_enable')) {
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