$(function () {  
    var _editArea = $('#editArea');  
    var _editAreaInterval;  
     //表情配置
     $('#web_wechat_face').qqFace({
        id : 'facebox', 
        assign:'editArea', 
        path:'arclist/' //表情存放的路径
     });
  
    $('#editArea').blur(function () {  
        clearInterval(_editAreaInterval);  
    });  
  
    //显示隐藏表情栏  
    $('.web_wechat_face').click(function () {  
        $('.box_ft_bd').toggleClass('hide');  
        resetMessageArea();  
    });  
  
    //切换表情主题  
    $('.exp_hd_item').click(function () {  
        var _this = $(this), i = _this.data('i');  
        $('.exp_hd_item,.exp_cont').removeClass('active');  
        _this.addClass('active');  
        $('.exp_cont').eq(i).addClass('active');  
        resetMessageArea();  
    });  
  
    resetMessageArea();  
      
    function resetMessageArea() {  
        $('#messageList').animate({ 'scrollTop': 999 }, 500);  
    }  

  
});  