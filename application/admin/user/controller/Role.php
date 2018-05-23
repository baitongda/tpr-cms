<?php
/**
 * @author: Axios
 *
 * @email: axioscros@aliyun.com
 * @blog:  http://hanxv.cn
 * @datetime: 2017/5/19 17:07
 */

namespace tpr\admin\user\controller;

use library\logic\NodeLogic;
use tpr\admin\common\controller\AdminLogin;
use think\Db;

class Role extends AdminLogin
{
    /**
     * 角色列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if($this->request->isPost()){
            $keyword = $this->request->param('keyword','');
            $roles = Db::name('role')
                ->where('role_name' , 'like' , '%' . $keyword . '%')
                ->whereOr('id',$keyword)
                ->select();
            $count = Db::name('role')
                ->where('role_name' , 'like' , '%' . $keyword . '%')
                ->whereOr('id',$keyword)
                ->count();

            foreach ($roles as &$r) {
                $r['admin_number'] = Db::name('admin')->where('role_id', $r['id'])->count();
            }

            $this->tableData($roles, $count);
        }

        return $this->fetch('index');
    }

    /**
     * 新增角色
     * @return mixed
     */
    public function add(){
        if($this->request->isPost()){
            $this->error("无权限");
            $insert = [
                'role_name'=>$this->request->param('role_name')
            ];

            Db::name('role')->insert($insert);
            $this->success(lang('success'));
        }

        return $this->fetch();
    }

    /**
     * 编辑角色
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit(){
        $id = $this->request->param('id',0);

        if($this->request->isPost()){
            $this->error("无权限");
            $update = $this->param;

            //tpr-framework1.0.18+ 会自动过滤无效字段
            if(Db::name('role')->where('id',$id)->update($update)){
                $this->success('成功');
            }else{
                $this->error("操作失败");
            }
        }

        $role = Db::name('role')->where('id',$id)->find();

        $this->assign('data' , $role);

        return $this->fetch();
    }

    /**
     * 删除角色
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del(){
        $this->error("无权限");
        $id = $this->request->param('id');

        $result = Db::name('role')->where('id',$id)->delete();
        if($result){
            $this->success(lang('success'));
        }else{
            $this->error(lang('error'));
        }
    }

    /**
     * 权限设置
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function auth(){
        $role_id = $this->request->param('role_id');
        if($this->request->isPost()){
            $this->error("无权限");
            $result = NodeLogic::adminNode(false);
            $node_list = $result['list'];

            $auth_node = isset($this->param['node']) ? $this->param['node']: [];
            $temp = [];
            foreach ($auth_node as $an){
                $temp[$an['path']] = $an['path'];
            }
            $auth_node = $temp;

            foreach ($node_list as $n){
                $exist = Db::name('role_node')->where('role_id',$role_id)->where('node_path',$n['path'])->count();
                if(isset($auth_node[$n['path']])){
                    $data = [
                        'role_id'=>$role_id,
                        'node_path'=>$n['path'],
                        'disabled'=>0
                    ];
                    if($exist){
                        $node = Db::name('role_node')->where('role_id',$role_id)->where('node_path',$n['path'])->find();
                        Db::name('role_node')->where('id',$node['id'])->update($data);
                    }else{
                        Db::name('role_node')->insert($data);
                    }
                }else if(!isset($auth_node[$n['path']]) && $exist){
                    Db::name('role_node')->where('role_id',$role_id)->where('node_path',$n['path'])->setField('disabled',1);
                }

            }

            $this->success(lang('success'));
        }
        return $this->fetch();
    }

}