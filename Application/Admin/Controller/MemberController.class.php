<?php
namespace Admin\Controller;
class MemberController extends CommonController {
    public function index(){
        $current_page = (int)I('p',1);
        $view_datas['search_key'] = $search_key = I('search_key');
        $m_user = D("User");
        $where = [];
        if($search_key){
            $where_s['id']  = array('like', "%{$search_key}%");
            $where_s['login_id']  = array('like',"%{$search_key}%");
            $where_s['_logic'] = 'or';
            $where['_complex'] = $where_s;
        }
        $count = $m_user->where($where)->count();
        $per_page = 15;//每页显示条数
        $page       = getpage($count,$per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $showPage       = $page->show();// 分页显示输出
        $view_datas['list'] = $m_user->where($where)->order("add_time DESC")->page($current_page.','.$per_page)->select();
        $admin_info = $_SESSION['my_info'];
        $view_datas['num'] = $count;
        $this->assign("admin_info", $admin_info);
        $this->assign("datas", $view_datas);
        $this->assign("page", $showPage);
        $this->display("memberlist");
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
            $user_vip_log_data["type"] = 2;
            $user_vip_log_data["add_time"] = time();
            $user_vip_log_data["end_time"] = strtotime("+1 month");
            $s=$user_vip_log_m->add($user_vip_log_data);
            if($s){
                $user_m = D('User');
                $user_m->wxMessagewxYwlcMsg($user_id,'恭喜您获得《会还款》'.$vip_m.'个月VIP','系统赠送《会还款》'.$vip_m.'个月VIP',date("Y-m-d H:i:s"),'请尽快领取','点击领取','',HTTP_HOST.'/index/user/plusdes.html');
            }
        }        
        $this->assign("user_info", $user_info);
        $this->assign("return_url", $url);
        $this->display();
    }
}