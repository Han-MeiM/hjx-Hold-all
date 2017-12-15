<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller
{
    public function login()
    {
        if (request()->isPost()){
            // 获取上传的表单
            $datas = input('post.');
            $result = action('geetest/geetest/verifyLogin',[
                'geetest_challenge' => $datas['geetest_challenge'],
                'geetest_validate' => $datas['geetest_validate'],
                'geetest_seccode' => $datas['geetest_seccode']
            ]);
            if ($result == 'success'){
                session('user::name',$datas['user_name']);
                return 'success';
            }else{
                return 'fail';
            }
        }else {
            // 判断是否登陆，如果登陆了跳转到主界面
            if (session('user::name')){
                $this->redirect('login/getSession');
                exit;
            }
            return $this->fetch();
        }
    }

    public function getSession()
    {
        $user_name = session('user::name');
        // 判断是否登陆，如果没登陆跳转到登陆界面
        if (!$user_name){
            $this->redirect('login/login');
            exit;
        }
        echo '您输入的姓名是:' . session('user::name');
    }
}