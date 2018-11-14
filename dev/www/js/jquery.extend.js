/**
 * 
      下拉框添加搜索功能: 
      要求: 1.加载本文件 
            2.设置input标签 ：添加id为makeupCo的属性 + 添加onfocus="setfocus(this)"  + oninput="setinput(this) 两个事件函数
            3.设置select标签：添加id为typenum的属性  + 添加select_search的class属性  + 添加onchange="changeF(this)"时间函数
      示例：
        <input type="text" id="makeupCo" onfocus="setfocus(this)" oninput="setinput(this);" placeholder="请选择或输入" value="<{echo $this->consume_type[$usetype];}>" /> 
        <select name="" id="typenum" class="select_search" onchange="changeF(this)" size="10">  
            <{foreach $this->consume_type as $id =>$name}>
             <option value="{$id}" <{if $id === $usetype}> selected<{/if}>>{$name}</option>
            <{/foreach}>
        </select> 
 *
 */
 //页面加载初始化事件
$(document).ready(function(){
    $('#typenum').css('display', 'none');
});

var TempArr=[];//存储option-name
var TempIds=[]; //存储option-value
  
$(function(){
    /*先将数据存入数组*/
    $("#typenum option").each(function(index, el) {
        TempArr[index] = $(this).text();
        TempIds[index] = $(this).val();
    });
    $(document).bind('click', function(e) {
        var e = e || window.event; //浏览器兼容性
        var elem = e.target || e.srcElement;
        while (elem) { //循环判断至跟节点，防止点击的是div子元素 
            if (elem.id && (elem.id == 'typenum' || elem.id == "makeupCo")) {
                return;
            }
            elem = elem.parentNode;
        }
        $('#typenum').css('display', 'none'); //点击的不是div或其子元素 
    });
});
  
function changeF(this_) {
    $(this_).prev("input").val($(this_).find("option:selected").text());
    $("#typenum").css({"display":"none"});
}

function setfocus(this_){
    $("#typenum").css({"display":""});
    var select = $("#typenum");
    select.html('');
    for(i=0;i<TempArr.length;i++){
        var option = $("<option value="+TempIds[i]+"></option>").text(TempArr[i]);
        select.append(option);
    }
}
  
function setinput(this_){
    var select = $("#typenum");
    select.html("");
    for(i=0;i<TempArr.length;i++){
        //若找到以txt的内容开头的，添option
        if(TempArr[i].substring(0,this_.value.length).indexOf(this_.value)==0){
            var option = $("<option value="+TempIds[i]+"></option>").text(TempArr[i]);
            select.append(option);  
        }
    }
}