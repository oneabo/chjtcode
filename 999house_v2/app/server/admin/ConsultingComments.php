<?php
namespace app\server\admin;

use app\common\traits\TraitInstance;
use app\common\base\ServerBase;
use think\Db;
use think\Exception;

class ConsultingComments extends ServerBase
{
    /**
     * 根据id获取评论统计
     */
    public static $table = 'consulting_comments';
    public function getCountById($id,$pid){
    //todo 改成redis
    if(is_string($id) ) {
            $list =  $this->db->name(self::$table)
                    ->where('article_id','=',$id)
                    ->where('status','=',1)
                    ->where('cate_pid','=',$pid)
                    ->count() ?? 0;
    }else{
           $list = $this->db->name(self::$table)
               ->where('article_id','in',$id)
               ->field('count(id) as count,article_id')
               ->where('status','=',1)
               ->where('cate_pid','=',$pid)
               ->group('article_id')
               ->select()->toArray();
    }

    return $list;
    }


}