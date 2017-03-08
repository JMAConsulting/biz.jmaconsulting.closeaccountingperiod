{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
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
*}

<table class="form-layout-compressed">
  <tr>
    <td class="label">{$form.prior_financial_period.label}</td>
    <td class="content">{$form.prior_financial_period.html}</td>
  </tr>
</table>

<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {

  function checkPeriod() {
    var m = $('#prior_financial_period_M').val();
    var y = $('#prior_financial_period_Y').val();
    var speriod = m + '-' + y;
    var hperiod = '{/literal}{$priorFinancialPeriod}{literal}';
    if (((hperiod && speriod == '') || (hperiod && speriod != '')) && (speriod != hperiod)) {
      var msg = '{/literal}{ts}Changing the Prior Financial Period may result in problems calculating closing account balances accurately and / or exporting of financial transactions. Do you want to proceed?{/ts}{literal}';
      return confirm(msg);
    }
  }

  $('input[name=_qf_PriorFinancialPeriod_upload]').on('click', checkPeriod);

});
</script>
{/literal}