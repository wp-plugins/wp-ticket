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
$('#submit_tickets').validate({
onfocusout: false,
onkeyup: false,
onclick: false,
errorClass: 'text-danger',
rules: {
  'ticket_topic':{
required:true,
},
emd_ticket_first_name : {
required  : true,
},
emd_ticket_last_name : {
required  : true,
},
emd_ticket_email : {
required  : true,
email  : true,
},
emd_ticket_phone : {
required  : false,
},
blt_title : {
required  : true,
},
blt_content : {
required  : true,
},
emd_ticket_attachment : {
required  : false,
},
},
success: function(label) {
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
});
