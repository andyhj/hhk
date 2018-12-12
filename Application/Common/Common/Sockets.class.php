<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\Common;

/**
 * Description of Socket
 *
 * @author Administrator
 */
use Common\Common\Socket;

class Sockets {

    public static $addr = '127.0.0.1';
    public static $port = '7010';
    public static $port2 = '7011';
    public static $sock = null;

    /**
     * whether debug message
     */
    public static $debug = false;

    /**
     * constructor
     */
    public function __construct() {
        
    }

    /**
     * destructor
     */
    public function __destruct() {
        
    }

    /**
     * 清理字符串\0后的乱码
     */
    public static function clean_string($str) {
        $pos = strpos($str, "\0"); //查找是否有"\0"
        if ($pos !== FALSE) {
            $str = substr($str, 0, $pos);
        }
        return $str;
    }

    /**
     * 设置sock地址和端口
     */
    public static function set_address() {
        self::$addr = C('SOCKET.DB_HOST');
        self::$port = C('SOCKET.DB_PORT1');
        self::$port2 = C('SOCKET.DB_PORT2');
    }
    /**
     * Sockgw unified call interface
     *
     * @param string $methodName
     * @param int $srvType
     * @param int $methodID
     * @param int $uid
     * @param array $extra, request body part, for example:
     *   $extra = array(
     * 							'uid' => array(type=>'int', 'size'=>4, 'value'=>9533),
     * 							'type' => array(type=>'int', 'size'=>2, 'value'=>1),
     * 							'username' => array(type=>'string', 'size'=>16, 'value'=>'gavin')
     * 						);
     *   注：现只支持int(2)、int(4)、string三种数据类型
     *
     * @return mixed, or an integer, or an array
     *     -101: 套接字初始化失败
     *     -102: 套接字连接失败
     *  array(): socket server返回的数组
     */
    public static function call($methodName, $srvType, $methodID, $uid = 0, $extra = array()) {
        // 设置请求地址
        self::set_address();
        // 请求消息“头”长度为10个字节(byte)
        $headlen = 10;
        // 检查请求“体”参数输入
        $extra_pack = '';
        if (!empty($extra)) {
            $pack_format = '';
            foreach ($extra AS &$it) {
                $headlen += intval($it['size']);
                if ($it['type'] == 'int') {
                    if ($it['size'] == 2) {
                        $extra_pack .= pack('s', $it['value']);
                    } else {
                        $extra_pack .= pack('l', $it['value']);
                    }
                } elseif ($it['type'] == 'char') {
                    for ($i = 0; $i < $it['size']; $i++) {
                        $extra_pack .= pack('c', $it['value'][$i]);
                    }
                } else {
                    $extra_pack .= pack('S', $it['size']); //开始2个字节表示字符串的长度
                    $headlen += 2;
                    $extra_pack .= pack('a' . $it['size'], $it['value']);
                }
            }
        }
        // 请求头数据包(长度固定为10)，长度结构：消息总长度(2)|服务器类型(2)|方法ID(2)|UserID(4)
        $in = pack('s3l', $headlen, $srvType, $methodID, $uid) . $extra_pack;  // machine byte order
        // 创建Socket对象
        $sock = new Socket(self::$debug);
        // 初始化
        if ($sock->init()) {
            // 连接
            if ($sock->connect(self::$addr, self::$port)) {
                // 发送数据
                $sock->send($in);
                // 接收数据过程
                // 开始接收前14个字节
                $prelen = 14;       //请求头后追加的4个字节是$retcode
                $metArray = array(
                    'empty_function'
                );

                if (!in_array($methodName, $metArray)) {
                    $out = $sock->recv($prelen);  //接收前14个字节
                    $headarr = unpack('slen/stype/smethod/luid/lretcode', $out); //解包
                    //$out     = $sock->recv($headarr['len']-$prelen);	//接收余下的所有字节
                    $out = '';
                    // 关闭套接字
                    $sock->close();
                    // 分发到指定的方法处理
                    if ($methodName) {
                        return self::$methodName($headarr['retcode'], $out);
                    } else {
                        die("Response method '{$methodName}' not exists.");
                    }
                } else {
                    return array('retcode' => 0);
                }
            } //: connect
            else {
                return -101; //连接失败
            }
        } //: init
        else {
            return -100; //初始化失败
        }
    }

//: method call end
    //向服务器请求id返回
    public static function call_id($methodName, $srvType, $methodID, $uid = 0, $extra = array()) {
        self::set_address();
        $headlen = 10;
        $extra_pack = '';
        if (!empty($extra)) {
            $pack_format = '';
            foreach ($extra AS &$it) {
                $headlen += intval($it['size']);
                if ($it['type'] == 'int') {
                    if ($it['size'] == 2) {
                        $extra_pack .= pack('s', $it['value']);
                    } else {
                        $extra_pack .= pack('l', $it['value']);
                    }
                } elseif ($it['type'] == 'char') {
                    for ($i = 0; $i < $it['size']; $i++) {
                        $extra_pack .= pack('c', $it['value'][$i]);
                    }
                } else {
                    $extra_pack .= pack('S', $it['size']);
                    $headlen += 2;
                    $extra_pack .= pack('a' . $it['size'], $it['value']);
                }
            }
        }
        $in = pack('s3l', $headlen, $srvType, $methodID, $uid) . $extra_pack;  // machine byte order
        $sock = new Socket(self::$debug);
        if ($sock->init()) {
            if ($sock->connect(self::$addr, self::$port2)) {
                $sock->send($in);
                $prelen = 14;              //请求头后追加的4个字节是$retcode
                $out = $sock->recv($prelen);  //接收前14个字节
                $headarr = unpack('slen/stype/smethod/luid/lretcode', $out); //解包
                $out = $sock->recv($headarr['len'] - $prelen); //接收余下的所有字节
                $sock->close();
                if (class_method_exists('Sockets', $methodName)) {
                    return self::$methodName($headarr['retcode'], $out);
                } else {
                    die("Response method '{$methodName}' not exists.");
                }
            } //: connect
            else {
                return -101; //连接失败
            }
        } //: init
        else {
            return -100; //初始化失败
        }
    }

    /**
     * 大厅3号协议：获取大厅游戏类型
     *
     * $srvType=10, $methodID=3, $uid=0
     *
     * @return array list info
     */
    private function lobby_getgametype($retcode, $data) {
        $start = 2;  //前两个字节是listnum
        $list_arr = unpack('slistnum', substr($data, 0, $start)); //前两个字节是listnum
        $listnum = $list_arr['listnum'];

        $result = array('retcode' => $retcode, 'listnum' => $listnum, 'listbody' => array());
        if ($listnum > 0 && strlen($data) > 2) {
            $data_sublen = 18; //2个字节的game_type + 16个字节的game_name
            for ($i = 0; $i < $listnum; $i++) {
                $data_unpack = unpack('skey/a16value', substr($data, $start + $i * $data_sublen, $data_sublen));
                $data_unpack['value'] = self::clean_string($data_unpack['value']);
                $result['listbody'][] = array('gametype' => $data_unpack['key'], 'gamename' => $data_unpack['value']);
            }
        }

        return $result;
    }

    /**
     * 大厅4号协议：获取游戏房间
     *
     * $srvType=10, $methodID=4, $uid=0, array('gametype'=>array(type=>'int', 'size'=>2, 'value'=>1))
     *
     * @return array list info
     */
    private function lobby_getgameroom($retcode, $data) {
        $start = 2;  //前两个字节是listnum
        $list_arr = unpack('slistnum', substr($data, 0, $start)); //前两个字节是listnum
        $listnum = $list_arr['listnum'];

        $result = array('retcode' => $retcode, 'listnum' => $listnum, 'listbody' => array());
        if ($listnum > 0 && strlen($data) > 2) {
//			for($i=0; $i<$listnum; $i++) {
//
//			}
            //4个字节的room_id + 2个字节的max_count + 2个字节的current_count + 4个字节的point_limit + 16个字节的Name + 不定长的Desc
            $bodypart = substr($data, $start);
            $desclen = strlen($bodypart) - 28; // 28=4+2+2+4+16
            $data_unpack = unpack('lroomid/smaxcount/scurrcount/lpointlimit/a16name/a' . $desclen . 'desc', $bodypart);
            $data_unpack['name'] = self::clean_string($data_unpack['name']);
            $data_unpack['desc'] = self::clean_string($data_unpack['desc']);
            $result['listbody'][] = array(
                'roomid' => $data_unpack['roomid'],
                'maxcount' => $data_unpack['maxcount'],
                'currcount' => $data_unpack['currcount'],
                'pointlimit' => $data_unpack['pointlimit'],
                'name' => $data_unpack['name'],
                'desc' => $data_unpack['desc'],
            );
        }

        return $result;
    }

    /**
     * 大厅5号协议：获取游戏房间人数
     *
     * $srvType=10, $methodID=5, $uid=0, array(array('type'=>'int', 'size'=>2, 'value'=>6), array('type'=>'int', 'size'=>4, 'value'=>100))
     *
     * @return array list info
     */
    private function lobby_getroomnum($retcode, $data) {
        $start = 2;  //前两个字节是listnum
        $list_arr = unpack('slistnum', substr($data, 0, $start)); //前两个字节是listnum
        $listnum = $list_arr['listnum'];

        $result = array('retcode' => $retcode, 'listnum' => $listnum, 'listbody' => array());
        if ($listnum > 0 && strlen($data) > 2) {
            $data_sublen = 6; //4个字节的room_id + 2个字节的current_count
            for ($i = 0; $i < $listnum; $i++) {
                $data_unpack = unpack('lroomid/scurrcount', substr($data, $start + $i * $data_sublen, $data_sublen));
                $result['listbody'][] = array('roomid' => $data_unpack['roomid'], 'currcount' => $data_unpack['currcount']);
            }
        }

        return $result;
    }

    /**
     * 大厅7号协议：请求币种数量变化
     *
     * $srvType=10, $methodID=7, $uid=5, $extra=array('action'=>array(type=>'string', 'size'=>1, 'value'=>1),'addcoin'=>array(type=>'int', 'size'=>4, 'value'=>1000))
     *
     * @return array('retcode'=>0, 'restcoin'=>10000)
     */
    private function call_back($retcode, $data) {
        //$start   = 4;		//剩余四个字节就是用户的余额
        //$tarr    = unpack('lcoin', substr($data, 0, $start));	//剩余四个字节就是用户的余额
        //$coin    = $tarr['numbers'];
        $result = array('retcode' => $retcode);
        return $result;
    }

    /**
     * 大厅51号协议：获取指定的玩家状态
     *
     * $srvType=10, $methodID=51, $uid=0, array('listnum'=>array(type=>'int', 'size'=>2, 'value'=>2), array(type=>'int', 'size'=>4, 'value'=>5), array(type=>'int', 'size'=>4, 'value'=>55))
     *
     * @return array list info(user status)
     */
    private function lobby_getuserstatus($retcode, $data) {
        $start = 2;  //前两个字节是listnum
        $list_arr = unpack('slistnum', substr($data, 0, $start)); //前两个字节是listnum
        $listnum = $list_arr['listnum'];

        $result = array('retcode' => $retcode, 'listnum' => $listnum, 'listbody' => array());
        if ($listnum > 0 && strlen($data) > 2) {
            $data_sublen = 5; //4个字节的uid(int) + 1个字节的status(char)
            for ($i = 0; $i < $listnum; $i++) {
                $data_unpack = unpack('luid/cstatus', substr($data, $start + $i * $data_sublen, $data_sublen));
                $result['listbody'][] = array('uid' => $data_unpack['uid'], 'status' => $data_unpack['status']);
            }
        }

        return $result;
    }

    /**
     * 100号协议，调试球员等级及属性点分配
     */
    private function lobby_toolplayerpoint($retcode, $data) {
        if ($retcode == 0) {
            $att = array('0' => 's1', '1' => 's2', '2' => 's3', '3' => 's4', '4' => 's5', '5' => 's6');
            //$att = array('0'=>'进攻', '1'=>'控球', '2'=>'力量', '3'=>'防守', '4'=>'抢断', '5'=>'速度');
            for ($i = 0; $i < 6; $i++) {
                $s = unpack('Sv', substr($data, $i * 2, 2));
                $value[$att[$i]] = $s['v'];
            }
        }
        $result = array('retcode' => $retcode, 'data' => $value);
        return $result;
    }

}
