<?php
namespace Admin\Controller;
class MemberController extends CommonController {
    public function index(){
        $current_page = (int)I('p',1);
        $view_datas['search_key'] = $search_key = I('search_key');
        $m_user = D("User");
        $user_vip_model = M("user_vip");
        $where = [];
        if($search_key){
            $where_s['u_id']  = array('like', "%{$search_key}%");
            $where_s['login_id']  = array('like',"%{$search_key}%");
            $where_s['_logic'] = 'or';
            $where['_complex'] = $where_s;
        }
        $count = $m_user->where($where)->count();
        $per_page = 15;//每页显示条数
        $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $showPage       = $page->show();// 分页显示输出
        $user_list = $m_user->where($where)->order("add_time DESC")->page($current_page.','.$per_page)->select();
        if($user_list){
            foreach ($user_list as $value) {
                $user_vip_info = $user_vip_model->where(["u_id"=>$value['u_id']])->find();
                $is_plus = '否';
                $vip_end_time = '';
                //判断是否plus会员
                if($user_vip_info && strtotime($user_vip_info["end_time"])> time()){
                    $is_plus = '是';
                    $vip_end_time = $user_vip_info["end_time"];
                }
                $value['is_plus'] = $is_plus;
                $value['vip_end_time'] = $vip_end_time;
                $view_datas['list'][] = $value;
            }
        }
        
        $admin_info = $_SESSION['my_info'];
        $view_datas['num'] = $count;
        $this->assign("admin_info", $admin_info);
        $this->assign("datas", $view_datas);
        $this->assign("page", $showPage);
        $this->display("memberlist");
    }
    /**
     * vip记录
     */
    public function plusdes(){
        $user_vip_log_m = M("user_vip_log");
        $m_user = D("User");
        $current_page = (int)I('p',1);
        $view_datas['search_key'] = $search_key = I('search_key');
        $where = [];
        if($search_key){
            $where['u_id'] = $search_key;
        }
        $count = $user_vip_log_m->where($where)->count();
        $per_page = 15;//每页显示条数
        $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $showPage       = $page->show();// 分页显示输出
        $user_vip_log_list = $user_vip_log_m->where($where)->order("add_time DESC")->page($current_page.','.$per_page)->select();
        if($user_vip_log_list){
            foreach ($user_vip_log_list as $value) {
                $user_info = $m_user->getUserOne($value['u_id']);
                $value['login_id'] = $user_info['login_id'];
                $value['u_name'] = $user_info['u_name'];
                $view_datas['list'][] = $value;
            }
        }
        $view_datas['num'] = $count;
        $this->assign("datas", $view_datas);
        $this->assign("page", $showPage);
        $this->display();
    }
    public function sendvip(){
        $user_id = (int)I('u_id');
        $url = U( 'member/index/');
        if(!$user_id){
            $this->error('参数错误', $url);
        }
        $user_m = D('User');
        $user_info = $user_m->getUserOne($user_id);
        if(!$user_info){
            $this->error('用户不存在', $url);
        }
        if(is_post()){
            $vip_m = (int)trim(I("post.vip_m"));
            if(!$vip_m){
                $this->error('请选择月数', $url);
            }
            $user_vip_log_m = M("user_vip_log");
            $user_vip_log_data["u_id"] = $user_id;
            $user_vip_log_data["vip_m"] = $vip_m;
            $user_vip_log_data["type"] = 2;
            $user_vip_log_data["add_time"] = time();
            $user_vip_log_data["end_time"] = strtotime("+1 month");
            $s=$user_vip_log_m->add($user_vip_log_data);
            if($s){
                $user_m = D('User');
                $user_m->wxMessagewxYwlcMsg($user_id,'恭喜您获得《会还款》'.$vip_m.'个月VIP','系统赠送《会还款》'.$vip_m.'个月VIP',date("Y-m-d H:i:s"),'请尽快领取','点击领取','',HTTP_HOST.'/index/user/plusdes.html');
                $this->success('赠送成功', $url);
            }
        }        
        $this->assign("user_info", $user_info);
        $this->assign("return_url", $url);
        $this->display();
    }
}