<?php
header("Content-Type:text/html; charset=utf-8");
require_once('inc/php/config.php');
require_once($cfg_root.'inc/php/mydb.php');
$db=new MYDB($cfg_db_file);
if(array_key_exists('pwd',$_POST) && array_key_exists('name',$_POST)){
    
    $info=$db->run("select name,pwd,date from datas where name='{$_POST['name']}'");
    if(!$info[0]){
        exit(json_encode(array('status'=>'error_code:1000')));
    }
    if(count($info[1])==0){
        exit(json_encode(array('status'=>'没有找到用户名')));
    }
    if($info[1][0]['pwd']!=sha1($_POST['pwd'])){
        exit(json_encode(array('status'=>'密码错误')));
    }
    $data=$info[1][0];
    $data['status']='ok';
    unset($data['pwd']);
    $time=time();
    $data['pay']='1';
    if(strtotime($data['date'])<$time){$data['pay']='0';}
    exit(json_encode($data));
}


?>