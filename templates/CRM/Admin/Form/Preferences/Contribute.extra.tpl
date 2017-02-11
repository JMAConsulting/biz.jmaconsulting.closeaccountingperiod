{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      showHideElement('financial_account_balance_enabled', 'fiscalYearStart');
      $("#financial_account_balance_enabled").click(function() {
        showHideElement('financial_account_balance_enabled', 'fiscalYearStart');
      });
      $('input[name=_qf_Contribute_next]').on('click', checkPeriod);
      function checkPeriod() {
        var speriod = $('#prior_financial_period').val();
      	var hperiod = '{/literal}{$priorFinancialPeriod}{literal}';
      	if (((hperiod && speriod == '') || (hperiod && speriod != '')) && (speriod != hperiod)) {
	  var msg = '{/literal}{ts}Changing the Prior Financial Period may result in problems calculating closing account balances accurately and / or exporting of financial transactions. Do you want to proceed?{/ts}{literal}';
          return confirm(msg);
        }
      }
      function showHideElement(checkEle, toHide) {
        if ($('#' + checkEle).prop('checked')) {
          $("tr.crm-preferences-form-block-" + toHide).show();
        }
        else {
          $("tr.crm-preferences-form-block-" + toHide).hide();
        }
      }
    });
  </script>
{/literal}