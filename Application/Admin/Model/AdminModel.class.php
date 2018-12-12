<?php
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model {

    public function auth($datas) {
        $verify = new \Think\Verify();
        if (!$verify->check($datas['verify_code'])) {
            die(json_encode(array('status' => 0, 'info' => "验证码错误啦，再输入吧")));
        }
        $M = M("admin");
        if ($M->where("`email`='" . $datas['email'] . "'")->count() >= 1) {
            $info = $M->where("`email`='" . $datas["email"] . "'")->find();
            if ($info['status'] == 0) {
                return array('status' => 0, 'info' => "你的账号被禁用，有疑问联系管理员吧");
            }
            if ($datas['op_type'] == 2) {
                $rc = randCode(5);
                $code = $info['aid'] . md5($rc);
                $url = C("SYSTEMCONFIG.WEB_ROOT")."Public/findPwd/code/".$code.".html";
                $body = "请在浏览器上打开地址：<a href='$url'>$url</a> 进行密码重置操作                            ";
                $return = sendMail($datas["email"], "找回密码", $body);
                if ($return == 1) {
                    $info['find_code'] = $rc;
                    $M->save($info);
                    return array('status' => 1, 'info' => "重置密码邮件已经发往你的邮箱" . $_POST['email'] . "中，请注意查收");
                } else {
                    return array('status' => 0, 'info' => "$return");
                }
                exit;
            }
            if ($info['pwd'] == md5($datas["email"].$datas['pwd'])) {
                $systemConfig = include WEB_ROOT . 'Application/Common/Conf/systemConfig.php';
                $loginMarked = md5($systemConfig['TOKEN']['admin_marked']);
                $shell = $info['aid'] . md5($info['pwd'] . C('AUTH_CODE'));
                $_SESSION[$loginMarked] = "$shell";
                $shell.= "_" . time();
                setcookie($loginMarked, "$shell", 0, "/");
                $_SESSION['my_info'] = $info;
                return array('status' => 1, 'info' => "登录成功", 'url' => U("index/index"));
            } else {
                return array('status' => 0, 'info' => "账号或密码错误");
            }
        } else {
            return array('status' => 0, 'info' => "不存在邮箱为：" . $datas["email"] . '的管理员账号！');
        }
    }

    public function findPwd($datas) {
        $M = M("admin");
        $verify = new \Think\Verify();
        if (!$verify->check($datas['verify_code'])) {
            die(json_encode(array('status' => 0, 'info' => "验证码错误啦，再输入吧")));
        }
//        $this->check_verify_code();
        if (trim($datas['pwd']) == '') {
            return array('status' => 0, 'info' => "密码不能为空");
        }
        if (trim($datas['pwd']) != trim($datas['pwd1'])) {
            return array('status' => 0, 'info' => "两次密码不一致");
        }
        $data['aid'] = $_SESSION['aid'];
        $data['pwd'] = md5($datas["email"].$datas['pwd']);
        $data['find_code'] = NULL;
        if ($M->save($data)) {
            return array('status' => 1, 'info' => "你的密码已经成功重置", 'url' => U('Access/index'));
        } else {
            return array('status' => 0, 'info' => "密码重置失败");
        }
    }

}

?>
