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
use library\connector\Mysql;

class Role extends AdminLogin
{
    /**
     * 角色列表
     * @throws \ErrorException
     * @throws \tpr\db\exception\BindParamException
     * @throws \tpr\db\exception\Exception
     * @throws \tpr\db\exception\PDOException
     * @throws \tpr\framework\Exception
     */
    public function index()
    {
        if($this->request->isPost()){
            $keyword = $this->request->param('keyword','');
            $roles = Mysql::name('role')
                ->where('role_name' , 'like' , '%' . $keyword . '%')
                ->whereOr('id',$keyword)
                ->select();
            $count = Mysql::name('role')
                ->where('role_name' , 'like' , '%' . $keyword . '%')
                ->whereOr('id',$keyword)
                ->count();

            foreach ($roles as &$r) {
                $r['admin_number'] = Mysql::name('admin')->where('role_id', $r['id'])->count();
            }

            $this->tableData($roles, $count);
        }

        return $this->fetch('index');
    }

    /**
     * 新增角色
     * @return mixed
     * @throws \ErrorException
     * @throws \tpr\db\exception\BindParamException
     * @throws \tpr\db\exception\Exception
     * @throws \tpr\db\exception\PDOException
     * @throws \tpr\framework\Exception
     */
    public function add(){
        if($this->request->isPost()){
            $this->error("无权限");
            $insert = [
                'role_name'=>$this->request->param('role_name')
            ];

            Mysql::name('role')->insert($insert);
            $this->success(lang('success'));
        }

        return $this->fetch();
    }

    /**
     * 编辑角色
     * @return mixed
     * @throws \ErrorException
     * @throws \tpr\db\exception\BindParamException
     * @throws \tpr\db\exception\Exception
     * @throws \tpr\db\exception\PDOException
     * @throws \tpr\framework\Exception
     */
    public function edit(){
        $id = $this->request->param('id',0);

        if($this->request->isPost()){
            $this->error("无权限");
            $update = $this->param;

            //tpr-framework1.0.18+ 会自动过滤无效字段
            if(Mysql::name('role')->where('id',$id)->update($update)){
                $this->success('成功');
            }else{
                $this->error("操作失败");
            }
        }

        $role = Mysql::name('role')->where('id',$id)->find();

        $this->assign('data' , $role);

        return $this->fetch();
    }

    /**
     * 删除角色
     * @throws \ErrorException
     * @throws \tpr\db\exception\BindParamException
     * @throws \tpr\db\exception\Exception
     * @throws \tpr\db\exception\PDOException
     * @throws \tpr\framework\Exception
     */
    public function del(){
        $this->error("无权限");
        $id = $this->request->param('id');

        $result = Mysql::name('role')->where('id',$id)->delete();
        if($result){
            $this->success(lang('success'));
        }else{
            $this->error(lang('error'));
        }
    }

    /**
     * 权限设置
     * @return mixed
     * @throws \ErrorException
     * @throws \tpr\db\exception\BindParamException
     * @throws \tpr\db\exception\DataNotFoundException
     * @throws \tpr\db\exception\Exception
     * @throws \tpr\db\exception\PDOException
     * @throws \tpr\framework\Exception
     * @throws \tpr\framework\exception\DbException
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
                $exist = Mysql::name('role_node')->where('role_id',$role_id)->where('node_path',$n['path'])->count();
                if(isset($auth_node[$n['path']])){
                    $data = [
                        'role_id'=>$role_id,
                        'node_path'=>$n['path'],
                        'disabled'=>0
                    ];
                    if($exist){
                        $node = Mysql::name('role_node')->where('role_id',$role_id)->where('node_path',$n['path'])->find();
                        Mysql::name('role_node')->where('id',$node['id'])->update($data);
                    }else{
                        Mysql::name('role_node')->insert($data);
                    }
                }else if(!isset($auth_node[$n['path']]) && $exist){
                    Mysql::name('role_node')->where('role_id',$role_id)->where('node_path',$n['path'])->setField('disabled',1);
                }

            }

            $this->success(lang('success'));
        }
        return $this->fetch();
    }

}