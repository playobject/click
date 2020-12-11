function sear() {
    val=document.getElementById("sear").value;
    check=document.getElementById("check").checked ? 'pay' : 'all';
    near=document.getElementById("near").checked ? 'near' : 'all';
    if (val!=''){
        location.search="?type=all&q="+val;
        return null;
    }
    if(near=='near'){
        location.search="?type=near";
        return null;
    }
    if(check=='pay'){
        location.search="?type=pay";
        change_href('pay');
        return null;
    }
    location.search="?type=all";
    return null;
}

function change_page(page='1'){
    old_search=location.search;
    if (old_search.indexOf('page=')>0){
        location.search=old_search.split('page=')[0]+'page='+page;
    }else{
        location.search=old_search.length>4 ? old_search+'&page='+page : '?page='+page;
    }
}

function sub(id){
    let type=document.getElementById("check_"+id).checked ? 'del' : 'edit';
    document.getElementById("type_"+id).value=type;
    if(type=='edit'){
        document.getElementById("form_"+id).submit();
    }else{
        if (confirm('确定要删除该用户吗？')) {
            document.getElementById("form_"+id).submit();
        } else {
            return false;
        }
    }
}

window.onload=function (){

}



