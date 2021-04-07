<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [
        '999admin'  =>  'admin',  //后台映射
    ],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['common','server'],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,

    //域名
    'domain_name' => 'http://act.999house.com',//'http://bobing.test.com',
    //前端
    'index_name'  => 'http://act.999house.com/moon2/index.html',//moon.999house.com/moon/index.html?merch_id=kJR9dO&activities_id=0dB6d2

    'wxH5config' => [
        'token' => '5fb0e697f079010c926803370dd836ff',
        'wxAppId' => 'wx699388855425afac',
        'wxAppSecret' => 'efc48d74046a041e762cd7751a4648b1',
        'uri' => 'http://chfx.999house.com/?m=agentapi/PublicAjax/requestProxyPass',
    ],
    'miniconfig' =>[
            'appid'     => 'wx16b7695f814f1aaf',
            'secret'    => 'eab7c7f2314e3371338ed6582d385023',
    ]
];
