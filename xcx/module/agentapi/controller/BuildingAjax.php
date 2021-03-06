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
class BuildingAjax extends Common{
    //获取热门楼盘数据
    public function getHotBuildingData(){
        $page=Context::Post('page');
        $city = !empty(Context::Post('city')) ? Context::Post('city') : "厦门";
        $agentId = Session::get('agent_id');   //经纪人id
        //排序 推荐>开通数>在售>时间
//        $buildingData=$this->db->Name('xcx_building_building')->select('bb.*,count(ab.id) tk_num','bb')->leftJoin('xcx_agent_building','ab','bb.id=ab.building_id')->where_equalTo('bb.status',1)->groupBy('bb.id')->page($page,self::MYLIMIT)->orderBy('bb.is_hot','desc')->orderBy('tk_num','desc')->orderBy("FIELD(bb.sales_status,'在售','待售','售完')")->orderBy('bb.create_time','desc')->execute();
        //$buildingData=$this->db->Name('xcx_building_building')->select('bb.*,count(ab.id) tk_num','bb')->leftJoin('xcx_agent_building','ab','bb.id=ab.building_id')->where_equalTo('bb.status',1)->where_equalTo('bb.is_hot',1)->groupBy('bb.id')->page($page,self::MYLIMIT)->execute();
//				$buildingData=$this->db->Name('xcx_building_building')->select()->where_equalTo('status',1)->where_equalTo('is_hot',1)->where_like('city',"%{$city}%")->groupBy('id')->page($page,self::MYLIMIT)->execute();
				$buildingData=$this->db->Name('xcx_building_building')->select('bb.*, ab.is_focus','bb')->leftJoin('xcx_agent_building','ab',"(bb.id=ab.building_id and ab.agent_id={$agentId})")->where_equalTo('bb.status',1)->where_equalTo('bb.is_hot',1)->where_like('bb.city',"%{$city}%")->where_equalTo('bb.is_delete', 0)->orderBy("FIELD(bb.sales_status,'在售','待售','售完')")->orderBy('bb.create_time','desc')->page($page,self::MYLIMIT)->execute();
        // 当查不到当前区域热门楼盘时，查询普通楼盘
        if(empty($buildingData)) {
            $buildingData=$this->db->Name('xcx_building_building')->select('bb.*, ab.is_focus','bb')->leftJoin('xcx_agent_building','ab',"(bb.id=ab.building_id and ab.agent_id={$agentId})")->where_equalTo('bb.status',1)->where_like('bb.city',"%{$city}%")->where_equalTo('bb.is_delete', 0)->orderBy("FIELD(bb.sales_status,'在售','待售','售完')")->orderBy('bb.create_time','desc')->page($page,self::MYLIMIT)->execute();
        }
        if(!empty($buildingData)){
            foreach($buildingData as &$val){
                $val['fold']=floatval($val['fold']);
//                $val['commission']=floatval($val['commission']);
                // 佣金
                switch ($val['commission_type']) {
                    // 固定金额
                    case 1:
                        $val['commission'] = $val['commission'] . "元";
                        break;
                    // 百分比
                    case 2:
//                        $ratio = bcdiv($val['commission'], 100, 2);
//                        $val['commission'] = bcmul($val['fold'], $ratio, 2);
                        $val['commission'] = $val['commission'] . "%";
                        break;
                    default:
                        $val['commission'] = 0;
                        break;
                }
                $val['flag']=empty($val['flag'])?[]:explode(',',$val['flag']);
                $val['is_focus'] = empty($val['is_focus']) ? 0 : $val['is_focus'];
                // 房屋类型
                $house_type = explode(',', $val['house_type']);
                $val['house_type'] = !empty($house_type) ? $house_type['0'] : '';
            }
            return $this->success($buildingData);
//            echo json_encode(['success'=>true,'data'=>$buildingData],JSON_UNESCAPED_UNICODE);
        }else{
            return $this->error();
//            echo json_encode(['success'=>false]);
        }
    }
    //获取楼盘详情页数据
    public function getBuildingDetail(){
        $id=Context::Post('id');    //楼盘id
        $data['agent_id'] = Session::get('agent_id');   //经纪人id
        //获取经纪人与楼盘对应信息
        $agentBuildingInfo=$this->db->Name('xcx_agent_building')->select()->where_equalTo('building_id',$id)->where_equalTo('agent_id',$data['agent_id'])->firstRow();
        if(empty($agentBuildingInfo)){
            $agentBuildingInfo=['is_focus'=>0,'status'=>0];
        }
        $data['agentBuildingInfo']=$agentBuildingInfo;
        //获取经纪人与楼盘对应信息
        $circularize=$this->db->Name('xcx_building_circularize')->select()->where_equalTo('user_id',0)->where_equalTo('building_building_id',$id)->where_equalTo('agent_user_id',$data['agent_id'])->where_equalTo('user_type','2')->firstRow();
        if(empty($circularize)){
            $data['circularize']=['kaipan_notice'=>'0','jianjia_notice'=>'0'];
        }else{
            $data['circularize']=['kaipan_notice'=>$circularize['kaipan_notice'],'jianjia_notice'=>$circularize['jianjia_notice']];
        }
        //获取楼盘信息
        $data['buildingInfo']=$this->db->Name('xcx_building_building')->select()->where_equalTo('id',$id)->firstRow();
        $data['buildingInfo']['fold']=floatval($data['buildingInfo']['fold']);
//        $data['buildingInfo']['commission']=floatval($data['buildingInfo']['commission']);
        // 佣金
        switch ($data['buildingInfo']['commission_type']) {
            // 固定金额
            case 1:
                $data['buildingInfo']['commission'] = $data['buildingInfo']['commission'] . "元";
                break;
            // 百分比
            case 2:
//                $ratio = bcdiv($data['buildingInfo']['commission'], 100, 2);
//                $data['buildingInfo']['commission'] = bcmul($data['buildingInfo']['fold'], $ratio, 2);
                $data['buildingInfo']['commission'] = $data['buildingInfo']['commission'] . "%";
                break;
            default:
                $data['buildingInfo']['commission'] = 0;
                break;
        }
        // 房屋类型
        $data['buildingInfo']['house_type_str'] = $data['buildingInfo']['house_type'];
        $house_type = explode(',', $data['buildingInfo']['house_type']);
        $data['buildingInfo']['house_type'] = !empty($house_type) ? $house_type['0'] : '';

        $data['buildingInfo']['kaipang_time']=date('Y-m-d',$data['buildingInfo']['kaipang_time']);
        //获取楼盘轮播图信息
        $shuffleInfo=$this->db->Name('xcx_building_shuffle')->select()->where_equalTo('building_id',$id)->execute();
        if(empty($shuffleInfo)){$shuffleInfo=[];}
        $data['shuffleInfo']=$shuffleInfo;
        //获取主力户型信息
        $doorInfo=$this->db->Name('xcx_building_floor')->select("bd.*,bf.year_number","bf")->where_equalTo('bf.building_id',$id)->rightJoin("xcx_building_unit","bu","bf.id=bu.floor_id")->rightJoin("xcx_building_door","bd","bu.id=bd.unit_id")->orderBy('bd.is_hot','desc')->page(1,4)->execute();
        if(!empty($doorInfo)){
            foreach($doorInfo as &$doorval){
                $doorval['construction_area']=floatval($doorval['construction_area']);
            }
        }else{
            $doorInfo=[];
        }
        $data['doorInfo']=$doorInfo;
        //获取楼盘周边地图信息
        $mapInfoArr=[];$temp=[];
        $mapInfo=$this->db->Name('xcx_building_map')->select()->where_equalTo('building_id',$id)->execute();
        if(!empty($mapInfo)){
            foreach($mapInfo as $mapval){
                $mapval['distance']=intval($mapval['distance']);
                $temp[$mapval['keyword']][]=$mapval;
            }
            foreach($temp as $k=>$v){
                $mapInfoArr[]=['title'=>$k,'is_show'=>false,'data'=>$v];
            }
            $mapInfoArr[0]['is_show']=true;
        }
        $data['mapInfo']=$mapInfoArr;
        //获取楼栋信息
        $floorInfo=$this->db->Name('xcx_building_floor')->select()->where_equalTo('building_id',$id)->where_equalTo('status',1)->execute();
        if(empty($floorInfo)){$floorInfo=[];}
        $data['floorInfo']=$floorInfo;
        //获取楼盘推荐信息
        $lpList=$this->db->Name('xcx_building_building')->select()->where_notEqualTo('id',$id)->where_equalTo('is_hot',1)->where_equalTo('status',1)->orderBy('sort')->page(1,self::MYLIMIT)->execute();
        if(empty($lpList)){
            $lpList=[];
        }else{
            foreach($lpList as &$val){
                $val['fold']=floatval($val['fold']);
                $val['views_number']=$this->formatting_number($val['views_number']);
                $val['flag']=empty($val['flag'])?[]:explode(',',$val['flag']);
                list($val['is_focus'],$val['is_status'])=$this->getAgentBuilding($val['id']);
            }
        }
        $data['lpList']=$lpList;
        return $this->success($data);
    }
    //获取楼盘详情页子数据
    public function getBuildingDetail2(){
        $id=Context::Post('id');    //楼盘id
        //获取楼盘信息
        $data['buildingInfo']=$this->db->Name('xcx_building_building')->select()->where_equalTo('id',$id)->firstRow();
        $data['buildingInfo']['fold']=floatval($data['buildingInfo']['fold']);
        $data['buildingInfo']['commission']=floatval($data['buildingInfo']['commission']);
        $data['buildingInfo']['kaipang_time']=date('Y-m-d',$data['buildingInfo']['kaipang_time']);
        $data['buildingInfo']['jiaofang_time']=date('Y-m-d',$data['buildingInfo']['jiaofang_time']);
        $data['buildingInfo']['license_time']=date('Y-m-d',$data['buildingInfo']['license_time']);
        //获取楼盘周边地图信息
        $mapInfoArr=[];$temp=[];
        $mapInfo=$this->db->Name('xcx_building_map')->select()->where_equalTo('building_id',$id)->execute();
        if(!empty($mapInfo)){
            foreach($mapInfo as $mapval){
                $mapval['distance']=intval($mapval['distance']);
                $temp[$mapval['keyword']][]=$mapval;
            }
            foreach($temp as $k=>$v){
                if($k=="公交"){
                    $mapInfoArr[]=['title'=>$k,'img'=> '../../static/image/icon-map-traffic.png', 'show_img'=>'../../static/image/icon-map-traffic_actice.png','data'=>$v];
                }else if($k=="学校"){
                    $mapInfoArr[]=['title'=>$k,'img'=> '../../static/image/icon-map-education.png', 'show_img'=>'../../static/image/icon-map-education_active.png','data'=>$v];
                }else if($k=="医院"){
                    $mapInfoArr[]=['title'=>$k,'img'=> '../../static/image/icon-map-hospital.png', 'show_img'=>'../../static/image/icon-map-hospital_active.png','data'=>$v];
                }else if($k=="购物"){
                    $mapInfoArr[]=['title'=>$k,'img'=> '../../static/image/icon-map-shopping.png', 'show_img'=>'../../static/image/icon-map-shopping_active.png','data'=>$v];
                }else if($k=="美食"){
                    $mapInfoArr[]=['title'=>$k,'img'=> '../../static/image/icon-map-food.png', 'show_img'=>'../../static/image/icon-map-food_active.png','data'=>$v];
                }
            }
        }
        $data['mapInfo']=$mapInfoArr;
        return $this->success($data);
    }
    //修改开盘/降价提醒
    public function updateNotice(){
        $id=Context::Post('id');    //楼盘id
        $agent_id=Session::get('agent_id');   //经纪人id
        $tag=Context::Post('tag');    //标识
        $notice=Context::Post('notice');    //提醒开关
        if($tag=="kp"){
            $data['kaipan_notice']=$notice;
            $parameter['kaipan_notice']=$notice;
            $parameter['jianjia_notice']='0';
        }else{
            $data['jianjia_notice']=$notice;
            $parameter['kaipan_notice']='0';
            $parameter['jianjia_notice']=$notice;
        }
        $circularize=$this->db->Name('xcx_building_circularize')->select()->where_equalTo('user_id',0)->where_equalTo('building_building_id',$id)->where_equalTo('agent_user_id',$agent_id)->where_equalTo('user_type','2')->firstRow();
        if(empty($circularize)){     //添加操作
            $res=$this->db->Name('xcx_building_circularize')->insert(['building_building_id'=>$id,'agent_user_id'=>$agent_id,'user_type'=>'2','kaipan_notice'=>$parameter['kaipan_notice'],'jianjia_notice'=>$parameter['jianjia_notice'],'create_time'=>time(),'update_time'=>time()])->execute();
        }else{  //修改
            $res=$this->db->Name('xcx_building_circularize')->update($data)->where_equalTo('id',$circularize['id'])->execute();
        }
        if($res){
            echo json_encode(['success'=>true]);
        }else{
            echo json_encode(['success'=>false,'message'=>'保存失败']);
        }
    }
    //获取经纪人与楼盘的对应信息
    public function getAgentBuilding($building_id){
        $res=[];
        $agentBuildingInfo=$this->db->Name('xcx_agent_building')->select()->where_equalTo('building_id',$building_id)->where_equalTo('agent_id',Session::get('agent_id'))->firstRow();
        if(empty($agentBuildingInfo)){
            $res[]=0;
            $res[]=0;
        }else{
            $res[]=$agentBuildingInfo['is_focus'];
            $res[]=$agentBuildingInfo['status'];
        }
        return $res;
    }
    //获取楼盘所对应的户型信息
    public function getBuildingDoor(){
        $id=Context::Post('id');    //楼盘id
        $page=Context::Post('page');
        $page = empty($page) ? 1 : intval($page);
        $doorInfo=$this->db->Name('xcx_building_floor')->select("bd.*","bf")->where_equalTo('bf.building_id',$id)->rightJoin("xcx_building_unit","bu","bf.id=bu.floor_id")->rightJoin("xcx_building_door","bd","bu.id=bd.unit_id")->orderBy('bd.is_hot','desc')->page($page,self::MYLIMIT)->execute();
        if(!empty($doorInfo)){
            foreach($doorInfo as &$doorval){
                $doorval['construction_area']=floatval($doorval['construction_area']);
            }
        }else{
            $doorInfo=[];
        }
        $data['doorInfo']=$doorInfo;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取户型的详细信息
    public function getBuildingDoorDetail(){
        $id=Context::Post('id');    //户型id
        //获取户型信息
        $data['doorInfo']=$this->db->Name('xcx_building_door')->select()->where_equalTo('id',$id)->firstRow();
        $data['doorInfo']['construction_area']=floatval($data['doorInfo']['construction_area']);
        //获取户型轮播图
        $data['doorImgInfo']=$this->db->Name('xcx_building_doorimg')->select()->where_equalTo('door_id',$id)->orderBy('sort')->execute();
        //获取楼盘信息
        $data['buildingInfo']=$this->db->Name('xcx_building_door')->select("bb.*","bd")->rightJoin("xcx_building_unit","bu","bd.unit_id=bu.id")->rightJoin("xcx_building_floor","bf","bu.floor_id=bf.id")->rightJoin("xcx_building_building","bb","bf.building_id=bb.id")->firstRow();
        $data['buildingInfo']['fold']=floatval($data['buildingInfo']['fold']);
        $data['buildingInfo']['commission']=floatval($data['buildingInfo']['commission']);
        $data['buildingInfo']['views_number']=$this->formatting_number($data['buildingInfo']['views_number']);
        //获取楼盘经纪人与楼盘带客量
        $data['buildingInfo']['myAgentNum']=$this->db->Name('xcx_agent_building')->select('count(*)')->where_equalTo('building_id',$data['buildingInfo']['id'])->firstColumn();
        $data['buildingInfo']['myCustomerNum']=$this->db->Name('xcx_building_reported')->select('count(*)')->where_equalTo('building_id',$data['buildingInfo']['id'])->where_greatThanOrEqual('status_type',2)->firstColumn();
        //获取楼盘下的其余2个户型
        $remainingDoor=$this->db->Name('xcx_building_floor')->select("bd.*","bf")->where_equalTo('bf.building_id',$data['buildingInfo']['id'])->where_notEqualTo('bd.id',$id)->rightJoin("xcx_building_unit","bu","bf.id=bu.floor_id")->rightJoin("xcx_building_door","bd","bu.id=bd.unit_id")->orderBy('bd.is_hot','desc')->orderBy('is_hot','desc')->page(1,2)->execute();
        if(!empty($remainingDoor)){
            foreach($remainingDoor as &$doorval){
                $doorval['construction_area']=floatval($doorval['construction_area']);
            }
        }else{
            $remainingDoor=[];
        }
        $data['remainingDoorInfo']=$remainingDoor;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取楼栋详情数据
    public function getBuildingFloor(){
        $id=Context::Post('id');    //楼盘id
        //获取楼盘信息
        $data['buildingInfo']=$this->db->Name('xcx_building_building')->select()->where_equalTo('id',$id)->firstRow();
        //获取楼栋信息
        $floorInfo=$this->db->Name('xcx_building_floor')->select("bf.*,COUNT(bu.id) unit_num","bf")->leftJoin("xcx_building_unit","bu","bf.id=bu.floor_id")->where_equalTo('bf.building_id',$id)->where_equalTo('bf.status',1)->groupBy("bf.id")->execute();
        if(empty($floorInfo)){
            $floorInfo=[];
        }else{
            foreach($floorInfo as &$floorVal){
                $floorVal['kaipan_time']=date('y.m.d',$floorVal['kaipan_time']);
                $floorVal['jiaofan_time']=date('y.m.d',$floorVal['jiaofan_time']);
            }
        }
        $data['floorInfo']=$floorInfo;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //获取楼栋单元户型数据
    public function getBuildingUnit(){
        $floor_id=Context::Post('id');    //楼栋id
        //获取楼栋单元信息
        $unitInfo=[];
        $unitTemp=$this->db->Name('xcx_building_unit')->select()->where_equalTo('floor_id',$floor_id)->orderBy('sort')->execute();
        if(!empty($unitTemp)){
            foreach($unitTemp as $val){
                $unitInfo[]=['title'=>$val['title'],'floor_number'=>$val['floor_number'],'stairs_number'=>$val['stairs_number'],'data'=>$this->getBuildingDoorData($val['id'])];
            }
        }
        $data['unitInfo']=$unitInfo;
        echo json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    //根据户型单元id获取所有户型
    public function getBuildingDoorData($id){
        $res=[];
        $doorData=$this->db->Name('xcx_building_door')->select()->where_equalTo('unit_id',$id)->orderBy('sort')->execute();
        if(!empty($doorData)){
            foreach($doorData as &$doorVal){
                $doorVal['construction_area']=floatval($doorVal['construction_area']);
            }
            $res=$doorData;
        }
        return $res;
    }
    //楼盘开通事件
    public function setBuildingStatus(){
        $id=Context::Post('id');    //楼栋id
        $agent_id=Session::get('agent_id');   //经纪人id
        $agentBuildingData=$this->db->Name('xcx_agent_building')->select()->where_equalTo('agent_id',$agent_id)->where_equalTo('building_id',$id)->firstRow();
        if(empty($agentBuildingData)){  //添加事件
            $res=$this->db->Name('xcx_agent_building')->insert(['agent_id'=>$agent_id,'building_id'=>$id,'is_focus'=>'0','status'=>'1','create_time'=>time(),'update_time'=>time()])->execute();
        }else{  //修改事件
            $res=$this->db->Name('xcx_agent_building')->update(['status'=>'1'])->where_equalTo('id',$agentBuildingData['id'])->execute();
        }
        if($res)
            echo json_encode(['success'=>true]);
        else
            echo json_encode(['success'=>false]);
    }
    //楼盘的关注与取消关注
    public function setFocus(){
        $id=Context::Post('id');    //楼栋id
        $agent_id=Session::get('agent_id');   //经纪人id
        $is_focus=Context::Post('is_focus');   //要修改的关注状态
        if($is_focus=="1"){ //关注
            $agentBuildingData=$this->db->Name('xcx_agent_building')->select()->where_equalTo('agent_id',$agent_id)->where_equalTo('building_id',$id)->firstRow();
            if(empty($agentBuildingData)){  //添加事件
                $res=$this->db->Name('xcx_agent_building')->insert(['agent_id'=>$agent_id,'building_id'=>$id,'is_focus'=>$is_focus,'status'=>'1','create_time'=>time(),'update_time'=>time()])->execute();
            }else{  //修改事件
                $res=$this->db->Name('xcx_agent_building')->update(['is_focus'=>$is_focus])->where_equalTo('id',$agentBuildingData['id'])->execute();
            }
        }else{  //取消关注
            $res=$this->db->Name('xcx_agent_building')->update(['is_focus'=>$is_focus])->where_equalTo('agent_id',$agent_id)->where_equalTo('building_id',$id)->execute();
        }
        if($res)
            echo json_encode(['success'=>true]);
        else
            echo json_encode(['success'=>false]);
    }
    //获取楼盘页面所需数据
    public function getBuildingHome(){
        $data['sortData']['2']=[
            ['text'=>'单价', 'data'=>[['val'=>'0-10000','name'=>'1万以内'],['val'=>'10000-30000','name'=>'1-3万'], ['val'=>'30000-50000','name'=>'3-5万'], ['val'=>'50000-70000','name'=>'5-7万'], ['val'=>'70000-90000','name'=>'7-9万'], ['val'=>'90000-0','name'=>'9万以上'],  ['val'=>'0-0','name'=>'不限']]]
//              ['text'=>'总价', 'data'=>['100万以内', '100万-150万', '150万-200万', '200万-300万', '300万-500万', '500万以上', '不限']]
        ];
        $data['sortData']['3']= ["普通住宅", "别墅", "商铺", "写字楼"];
        $data['sortData']['4']= [
            ['title'=>'面积', 'data'=>[["val"=>'0-50','title'=>"50m²以下", 'is_checked'=>false], ["val"=>'50-70','title'=>"50-70m²", 'is_checked'=>false], ["val"=>'70-90','title'=>"70-90m²", 'is_checked'=>false], ["val"=>'90-110','title'=>"90-110m²", 'is_checked'=>false], ["val"=>'110-130','title'=>"110-130m²", 'is_checked'=>false], ["val"=>'130-150','title'=>"130-150m²", 'is_checked'=>false], ["val"=>'150-200','title'=>"150-200m²", 'is_checked'=>false], ["val"=>'200-0','title'=>"200m²以上", 'is_checked'=>false]] ],
            ['title'=>'特色标签', 'data'=> [['title'=> "闪电结佣", 'is_checked'=> false], ['title'=> "电商优惠", 'is_checked'=> false ], ['title'=>"九房验真", 'is_checked'=> false],['title'=> "带看卷", 'is_checked'=>false]]],
            ['title'=> '装修情况', 'data'=> [['title'=> "毛坯", 'is_checked'=> false], ['title'=> "简装", 'is_checked'=>false], ['title'=> "中装", 'is_checked'=> false], ['title'=> "精装", 'is_checked'=> false]]],
            ['title'=>'销售状态', 'data'=>[['title'=>"在售", 'is_checked'=>false], ['title'=>"售完", 'is_checked'=>false]]]
//          ['title'=>'关注状态', 'data'=>[['title'=>"已关注", 'is_checked'=>false], ['title'=>"未关注", 'is_checked'=>false]]]
        ];
        $data['sortData']['5']=["默认排序", "佣金最高", "人气最旺"];
        return $this->success($data);
    }
    //获取经纪人所对应的楼盘信息(我的楼盘)
    public function getBuildingData(){
        $agent_id=Session::get('agent_id');   //经纪人id
        $page=Context::Post('page');
        $is_serach=empty(Context::Post('is_serach'))?false:true;
        //条件搜索
        $myDB=$this->db->Name('xcx_agent_building')->select("a.id,a.agent_id,a.building_id,a.create_time,b.name,b.flag,b.sales_status,b.pic,b.house_type,b.city,b.area,b.fold,b.commission_type,b.commission,b.store_manager_commission,b.team_member_commission,b.views_number,b.is_open_project,a.is_focus","a")->leftJoin("xcx_building_building","b","a.building_id=b.id")->orderBy('b.sort', 'desc')->page($page,10)->where_equalTo('a.agent_id',$agent_id)->where_equalTo('b.is_delete', 0);
        if(!empty(Context::Post('is_my'))){
            $myDB->where_equalTo('a.status',1);
        }
        if(!empty(Context::Post('searchText'))){    //楼盘名称搜索
            $myDB->where_like('b.name',"%".Context::Post('searchText')."%");
        }
        if(!empty(Context::Post('city'))){    //城市搜索
            $myDB->where_like('b.city',"%".Context::Post('city')."%");
        }
        if(!empty(Context::Post('area'))){    //区域搜索
            $myDB->where_like('b.area',"%".Context::Post('area')."%");
        }
        if(!empty(Context::Post('fold'))){    //价格搜索
            $fold=explode('-',Context::Post('fold'));
            if(!empty($fold[1])){
                $myDB->where_greatThanOrEqual('b.fold',$fold[0]);
                $myDB->where_lessThanOrEqual('b.fold',$fold[1]);
            }else{
                if(!empty($fold[0])){
                    $myDB->where_greatThanOrEqual('b.fold',$fold[0]);
                }
            }
        }
        if(!empty(Context::Post('house_type'))){    //房屋类型搜索
            $myDB->where_like('b.house_type',"%".Context::Post('house_type')."%");
        }
        $moreData = htmlspecialchars_decode(Context::Post('moreData'));
        $moreData = json_decode($moreData, TRUE);
        if(!empty($moreData)){    //更多搜索
//            $moreData=json_decode(Context::Post('moreData'),true);
            $buildingIds=[];
            foreach($moreData as $value){
                if($value['title']=='面积'){
                    $is_arr=[];
                    $doorRow=(new Query())->Name('xcx_building_door')->select("bb.id","bd");
                    $where_express="";
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr[]=true;
                            $construction_area=explode('-',$v['val']);
                            if(!empty($construction_area[1])){
                                $where_express.=" (construction_area>=".$construction_area[0].' AND construction_area<'.$construction_area[1]." ) OR";
                            }else{
                                $where_express.=" (construction_area>=".$construction_area[0]." ) OR";
                            }
                        }
                    }
                    if(!empty($where_express)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$doorRes=$doorRow->where_express($where_express)->leftJoin("xcx_building_unit","bu","bd.unit_id=bu.id")->leftJoin("xcx_building_floor","bf","bu.floor_id=bf.id")->leftJoin("xcx_building_building","bb","bf.building_id=bb.id")->execute();
                        if(!empty($doorRes)){
                            foreach($doorRes as $vv){
                                $buildingIds[]=$vv['id'];
                            }
                        }
                    }
                    if(!empty($is_arr)){
                        $buildingIds=array_unique($buildingIds);
                        $myDB->where_in('b.id',$buildingIds);
                    }
                }
                if($value['title']=='特色标签'){
                    $where_express="";
                    $is_arr2=[];
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr2[]=true;
                            $where_express.=" b.flag like \"%".$v['title']."%\" OR";
                        }
                    }
                    if(!empty($is_arr2)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$myDB->where_express($where_express);
                    }
                }
                if($value['title']=='装修情况'){
                    $where_express="";
                    $is_arr3=[];
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr3[]=true;
                            $where_express.=" b.decoration=\"".$v['title']."\" OR";
                        }
                    }
                    if(!empty($is_arr3)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$myDB->where_express($where_express);
                    }
                }
                if($value['title']=='销售状态'){
                    $where_express="";
                    $is_arr4=[];
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr4[]=true;
                            $where_express.=" b.sales_status=\"".$v['title']."\" OR";
                        }
                    }
                    if(!empty($is_arr4)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$myDB->where_express($where_express);
                    }
                }
            }
        }
        if(!empty(Context::Post('my_sort'))){    //排序搜索
            $my_sort=Context::Post('my_sort');
            if($my_sort=="默认排序"){
                $myDB->orderBy('a.create_time','desc');
            }else if($my_sort=="佣金最高"){
                $myDB->orderBy('b.commission','desc');
            }else if($my_sort=="人气最旺"){
                $myDB->orderBy('b.views_number','desc');
            }
        }else{
            $myDB->orderBy('a.create_time','desc');
        }
        $buildingIndo=$myDB->execute();
//        $userInfo = $this->getUserInfo();
        if(!empty($buildingIndo)){
            foreach($buildingIndo as &$val){
                $val['is_open_project']=empty($val['is_open_project'])?false:true;
                $val['fold']=floatval($val['fold']);
                $val['views_number']=$this->formatting_number($val['views_number']);
                $val['flag']=empty($val['flag'])?[]:explode(',',$val['flag']);
                // 房屋类型
                $house_type = explode(',', $val['house_type']);
                $val['house_type'] = !empty($house_type) ? $house_type['0'] : '';
                // 佣金
                switch ($val['commission_type']) {
                    // 固定金额
                    case 1:
                        $val['commission'] = $val['commission'] . "元";
                        break;
                    // 百分比
                    case 2:
//                        $ratio = bcdiv($val['commission'], 100, 2);
//                        $val['commission'] = bcmul($val['fold'], $ratio, 2);
                        $val['commission'] = $val['commission'] . "%";
                        break;
                    default:
                        $val['commission'] = 0;
                        break;
                }
//                switch ($userInfo['type']){
//                    case 0:
//                        $val['commission'] = $val['commission'];
//                        break;
//                    case 1:
//                        $val['commission'] = $val['store_manager_commission'];
//                        break;
//                    default:
//                        $val['commission'] = $val['team_member_commission'];
//                        break;
//                }
            }
//            echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>$buildingIndo],JSON_UNESCAPED_UNICODE);
            return $this->success($buildingIndo);
        }else{
//            if(!empty($is_serach)){
////                echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>[]]);
//                return $this->error('无数据');
//            }else{
////                echo json_encode(['success'=>false]);
//                return $this->error('无数据');
//            }
            return $this->success([], '无数据');
        }
    }
    //获取经纪人所对应的楼盘信息(所有楼盘)
    public function getBuildingData2(){
//        DataBase::log(__FILE__.__LINE__,$_POST);
				$page=Context::Post('page');
        $is_serach=empty(Context::Post('is_serach'))?false:true;
        //条件搜索
        $myDB=$this->db->Name('xcx_building_building')->select()->page($page,10)->where_equalTo('status',1)->where_equalTo('is_delete', 0);
        if(!empty(Context::Post('searchText'))){    //楼盘名称搜索
            $myDB->where_like('name',"%".Context::Post('searchText')."%");
        }
        if(!empty(Context::Post('city'))){    //城市搜索
            $myDB->where_like('city',"%".Context::Post('city')."%");
        } else {
            $myDB->where_like('city',"%厦门%"); // 默认城市
        }
        if(!empty(Context::Post('area'))){    //区域搜索
            $myDB->where_like('area',"%".Context::Post('area')."%");
        }
        if(!empty(Context::Post('fold'))){    //价格搜索
            $fold=explode('-',Context::Post('fold'));
            if(!empty($fold[1])){
                $myDB->where_greatThanOrEqual('fold',$fold[0]);
                $myDB->where_lessThanOrEqual('fold',$fold[1]);
            }else{
                if(!empty($fold[0])){
                    $myDB->where_greatThanOrEqual('fold',$fold[0]);
                }
            }
        }
        if(!empty(Context::Post('house_type'))){    //房屋类型搜索
            $myDB->where_like('house_type',"%".Context::Post('house_type')."%");
        }
        $moreData = htmlspecialchars_decode(Context::Post('moreData'));
        $moreData = json_decode($moreData, TRUE);
        if(!empty($moreData)){    //更多搜索
//            $moreData=json_decode(Context::Post('moreData'),true);
            $buildingIds=[];
            foreach($moreData as $value){
                if($value['title']=='面积'){
                    $is_arr=[];
                    $doorRow=(new Query())->Name('xcx_building_door')->select("bb.id","bd");
                    $where_express="";
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr[]=true;
                            $construction_area=explode('-',$v['val']);
                            if(!empty($construction_area[1])){
                                $where_express.=" (construction_area>=".$construction_area[0].' AND construction_area<'.$construction_area[1]." ) OR";
                            }else{
                                $where_express.=" (construction_area>=".$construction_area[0]." ) OR";
                            }
                        }
                    }
                    if(!empty($where_express)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$doorRes=$doorRow->where_express($where_express)->leftJoin("xcx_building_unit","bu","bd.unit_id=bu.id")->leftJoin("xcx_building_floor","bf","bu.floor_id=bf.id")->leftJoin("xcx_building_building","bb","bf.building_id=bb.id")->execute();
                        if(!empty($doorRes)){
                            foreach($doorRes as $vv){
                                $buildingIds[]=$vv['id'];
                            }
                        }
                    }
                    if(!empty($is_arr)){
                        $buildingIds=array_unique($buildingIds);
                        $myDB->where_in('id',$buildingIds);
                    }
                }
                if($value['title']=='特色标签'){
                    $where_express="";
                    $is_arr2=[];
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr2[]=true;
                            $where_express.=" flag like \"%".$v['title']."%\" OR";
                        }
                    }
                    if(!empty($is_arr2)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$myDB->where_express($where_express);
                    }
                }
                if($value['title']=='装修情况'){
                    $where_express="";
                    $is_arr3=[];
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr3[]=true;
                            $where_express.=" decoration=\"".$v['title']."\" OR";
                        }
                    }
                    if(!empty($is_arr3)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$myDB->where_express($where_express);
                    }
                }
                if($value['title']=='销售状态'){
                    $where_express="";
                    $is_arr4=[];
                    foreach($value['data'] as $v){
                        if(!empty($v['is_checked'])){
                            $is_arr4[]=true;
                            $where_express.=" sales_status=\"".$v['title']."\" OR";
                        }
                    }
                    if(!empty($is_arr4)){
                        $where_express=trim($where_express,"OR");
                        $where_express="(".$where_express.") ";
                        @$myDB->where_express($where_express);
                    }
                }
            }
        }
        if(!empty(Context::Post('my_sort'))){    //排序搜索
            $my_sort=Context::Post('my_sort');
            if($my_sort=="默认排序"){
                $myDB->orderBy('sort', 'desc');
            }else if($my_sort=="佣金最高"){
                $myDB->orderBy('commission','desc');
            }else if($my_sort=="人气最旺"){
                $myDB->orderBy('views_number','desc');
            }
        }else{
            $myDB->orderBy('sort');
        }
        $buildingIndo=$myDB->execute();
//        $userInfo = $this->getUserInfo();
        if(!empty($buildingIndo)){
            foreach($buildingIndo as &$val){
                // 佣金
                switch ($val['commission_type']) {
                    // 固定金额
                    case 1:
                        $val['commission'] = $val['commission'] . "元";
                        break;
                    // 百分比
                    case 2:
//                        $ratio = bcdiv($val['commission'], 100, 2);
//                        $val['commission'] = bcmul($val['fold'], $ratio, 2);
                        $val['commission'] = $val['commission'] . "%";
                        break;
                    default:
                        $val['commission'] = 0;
                        break;
                }
//                switch ($userInfo['type']){
//                    case 0: //店员
//                        $val['commission'] = $val['commission'];
//                        break;
//                    case 1://店长
//                        $val['commission'] = $val['store_manager_commission'];
//                        break;
//                    default :
//                        $val['commission'] = $val['team_member_commission'];
//                        break;
//                }
                $val['is_open_project']=empty($val['is_open_project'])?false:true;
                $val['fold']=floatval($val['fold']);
                $val['views_number']=$this->formatting_number($val['views_number']);
                $val['flag']=empty($val['flag'])?[]:explode(',',$val['flag']);
                list($val['is_focus'],$val['is_status'])=$this->getAgentBuilding($val['id']);
                // 房屋类型
                $house_type = explode(',', $val['house_type']);
                $val['house_type'] = !empty($house_type) ? $house_type['0'] : '';
                $val['building_id'] = $val['id'];
            }
//            echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>$buildingIndo],JSON_UNESCAPED_UNICODE);
            return $this->success($buildingIndo);
        }else{
//            if(!empty($is_serach)){
////                echo json_encode(['success'=>true,'is_serach'=>$is_serach,'data'=>[]]);
//                return $this->error('无数据');
//            }else{
////                echo json_encode(['success'=>false,'data'=>[]]);
//                return $this->error('无数据');
//            }
            return $this->success([], '无数据');
        }
    }
    //添加楼盘意见反馈数据
    public function addFeedback(){
//        $data['building_id']=Context::Post('id');    //楼盘id
        $data['agent_id']=Session::get('agent_id');   //经纪人id
        $data['matter_type']=Context::Post('matter_type');
        $data['building_correct_info']=Context::Post('building_correct_info');
        $data['client_side_type']=2;
        $data['create_time']=time();
        $data['update_time']=time();
        $res=$this->db->Name('xcx_building_correct')->insert($data)->execute();
        if($res)
            echo json_encode(['success'=>true]);
        else
            echo json_encode(['success'=>false]);
    }

    // 直辖市
    protected function getDirect()
    {
        $directCityCode = [
            '110000' => [
                'name' => '北京市',
                'code' => ['110100','110200'],
            ], // 北京
            '120000' => [
                'name' => '天津市',
                'code' => ['120100', '120200'],
            ], // 天津
            '310000' => [
                'name' => '上海市',
                'code' => ['310100', '310200'],
            ], // 上海
            '500000' => [
                'name' => '重庆市',
                'code' => ['500100', '500200', '500300'],
            ],// 重庆
        ];

        return $directCityCode;
    }

    // 获取城市
    public function getCity()
    {
        try {
            $city = $this->db->Name('xcx_city')->select('city_no, city_name, is_common, province_no')->where_equalTo('status', 1)->execute();
            $common = [];
            $data = [];
            if(!empty($city)) {
                $directCityCode = $this->getDirect();
                foreach ($city as $v) {
                    // 直辖市转换
                    if(array_key_exists($v['province_no'], $directCityCode)) {
                        $v['city_name'] = $directCityCode[$v['province_no']]['name'];
                    }
                    // 获取首字母分类
                    $key = $this->getFirstChar($v['city_name']);
                    $data[$key][$v['city_no']] = $v['city_name'];
                    // 是否常用城市
                    if($v['is_common']) {
                        $common[] = [$v['city_no'] => $v['city_name']];
                    }
                }
            }

            $res = [
                'common' => $common,
                'city' => $data,
            ];
            return $this->success($res);
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }

    // 获取区域
    protected function getArea()
    {
        try {
            $cityCode = Context::Post('city_code');

            if(empty($cityCode)) {
                return $this->error('参数缺失');
            }

            $isDirct = FALSE;// 是否是直辖市

            $dbSQL = $this->db->Name('xcx_area')
                ->select('area_no, area_name')
                ->where_equalTo('status', 1);

            $directCityCode = $this->getDirect();
            foreach ($directCityCode as $val) {
                if(in_array($cityCode, $val['code'])) {
                    $isDirct = TRUE;
                    $codeWhereIn = $val['code'];
                    break;
                }
            }

            if($isDirct && !empty($codeWhereIn)) {// 直辖市特殊处理
                $dbSQL->where_in('city_no', $codeWhereIn);
            } else {
                $dbSQL->where_equalTo('city_no', $cityCode);
            }

            $city = $dbSQL->execute();

            $data = [];

            if(!empty($city)) {
                foreach ($city as $v) {
                    $data[$v['area_no']] = $v['area_name'];
                }
                return $this->success($data);
            } else {
                return $this->success([], '暂无区域信息');
            }
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }

    //获取报备规则
    protected function getReport(){
        try {
            $id = Context::Post('id');
            if(empty($id)) {
                return $this->error('参数缺失');
            }
            $data = $this->db->Name('xcx_building_report')
                ->select('online_rules,commission_rules,report_rules,look_rules,servant_rules,target_rules')
                ->where_equalTo('unit_id', $id)->where_equalTo('status', 1)->firstRow();

            if($data) {
                return $this->success($data);
            }
            else{
                return $this->success([],'暂无设置报备规则');
            }

        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }

    }

    // 中文转首字母
    protected function getFirstChar($str=''){
        if( !$str ) return null;
        $fchar=ord($str{0});
        if($fchar>=ord("A") and $fchar<=ord("z") )return strtoupper($str{0});
        $s= $this->safe_encoding($str);
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319 and $asc<=-20284)return "A";
        if($asc>=-20283 and $asc<=-19776)return "B";
        if($asc>=-19775 and $asc<=-19219)return "C";
        if($asc>=-19218 and $asc<=-18711)return "D";
        if($asc>=-18710 and $asc<=-18527)return "E";
        if($asc>=-18526 and $asc<=-18240)return "F";
        if($asc>=-18239 and $asc<=-17923)return "G";
        if($asc>=-17922 and $asc<=-17418)return "H";
        if($asc>=-17417 and $asc<=-16475)return "J";
        if($asc>=-16474 and $asc<=-16213)return "K";
        if($asc>=-16212 and $asc<=-15641)return "L";
        if($asc>=-15640 and $asc<=-15166)return "M";
        if($asc>=-15165 and $asc<=-14923)return "N";
        if($asc>=-14922 and $asc<=-14915)return "O";
        if($asc>=-14914 and $asc<=-14631)return "P";
        if($asc>=-14630 and $asc<=-14150)return "Q";
        if($asc>=-14149 and $asc<=-14091)return "R";
        if($asc>=-14090 and $asc<=-13319)return "S";
        if($asc>=-13318 and $asc<=-12839)return "T";
        if($asc>=-12838 and $asc<=-12557)return "W";
        if($asc>=-12556 and $asc<=-11848)return "X";
        if($asc>=-11847 and $asc<=-11056)return "Y";
        if($asc>=-11055 and $asc<=-10247)return "Z";
        return null;
    }
    protected function safe_encoding($string) {
        $_outEncoding = "GB2312";
        $encoding="UTF-8";
        for($i=0;$i<strlen($string);$i++) {
            if(ord($string{$i})<128) continue;
            if((ord($string{$i})&224)==224) { //第一个字节判断通过
                $char=$string{++$i};
                if((ord($char)&128)==128) { //第二个字节判断通过
                    $char=$string{++$i};
                    if((ord($char)&128)==128) {
                        $encoding="UTF-8";
                        break;
                    }
                }
            }
            if((ord($string{$i})&192)==192) { //第一个字节判断通过
                $char=$string{++$i};
                if((ord($char)&128)==128) { //第二个字节判断通过
                    $encoding="GB2312";
                    break;
                }
            }
        }
        if(strtoupper($encoding)==strtoupper($_outEncoding))
            return $string;
        else
            return iconv($encoding,$_outEncoding,$string);
    }

    //一键复制
    public function copy_building_info(){
        //获取id
        $id = Context::Post('id');
        $copyTemplate = $this->db->Name('report_copy')->where_equalTo('status',1)->select('type,id')->execute();
        $data = array_column($copyTemplate,'id','type');
        if(!$data){
            return  $this->error('一键复制参数未配置,请到后台自行配置');
        }
        $detail = $this->db->Name('xcx_building_reported')
            ->select("
             br.id,
             br.said,
             br.agent_id,
             br.user_name as name,
             br.user_phone as phone,
             br.user_gender as gender,
             br.building_id,
             br.take_time,
             br.create_time,
             br.describe,
             bb.name as building_name,
             u.name as agent_user,
             u.phone as agent_phone
              ", "br")
            ->leftJoin('xcx_agent_user', 'u', "u.id=br.agent_id")
            ->leftJoin('xcx_building_building', 'bb', "bb.id=br.building_id")
            ->where_in("br.id", $id)
            ->execute();

        //数据处理
        foreach ($detail as $key => &$value){
            $value['take_time'] = date('Y-m-d H:i:s',$value['take_time']);
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            $value['visitors'] = empty($value['agent_user']) ? '' : $value['agent_user'];
            $value['gender'] = 2 == $value['gender'] ? '女' : '男';
            $storeInfo = $this->db->Name('xcx_store_agent')
                ->where_equalTo('ag.agent_id',$value['agent_id'])
                ->leftJoin('xcx_store_store','ss','ss.id = ag.store_id')
                ->select('ag.store_id,ss.title','ag')->firstRow();

            if(empty($storeInfo['store_id'])){
                $value['brokerage'] = '九房网('.$value['agent_user'].')';
            }else{
                $value['brokerage'] = '九房网('.$storeInfo['title'].')';
            }

           $value = array_intersect_key($value,$data) ;
        }
        return $this->success($detail);
    }
}