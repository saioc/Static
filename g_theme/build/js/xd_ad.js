$(document).ready(function() {
    $("#menu").click(function(event) {
        $(this).toggleClass('on');
        $(".list").toggleClass('closed');
    });
});
$(document).ready(function(){
  $("#menu").click(function(){
    $('body').toggleClass(function(){
      return 'body';
    });
  });
});