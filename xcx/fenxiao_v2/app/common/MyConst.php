<?php
namespace app\common;

class MyConst{
    const STATUS_NORMAL = 1;//正常
    const STATUS_DISABLED = 1;//已禁用
    const STATUS_DELETE = 1;//已删除
    public static function getUserTypes(){
        return array('普通用户', '淘房师');
    }

    /**
     * 主题模块
     */
    const MODULE_LIST = [
        'banner' => 'banner图',
        'estates_new'=>'新房',
        'news'=>'新闻',
        'coupon'=>'优惠劵',
        'liveRoom'=>'直播间',
        'signup'=>'看房报名',
        'subject'=>'主题专题',
        'label' => '标签'
    ];

    //新房销售状态
    const ESTATESNEW_SALE_STATUS = [
        '1'=> '待售',
        '2'=> '在售',
        '3'=> '售罄',
        '4'=> '尾盘',
    ];
    //楼盘相册类别
    const BUILDINGPHOTOS_CATEGORYS = [
        '1'=> '效果图',
        '2'=> '实景图',
        '3'=> '样板间',
        '4'=> '区位',
        '5'=> '小区配套',
        '6'=> '项目现场',
        '7'=> '楼栋',
        '8'=> '预售许可证',
        '9'=> '视频看房',
        '10' => '交通图',
        '11' => '楼盘封面',
    ];
    //建筑用途
    const HOUSE_PURPOSE = [
        '1'=> '住宅',
        '2'=> '别墅',
        '3'=> '商住',
        '4'=> '写字楼',
        '5'=> '公寓',
        '6'=> '车位',
        '7'=> '花园住宅',
        '8'=> '地皮商铺',
    ];

    const HOUSE_PURPOSE_OBJECT = [
        ['id' => 1,'name' => '住宅'],
        ['id' => 2,'name' => '别墅'],
        ['id' => 3,'name' => '商住'],
        ['id' => 4,'name' => '写字楼'],
        ['id' => 5,'name' => '公寓'],
        ['id' => 6,'name' => '车位'],
        ['id' => 7,'name' => '花园住宅'],
        ['id' => 8,'name' => '地皮商铺'],
    ];
    //朝向
    const ORIENTATION = [
        '1'=> '东',
        '2'=> '南',
        '3'=> '西',
        '4'=> '北',
        '5'=> '南北',
        '6'=> '东西',
        '7'=> '东南',
        '8'=> '东北',
        '9'=> '西南',
        '10'=> '西北',
    ];
    //几居室
    const ROOMS=[
        '1'=> '一室',
        '2'=> '两室',
        '3'=> '三室',
        '4'=> '四室',
        '5'=> '五室',
        '6'=> '五室+',
        '7'=> '别墅',
    ];
    //栏目名称标识
    const CLOUMNFLAG =[
       [
           'id'         =>'shipin',
           'name'       => '视频',
           'col_id'     => 13
       ] ,
        [
            'id'     =>'zixuan',
            'name'  => '资讯',
            'col_id'     => 9
        ] ,
    ];

    const JIUFANG_LOGIN = 'jiufang_login:'; //redis-登录时候的key加文件夹
    const TAG_REDIS = 'tag_redis'; //标签redis
    const FEATURE_TAG = 'feature_tag'; //特色标签key

    /**
     * Redis
     * estates 楼盘相关
     * list 榜单
     * condition 条件列表
     * interest_rate 房贷利率
     * 
     */
    const ESTATES_LIST_POPULAR = 'estates:list:popular';// 人气榜
    const ESTATES_LIST_SEARCH = 'estates:list:search';// 热搜榜
    const ESTATES_CONDITION = 'estates:condition:';// 条件列表
    const ESTATES_INTEREST_RATE = 'estates:interest_rate';// 房贷利率
    const NEWS_HOS_LIST = '9H:news:hos:list';// 热讯榜

    const WX_H5 = 'wxh5';//登录类别
    const H5 = 'h5';
    const NEWS_ADV_READING_FLAG = '9H:news_adv_reading_falg';//用户阅读广告位置
    const NEWS_ADV_INFO_FLAG = '9H:estates_adv_info_flag';//用户详情广告位置
    const ESTATES_ADV_READING_FLAG = '9H:estates_adv_reading_flag';//用户阅读广告位置-楼盘广告

    const WX_SETTING = '9H:wx_setting';//微信配置保存

    const NEW_HOUSE = 1;//新房
    const SECOND_HAND_HOUSING = 2;//二手房

    /**
     * 短信验证码
     */
    const MSG_CODE = 'msg_code:';// 验证码存储位置
    const MSG_CODE_LOGIN = 'msg_code:login:';// 登陆用
    const MSG_CODE_SIGN = 'msg_code:sign:';// 报名用
    const MSG_CODE_SIGN_DISCOUNT = 'msg_code:sign_discount:';// 报名用

    //广告图
    const MY_AD = 'h5_my_ad';
    const IMG_YES = 2;//有图片
    const IMG_NO = 1; //没有图片

    const HOUSE_INSPECTION_NO = '考虑看房';
    const HOUSE_INSPECTION_YES = '已看过房';
    const WXXCX_SESSION_KEY    = '9H:wxxcx_sessionkey';
    const ROLE_EDIT_FLAG       = '9h:edit_flag_role'; //权限是否跟新标识


}