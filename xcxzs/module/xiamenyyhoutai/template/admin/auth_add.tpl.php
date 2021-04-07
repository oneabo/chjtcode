<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>
        X-admin v1.0
    </title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="/public/css/admin/x-admin.css?t=<?php echo JsVer;?>" media="all">
</head>
<body>
<div class="x-body">
    <form class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red"></span>菜单
            </label>
            <div class="layui-input-inline" style="width:500px;">
                <select name="menu_id">
                    <?php foreach($menu as $v){?>
                        <option value="<?php echo $v['id'];?>"><?php echo $v['name'];?></option>
                    <?php }?>
                </select>
            </div>
        </div>
		<div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red"></span>控制器
            </label>
            <div class="layui-input-inline" style="width:500px;">
                <input type="text" name="controller" autocomplete="off" class="layui-input">
            </div>
        </div>
		<div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red"></span>控制器名称
            </label>
            <div class="layui-input-inline" style="width:500px;">
                <input type="text" name="controller_name" autocomplete="off" class="layui-input">
            </div>
        </div>
		<div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red"></span>方法
            </label>
            <div class="layui-input-inline" style="width:500px;">
                <input type="text" name="action" autocomplete="off" class="layui-input">
            </div>
        </div>
		<div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red"></span>方法名称
            </label>
            <div class="layui-input-inline" style="width:500px;">
                <input type="text" name="action_name" autocomplete="off" class="layui-input">
            </div>
        </div>
		<div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red"></span>状态
            </label>
            <div class="layui-input-inline" style="width:500px;">
                <select name="status">
					<option value="1">启用</option>
					<option value="0">禁用</option>
				</select>
            </div>
        </div>
        <div class="layui-form-item" style="text-align:center;">
            <button  class="layui-btn" lay-filter="btn" lay-submit="">
                保存
            </button>
        </div>
    </form>
</div>
<script src="/public/js/layui/layui.js" charset="utf-8"></script>
<script src="/public/js/admin/x-layui.js" charset="utf-8"></script>
<script>
    layui.use(['form','layer','upload'], function(){
        $ = layui.jquery;
        var form = layui.form() ,layer = layui.layer;
        //监听提交
        form.on('submit(btn)', function(data){
            //发异步，把数据提交给php
            $.ajax({
                type: 'POST',
                url: "/xiamenyyhoutai/admin/auth_addsave",
                data: data.field,
                dataType: "json",
                success: function(res){
                    if(res.ajax_error){
                        layer.msg('您没有操作权限!',{icon: 2,time:1500});return false;
                    }
                    if(res.success){
                        layer.alert("保存成功", {icon: 6},function () {
                            // 获得frame索引
                            var index = parent.layer.getFrameIndex(window.name);
                            //关闭当前frame
                            parent.layer.close(index);
							parent.location.reload();
                        });
                    }else{
                        layer.alert("保存失败", {icon: 5});
                    }
                },
                error:function(res){
                    layer.alert("请求失败", {icon: 2});
                }
            });
            return false;
        });
    });
</script>
</body>
</html>