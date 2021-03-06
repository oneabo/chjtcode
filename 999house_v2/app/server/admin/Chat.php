<?php

namespace app\server\admin;

use app\common\manage\TaskManage;
use app\task\SystemMsg;
use Exception;
use app\common\base\ServerBase;


/*
 * 公用城市操作
 * */
class Chat extends ServerBase
{
    /**
     *
     */
    public function dialogueList($user_id)
    {

        if (empty($user_id)) {
            return false;
        }

        $sql_string = "select 	        user_id,
                                    id,
                                    to_type,
                                    update_time,
                                     headimgurl,
                                     nickname from  (SELECT
                                    cd.to_id as user_id,
                                    cd.id,
                                    cd.to_type,
                                    cd.update_time,
                                    u.groop_ico as headimgurl,
                                    u.group_name as nickname
                                FROM
                                    9h_chat_dialogue AS cd
                                    LEFT JOIN 9h_chat_group AS u ON u.id = cd.to_id 
                                WHERE
                                    cd.to_id  in  (select group_id from 9h_chat_group_user where user_id={$user_id})
                                    AND to_type = 2 UNION ALL
                                SELECT
                                    cd.to_id as user_id,
                                      cd.id,
                                    cd.to_type,
                                    cd.update_time,
                                    if(u.user_avatar='' || u.user_avatar=null,u.headimgurl,u.user_avatar) as headimgurl,
                                    if(u.user_name ='' || u.user_name =null ,u.nickname,u.user_name) as nickname
                                FROM
                                    9h_chat_dialogue AS cd
                                    LEFT JOIN 9h_user AS u ON u.id = cd.to_id 
                                WHERE
                                    user_id = {$user_id}
                                    AND to_type = 1 UNION ALL
                                SELECT
                                cd.user_id as user_id,
                                    cd.id,
                                    cd.to_type,
                                    cd.update_time,
                                    if(u.user_avatar='' || u.user_avatar=null,u.headimgurl,u.user_avatar) as headimgurl,
                                    if(u.user_name ='' || u.user_name =null ,u.nickname,u.user_name) as nickname
                                FROM
                                    9h_chat_dialogue AS cd
                                    LEFT JOIN 9h_user AS u ON u.id = cd.user_id 
                                WHERE
                                    to_id = {$user_id} 
                                    AND to_type = 1) as tab1 ORDER BY update_time desc  ";

        $list = $this->db->query($sql_string);

        return $list;
    }

    public function getChatListByUser($dialogue_id)
    {
        $list = $this->db->name('chat')->alias('c')
            ->leftJoin('user su', 'su.id=c.send_user_id')
            ->leftJoin('user tu', 'tu.id=c.to_user_id')
            ->field("	IF(su.user_name='' || su.user_name=null  , su.nickname ,su.user_name  ) AS send_name,
                            If( tu.user_name='' ||tu.user_name=null  , tu.nickname,tu.user_name ) AS to_name,
                            If( su.user_avatar= '' || su.user_avatar=null, su.headimgurl,su.user_avatar ) AS send_head,
                            if( tu.user_avatar='' || tu.user_avatar=null , tu.headimgurl, tu.user_avatar ) AS tu_head,
                            c.msg_type,
                            c.msg,
                            c.msg_url,
                            c.send_user_id,
                            c.to_user_type,
                            c.send_time")
            ->where('c.chat_dialogue_id', '=', $dialogue_id)
            ->order('c.id asc')
            ->select();
        if (!empty($list)) {
            $list = $list->toArray();
        } else {
            $list = [];
        }

        return $list;

    }

    public function addChatGroup($group_id, $user_id)
    {
        if (empty($group_id) || empty($user_id)) {
            return $this->responseFail('参数错误');
        }
        $info = $this->db->name('chat_group_user')->where('group_id', '=', $group_id)
            ->where('user_id', '=', $user_id)->find();

        if (!empty($info)) {
            return $this->responseFail('你已经在群里面了哦');
        }

        $data = [
            'user_id' => $user_id,
            'group_id' => $group_id,
            'update_time' => time(),
            'create_time' => time()
        ];

        $result = $this->db->name('chat_group_user')->insert($data);

        if ($result === false) {
            return $this->responseFail();
        } else {
            return $this->responseOk();
        }
    }

    public function leaveChatGroup($group_id, $user_id)
    {
        if (empty($group_id) || empty($user_id)) {
            return $this->responseFail('参数错误');
        }
        $info = $this->db->name('chat_group_user')->where('group_id', '=', $group_id)
            ->where('user_id', '=', $user_id)->find();
        if (empty($info)) {
            return $this->responseFail('已经退出群聊无需重复退出');
        }


        $result = $this->db->name('chat_group_user')->where('user_id', '=', $user_id)
            ->where('group_id', '=', $group_id)->delete();

        if ($result === false) {
            return $this->responseFail();
        } else {
            return $this->responseOk();
        }
    }

    /**
     * 获取最后一条聊天纪录
     * @param $dialogue_ids
     */
    public function getLastMsgByDialogue($dialogue_ids)
    {
        if(empty( $dialogue_ids) ){
            return $this->responseFail();
        }

        if(is_array($dialogue_ids)){
            $list =  $this->db->name('chat')->field('id,msg,msg_url,msg_type,chat_dialogue_id')->where('id','in',function($query) use ($dialogue_ids){
                    $query->name('chat')->where('chat_dialogue_id','in',$dialogue_ids)->field('max(id) as id')->group('chat_dialogue_id');
            })->select();
        }else{
            $list =  $this->db->name('chat')->field('id,msg,msg_url,msg_type,chat_dialogue_id')->where('id','=',function($query) use ($dialogue_ids){
                    $query->name('chat')->where('chat_dialogue_id','=',$dialogue_ids)->field('max(id) as id')->group('chat_dialogue_id');
            })->select();
        }

        return  $this->responseOk($list->toArray());

    }

    /**getChatListBYDialogueId
     * 获取未读消息条数
     */
    public function getNotReadMsgCount($dialogue_ids,$user_id=null){
        if(empty( $dialogue_ids) ){
            return $this->responseFail();
        }
        if(is_array($dialogue_ids)){
            $list =  $this->db->name('chat')->field('id,count(1=1) as not_read,chat_dialogue_id')
                ->where('chat_dialogue_id','in',$dialogue_ids)
                ->where('is_read','=',0)
                ->where('to_user_id','=',$user_id)
                ->group('chat_dialogue_id')->select();
        }else{
            $list =  $this->db->name('chat')->field('id,count(1=1) as not_read,chat_dialogue_id')
                ->where('chat_dialogue_id','=',$dialogue_ids)
                ->where('to_user_id','=',$user_id)
                ->where('is_read','=',0)
                ->group('chat_dialogue_id')->select();
        }
        return  $this->responseOk($list->toArray());
    }

    /**
     * 获取参加群聊的人数
     * @param int $groupid
     */
    public function getGroupCount($groupid =-1,$is_index=0){
        $where[] = ['g.status','=',1];
        if(empty($groupid)){
            $this->responseFail();
        }
        if($groupid !=-1){
           if(is_array($groupid)){
               $where[] = ['gu.group_id','in',$groupid];
           }else{
               $where[] = ['gu.group_id','=',$groupid];
           }
        }

        if($is_index ==1){
            $where[] = ['g.is_index','=',1];
        }
        $data = $this->db->name('chat_group_user')->alias('gu')
            ->leftJoin('user u','u.id=gu.user_id')
            ->leftJoin('chat_group g','gu.group_id=g.id')
            ->field('g.group_name,g.groop_ico,g.id,GROUP_CONCAT(if(isnull(u.headimgurl) || u.headimgurl="",u.user_avatar, u.headimgurl)) as user_head,count(1=1) as user_count')
            ->where($where)
            ->group('gu.group_id')
            ->select();
        if(!empty($data)){
            return $this->responseOk($data->toArray());
        }
        return $this->responseOk([]);

    }

    /**
     * 获取
     */
    public function getChatListByMsg($search,$user_id){
        if(empty($search)){
           return $this->responseFail('搜索内容不能为空');
        }
        $list = $this->db->name('chat')->alias('c')
                                             ->leftJoin('user su','su.id=c.send_user_id')
                                             ->leftJoin('user tu','tu.id=c.to_user_id')
                                             ->field('	IF(su.user_name=""|| su.user_name=null  , su.nickname ,su.user_name  ) AS send_name,
                                                            If( tu.user_name="" ||tu.user_name=null  , tu.nickname,tu.user_name ) AS to_name,
                                                            If( su.user_avatar= "" || su.user_avatar=null, su.headimgurl,su.user_avatar ) AS send_head,
                                                            if( tu.user_avatar="" || tu.user_avatar=null , tu.headimgurl, tu.user_avatar ) AS tu_head,
                                                            c.chat_dialogue_id,c.msg,c.send_time,c.id as chat_id')
                                             ->where('group_id','=',0)
                                             ->whereLike('msg',"%{$search}%")
                                             ->whereRaw("send_user_id=:user_id OR to_user_id=:to_id",['user_id'=> $user_id,'to_id'=>$user_id])
                                             ->select();
        if(!empty($list)){
            return  $this->responseOk($list->toArray());
        }else{
            return  $this->responseFail([]);
        }

    }

    public function getChatListByPage($data){

        $wheres = [];
        $order  = '';
        if(!empty($data['type']) && !empty($data['chat_id']) ){
            if($data['type'] =='next'){
                $wheres[] = ['c.id','>=',$data['chat_id']];
                $order= 'c.id asc';
            }else{
                $wheres[] = ['c.id','<=',$data['chat_id']];
                $order= 'c.id desc';
            }
        }
        $list = $this->db->name('chat')->alias('c')
            ->leftJoin('user su','su.id=c.send_user_id')
            ->leftJoin('user tu','tu.id=c.to_user_id')
            ->field('	IF(su.user_name=""|| su.user_name=null  , su.nickname ,su.user_name  ) AS send_name,
                                                            If( tu.user_name="" || tu.user_name=null  , tu.nickname,tu.user_name ) AS to_name,
                                                            If( su.user_avatar= "" || su.user_avatar=null, su.headimgurl,su.user_avatar ) AS send_head,
                                                            if( tu.user_avatar="" || tu.user_avatar=null , tu.headimgurl, tu.user_avatar ) AS tu_head,
                                                            c.chat_dialogue_id,c.msg,c.send_time,c.id,c.msg_url,c.msg_type,c.send_user_id')
            ->where('c.chat_dialogue_id','=',$data['dialogue_id'])
            ->where($wheres)
            ->order('c.id desc')
            ->paginate($data['pageSize']);

        if ($list->isEmpty()) {
            $result['list'] = [];
            $result['total'] = 0;
            $result['last_page'] = 0;
            $result['current_page'] = 0;
        } else {
            $list = $list->toArray();
            $result['total'] = $list['total'];
            $result['last_page'] = $list['last_page'];
            $result['current_page'] = $list['current_page'];
            $result['list'] = $list['data'];
        }

        return  $this->responseOk($result);
    }

    public function addFriend($user_id,$f_user_id,$lastPy='QA'){
        if(empty($user_id) || empty($f_user_id)){
            return $this->responseFail('参数错误');
        }
        $res  = $this->db->name('chat_friend')->where('user_id','=',$user_id)
                     ->where('friend_user_id','=',$f_user_id)->find();

        if(!empty($res)){
            return  $this->responseFail('已经是好友关系了不需要重复添加哦');
        }
        $dialogue_id = 0;
        $dialogue = $this->db->name('chat_dialogue')->whereRaw('user_id=:user_id and to_id=:to_id',['user_id'=>$user_id,'to_id'=>$f_user_id])
            ->whereOrRaw('user_id=:to_id and to_id=:user_id',['user_id'=>$user_id,'to_id'=>$f_user_id])
            ->field('id')
            ->find();

        if(!empty($dialogue)){
            $dialogue_id=$dialogue['id'];
        }
        $data  = [
            'user_id'           => $user_id,
            'friend_user_id'    => $f_user_id,
            'create_time'       => time(),
            'update_time'       => time(),
            'py_group'          => $lastPy,
            'dialogue_id'       => $dialogue_id
        ];



        $result  = $this->db->name('chat_friend')->insert($data);
        //判断是否存在会话

        if($result === false){
            return $this->responseFail();
        }

        return  $this->responseOk();
    }

    public function getFriendList($data){

        if(empty($data['user_id'])){
           return $this->responseFail('用户id不能为空');
        }

        $list = $this->db->name('chat_friend')->alias('cf')
                ->leftJoin('user u','cf.friend_user_id=u.id')
                ->field('cf.id,cf.user_id,cf.py_group,cf.friend_user_id,If( u.user_name="" ||u.user_name=null  , u.nickname,u.user_name ) AS friend_name,
                          If( u.user_avatar= "" || u.user_avatar=null, u.headimgurl,u.user_avatar ) AS friend_head,cf.dialogue_id')
                ->where('cf.user_id','=',$data['user_id'])
                ->paginate($data['pageSize']);


        if ($list->isEmpty()) {
            $result['list'] = [];
            $result['total'] = 0;
            $result['last_page'] = 0;
            $result['current_page'] = 0;
        } else {
            $list = $list->toArray();
            $result['total'] = $list['total'];
            $result['last_page'] = $list['last_page'];
            $result['current_page'] = $list['current_page'];
            $result['list'] = $list['data'];
        }

        return $this->responseOk($result);
    }

    /**
     * 添加系统消息
     */
    public function addSyetemMsg($data,$users=null){
        $msg_data = [
            'id'              => $data['id']  ,
            'title'           => $data['title'],
            'contxt'          => $data['context'],
            'estate_id'       => $data['estate_id'],
            'status'          => $data['status'],
            'cover'          =>  $data['cover'],
            'chat_type'       => $data['chat_type'],
            'sub_context'     => $data['sub_context'],
            'update_time'     => time(),
            'create_time'     => time(),
        ];
        if(empty($data['title']) ||  empty($data['sub_context']) || empty($data['chat_type'])){
            return $this->responseFail('关键参数不能为空');
        }

        $result = $this->db->name('chat_system_msg')->insert($msg_data,true);
        if($result  === false){
            return $this->responseFail();
        }

        $msg_data['id'] = $result;
        if( !empty($users) ){
            $msg_data['type']       = 2; //发送指定得用户
            $msg_data['user_list']  = $users;
        }else{
            $msg_data['type'] = 1; //全部发送
        }
        $msg_data['name'] = empty($data['name']) ? '':$data['name'];
        TaskManage::getInstance()->asyncPost($msg_data,SystemMsg::class); //将任务投递给 异步task 进程处理

        return $this->responseOk();
    }


    public function editSyetemMsg($data){
        $msg_data = [
            'id'              => $data['id']  ,
            'title'           => $data['title'],
            'contxt'          => $data['context'],
            'status'          => $data['status'],
            'cover'          => $data['cover'],
            'chat_type'       => $data['chat_type'],
            'sub_context'     => $data['sub_context'],
            'update_time'     => time(),
            'create_time'     => time(),
        ];

        if(empty($data['title']) || empty($data['context']) || empty($data['sub_context']) || empty($data['chat_type'])){
            return $this->responseFail('关键参数不能为空');
        }

        $result = $this->db->name('chat_system_msg')->where('id','=',$msg_data['id'])->update($msg_data);
        if($result  === false){
            return $this->responseFail();
        }

        return $this->responseOk();

    }

    /**
     * 将消息设置为已读
     */
    public function setMsgReadStatus($chat_id,$user_id){
      if(empty($chat_id) || empty($user_id)){
          return $this->responseFail();
      }
      $result =   $this->db->name('chat')->where('chat_dialogue_id','=',$chat_id)->where('to_user_id','=',$user_id)->update(['is_read'=>1]);
      if($result===false){
          return $this->responseFail();
      }
      return  $this->responseOk();
    }

    /**
     * @param $data
     *
     */
    public function addSyetemMsgByUser($data,$user_id){
        $inser_data  = [
            'system_msg_id' => $data['id'],
            'is_read'       => 2,
            'user_id'       => $user_id,
            'create_time'   => time(),
            'update_time'   => time(),
        ];
        $this->db->name('chat_stytem_msg_user')->insert($inser_data);
    }

    public function getSystemList($serach){
        $where = [];
        if(!empty($serach['title']) ){
            $where[] = ['title','like',"{$serach['title']}"];
        }
        if(!empty($serach['chat_type']) && $serach['chat_type'] !=-1){
            $where[] = ['chat_type','=',"{$serach['chat_type']}"];
        }
        $list   = $this->db->name('chat_system_msg')-> where($where)->paginate($serach['pageSize']);
        if ($list->isEmpty()) {
            $result['list'] = [];
            $result['total'] = 0;
            $result['last_page'] = 0;
            $result['current_page'] = 0;
        } else {
            $list = $list->toArray();
            $result['total'] = $list['total'];
            $result['last_page'] = $list['last_page'];
            $result['current_page'] = $list['current_page'];
            $result['list'] = $list['data'];
        }

        return $this->responseOk($result);
    }

    /**
     * 根据id获取小程序二维码
     */
    public function getFriendCodeInfo($user_id){

        if(empty($user_id)){
            return [];
        }

       $info = $this->db->name('chat_friend_code')->where('user_id','=',$user_id)->find();

        return $info;
    }

    public function AddFriendCodeInfo($data){
        $indata = [
            'user_id'       => $data['user_id'],
            'friend_url'    => $data['friend_url'],
            'update_time'   => $data['update_time'],
            'create_time'   => $data['create_time'],
        ];

       $result = $this->db->name('chat_friend_code')->insert($indata);

       $result ===false ? false:true;
    }

    /**
     * 根据类型获取聊天纪录
     */
    public function getMsgListBytype($type,$user_id,$pageSize){
        if($type && $user_id){
            $list =  $this->db->name('chat_system_msg')->alias('sm')
                ->leftJoin('chat_stytem_msg_user smu','sm.id=smu.system_msg_id')
                ->where('smu.user_id','=',$user_id)
                ->field('sm.sub_context,sm.update_time,sm.title,sm.id,sm.cover,sm.estate_id,smu.is_read,en.name,en.list_cover,en.logo')
                ->leftJoin('estates_new en','en.id = sm.estate_id')
//                ->where('sm.status','=',1)
                ->where('sm.chat_type','=',$type)
                ->order('sm.update_time desc')
                ->paginate($pageSize);

            if ($list->isEmpty()) {
                $result['list'] = [];
                $result['total'] = 0;
                $result['last_page'] = 0;
                $result['current_page'] = 0;
            } else {
                $list = $list->toArray();
                foreach ($list['data'] as $k=> &$v){
                    $v['is_cover'] = !empty($v['list_cover'] && !empty($v['logo'])) ? 1:0;
                    unset($v['list_cover']);
                    unset($v['logo']);
                }
                $result['total'] = $list['total'];
                $result['last_page'] = $list['last_page'];
                $result['current_page'] = $list['current_page'];
                $result['list'] = $list['data'];
            }

            return $this->responseOk($result);
        }

        return [];
    }
    public function getSysteMsgNotReadCount($type,$user_id){
        if($type && $user_id){
            $count =  $this->db->name('chat_system_msg')->alias('sm')
                ->leftJoin('chat_stytem_msg_user smu','sm.id=smu.system_msg_id')
                ->where('smu.user_id','=',$user_id)
                ->where('smu.is_read','=',2)
                ->where('sm.chat_type','=',$type)
                ->limit(0,20)
                ->order('sm.update_time desc')
                ->select();
//            var_dump($this->db->getLastSql());
            return  empty($count) ? 0 : count($count->toArray());

        }

        return [];
    }
    public function getMsgLastInfo($type,$user_id){
        if($type && $user_id){
           $info =  $this->db->name('chat_system_msg')->alias('sm')
                ->leftJoin('chat_stytem_msg_user smu','sm.id=smu.system_msg_id')
                ->where('smu.user_id','=',$user_id)
                ->field('sm.sub_context,sm.update_time,sm.title,sm.id,smu.is_read')
//                ->where('sm.status','=',1)
                ->where('sm.chat_type','=',$type)
                ->order('sm.update_time desc')
                ->find();
//            var_dump($this->db->getLastSql());
           return  !empty($info) ? $info : [];

        }

        return [];
    }

    public function getSystemMsgInfo($id,$user_id,$is_read=false){
        if($id){
            $info =  $this->db->name('chat_system_msg')->alias('sm')
                ->field('sm.sub_context,sm.update_time,sm.title,sm.id,sm.contxt')
                ->where('sm.status','=',1)
                ->where('sm.id','=',$id)
                ->find();
//            var_dump($this->db->getLastSql());


        }
        if($is_read && !empty($info)){

            $this->db->name('chat_stytem_msg_user')->where('system_msg_id','=',$id)
                ->where('user_id','=',$user_id)->update(['is_read'=>1]);
        }

        return  !empty($info) ? $info : [];
    }

    public function getCountNotRead($user_id){
        $msg_count       = $this->db->name('chat')->where('to_user_id','=',$user_id)->where('is_read','=',0)->count();
        $stytem_count    =$this->db->name('chat_stytem_msg_user')
            ->where('user_id','=',$user_id)
            ->where('is_read','=',2)
            ->order('update_time desc')
            ->limit(0,20)
            ->select();
        return  [
            'msg_count'     => $msg_count,
            'stytem_count'  => empty($stytem_count) ? 0 : count($stytem_count->toArray()),
            'total_count'   => $msg_count + $stytem_count,
        ];
    }

    /**
     * 用户是否有相关楼盘的未读消息
     */
    public function userIsEStatesMsg($user_id,$e_id){
        if(!empty($user_id) && !empty($e_id)){
            $count =  $this->db->name('chat_system_msg')->alias('sm')
                    ->leftJoin('chat_stytem_msg_user smu','sm.id=smu.system_msg_id')
                    ->where('smu.user_id','=',$user_id)
                    ->where('sm.chat_type','=',4)
                    ->where('smu.is_read','=',2)
                    ->where('sm.status','=',1)
                    ->where('sm.estate_id','=',$e_id)
                    ->count();

           return  $count;
        }

    }

    /**
     *
     * @param $user_id
     * @param $e_id
     * @return bool
     * @throws \think\db\exception\DbException
     */
    public function SetReadByuserIsEStatesMsg($user_id,$e_id){
        $result =  $this->db->name('chat_system_msg')->alias('sm')
            ->leftJoin('chat_stytem_msg_user smu','sm.id=smu.system_msg_id')
            ->where('smu.user_id','=',$user_id)
            ->where('sm.chat_type','=',4)
            ->where('smu.is_read','=',2)
            ->where('sm.status','=',1)
            ->where('sm.estate_id','=',$e_id)
            ->update(['smu.is_read'=>1]);
//        echo $this->db->getLastSql();
        return $result ===false ? false :true;
    }

}