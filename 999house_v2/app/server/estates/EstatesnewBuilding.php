<?php
namespace app\server\estates;


use app\common\base\ServerBase;
use think\Exception;

class EstatesnewBuilding extends ServerBase
{

    // 楼栋列表
    public function getList($search = [], $field='en.*', $pagesize = 0){
        try {
            $where = [];

            $where[] = ['enb.estate_id', '=', intval($search['estate_id'])];

            if(!empty($search['name'])){
                $where[]=  ['enb.name','like', '%'.$search['name'].'%'];
            }

            // 未被软删除
            $where[] = ['enb.is_delete', '=', 0];

            $order = ['enb.id'=>'desc'];
            if(!empty($search['sort'])){//排序
                $order = ['enb.sort'=>$search['sort'],'enb.id'=>'desc'];
            }

            $myDB = $this->db->name('estates_new_building')->alias('enb');

            if(!empty($search['getTime'])) {
                $myDB->join('estates_new_time ent', "ent.building_id=enb.id", 'left')->group('enb.id');
            }

            $myDB->field($field)->where($where)->order($order);


            if(!empty($pagesize)) {
                $result = array(
                    'list'  =>  [],
                    'total' =>  0,
                    'last_page' =>  0,
                    'current_page'  =>  0
                );

                $list = $myDB->paginate($pagesize)->toArray();

                if(empty($list['data'])) {
                    $result['list'] = [];
                } else {
                    $result['total'] = $list['total'];
                    $result['last_page'] = $list['last_page'];
                    $result['current_page'] = $list['current_page'];
                    $result['list'] =$list['data'];
                }
            } else {
                $list = $myDB->select()->toArray();
                if(empty($list)) {
                    $result['list'] = [];
                } else {
                    $result['list'] =$list;
                }
            }

            if(!empty($result['list'])){
                if($search['getHouses']=='1'){//获取楼盘下的所属户型
                    $building_ids = array_column($result['list'],'id');
                    $houses = $this->db->name('estates_new_house')->field('id,building_id,name,img,price as price_avg,price_total as price,price_str,built_area,rooms,house_purpose,sale_status,orientation')->where([
                        ['building_id','in',$building_ids]
                    ])->select()->toArray();
                    unset($building_ids);

                    if(!empty($houses)){
                        $new_houses = [];
                        foreach ($houses as $v){
                            $v['price'] = intval($v['price']);
                            $new_houses[$v['building_id']][] = $v;
                        }
                        unset($houses);
                        foreach ($result['list'] as &$item){
                            $item['house_list'] = $new_houses[$item['id']];
                        }
                        unset($new_houses);unset($item);
                    }
                }
            }

            if(!empty($result['list'])) {
                foreach($result['list'] as $key => $val) {
                    $result['list'][$key]['open_time'] = !empty($val['open_time']) ? date('Y-m-d', $val['open_time']) : "";
                    $result['list'][$key]['delivery_time'] = !empty($val['delivery_time']) ? date('Y-m-d', $val['delivery_time']) : "";
                }
            }

            return $this->responseOk($result);
        } catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
        
    }

    //添加操作
    public function add($data, $other = [])
    {
        try{
            $time = time();
            $data['create_time'] = $time;
            $data['update_time'] = $time;

            $this->db->startTrans();
            
            $id = $this->db->name('estates_new_building')->insertGetId($data);   //将数据存入并返回自增 ID
            if(empty($id)){
                $this->db->rollback();
                return $this->responseFail(['code'=>0,'msg'=>'操作失败-1']);
            }

            // 许可证处理
            $saleLicense = $other;
            if(!empty($saleLicense)) {
                foreach($saleLicense as &$v) {
                    if(!empty($v['select'])) {
                        $v['building'][] = $id;
                        unset($v['select']);
                    }
                    $v['building'] = !empty($v['building']) ? implode(',', $v['building']) : "";
                }
                $update = [
                    'sales_license' => json_encode($saleLicense),
                    'update_time' => time(),
                ];
                $estateId = $data['estate_id'] ?? 0;
                $res = $this->db->name('estates_new')->where(['id' => $estateId])->update($update);
                if(empty($res)) {
                    $this->db->rollback();
                    return $this->responseFail(['code'=>0,'msg'=>'操作失败-2']);
                }
            }

            $this->db->commit();
            return $this->responseOk($id);
        } catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    //修改状态
    public function edit($id, $data, $other = []){
        try{
            $id = intval($id);
            if(empty($id)){
                return $this->responseFail(['code'=>0,'msg'=>'缺少必要参数']);
            }
            unset($data['id']);//不可变更id

            $data['update_time'] = time();

            $this->db->startTrans();

            $rs = $this->db->name('estates_new_building')->where(['id'=>$id])->update($data);

            if(empty($rs)){
                $this->db->rollback();
                return $this->responseFail(['code'=>0,'msg'=>'操作失败']);
            }

            // 许可证处理
            $saleLicense = $other;
            if(!empty($saleLicense)) {
                foreach($saleLicense as &$v) {
                    if(!empty($v['select'])) {// 选中该许可证
                        if(!in_array($id, $v['building'])) {// 原先不在绑定楼栋内，绑定
                            $v['building'][] = $id;
                        }
                        unset($v['select']);
                    } else {// 未选中该许可证
                        $key = array_search($id, $v['building']);
                        if(false !== $key) {// 原先在绑定楼栋内，解绑
                            array_splice($v['building'], $key, 1);
                        }
                    }
                    $v['building'] = !empty($v['building']) ? implode(',', $v['building']) : "";
                }
                $update = [
                    'sales_license' => json_encode($saleLicense),
                    'update_time' => time(),
                ];
                $estateId = $data['estate_id'] ?? 0;
                $res = $this->db->name('estates_new')->where(['id' => $estateId])->update($update);
                if(empty($res)) {
                    $this->db->rollback();
                    return $this->responseFail(['code'=>0,'msg'=>'操作失败-2']);
                }
            }

            $this->db->commit();

            return $this->responseOk();
        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }

    // 删除楼盘
    public function delete($id)
    {
        try{
            $id = intval($id);
            if(empty($id)){
                return $this->responseFail(['code'=>0,'msg'=>'缺少必要参数']);
            }
            
            $res = $this->db->name('estates_new_building')->where("id", $id)->delete();
            if($res){
                return $this->responseOk($res);
            }else{
                return $this->responseFail(['code'=>0,'msg'=>'删除失败']);
            }

        }catch (Exception $e){
            return $this->responseFail(['code'=>0,'msg'=>$e->getMessage()]);
        }
    }


}