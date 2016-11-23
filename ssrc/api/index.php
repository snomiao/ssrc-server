<?php //ssrc/api/

require_once('../cres.php');

function jsonLsRes(){
    $result = CRes::Q('SELECT id,t_fileup,t_update,votereview,votecomment,count_download,status FROM res', '检索资源');

    $json_lsres = array();
    
    while($row = mysql_fetch_array($result)){
        $status         = (int)$row['status'];
        $cansee         = CRes::ResPermissionQ("See", $row);

        if($cansee){
            $json_lsres[] = array(
                'id'            => (int)$row['id'],
                't_fileup'       => (int)$row['t_fileup'],
                't_update'       => (int)$row['t_update'],
                'votereview'     => (int)$row['votereview'],
                'votecomment'    => (int)$row['votecomment'],
                'count_download' => (int)$row['count_download']
                );
        }
    }

    return json_encode($json_lsres);
}

function _jsonResInfo($where){

    $result = CRes::Q('SELECT id,t_create,author_bbsid,author_name,name,content,b_gamebase,fromurl,e_type FROM res WHERE ' . $where,'获取资源详情' );

    $json_ret = array();
    while($row = mysql_fetch_array($result)){
        $json_ret[] = array(
            'id'          => encodeCSID($row['id']),
            't_create'    => (int)$row['t_create'],
            'author_bbsid'=> (int)$row['author_bbsid'],
            'author_name' => $row['author_name'],
            'name'        => $row['name'],
            'content'     => $row['content'],
            'b_gamebase'  => (int)$row['b_gamebase'],
            'fromurl'     => $row['fromurl'],
            'e_type'      => $row['e_type']
            );
    }

    return json_encode($json_ret);
}

function jsonResInfo(){
    $resIds_str = $_REQUEST['resids'];
    $resIds_raw = (explode(",",$resIds_str));
    $resIds_num = array_map("decodeCSID", $resIds_raw);
    $resIds_query = '(' . implode(', ',$resIds_num) . ')';

    return _jsonResInfo('id IN '. $resIds_query);
}

function jsonResInfoUpdate(){
    $t_update = (int)$_REQUEST['t_update'];

    return _jsonResInfo("t_update > $t_update");
}

//http://ssrc.snowstarcyan.com/ssrc/api/

$query = DStr($_GET['q'], 'nothing');
switch ($query) {

case 'lsres':
    echo jsonLsRes();
    break;
    
case 'resinfo':
    echo jsonResInfo();
    break;

case 'resinfoupdate':
    echo jsonResInfoUpdate();
    break;

case 'nothing':
    echo "nothing";
    break;

default:
    echo "unknown query: $query";
    break;
}