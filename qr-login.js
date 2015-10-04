jQuery(document).ready(function($){


  $('#reset-token').on('click', function(){
    data = {
      action: 'reset_token'
    };
    $.post(ajaxurl, data, function(response) {
      location.reload();
    });
  });


});

