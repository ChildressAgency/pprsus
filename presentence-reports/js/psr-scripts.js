/*jQuery(document).on('acf/validate_field', function(e, field){
/*  var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
      sURLVariables = sPageURL.split('&'),
      sParameterName,
      i;

    for (i = 0; i < sURLVariables.length; i++) {
      sParameterName = sURLVariables[i].split('=');

      if (sParameterName[0] === sParam) {
        return sParameterName[1] === undefined ? true : sParameterName[1];
      }
    }
  };
*/
/*
var validate_worksheet = getQueryVariable('validate_worksheet');
console.log(validate_worksheet);
if(validate_worksheet == 'true'){
  var $field = $(field);
  console.log($field);
  if($field.find('input').val() == ''){
    $field.data('validation', false);
  }
}
});*/

//http://koalyptus.github.io/TableFilter/

if (document.getElementById('psr-table')) {
  var dashboard_filter = new TableFilter(document.querySelector('.psr-table'), {
    base_path: '/wp-content/plugins/presentence-reports/js/',
    filters_row_index: 1,
    clear_filter_text: 'Display All',
    col_0: 'select',
    col_1: 'select',
    col_2: 'none',
    col_3: 'none',
    col_4: 'select'
  });
  dashboard_filter.init();
}

if (getQueryVariable('worksheet_id') != false) {
  acf.addFilter('validation_complete', function (json, $form) {
    console.log($form);
    return json;
  });


  if (getQueryVariable('validate_worksheet') == false) {
    window.acf.validation.active = false;
  }
}

function getQueryVariable(variable) {
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i = 0; i < vars.length; i++) {
    var pair = vars[i].split("=");
    if (pair[0] == variable) {
      return pair[1];
    }
  }
  return (false);
}


