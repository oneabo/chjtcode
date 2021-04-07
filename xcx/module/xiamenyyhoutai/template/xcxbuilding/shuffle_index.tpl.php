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
<div id="my_body" class="x-body">
    <form class="layui-form x-center" style="width:100%" action="javascript:void(0);">
        <div class="layui-form-pane" style="margin-top: 15px;">
            <div class="layui-form-item">
                <label class="layui-form-label">标题</label>
                <div class="layui-input-inline">
                    <input class="layui-input" id="title">
                </div>
                <div class="layui-input-inline" style="width:80px">
                    <button class="layui-btn" onclick="getPageData();"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
        </div>
    </form>
    <xblock>
        <button class="layui-btn" onclick="click_edit('添加','/xiamenyyhoutai/xcxbuilding/shuffle_add',<?=$data['id']?>,'680','450');"><i class="layui-icon">&#xe608;</i>添加</button>
    </xblock>
    <table class="layui-table layui-form">
        <thead>
        <tr>
            <th>ID</th>
            <th>图片</th>
            <th>标题</th>
            <th>地址</th>
            <th>排序</th>
            <th>状态</th>
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
        form.on('switch(status)', function(data){
            var id=data.value;
            var status=data.elem.checked==true?1:0;
            ajax("/xiamenyyhoutai/xcxbuilding/shuffle_status",{id:id,status:status},function(res){
                if(!res.success){
                    layer.msg('状态修改失败', {icon: 5});
                    $(data.elem).prop('checked',!$(data.elem).prop('checked'));
                    form.render('checkbox');
                }
            });
        });
    });
    function getPageData() {
        var curr = arguments[0] ? arguments[0] : 1;
        var limit = arguments[1] ? arguments[1] : PAGELIMIT;
        var title=$('#title').val();
        ajax("/xiamenyyhoutai/xcxbuilding/shuffle_page",{id:<?=$data['id']?>,title:title,curr:curr,limit:limit},function (data) {
            if(data.success){
                $("#tbody").empty();
                for(var i in data['data']){
                    var id = data['data'][i]['id'];
                    var status='';
                    if(data['data'][i]['status']==1){
                        status='<input type="checkbox" name="status" lay-text="开启|禁用" lay-filter="status" lay-skin="switch" value="'+id+'" checked>';
                    }else{
                        status='<input type="checkbox" name="status" lay-text="开启|禁用" lay-filter="status" lay-skin="switch" value="'+id+'">';
                    }
                    var $content = '<tr>' +
                        '<td>'+parseInt((curr-1)*PAGELIMIT+parseInt(i)+1)+'</td>' +
                        '<td><img class="my-detail-img" src="'+data['data'][i]['img']+'" alt="暂无图片" /></td>' +
                        '<td>'+data['data'][i]['title']+'</td>' +
                        '<td>'+data['data'][i]['url']+'</td>' +
                        '<td>'+data['data'][i]['sort']+'</td>' +
                        '<td>'+status+'</td>' +
                        '<td>'+data['data'][i]['create_time']+'</td>' +
                        '<td class="td-manage">'+
                        '<button type="button" class="layui-btn layui-btn layui-btn-xs" onclick="click_edit(\'编辑\',\'/xiamenyyhoutai/xcxbuilding/shuffle_edit\','+id+',\'680\',\'450\')" ><i class="layui-icon">&#xe642;</i>编辑</button>'+
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
            ajax("/xiamenyyhoutai/xcxbuilding/shuffle_del",{id:id},function(res){
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
</script>