<?php

namespace app\admin\controller;

use app\member\model\MemberTokenModel;
use think\Db;
use app\admin\model\AdminModel;

class Login extends AdminController
{
    private $model_admin = [];
    public function __construct()
    {
        parent::__construct();
        $this->model_admin = new AdminModel();
    }
    /**
     * 登录
     */
    public function login(){
        $admin_account = input('post.admin_account');
        $admin_password = input('post.admin_password');
        if(empty($admin_account) || empty($admin_password))return return_info(300);
        //管理后台登录
        $admin = $this->model_admin->getInfo(['admin_account' => $admin_account], [], 'admin_id,admin_account,admin_name, admin_password,login_num, salt');
        if (!$admin) {
            return return_info('300', '用户不存在');
        }
        if (md5($admin_password.$admin['salt']) != $admin['admin_password']) {
            return return_info('300', '密码错误');
        }
        $data = [];
        $data['login_num'] = $admin['login_num'] + 1;
        $data['last_login_time'] = date('Y-m-d H:i:s');
        $this->model_admin->save($data,[['admin_id','=',$admin['admin_id']]]);

        $model_member_token = new MemberTokenModel();
        $token = $model_member_token->save_token($admin['admin_id'],$admin['admin_account'],$admin['admin_password']);

        $res = [
            'admin_account' => $admin['admin_account'],
            'admin_name' => $admin['admin_name'],
            'sess' => $token,
        ];
        return return_info(200,'登录成功',$res);
    }
    /**
     * 修改密码
     */
    public function set_password(){
        $old_password = input('post.old_password');
        $admin_password = input('post.admin_password');
        if(empty($old_password) || empty($admin_password))return return_info(300);

        if(!isset($this->admin_id)){
            return return_info(700,'登录失效，请重新登录');
        }

        $admin = AdminModel::get($this->admin_id);
        if (!$admin) {
            return return_info('300', '账户信息有误，请重新登录');
        }
        if(md5($old_password.$admin->salt) != $admin->admin_password){
            return return_info('300', '旧密码错误');
        }
        $salt = rand(1000,9999);
        $admin->salt = $salt;
        $admin->admin_password = md5($admin_password.$salt);
        if ($admin->save()){
//            echo Db::getLastSql();
            return return_info(200, '操作成功，请重新登录');
        }else{
            return return_info(300, '操作失败');
        }
    }


}