<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>欢迎页面-X-admin2.0</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/public/css/admin/x-admin2.css?t=<?php echo JsVer;?>" media="all">
    <script src="/public/js/jquery-2.0.0.min.js" charset="utf-8"></script>
    <script src="/public/js/layui2/layui.js" charset="utf-8"></script>
    <script src="/public/js/admin/x-layui.js" charset="utf-8"></script>
    <script src="/public/js/admin/public.js" charset="utf-8"></script>
    <style>.layui-input{line-height: 38px;}.myimg{margin-left: 10px;}#container {width:400px;height:300px;margin: 20px 0;}</style>
</head>
<body>
<div class="x-body">
    <xblock>
        <button class="layui-btn" onclick="click_edit('添加','/xiamenyyhoutai/xcxbuilding/unit_add',<?=$data['id']?>,'680','450');"><i class="layui-icon">&#xe608;</i>添加</button>
    </xblock>
    <table class="layui-table layui-form">
        <thead>
        <tr>
            <th>ID</th>
            <th>单元</th>
            <th>楼层数</th>
            <th>梯户比</th>
            <th>排序</th>
            <th>添加时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody id="tbody"></tbody>
    </table>
    <div id="page"></div>
</div>
</body>
</html>
<script>
    layui.use(['form','element','laypage'], function(){
        form = layui.form;
        laypage = layui.laypage;
        getPageData();
    });
    function getPageData() {
        var curr = arguments[0] ? arguments[0] : 1;
        var limit = arguments[1] ? arguments[1] : PAGELIMIT;
        var keyword=$('#keyword2').val();
        ajax("/xiamenyyhoutai/xcxbuilding/unit_page",{id:<?=$data['id']?>,keyword:keyword,curr:curr,limit:limit},function (data) {
            if(data.success){
                $("#tbody").empty();
                for(var i in data['data']){
                    var id = data['data'][i]['id'];
                    var $content = '<tr>' +
                        '<td>'+parseInt((curr-1)*PAGELIMIT+parseInt(i)+1)+'</td>' +
                        '<td>'+data['data'][i]['title']+'</td>' +
                        '<td>'+data['data'][i]['floor_number']+'层</td>' +
                        '<td>'+data['data'][i]['stairs_number']+'户</td>' +
                        '<td>'+data['data'][i]['sort']+'</td>' +
                        '<td>'+data['data'][i]['create_time']+'</td>' +
                        '<td class="td-manage">'+
                        '<button type="button" class="layui-btn layui-btn-normal layui-btn-xs" onclick="onUnitInfo('+'\''+data['data'][i]['title']+'\''+','+id+')" ><i class="layui-icon">&#xe705;</i>户型信息</button>'+
                        '<button type="button" class="layui-btn layui-btn-xs" onclick="click_edit(\'编辑\',\'/xiamenyyhoutai/xcxbuilding/unit_edit\','+id+',\'680\',\'450\')" ><i class="layui-icon">&#xe642;</i>编辑</button>'+
                        '<button type="button" class="layui-btn-danger layui-btn layui-btn-xs" onclick="click_del('+id+')"><i class="layui-icon">&#xe640;</i>删除</button>'+
                        '</td>' +
                        '</tr>';
                    $("#tbody").append($content);
                }
                $('#my_body').data('curr',curr);
                form.render('checkbox');
                pages(data['count'],curr);
            }else{
                if(curr != 1){
                    getPageData(parseInt(curr-1));
                }else{
                    $("#tbody").empty();
                    $("#tbody").append("<span>您还没有添加数据！！！</span>");
                }
            }
        })
    }
    function pages(allcount,curr) {
        laypage.render({
            elem: 'page'
            ,limit:PAGELIMIT
            ,count: allcount
            ,curr:curr
            ,layout:['prev', 'page', 'next','limit','skip','count']
            ,jump: function (obj, first) {
                if (!first) {
                    PAGELIMIT=obj.limit;
                    getPageData(obj.curr,PAGELIMIT);
                }
            }
        });
    }
    //删除
    function click_del(id){
        layer.confirm('确认要删除吗？',function(){
            ajax("/xiamenyyhoutai/xcxbuilding/unit_del",{id:id},function(res){
                if(res.success){
                    layer.msg('已删除!',{icon:6,time:300},function(){
                        getPageData($('#my_body').data('curr'));
                    });
                }else{
                    layer.msg(res.message,{icon: 5});
                }
            });
        });
    }
    function onUnitInfo(title,id){
        parent.addCardItem(title,"/xiamenyyhoutai/xcxbuilding/door_index?id="+id,id);
    }
</script>