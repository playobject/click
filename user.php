<?php
header("Content-Type:text/html; charset=utf-8");
require_once('inc/php/config.php');
require_once($cfg_root.'inc/php/mydb.php');


if(!array_key_exists('n',$_COOKIE)){
    if(!array_key_exists('n',$_GET) || $_GET['n']!='xia'){
        header('HTTP/1.1 404 Not Found');exit();
    }else{
        //setcookie('n','xia',time()+18000);
        setcookie('n','xia');
    }
}else{
    if($_COOKIE['n']!='xia'){
        header('HTTP/1.1 404 Not Found');exit();
    }
}

$db=new MYDB($cfg_db_file);
$db->run("create table if not exists datas(
    id integer primary key unique not null,
    name text unique not null,
    tel text not null,
    pwd text not null,
    date text not null,
    lastip text default '',
    lastmac text default '',
    lasttime integer default 0,
    banben integer default 0,
    jiange integer default 0,
    chip integer default 0,
    search text default '',
    shebei integer default 0,
    keys text default '')"
);

function alert($info){
    exit("<script type='text/javascript'>window.opener.location.reload();alert('{$info}');window.close();</script>");
}

function add(){
    global $db;
    $name=array_key_exists('name',$_POST) ? strlen(trim($_POST['name']))<2 ? alert("用户名错误") : trim($_POST['name']) : alert("没有用户名");
    $tel=array_key_exists('tel',$_POST) ? strlen(trim($_POST['tel']))!=11 ? alert("手机号错误") : trim($_POST['tel']) : alert("没有手机号");
    if (array_key_exists('pwd',$_POST)){
        if(strlen(trim($_POST['pwd']))==0){
            $pwd=sha1('123456');
        }elseif(strlen(trim($_POST['pwd']))>0 && strlen(trim($_POST['pwd']))<5) {
            alert("密码错误");
        }else{
            $pwd=sha1(trim($_POST['pwd']));
        }
    }else{
        $pwd=sha1('123456');
    }
    $date=array_key_exists('date',$_POST) ? strlen(trim($_POST['date']))!=10 ? alert("日期错误") : trim($_POST['date']) : alert("没有日期");
    $tmp=$db->run("insert into datas(name,pwd,tel,date) values('{$name}','{$pwd}','{$tel}','{$date}')");
    if($tmp[0]){
        alert("添加用户{$name}成功");
    }else{
        alert("添加用户{$name}失败： {$tmp[1][2]}");
    }
}

function del(){
    global $db;
    $name=array_key_exists('name',$_POST) ? strlen(trim($_POST['name']))<2 ? alert("用户名错误") : trim($_POST['name']) : alert("没有用户名");
    $id=array_key_exists('id',$_POST) ? $_POST['id'] : alert("没有用户");
    $tmp=$db->run("delete from datas where id='{$id}'");
    if($tmp[0]){
        alert("删除用户{$name}成功");
    }else{
        alert("删除用户{$name}失败： {$tmp[1][2]}");
    }
}

function edit(){
    global $db;
    $id=array_key_exists('id',$_POST) ? $_POST['id'] : alert("没有用户");
    $name=array_key_exists('name',$_POST) ? strlen(trim($_POST['name']))<2 ? alert("用户名错误") : trim($_POST['name']) : alert("没有用户名");
    $tel=array_key_exists('tel',$_POST) ? strlen(trim($_POST['tel']))!=11 ? alert("手机号错误") : trim($_POST['tel']) : alert("没有手机号");
    $date=array_key_exists('date',$_POST) ? strlen(trim($_POST['date']))!=10 ? alert("日期错误") : trim($_POST['date']) : alert("没有日期");
    if (array_key_exists('pwd',$_POST)){
        if(strlen(trim($_POST['pwd']))==0){
            $tmp=$db->run("update datas set tel='{$tel}',date='{$date}' where id='{$id}'");
        }elseif(strlen(trim($_POST['pwd']))>0 && strlen(trim($_POST['pwd']))<5) {
            alert("密码错误");
        }else{
            $pwd=sha1(trim($_POST['pwd']));
            $tmp=$db->run("update datas set tel='{$tel}',pwd='{$pwd}',date='{$date}' where id='{$id}'");
        }
    }else{
        alert("没有密码");
    }
    
    if($tmp[0]){
        alert("用户{$name}修改成功");
    }else{
        alert("用户{$name}修改失败： {$tmp[1][2]}");
    }
}

function add_user(){
    global $db;
    $pwd=sha1('123456');
    for($i=0 ; $i<1000 ; $i++){
        $n=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0');
        $name_len=mt_rand(6,15);
        $name='';
        for ($p=0 ; $p<$name_len ; $p++){
            $name=$name.$n[mt_rand(0,61)];
        }
        $tel=mt_rand (13000000000,18999999999);
        $date=date('Y-m-d',mt_rand (1577808000,1609344000));
        $db->run("insert into datas(name,tel,pwd,date) values('{$name}','{$tel}','{$pwd}','{$date}')");
    }
}


$type=array_key_exists('type',$_POST) ? trim($_POST['type']) : '';

switch ($type){
    case 'add':
        add();
        break;
    case 'del':
        del();
        break;
    case 'edit':
        edit();
        break;
    default:
        '';
}


if(array_key_exists('PHP_AUTH_USER',$_SERVER) && array_key_exists('PHP_AUTH_PW',$_SERVER)){
    if($_SERVER['PHP_AUTH_USER'] != 'xia' || $_SERVER['PHP_AUTH_PW'] != '599945'){
        header('WWW-Authenticate: Basic realm="会员列表页面"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }
}else{
    header('WWW-Authenticate: Basic realm="会员列表页面"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}



function get_datas(){
    global $db,$page_size;
    $info=$db->run('select id,name,tel,date from datas order by id desc')[1];
    $all_info=array();
    $pay_info=array();
    $near_info=array();
    $sear_info=array();
    $time=time();
    foreach($info as $v){
        $v['guoqi']='0';
        if(strtotime($v['date'])<$time){$v['guoqi']='1';}
        $all_info[]=$v;
        if(array_key_exists('q',$_GET) && $_GET['q']!=''){
            if(substr_count($v['name'],$_GET['q'])>0){
                $sear_info[]=$v;
            }
        }
        if(strtotime($v['date'])>$time){
            $pay_info[]=$v;
        }
        if ((strtotime($v['date'])-$time)<86400*3 && (strtotime($v['date'])-$time)>0){
            $near_info[]=$v;
        }
    }
    $data=array();
    $data['all']=count($all_info);
    $data['pay']=count($pay_info);
    $data['near']=count($near_info);
    $data['sear']=count($sear_info);
    $data['q']='';
    if(array_key_exists('q',$_GET) && $_GET['q']!=''){
        $data['q']=$_GET['q'];
        $data['check_status']='';
        $data['near_status']='disabled';
        $max_page=(int)ceil($data['sear']/$page_size);
        $page=array_key_exists('page',$_GET) ? $_GET['page']=='' ? 1 : (int)$_GET['page'] : 1;
        $page=$page<=$max_page ? $page : $max_page;
        $data['info']=array_slice($sear_info,($page-1)*$page_size,$page_size);
        $page_str='';
        if($page>1){
            $pre_page=$page-1;
            $page_str=$page_str."<a href='javascript:change_page(\"1\")' >首页</a>　<a href='javascript:change_page(\"{$pre_page}\")' >上一页</a>　";
        }
        if($page<$max_page){
            $next_page=$page+1;
            if(strlen($page_str)>2){$page_str=$page_str.'　';}
            $page_str=$page_str."<a href='javascript:change_page(\"{$next_page}\")' >下一页</a>　<a href='javascript:change_page(\"{$max_page}\")' >末页</a>";
        }
        $data['page_str']=$page_str;
        return $data;
    }
    if(array_key_exists('type',$_GET) && $_GET['type']!='all'){
        if($_GET['type']=='near'){
            $data['check_status']='checked disabled';
            $data['near_status']='checked';
            $max_page=(int)ceil($data['near']/$page_size);
            $page=array_key_exists('page',$_GET) ? $_GET['page']=='' ? 1 : (int)$_GET['page'] : 1;
            $page=$page<=$max_page ? $page : $max_page;
            $data['info']=array_slice($near_info,($page-1)*$page_size,$page_size);
            $page_str='';
            if($page>1){
                $pre_page=$page-1;
                $page_str=$page_str."<a href='javascript:change_page(\"1\")' >首页</a>　<a href='javascript:change_page(\"{$pre_page}\")' >上一页</a>　";
            }
            if($page<$max_page){
                $next_page=$page+1;
                if(strlen($page_str)>2){$page_str=$page_str.'　';}
                $page_str=$page_str."<a href='javascript:change_page(\"{$next_page}\")' >下一页</a>　<a href='javascript:change_page(\"{$max_page}\")' >末页</a>";
            }
            $data['page_str']=$page_str;
            return $data;
        }
        if($_GET['type']=='pay'){
            $data['check_status']='checked';
            $data['near_status']='';
            $max_page=(int)ceil($data['pay']/$page_size);
            $page=array_key_exists('page',$_GET) ? $_GET['page']=='' ? 1 : (int)$_GET['page'] : 1;
            $page=$page<=$max_page ? $page : $max_page;
            $data['info']=array_slice($pay_info,($page-1)*$page_size,$page_size);
            $page_str='';
            if($page>1){
                $pre_page=$page-1;
                $page_str=$page_str."<a href='javascript:change_page(\"1\")' >首页</a>　<a href='javascript:change_page(\"{$pre_page}\")' >上一页</a>　";
            }
            if($page<$max_page){
                $next_page=$page+1;
                if(strlen($page_str)>2){$page_str=$page_str.'　';}
                $page_str=$page_str."<a href='javascript:change_page(\"{$next_page}\")' >下一页</a>　<a href='javascript:change_page(\"{$max_page}\")' >末页</a>";
            }
            $data['page_str']=$page_str;
            return $data;
        }
        $data['info']=array();
        $data['page_str']='';
        return $data;
    }else{
        $data['check_status']='';
        $data['near_status']='disabled';
        $max_page=(int)ceil($data['all']/$page_size);
        $page=array_key_exists('page',$_GET) ? $_GET['page']=='' ? 1 : (int)$_GET['page'] : 1;
        $page=$page<=$max_page ? $page : $max_page;
        $data['info']=array_slice($all_info,($page-1)*$page_size,$page_size);
        $page_str='';
        if($page>1){
            $pre_page=$page-1;
            $page_str=$page_str."<a href='javascript:change_page(\"1\")' >首页</a>　<a href='javascript:change_page(\"{$pre_page}\")' >上一页</a>　";
        }
        if($page<$max_page){
            $next_page=$page+1;
            if(strlen($page_str)>2){$page_str=$page_str.'　';}
            $page_str=$page_str."<a href='javascript:change_page(\"{$next_page}\")' >下一页</a>　<a href='javascript:change_page(\"{$max_page}\")' >末页</a>";
        }
        $data['page_str']=$page_str;
        return $data;
    }

}
$info=get_datas();


?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width , initial-scale=1.0 , user-scalable=0 , minimum-scale=1.0 , maximum-scale=1.0" />
        <title>用户管理</title>
        <link type="text/css" rel="stylesheet" href="/style/main.css">
        <script type="text/javascript" src="/style/main.js"></script>
    </head>
    <body>
        <div class="main">
            <p class="info">总用户<?php echo $info['all']; ?>位，生效<?php echo $info['pay']; ?>位，即将失效<?php echo $info['near']; ?>位</p>
            <div class="search">
                <input type="text" id="sear" value="<?php echo $info['q']; ?>" onchange="sear()" /><br>
                未过期
                <input type="checkbox" id="check" <?php echo $info['check_status']; ?> onchange="sear()" />
                　　　即将过期
                <input type="checkbox" id="near" <?php echo $info['near_status']; ?> onchange="sear()" />
            </div>
            <ul id="list">
                <li>
                <form method="post" autocomplete="off" target="_blank">
                    增
                    <input type="hidden" name="type" value="add" />
                    <input type="text" name="name" value="" />　
                    <input type="password" name="pwd" value="" />　
                    <input type="text" name="tel" value="" />　
                    <input type="date" name="date" value="2000-01-01" />　
                    <input type="button" onclick="submit()" value="增加" />
                </form>
                </li>
<?php
foreach($info['info'] as $v){
    $guoqi=$v['guoqi']=='1' ? 'style="color:#ccc;"' : '';
    echo <<<formstr
                <li>
                    <form method="post" autocomplete="off" id="form_{$v['id']}" target="_blank">
                        <input type="checkbox" id="check_{$v['id']}" />
                        <input type="hidden" name="id" value="{$v['id']}" />
                        <input type="hidden" name="type" id="type_{$v['id']}" value="edit" />
                        <input type="text" name="name" value="{$v['name']}" />　
                        <input type="password" name="pwd" value="" />　
                        <input type="text" name="tel" value="{$v['tel']}" />　
                        <input type="date" {$guoqi} name="date" value="{$v['date']}" />　
                        <input type="button" onclick="sub({$v['id']})" value="提交" />
                    </form>
                </li>
formstr;
}
echo <<<sdfhj
            </ul>
            <p class="page_list">
                {$info['page_str']}
            </p>
sdfhj;
?>
        </div>
    </body>
</html>