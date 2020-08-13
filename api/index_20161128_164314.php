<?php //ssrc/api/

require_once('../cres.php');

function jsonLsRes(){
    // PS: 这里只需要简单加个(int)就可以防注入了
    $timestamp = (int)$_REQUEST['t'];
    $result = CRes::Q("SELECT id,t_fileup,t_update,votereview,votecomment,count_download,status,t_create,author_bbsid,author_name,name,content,b_gamebase,fromurl,e_type FROM res WHERE t_update>=$timestamp", '检索资源');

    $json_lsres = array();
    
    while($row = mysql_fetch_array($result)){
        $status = (int)$row['status'];
        $cansee = CRes::ResPermissionQ("See", $row);

        if($cansee){
            $json_lsres[] = array(
                'id' => (int)$row['id'],
                'tf' => (int)$row['t_fileup'],
                'tu' => (int)$row['t_update'],
                'ts' => (int)$row['totalsize'],
                'vr' => (int)$row['votereview'],
                'vc' => (int)$row['votecomment'],
                'cd' => (int)$row['count_download'],
                'tc' => (int)$row['t_create'],
                'ai' => (int)$row['author_bbsid'],
                'an' =>      $row['author_name'],
                'n'  =>      $row['name'],
                'co' =>      $row['content'],
                'gb' => (int)$row['b_gamebase'],
                'ur' =>      $row['fromurl'],
                'ty' =>      $row['e_type'],
                'st' =>      $status
            );
        }
    }

    return json_encode(array('t'=>time(),'r'=>$json_lsres),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}

function jsonLsFile(){
    // 这里加了个decodeCSID()
    $resid     = decodeCSID($_GET['resid']);
    // 这里加了个(int)
    $timestamp = (int)$_GET['t'];

    $result = CRes::Q("SELECT f.id AS id,f.t_update AS t_update,PathFile(f.id) AS path,HEX(d.sha1) AS sha1,f.size AS size,f.deleted AS deleted FROM resfile AS f LEFT JOIN resdat AS d ON f.datid=d.id WHERE f.resid=$resid AND f.t_update>=$timestamp", '检索资源文件');
    $t_fileup = (int) mysql_fetch_array( CRes::Q("SELECT t_fileup FROM res WHERE id=$resid", "列出文件更新时间"))['t_fileup'];
    $json_lsfile = array();
    while($row = mysql_fetch_array($result)){
        $json_lsfile[] = array(
            'id' => (int)$row['id'],
            't'  => (int)$row['t_update'],
            'p'  =>$row['path'],
            'h'  =>$row['sha1'],
            's'  =>(int)$row['size'],
            'd'  =>(int)$row['deleted']
        );
    }

    return json_encode(array('t'=>$t_fileup,'r'=>$json_lsfile),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
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
case 'lsfile':
    echo jsonLsFile();
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