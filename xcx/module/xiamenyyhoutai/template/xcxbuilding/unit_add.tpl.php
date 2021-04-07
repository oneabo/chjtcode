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
    <style>.layui-input{line-height: 38px;}.myimg{margin-left: 10px;}</style>
</head>
<body>
<div class="x-body">
    <form class="layui-form layui-form-pane">
        <input type="hidden" name="floor_id" value="<?=$id;?>">
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>单元
            </label>
            <div class="layui-input-inline">
                <input type="text" name="title" lay-verify="required" placeholder="如：“ 1单元 ”" autocomplete="off" class="layui-input">
            </div>
            <label class="layui-form-label">
                <span class="x-red"></span>排序
            </label>
            <div class="layui-input-inline">
                <input type="number" name="sort" min="0" value="0" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>楼层
            </label>
            <div class="layui-input-inline">
                <select name="floor_number" lay-verify="required">
                    <?php for($i=1;$i<=50;$i++){?>
                        <option value="<?=$i?>"><?=$i?>层</option>
                    <?php } ?>
                </select>
            </div>
            <label class="layui-form-label">
                <span class="x-red">*</span>梯户比
            </label>
            <div class="layui-input-inline">
                <select name="stairs_number" lay-verify="required">
                    <?php for($i=1;$i<=30;$i++){?>
                        <option value="<?=$i?>"><?=$i?>户</option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="layui-form-item" style="text-align:center;">
            <button id="btn" type="button" class="layui-btn" lay-filter="btn" lay-submit>保存</button>
        </div>
    </form>
</div>
</body>
</html>
<script>
    layui.use(['form'], function(){
        var form = layui.form;
        form.on('submit(btn)', function(data){
            ajax("/xiamenyyhoutai/xcxbuilding/unit_doadd",data.field,function(res){
                if(res.success){
                    parent.layer.alert("保存成功", {icon: 6},function (index2) {
                        parent.getPageData(parent.$('#my_body').data('curr'));
                        parent.layer.close(index2);
                        var index = parent.layer.getFrameIndex(window.name);
                        parent.layer.close(index);
                    });
                }else{
                    parent.layer.msg(res.message,{icon: 5});
                }
            });
        });
    });

</script>