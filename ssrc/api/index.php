<?php //ssrc/api/

require_once('../cres.php');

$query = DStr($_GET['q'], 'nothing');
switch ($query) {
case 'lsres':
    $result = CRes::Q('SELECT * FROM res', '检索资源');
    //$json = array('status' => 'fail');
    
    $json_lsres = array();
    
    while($row = mysql_fetch_array($result)){
        $status         = (int)$row['status'];
        $cansee         = CRes::ResPermissionQ("See", $row);

        if($cansee){
            $json_lsres[] = array(
                'res'            => encodeCSID($row['id']),
                't_fileup'       => (int)$row['t_fileup'],
                't_update'       => (int)$row['t_update'],
                'votereview'     => (int)$row['votereview'],
                'votecomment'    => (int)$row['votecomment'],
                'count_download' => (int)$row['count_download']
                );
        }
    }
    //$json['status'] = 'success';

	echo json_encode($json_lsres);
    break;
case 'nothing':
?>
nothing
<?php

break;
default:

?>
unknown query: <?=$query?>
<?php

break;
}