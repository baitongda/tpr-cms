<?php
/**
 * @author: Axios
 *
 * @email: axioscros@aliyun.com
 * @blog:  http://hanxv.cn
 * @datetime: 2017/5/18 13:38
 */

namespace tpr\admin\user\controller;

use tpr\admin\common\controller\AdminLogin;
use tpr\admin\common\validate\AdminValidate;
use tpr\admin\user\service\AdminService;
use library\connector\Mysql;
use tpr\framework\Tool;

class Profile extends AdminLogin
{
    /**
     * 更新用户信息
     * @throws \ErrorException
     * @throws \tpr\db\exception\BindParamException
     * @throws \tpr\db\exception\Exception
     * @throws \tpr\db\exception\PDOException
     * @throws \tpr\framework\Exception
     */
    public function update()
    {
        if ($this->request->isPost()) {
            $this->error("无权限");
            $Validate = new AdminValidate();
            if (!$Validate->scene('profile')->check($this->param)) {
                $this->error($Validate->getError());
            }
            $this->param['update_at'] = time();
            $result = Mysql::name('admin')->where('id', $this->user['id'])->update($this->param);
            if ($result) {
                $this->user = AdminService::getSessionInfo($this->user['id']);
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $this->error("error");
    }

    /**
     * 上传头像文件
     */
    public function avatar()
    {
        $this->wrong(500,"无权限");
        $file = $this->request->file('file');

        if (empty($file)) {
            $this->wrong(500, "上传失败");
        }

        $save_name = Tool::uuid();
        $file->setSaveName($save_name);
        $info = $file->move( PUBLIC_PATH . "/uploads/images/");

        if (!empty($info)) {
            $pathname = $info->getPathname();
            $pathname = substr($pathname, strpos($pathname, "uploads"));
            $user = user_info();
            $user['avatar'] = '/' . $pathname;
            $this->response($user['avatar']);
        } else {
            $this->wrong(500, $file->getError());
        }
    }
}