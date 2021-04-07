<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of main
 *
 * @author Goods0
 */
include 'Common.php';
class SurroundingAjax extends Common{
    //添加意见反馈数据
    public function addSuggest(){
        $data['user_id']=Session::get('agent_id');    //用户id
        $data['client_side_type']=Context::Post('client_side_type');    //用户来源标识
        $data['ambitus_suggest']=Context::Post('ambitus_suggest');    //反馈内容
        $data['contact_way']=Context::Post('contact_way');    //联系方式
        $data['create_time']=time();
        $data['update_time']=time();
        $res=$this->db->Name('xcx_ambitus_suggest')->insert($data)->execute();
        if($res)
            // echo json_encode(['success'=>true,'id'=>$res]);
            return $this->success(['id'=>$res]);
        else
            // echo json_encode(['success'=>false,'message'=>"保存失败"]);
            return $this->error('保存失败');
    }
    public function uploadSuggestImg(){
        $id=Context::Get('id');    //反馈id
        $upfile = new UploadFiles(array('filepath'=>BasePath . DS .'upload' . DS .'suggest'));
        if($upfile->uploadeFile('file')){
            $arrfile = $upfile->getnewFile();
            //处理图片数据
            $info=(new Query())->Name('xcx_ambitus_suggest')->select()->where_equalTo('id',$id)->firstRow();
            $image_feedback=empty($info['image_feedback'])?[]:explode('|',$info['image_feedback']);
            $image_feedback[]='/upload/suggest/'.$arrfile;
            $data['image_feedback']=implode('|',$image_feedback);
            $res=$this->db->Name('xcx_ambitus_suggest')->update($data)->where_equalTo('id',$id)->execute();
            if($res){
                echo json_encode(['success'=>true]);
            }else{
                echo json_encode(['success'=>false,'message'=>"保存失败"]);
            }
        }else{
            $err = $upfile->gteerror();
            echo json_encode(['success'=>false,'message'=>$err]);exit;
        }
    }
    //获取经纪人的勿扰初始数据
    public function getDisturb(){
        $data=[];
        $dict=['每天','星期一','星期二','星期三','星期四','星期五','星期六','星期日'];
        $agent_id=Session::get('agent_id');   //经纪人id
        $disturbRow=$this->db->Name('xcx_agent_do_not_disturb')->select()->where_equalTo('agent_id',$agent_id)->firstRow();
        if(empty($disturbRow)){
            $disturbId=$this->db->Name('xcx_agent_do_not_disturb')->insert(['agent_id'=>$agent_id,'start_time'=>'00:00','end_time'=>'00:00','create_time'=>time(),'update_time'=>time()])->execute();
            $data['disturbInfo']=$this->db->Name('xcx_agent_do_not_disturb')->select()->where_equalTo('id',$disturbId)->firstRow();;
        }else{
            $data['disturbInfo']=$disturbRow;
        }
        $data['disturbInfo']['zouqi']=$dict[$data['disturbInfo']['cycle_type']];
        //echo json_encode($data,JSON_UNESCAPED_UNICODE);
        return $this->success($data);
    }
    //修改经纪人的勿扰初始数据
    public function updateDisturb(){
        $id=Context::Post('id');    //勿扰id
        $data['cycle_type']=Context::Post('cycle_type');
        $data['start_time']=Context::Post('start_time');
        $data['end_time']=Context::Post('end_time');
        $data['status_type']=Context::Post('status_type');
        $data['update_time']=time();
        $res=$this->db->Name('xcx_agent_do_not_disturb')->update($data)->where_equalTo('id',$id)->execute();
        if($res)
            echo json_encode(['success'=>true]);
        else
            echo json_encode(['success'=>false,'message'=>"保存失败"]);
    }
}