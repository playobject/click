<?php
header("Content-Type:text/html; charset=utf-8");
class MYDB{
    function __construct($db_name){
        try{
            $this->db=new PDO('sqlite:'.$db_name);
        }catch(Exception $e){
            exit($e->getMessage());
        }
        $this->db->setAttribute(PDO::ATTR_CASE,PDO::CASE_LOWER);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    }
    function run($q){
        if(substr_count($q,' or ')>0){exit('出现违法字符，请重新输入！');}
        if(strpos($q,'select')==0){
            $sel=$this->db->query($q);
            $select=$sel ? $sel->fetchAll() : '';
            return is_array($select) ? array(true,$select) : array(false,$this->db->errorInfo());
        }else{
            $run=$this->db->exec($q);
            return is_int($run) ? array(true,$run) : array(false,$this->db->errorInfo());
        }
    }
    function runs($arr){
        $this->db->beginTransaction();
        foreach($arr as $v){
            $tmp=$this->run($v);
            if(!$tmp){
                $this->db->rollBack();
                return array(False,$this->db->errorInfo());
            }
        }
        $this->db->commit();
        return array(True,'ok');
    }
}

?>