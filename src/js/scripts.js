
jQuery(document).ready(function() {

    $('#submit').click(function(){
        var username = $('.username').val();
        var password = $('.password').val();
        if(username == '') {
            $('.error').fadeOut('fast', function(){
                $(this).css('top', '27px');
            });
            $('.error').fadeIn('fast', function(){
                $(this).parent().find('.username').focus();
            });
            return false;
        }
        if(password == '') {
            $('.error').fadeOut('fast', function(){
                $(this).css('top', '96px');
            });
            $('.error').fadeIn('fast', function(){
                $(this).parent().find('.password').focus();
            });
            return false;
        }
        $.post("/admin.php/user/login",{username:username,password:password},function(result){
            console.log(result)
            if(result["status"]==200){
                location = "/admin.php";
            }else{
                $("#error").html(result["info"]);
            }
            
          });
    });

    $('.page-container form .username, .page-container form .password').keyup(function(){
        $(this).parent().find('.error').fadeOut('fast');
    });

});
