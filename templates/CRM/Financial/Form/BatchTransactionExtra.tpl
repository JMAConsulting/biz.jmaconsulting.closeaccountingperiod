{literal}
<script type="text/javascript">
CRM.$(function($) {
  $( document ).ajaxComplete(function(event, xhr, settings) {
    var url = 'http://text.com' + settings.url;
    url = new URL(url);
    var fname = url.searchParams.get('fnName');
    var fclass = url.searchParams.get('className');
    if ((fname == 'assignRemove' || 'bulkAssignRemove' == fname)
      && fclass == 'CRM_Financial_Page_AJAX')
    {
      var data = settings.data;
      var op = data.split("&op=");
      if (op[1] == undefined ) {
        op = data.split("&action=");
      }
      op = op[1].split("&");
      if (op[0].toLowerCase() == 'assign') {
        url = CRM.url("civicrm/batch/checkFinancialTrxnForBatchStatus?reset=1");
        CRM.$.get(url, null, function( msg ) {
	  if (msg) {
            CRM.alert(msg, ts('Batch Transaction'), 'error');
          }
        }, 'json');
      }
    }
  });
});
</script>
{/literal}
