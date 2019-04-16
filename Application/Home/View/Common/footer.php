
<style>
    .sub{
        background-color: #ffffff;
        width:100%;
        height: 1.1rem;
        font-size: 0.2rem;
        text-align: center;
        position: fixed;
        bottom: 0px;
        z-index: 50;
    }
    .sub_img{
        width: 0.4rem;
    }
    .nav_bg{
        margin: 0.2rem 0 0.2rem 0;
    }
    .sub_h{
        width:50%;
        letter-spacing: 2px;
        left: 25%;
        height: 1.35rem;
        text-align: center;
        position: fixed;
        bottom: 0px;
        z-index: 50;
    }
    .sub_h img{
        width: 1.35rem;
    }
    a{
        color: #595757;
    }
</style>
<div class="sub">
    <table width="100%">
        <tr>
            <td>
                <a href="<?php echo $wdjh;?>">
                <div class="nav_bg">
                    <div><?php if(isset($is_jh)&&$is_jh){?><img src="/src/img/home/wdjh_d.png" class="sub_img"><?php }else{?><img src="/src/img/home/wdjh.png" class="sub_img"><?php }?></div>
                    <div>我的计划</div>
                </div>
                </a>
            </td>
            <td width="30%">
            </td>
            <td>
                <a href="<?php echo $grzx;?>">
                <div class="nav_bg">
                    <div><?php if(isset($is_gr)&&$is_gr){?><img src="/src/img/home/grzx_d.png" class="sub_img"><?php }else{?><img src="/src/img/home/grzx.png" class="sub_img"><?php }?></div>
                    <div>个人中心</div>
                </div>
                </a>
            </td>
        </tr>
    </table>
</div>
<a href="<?php echo U("index/index");?>">
<div class="sub_h">
    <div><img src="/src/img/home/h.png"></div>
</div>
</a>