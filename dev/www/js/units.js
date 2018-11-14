/**
 * User: jecelyin 
 * Date: 12-1-30
 * Time: 下午6:56
 *
 */

$(document).ready(function(){
    //ie6下高亮表格当前行
    if($.browser.msie && $.browser.version == 6.0)
    {
        $('.dtable tbody tr').mouseover(function(){
            if(!$(this).hasClass('nohover'))
                $(this).addClass('tr_hover');
        }).mouseout(function(){
            $(this).removeClass('tr_hover');
        });
    }
    //菜单展开，折叠事件
    $('.mtitle div.mtxt').click(function(){
        var p = $(this).parent().parent();
        if(p.hasClass('mcollapsed')){
            $('#leftnav .mexpanded').removeClass('mexpanded').addClass('mcollapsed');
            p.addClass('mexpanded').removeClass('mcollapsed');
        }else if(p.hasClass('mexpanded')){
            p.addClass('mcollapsed').removeClass('mexpanded');
        }

        var id = p.attr('id');
        $.cookie('menu_last_expand', id, { expires: 365, path: '/' });
        //根据菜单栏宽度设置内容主界面的margin-left值
        $('#center_col').css('margin-left',$('#leftnav').css('width'));
    });
    //如果没有展开的菜单，则展开最后一次打开的菜单
    if($('#leftnav .mexpanded').length < 1)
    {
        var id = $.cookie('menu_last_expand');
        if(id)
        {
            $('#'+id).addClass('mexpanded');
        }
    }

    //日期选择widget
    $(".js_st").click(function(){
    	WdatePicker({skin:'whyGreen',dateFmt:'yyyy-M-d H:mm:ss',startDate:'%y-%M-%d 00:00:00'})
//      WdatePicker({doubleCalendar:false,dateFmt:'yyyy-MM-dd 00:00:00'});
    });
    $(".js_et").click(function(){
    	WdatePicker({skin:'whyGreen',dateFmt:'yyyy-M-d H:mm:ss',startDate:'%y-%M-%d 00:00:00'})
//      WdatePicker({doubleCalendar:true,dateFmt:'yyyy-MM-dd 23:59:59'});
    });
    $(".js_ts").click(function(){
    	WdatePicker({skin:'whyGreen',dateFmt:'yyyy-M-d H:mm:ss',startDate:'%y-%M-%d 00:00:00'})
//      WdatePicker({doubleCalendar:true,dateFmt:'yyyy-MM-dd HH:mm:ss'});
    });
    var table = $('table.tablesorter');
    if(table.length > 0 && table.find('tbody tr').length > 0)
    {
      table.tablesorter();
      // 不进行默认排序，要默认请使用<table cellspacing="1" class="tablesorter {sortlist: [[0,0],[4,0]]}">的方式
     /* table.trigger("update");
      // set sorting column and direction, this will sort on the first and third column
      //行数（0,), 排序0=ASC 1=DESC
      var sorting = [[0, 1]];
      // sort on the first column
      table.trigger("sorton",[sorting]);*/
    }

    //控制顶栏目或侧栏目移入移出背景颜色
    $('.side_column,.tab_header').hover(
    function(){
        $(this).css('background-color','#FFE4E1');
    },function(){
        $(this).css('background-color','');
    });

    //设置分页显示数量
    $('.set_page_num').click(function(){
        var v = Number(prompt('设置分页数量'));
        if(v > 0)
         $.post('?m=SMP_Index_Default',{act:'setPageNum',pageNum:v},function(data){
                    layer.msg(data);
                    setTimeout(function(){
                        location.reload();
                    },2000);
        },'json');
    });
});

/**
 * 链接跳转
 * @param url
 */
function linkto(url)
{
    location.href=url;
}

/**
 *  带分页页数页面跳转
 */
function jumpWithPage (url) {
    /*获取分页页数*/
    var page = $('.dpage').find('.pageinfo').find('#current_page').text();
    if(page) {
        location.href = url + '&page='+page;
    }else{
        location.href = url;
    }

}