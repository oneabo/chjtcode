<?php
namespace app\common\traits;

use think\Exception;


/**
 * 异步协程请求
 * Trait asyncHttp
 * @package app\common\lib\smsapi\module
 */
trait News{
    //久九房数据跟新到新的九房数据
   public static $cityArr = [
       0=>[
           'name'      =>'厦门市',
           'city_no'   => 350200
       ],
       10=>[
            'name'      =>'厦门市',
            'city_no'   => 350200
       ],
       11=>[
           'name'      =>'漳州市',
           'city_no'   => 350600
       ],
       12=>[
           'name'      =>'泉州市',
           'city_no'   => 350500

       ],
       13=>[
           'name'      =>'龙岩市',
           'city_no'   => 350800
       ],
       591=>[
           'name'      =>'福州市',
           'city_no'   => 350100
      ]

   ];
   public function oldNewstoNewNews($old_data){
       if($old_data['flag'] ){
           $flag_list = explode(',',$old_data['flag'] );
           in_array('i',$flag_list) && $is_index = 1;
       }
       $data  =[
         'name'             => $old_data['title'],
         'old_id'           => $old_data['id'],
         'title'            => $old_data['title'],
         'keyword'          => $old_data['keyword'],
         'description'      => $old_data['description'],
         'context'          => htmlspecialchars_decode($old_data['content']),
         'resource_type'    => 1,
         'num_read'         => $old_data['click'],
         'order_type'       => !empty($old_data['pic']) ? 1:0,
         'release_time'     => strtotime($old_data['addtime']),
         'status'           => $old_data['status'],
         'sort'             => 50,
         'img_path'         => !empty($old_data['pic']) ? json_encode([['name'=>'index','url'=>'/upload/images/old/'.$old_data['pic']]]) :json_encode([]),
         'has_comment'      => 1,
         'source_type'      => 1,
         'source_id'        => 1,//默认admin 发布
         'author'           => $old_data['source'],//默认admin 发布
         'update_time'      => time(),//
         'create_time'      => time(),//
         'is_top'           => 0,//
         'is_index'         => empty($is_index) ? 0:1,//
         'city_list'        => self::$cityArr[$old_data['area_id']]['city_no']

       ];

       $cate_id  = [];
       $is_propert_news  = 0;
       $lable = '';
       $is_is_original = 0;
       $lable_string =[];
       switch ( $old_data['type'] ){ //本地新闻
           case '1':
               $lable  .= '29'.',';
               $lable_string[] = '本地';
               $cate_id[] =11 ;break;
           case '2': '';
               if($old_data['belong'] == 11){
                   $is_propert_news  =1;
                   $data['oldforid'] = $old_data['property_id'];
                   $lable  .= '25'.',';
                   $lable_string[] = '小编踩盘';
               }elseif ($old_data['belong'] == 12){
                   $lable  .= '26'.',';
                   $lable_string[] = '政策解读';


               }elseif ($old_data['belong'] == 9){
                   $lable  .= '24'.',';
                   $lable_string[] = '土地拍卖';

               }elseif ($old_data['belong'] == 13){
                   $lable  .= '27'.',';
                   $lable_string[] = '楼盘预告';

               }elseif ($old_data['belong'] == 15) {
                   $is_is_original = 1;
                   $lable    = '36'.',';
               }elseif ($old_data['belong'] == 14) {
                   $lable  .= '28'.',';
                   $lable_string[] = '最新城建';
               }elseif ($old_data['belong'] == 19 ){
                   $lable  .= '42'.',';
                   $lable_string[] = '二手房资讯';
               }elseif ($old_data['belong'] == 0 ){
                   $lable  .= '30'.',';
                   $lable_string[] = '房产';
               }

               $cate_id[] = 12;break;
           case '4': '';//楼盘资讯
              if($old_data['property_id'] !=0){
                  $cate_id[] = 20;
                  $is_propert_news =1;
                  $data['oldforid'] = $old_data['property_id'];
                  $lable  .= '30'.',';
                  $lable_string[] = '房产';
              }else{ //不存在房产id的 直接归类到房产去
                  $lable  .= '30'.',';
                  $is_propert_news =0;
                  $lable_string[] = '房产';
                  $cate_id[] = 12;
              }
                break;
           case '5' :
               if($old_data['belong'] == 6){
                   $lable  .= '38'.',';
                   $lable_string[] = '新房日分析';
               }elseif ($old_data['belong'] == 7){
                   $lable  .= '39'.',';
                   $lable_string[] = '新房周分析';


               }elseif ($old_data['belong'] == 8){
                   $lable  .= '40'.',';
                   $lable_string[] = '新房月分析';

               }elseif ($old_data['belong'] == 17){
                   $lable  .= '41'.',';
                   $lable_string[] = '二手房分析';
               }
               $cate_id[] = 12;break;

           case '10': '';
               $lable  .= '29'.',';
               $lable_string[] = '本地';
               $cate_id[] = 11;break;
           case '18': //其他归类到本地
               $lable  .= '29'.',';
               $lable_string[] = '本地';
               $cate_id[] = 11;break;
       }
       $data['is_propert_news'] = $is_propert_news;
       $data['oldforid']        = $data['oldforid'] ?? '';
       $data['lable']           = trim($lable,',');
       $data['lable_string']    = json_encode($lable_string);
       $data['is_original']     = $is_is_original;
       foreach ($cate_id as $k => $v){
           if($v != 20){
               $data['cate_id'][] =[9,$v];
           }else{
               $data['cate_id'][] =[$v];
           }


       }
       $data['cate_id'] = json_encode($data['cate_id']);
       return [
           'info'       =>$data,
           'cate_id'    =>$cate_id,
       ];
   }
}
