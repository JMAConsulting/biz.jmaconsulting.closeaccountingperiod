<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Closeaccountingperiod_Form_Report_TrialBalance',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Trial Balance Report',
      'description' => 'Trial Balance Report(biz.jmaconsulting.closeaccountingperiod)',
      'class_name' => 'CRM_CloseAccountingPeriod_Form_Report_TrialBalance',
      'report_url' => 'biz.jmaconsulting.closeaccountingperiod/trialbalance',
      'component' => 'CiviContribute',
    ),
  ),
);