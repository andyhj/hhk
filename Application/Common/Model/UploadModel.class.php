<?php

namespace Common\Model;

class UploadModel {
    public function index() {
        $upload_name = isset($this->request->get['upload_name']) && $this->request->get['upload_name'] ? $this->request->get['upload_name'] : 'upload_images';
        $img_w = isset($this->request->get['img_w']) && $this->request->get['img_w'] ? $this->request->get['img_w'] :50;
        $img_h = isset($this->request->get['img_h']) && $this->request->get['img_h'] ? $this->request->get['img_h'] : 50;
        $data = array();
        if (!empty($_FILES)) {
            $obj_qiniu = new Qiniu($this->config->get('qiniu')['params']);
            $data['status'] = 1;
            $extArr = array("jpg", "png", "gif", "bmp");
            for ($i = 0; $i < count($_FILES[$upload_name]['name']); $i++) {
                $name = $_FILES[$upload_name]['name'][$i];
                $size = $_FILES[$upload_name]['size'][$i];
                $str_tmp_name = $_FILES[$upload_name]['tmp_name'][$i];
                $ext = strtolower($obj_qiniu->getExtend($str_tmp_name));
                if (!in_array($ext, $extArr)) {
                    $data['status'] = 0;
                    $data['error'][] = '图片' . $name . '格式错误！';
                }
                if ($size > (3 * 1024 * 1024)) {
                    $data['status'] = 0;
                    $data['error'][] = '图片' . $name . '不能大于3M！';
                }
            }

            if ($data['status']) {
                for ($i = 0; $i < count($_FILES[$upload_name]['name']); $i++) {
                    $str_tmp_name = $_FILES[$upload_name]['tmp_name'][$i];
                    $mix_file_nmae = $obj_qiniu->upload($str_tmp_name);
                    if ($mix_file_nmae) {
                        $this->load->model('tool/image');
                        if(isset($this->request->get['image_size'])){
                            $image_size = explode('_', $this->request->get['image_size']);
                            $img_w = intval($image_size[0]);
                            $img_h = intval($image_size[1]);
                        }
                        $image['url'] = $obj_qiniu->resize($mix_file_nmae,$img_w, $img_h);
                        $image['abs'] = $mix_file_nmae;
                    }else{
                        $image['url'] = '';
                        $image['abs'] = '';
                    }
                    $data['content'][] = $image;
                }
            }
        }
        echo json_encode($data);
        die();
    }
}
