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
{if $form.opening_balance}
  <table>
    <tr class="crm-contribution-form-block-opening_balance">
      <td class="label">{$form.opening_balance.label}</td>
      <td class="html-adjust">{$form.opening_balance.html}
      </td>
    </tr>
    <tr class="crm-contribution-form-block-current_period_opening_balance">
      <td class="label">{$form.current_period_opening_balance.label}</td>
      <td class="html-adjust">{$form.current_period_opening_balance.html}
      </td>
    </tr>
  </table>
  {literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $($('.crm-contribution-form-block-current_period_opening_balance')).insertAfter('.crm-contribution-form-block-is_default');
      $($('.crm-contribution-form-block-opening_balance')).insertAfter('.crm-contribution-form-block-is_default');
      $('#financial_account_type_id').on('change', showHideElement);
      showHideElement();
      function showHideElement() {
        var financialAccountType = $('#financial_account_type_id').val();
        var financialAccountTypes = '{/literal}{$limitedAccount}{literal}';
	if ($.inArray(financialAccountType, financialAccountTypes) > -1) {
	  $('tr.crm-contribution-form-block-current_period_opening_balance').show();
	  $('tr.crm-contribution-form-block-opening_balance').show();
	}
	else {
	  $('tr.crm-contribution-form-block-current_period_opening_balance').hide();
	  $('tr.crm-contribution-form-block-opening_balance').hide();
	}
      }
    });
  </script>
  {/literal}
{/if}