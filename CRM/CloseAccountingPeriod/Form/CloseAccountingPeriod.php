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
 | CiviCRM is distributed in the hope that it will be useful, but   |
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
 * This class generates form components for closing an account period.
 */
class CRM_CloseAccountingPeriod_Form_CloseAccountingPeriod extends CRM_Core_Form {

  /**
   * Contains list of Organization linked with financial account.
   *
   * @var array
   */
  public $_financialAccountOrg;

  /**
   * Set default values.
   *
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array();
    $date = Civi::settings()->get('prior_financial_period');
    if (!empty($date)) {
      $date = strtotime('+1 month', strtotime(date('01-m-Y', strtotime($date))));
    }
    else {
      $date = strtotime("-1 month", strtotime(date('01-m-Y')));
    }
    $defaults['closing_date'] = array(
      'M' => date('n', $date),
      'Y' => date('Y', $date),
    );
    if (count($this->_financialAccountOrg) == 1) {
      $defaults['contact_id'] = key($this->_financialAccountOrg);
    }
    if (CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::checkIfExported($date, $defaults['contact_id'])) {
      CRM_Core_Session::setStatus(ts('Some transactions in the reporting period have not yet been exported. Please ensure they are exported.'), ts('Warning'), 'warning');
    }
    return $defaults;
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $this->add('date', 'closing_date', ts('Accounting Period to Close'), CRM_Core_SelectValues::date(NULL, 'M Y', 2, 5), TRUE);
    $this->_financialAccountOrg = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getOrganizationNames();
    $this->add('select', 'contact_id',
      ts('Organization'),
      array('' => ts('- select -')) + $this->_financialAccountOrg,
      TRUE
    );
    $confirmClose = ts('Are you sure you want to close accounting period?');
    $this->addButtons(array(
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
        array(
          'type' => 'upload',
          'name' => ts('Close Accounting Period'),
          'js' => array('onclick' => 'return confirm(\'' . $confirmClose . '\');'),
        ),
      )
    );
    $this->addFormRule(array('CRM_CloseAccountingPeriod_Form_CloseAccountingPeriod', 'formRule'), $this);
  }

  /**
   * Global form rule.
   *
   * @param array $fields
   *   The input form values.
   * @param array $files
   *   The uploaded files if any.
   * @param $self
   *
   */
  public static function formRule($fields, $files, $self) {
    $error = array();
    $previousPriorFinPeriod = Civi::settings()->get('prior_financial_period');
    if (!empty($previousPriorFinPeriod)) {
      $priorFinPeriod = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::buildClosingDate($fields['closing_date']);
      if (strtotime($previousPriorFinPeriod) >= $priorFinPeriod) {
        $error['closing_date'] = ts('Closing Accounting Period Date cannot be less than prior Closing Accounting Period Date.');
      }
    }
    return $error;
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    // store the submitted values in an array
    $params = $this->controller->exportValues($this->_name);
    $redirectURL = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::createClosePeriodActivity($params);
    CRM_Core_Session::setStatus(ts("Accounting Period has been closed successfully!"), ts('Success'), 'success');
    CRM_Core_Session::singleton()->replaceUserContext($redirectURL);
  }

}
