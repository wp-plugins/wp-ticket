jQuery(document).ready(function() {
$=jQuery;
var $captcha_container = $('.captcha-container');
if ($captcha_container.length > 0) {
        var $image = $('img', $captcha_container),
        $anchor = $('a', $captcha_container);
        $anchor.bind('click', function(e) {
                e.preventDefault();
                $image.attr('src', $image.attr('src').replace(/nocache=[0-9]+/, 'nocache=' + +new Date()));
        });
}
$.validator.setDefaults({
    ignore: [],
});
$.extend($.validator.messages,submit_tickets_vars.validate_msg);
$('#emd_ticket_duedate').datetimepicker({
'dateFormat' : 'mm-dd-yy','timeFormat' : 'hh:mm'});
$.validator.addMethod('uniqueAttr',function(val,element){
  var unique = true;
  var data_input = $("form").serialize();
  $.ajax({
    type: 'GET',
    url: submit_tickets_vars.ajax_url,
    cache: false,
    async: false,
    data: {action:'emd_check_unique',data_input:data_input, ptype:'emd_ticket',myapp:'wp_ticket_com'},
    success: function(response)
    {
      unique = response;
    },
  });
  return unique;                
}, submit_tickets_vars.unique_msg);
$('#submit_tickets').validate({
onfocusout: false,
onkeyup: false,
onclick: false,
errorClass: 'text-danger',
rules: {
  emd_ticket_first_name:{
},
emd_ticket_last_name:{
},
emd_ticket_email:{
email  : true,
},
emd_ticket_phone:{
},
emd_ticket_duedate:{
},
blt_title:{
},
blt_content:{
},
emd_ticket_attachment:{
},
},
success: function(label) {
label.remove();
},
errorPlacement: function(error, element) {
if (typeof(element.parent().attr("class")) != "undefined" && element.parent().attr("class").search(/date|time/) != -1) {
error.insertAfter(element.parent().parent());
}
else if(element.attr("class").search("radio") != -1){
error.insertAfter(element.parent().parent());
}
else if(element.attr("class").search("select2-offscreen") != -1){
error.insertAfter(element.parent().parent());
}
else if(element.attr("class").search("selectpicker") != -1 && element.parent().parent().attr("class").search("form-group") == -1){
error.insertAfter(element.parent().find('.bootstrap-select').parent());
} 
else if(element.parent().parent().attr("class").search("pure-g") != -1){
error.insertAfter(element);
}
else {
error.insertAfter(element.parent());
}
},
});
$(document).on('click','#singlebutton_submit_tickets',function(event){
     var form_id = $(this).closest('form').attr('id');
     $.each(submit_tickets_vars.submit_tickets.req, function (ind, val){
         if(!$('input[name='+val+'],#'+ val).closest('.row').is(":hidden")){
             $('input[name='+val+'],#'+ val).rules("add","required"); 
         }
     });
     var valid = $('#' + form_id).valid();
     if(!valid) {
        event.preventDefault();
        return false;
     }
});
});
