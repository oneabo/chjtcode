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
    <script src="/public/js/admin/area.js" charset="utf-8"></script>
    <style>.layui-input{line-height: 38px;}.myimg{margin-left: 10px;}</style>
</head>
<body>
<div class="x-body">
    <form class="layui-form layui-form-pane">
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>店铺名称
            </label>
            <div class="layui-input-inline">
                <input type="text" id="title" name="title" required="" lay-verify="required" autocomplete="off" maxlength="250" class="layui-input" value="<?=$data['title']?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>省份
            </label>
            <div class="layui-input-inline">
                <select id="province" name="province" lay-filter="province" lay-verify="required" <? if(1 == $data['disabled']) {?> <!--disabled="disabled"--> <? }?> >
                    <option value="">请选择省</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>城市
            </label>
            <div class="layui-input-inline">
                <select id="city" name="city" lay-filter="city" lay-verify="required" <? if(1 == $data['disabled']) {?> <!--disabled="disabled"--> <? }?> >
                    <option value="">请选择市</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">
                <span class="x-red">*</span>地区
            </label>
            <div class="layui-input-inline">
                <select id="area" name="area" lay-filter="area" lay-verify="required">
                    <option value="">请选择县/区</option>
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
    var fileIndex="";
    var areaData = Area;
    layui.use(['form'], function(){
        form = layui.form;
        loadProvince();
        form.on('submit(btn)', function(data){
            var id="<?=$data['id']?>";
            var title=$('#title').val();
            var province=$('#province').find("option:selected").text();
            var city=$('#city').find("option:selected").text();
            var area=$('#area').find("option:selected").text();
            ajax("/xiamenyyhoutai/xcxstore/store_doedit",{id:id,title:title,province:province,city:city,area:area},function(res){
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
    function loadProvince() {
        var proHtml = '';
        for (var i = 0; i < areaData.length; i++) {
            if(areaData[i].provinceName=="<?=$data['province']?>"){
                proHtml += '<option value="' + areaData[i].provinceCode + '_' + areaData[i].mallCityList.length + '_' + i +'" selected>' + areaData[i].provinceName + '</option>';
                loadCity(areaData[i].mallCityList);
            }else{
                proHtml += '<option value="' + areaData[i].provinceCode + '_' + areaData[i].mallCityList.length + '_' + i +'">' + areaData[i].provinceName + '</option>';
            }
        }
        //初始化省数据
        $('form').find('select[name=province]').append(proHtml);
        form.render();
        form.on('select(province)', function(data) {
            $('form').find('select[name=area]').html('<option value="">请选择县/区</option>').parent().hide();
            var value = data.value;
            var d = value.split('_');
            var code = d[0];
            var count = d[1];
            var index = d[2];
            if (count > 0) {
                loadCity(areaData[index].mallCityList);
            } else {
                $('form').find('select[name=city]').parent().hide();
            }
        });
    }
    //加载市数据   '_' + citys[i].mallAreaList.length + '_' + i +
    function loadCity(citys) {
        var cityHtml = '';
        for (var i = 0; i < citys.length; i++) {
            if(citys[i].cityName=="<?=$data['city']?>"){
                cityHtml += '<option value="' + citys[i].cityCode + '_' + citys[i].mallAreaList.length + '_' + i +'" selected>' + citys[i].cityName + '</option>';
                loadArea(citys[i].mallAreaList);
            }else{
                cityHtml += '<option value="' + citys[i].cityCode + '_' + citys[i].mallAreaList.length + '_' + i +'">' + citys[i].cityName + '</option>';
            }
        }
        $('form').find('select[name=city]').html(cityHtml).parent().show();
        form.render();
        form.on('select(city)', function(data) {
            var value = data.value;
            var d = value.split('_');
            var code = d[0];
            var count = d[1];
            var index = d[2];
            if (count > 0) {
                loadArea(citys[index].mallAreaList);
            } else {
                $('form').find('select[name=area]').parent().hide();

            }
        });
    }
    //加载县/区数据
    function loadArea(areas) {
        var areaHtml = '';
        for (var i = 0; i < areas.length; i++) {
            if(areas[i].areaName=="<?=$data['area']?>"){
                areaHtml += '<option value="' + areas[i].areaCode + '" selected>' + areas[i].areaName + '</option>';
            }else{
                areaHtml += '<option value="' + areas[i].areaCode + '">' + areas[i].areaName + '</option>';
            }
        }
        $('form').find('select[name=area]').html(areaHtml).parent().show();
        form.render();
        form.on('select(area)', function(data) {
        });
    }
</script>