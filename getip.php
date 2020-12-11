<?php
header("Content-Type:text/html; charset=utf-8");
require_once('inc/php/config.php');
require_once($cfg_root.'inc/php/mydb.php');
$db=new MYDB($cfg_db_file);

$dingdan = '990559530055233';
$apikey = 'f862c6ksb9yptimud1e7ruyuu5joj2og';

$areas=array('安徽','北京','重庆','福建','广东','甘肃','广西','贵州','河北','湖北','黑龙江','河南','湖南','海南','吉林','江苏','江西','辽宁','内蒙古','宁夏','青海','四川','山东','上海','山西','陕西','天津','新疆','西藏','云南','浙江','香港','澳门','台湾');

function get_data($area){
    global $dingdan,$apikey;
    $url="http://dps.kdlapi.com/api/getdps?signature={$apikey}&orderid={$dingdan}&num=1&format=json&area={$area}";
    $ch=curl_init($url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $data=curl_exec($ch);
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    if ($code==200 || $code==304){
        $tmp=json_decode($data,true);
        if($tmp['code']==0){
            return [true,$tmp['data']['proxy_list'][0]];
        }else{
            return [false,"错误码：{$tmp['code']}，错误内容：{$tmp['msg']}"];
        }
    }else{
        return [false,'网络错误'];
    }
}

function proxy_isok($proxy){
    global $dingdan,$apikey;
    $url="https://dps.kdlapi.com/api/getdpsvalidtime?signature={$apikey}&orderid={$dingdan}&proxy={$proxy}";
    $ch=curl_init($url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $data=curl_exec($ch);
    $code=curl_getinfo($ch,CURLINFO_HTTP_CODE);
    if ($code==200 || $code==304){
        $tmp=json_decode($data,true);
        if($tmp['code']==0){
            return [true,$tmp['data'][$proxy]];
        }else{
            return [false,"错误码：{$tmp['code']}，错误内容：{$tmp['msg']}"];
        }
    }else{
        return [false,'网络错误'];
    }
}

if(!array_key_exists('name',$_POST) && !array_key_exists('area',$_POST)){
    exit(json_encode(array('status'=>'参数有误')));
}
if(!in_array($_POST['area'],$areas)){
    exit(json_encode(array('status'=>'代理区域错误')));
}
$info=$db->run("select date from datas where name='{$_POST['name']}'");
if(!$info[0]){
    exit(json_encode(array('status'=>'查找用户失败，请重试！')));
}
if(count($info[1])==0){
    exit(json_encode(array('status'=>'没有找到用户名')));
}
$data=array('status'=>'ok');
$time=time();
if(strtotime($info[1][0]['date'])<$time){
    exit(json_encode(array('status'=>'您没有权限！请联系管理员充值会员！')));
}

$n=0;
while (true) {
    $ip_tmp=get_data($_POST['area']);
    if($ip_tmp[0]){
        $pro=proxy_isok($ip_tmp[1]);
        if($pro[0]){
            if($pro[1]>60){
                $data['ip']=$ip_tmp[1];
                $date['time']=$pro[1];
                exit(json_encode($data));
            }else{
                $n=$n+1;
                if($n>5){
                    exit(json_encode(array('status'=>'后台已经连续多次获取有效性验证失效，请稍后重试或联系管理员')));
                }
            }
        }else{
            exit(json_encode(array('status'=>$pro[1])));
        }
    }else{
        exit(json_encode(array('status'=>$ip_tmp[1])));
    }
}


?>