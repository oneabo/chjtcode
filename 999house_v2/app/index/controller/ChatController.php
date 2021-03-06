<?php


namespace app\index\controller;


use app\common\base\UserBaseController;
use app\server\admin\Chat;
use app\server\user\User;
use app\websocket\Gateway;
use Co\WaitGroup;
use think\facade\Config;

class ChatController extends UserBaseController
{
    /**
     * 加入群聊
     */
    public function addGroup(){
       $group_id =  $this->request->post('group_id');
       $user_id  = $this->userId;
       if(empty($group_id)){
           return $this->error('请先选择加入的群');
       }
       $server  = new \app\server\admin\Chat();

       $result  = $server->addChatGroup($group_id,$user_id);
       if($result['code'] ==0){
           return $this->error($result['msg']);
       }
        $client_id = Gateway::getClientIdByUid($user_id);
        Gateway::joinGroup($client_id[0],$group_id);
        return $this->success();
    }

    /**
     * 退出群聊
     */
    public function leaveGroup(){
        $group_id =  $this->request->post('group_id');
        $user_id  = $this->userId;
        if(empty($group_id)){
            return $this->error('参数错误');
        }
        $server  = new \app\server\admin\Chat();

        $result  = $server->leaveChatGroup($group_id,$user_id);

        if($result['code'] ==0){
            return $this->error($result['msg']);
        }

        //将对应id移出 群聊
        $client_id = Gateway::getClientIdByUid($user_id);
        Gateway::leaveGroup($client_id[0],$group_id);
        return $this->success();
    }

    /**
     * 获取会话列表
     */
    public function getDialogueList(){
        $user_id    = $this->userId;
        $server  =  new Chat();
        $list    =  $server->dialogueList($user_id);

        $dialogu_ids = [];

        foreach ($list as $v ){
            $dialogu_ids[] = $v['id'];
        }
        $lastMsgList = $server->getLastMsgByDialogue($dialogu_ids);
        $notReadList = $server->getNotReadMsgCount($dialogu_ids);
        if($lastMsgList['code'] == 0){
           return $this->error($lastMsgList['msg']);
        }
        $lastMsgList  = $lastMsgList['result'];
        $notReadList  = $notReadList['result'];
        //拼接最后一条消息
        foreach ($list as $ks => $vs){
            foreach ($lastMsgList as $v){
                if($v['chat_dialogue_id'] == $vs['id']){
                    $list[$ks]['last_msg'] = $v['msg'];
                    break;
                }
            }
        }

        //拼接未读消息条数
        foreach ($list as $ks => $vs){
            foreach ($notReadList as $v){
                if($v['chat_dialogue_id'] == $vs['id']){
                    $list[$ks]['not_read'] = $v['not_read'];
                    break;
                }else{
                    $list[$ks]['not_read'] = 0;
                }
            }
        }
        $groupList = $server->getGroupCount(-1,1);
        //如果没有设置推送到首页的数据，直接取第一个群
        if(empty($groupList['result'])){
            $groupList = $server->getGroupCount(1,0);
        }
        $data['dialoguel']  = $list;
        $data['group_list'] = $groupList['result'] ?? [];
        $this->success($data);

    }

    /**
     * 获取群列表
     */
    public function getGroupList(){
        $server  =  new Chat();
        $groupList = $server->getGroupCount(-1,0);
        if(!empty($groupList['result'])){
            $this->success($groupList);
        }else{
            $this->success([],'该地区还没有群哦！');
        }

    }

    /**
     * 搜索聊天纪录会话信息
     */
    public function searchDialogueList(){
        $user_id    = $this->userId;
        $searchMsg  = $this->request->post('search_msg');
        if(empty($searchMsg) ){
            return  $this->error('请输入要查找的内容');
        }

        $server  = new Chat();
        $dialoguelist  =   $server->getChatListByMsg($searchMsg,$user_id);

        if(empty($dialoguelist['result'])){
            return $this->error('无聊天记录');
        }else{
            $dialoguelist = $dialoguelist['result'];
        }

        return $this->success($dialoguelist);

    }

    /**
     * 根据会话id获取聊天纪律哦
     */
    public function getChatListBYDialogueId(){
        $dialogue_id = $this->request->post('dialogue_id');
        $user_id     = $this->userId;
        $type        = $this->request->post('type','');
        $chat_id     = $this->request->post('chat_id');
        $pageSize     = $this->request->post('pageSize');
        $server      = new Chat();
        //分页获取
        $where = [
            'dialogue_id' => $dialogue_id,
            'type'       => $type ,
            'chat_id'     => $chat_id,
            'pageSize'    => $pageSize
        ];
        $list        = $server->getChatListByPage($where);

        $this->success($list['result']);
    }

    /**
     * 生成加密二维码
     */
    public function getChatUserInfo(){
        $user_id = $this->userId;
        $user_id = hashids_encode($user_id);
        $url     = Config::get('app.domain_name').'/index/chat/addFriend?code='.$user_id;
        $this->success($url);
    }

    /**
     * 添加好友
     */
    public function addFriend(){
        $user_id        = $this->userId;
        $friend_user_id = $this->request->get('code');
        $friend_user_id = hashids_decode($friend_user_id);
        if(empty($friend_user_id)){
            return $this->error('参数错误');
        }
        $server  = new Chat();
        $f_info  = (new User())->getInfo($friend_user_id)['result'];
        $lastPy  = getFirstCharter($f_info['nickname']);
        if (empty($lastPy)){
            $lastPy = 'QA';
        }
        $resutl  = $server->addFriend($user_id,$friend_user_id,$lastPy);

        if($resutl['code'] ==0){
            return $this->error($resutl['msg']);
        }

        return  $this->success();
    }

    public function getFriendList(){
        $user_id  = $this->userId;
        $page     = $this->request->post('page');
        $pageSize = $this->request->post('pageSize') ?? 10;
        $list     = (new Chat())->getFriendList([
            'page'      => $page,
            'pageSize'  => $pageSize,
            'user_id'   => $user_id
        ]);

        $this->success($list['result']);
    }

}