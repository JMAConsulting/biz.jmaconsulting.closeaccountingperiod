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
class CRM_CloseAccountingPeriod_Form_PriorFinancialPeriod extends CRM_Core_Form {

  /**
   * The id of the contact.
   *
   * @var int
   */
  public $_contactID;

  /**
   * Set variables up before form is built.
   */
  public function preProcess() {
    parent::preProcess();
    $this->_contactID = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
  }

  /**
   * Set default values.
   *
   * @return array
   */
  public function setDefaultValues() {
    $defaults = array();
    $defaults['prior_financial_period'] = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($this->_contactID);
    return $defaults;
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    $priorFinancialPeriod = CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::getPriorFinancialPeriod($this->_contactID);
    if ($priorFinancialPeriod) {
      $this->assign('priorFinancialPeriod', $priorFinancialPeriod);
    }
    $this->addDate('prior_financial_period', ts('Prior Financial Period'), TRUE);
    $this->addButtons(array(
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
        array(
          'type' => 'upload',
          'name' => ts('Set Prior Financial Period'),
        ),
      )
    );
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    // store the submitted values in an array
    $params = $this->controller->exportValues($this->_name);
    CRM_CloseAccountingPeriod_BAO_CloseAccountingPeriod::setPriorFinancialPeriod(
      $params['prior_financial_period'],
      $this->_contactID
    );
    CRM_Core_Session::setStatus(ts("Prior Financial Period has been set successfully!"), ts('Success'), 'success');
  }

}
