<?php //ssrc/res.php
set_time_limit(600); //For Uploading File
require_once('cres.php');
/**


    资源引用


**/
if(isset($_GET['img'])){
    $id     = decodeCSID($_GET['img']);
    $action = DStr($_GET['action'], 'ls');
    switch($action){
    case 'del':
        CheckLogin("DIE");
        $msg = CRes::DelImg($id);
        setcookie("msg",$msg,time()+20);
        if(isset($_SERVER['HTTP_REFERER'])) header('location:'.$_SERVER['HTTP_REFERER']);
        echo $msg;
        exit();
    case 'ls':
        header('location:'.CRes::UrlImg($id));
        exit();
    }
}
if(isset($_GET['file'])){
    $id = decodeCSID($_GET['file']);
    $action = DStr($_GET['action'], 'ls');
    switch($action){
    case 'del':
        CheckLogin("DIE");
        $msg = CRes::DelFile($id);
        setcookie("msg",$msg,time()+20);
        if(isset($_SERVER['HTTP_REFERER'])) header('location:'.$_SERVER['HTTP_REFERER']);
        echo $msg;
        exit();
    case 'ls':
        header('location:'.CRes::UrlFile($id) );
        exit();
    }
}
/**


    资源管理：后端


**/
if( isset($_REQUEST['action'])
&&  isset($_REQUEST['res'])  ){
    $res   = encodeCSID(decodeCSID($_REQUEST['res']));
    switch($_REQUEST['action']){
    case 'DeleteComment':
        CheckLogin("DIE");
        if(!$_REQUEST['cmt']) break;
        CRes::DeleteComment($_REQUEST['cmt']);
        header('location:./view/?res='.$res);
        exit();
    case 'CommentOO':
        CheckLogin("DIE");
        if(!$_REQUEST['cmt']) break;
        CRes::CommentOO($_REQUEST['cmt']);
        header('location:./view/?res='.$res);
        exit();
    case 'CommentXX':
        CheckLogin("DIE");
        if(!$_REQUEST['cmt']) break;
        CRes::CommentXX($_REQUEST['cmt']);
        header('location:./view/?res='.$res);
        exit();
    case 'DeleteReview':
        CheckLogin("DIE");
        if(!$_REQUEST['rvw']) break;
        CRes::DeleteReview($_REQUEST['rvw']);
        header('location:./view/?res='.$res);
        exit();
    case 'ReviewOO':
        CheckLogin("DIE");
        if(!$_REQUEST['rvw']) break;
        CRes::ReviewOO($_REQUEST['rvw']);
        header('location:./view/?res='.$res);
        exit();
    case 'ReviewXX':
        CheckLogin("DIE");
        if(!$_REQUEST['rvw']) break;
        CRes::ReviewXX($_REQUEST['rvw']);
        header('location:./view/?res='.$res);
        exit();
    }
}
if( isset($_GET['action'])
&&  $_GET['action'] == 'new'){
    CheckLogin("DIE");
    $resid = CRes::Create();
    header('Location:./res.php?action=edit&res='.encodeCSID($resid));
    header('ssrcinfo:'.encodeCSID($resid));
    exit();
}
if(isset($_POST['res'])){
    CheckLogin("DIE");
    $resid = decodeCSID($_POST['res']);
    $cRes  = new CRes($resid);
    $res   = encodeCSID($cRes->id);
    switch($action = DStr($_POST['action'], 'upload')){
    case 'del':
        $cRes->Delete();
        header('Location:./');
        exit();
    case 'edit':
        $cRes->Edit($_POST['resname'],$_POST['summary'],$_POST['rescontent'],$_POST['e_res_type']);
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'check':
        if(isset($_POST['checked']))
            $cRes->Check($_POST['author_name'], $_POST['fromurl']);
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'publish':
        if(isset($_POST['published']))
            $cRes->Publish($_SESSION['bbs_uid']);
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'b_gamebase':
        $b_gamebase = CRes::Gamebase($_POST);
        $cRes->SetGamebase($b_gamebase);
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'canEdit':
        echo $cRes->canEdit   ? 'Yes' : 'No';
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'canManage':
        echo $cRes->canManage ? 'Yes' : 'No';
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'canSee':
        echo $cRes->canSee    ? 'Yes' : 'No';
        header('Location:./res.php?action=edit&res='.$res);
        exit();
    case 'upload':
        $msg = '';
        //得到文件列表
        if(isset($_FILES['resfile']) && isset($_FILES['resfile']['error'])
        && isset($_POST['dir'])) {
            $dirid = decodeCSID($_POST['dir']);
            //确认目录存在
            if($dirid != 0){
                $row = CRes::QRow("SELECT 1 FROM resdir WHERE id=$dirid LIMIT 1", '该目录不存在', array('目录:' => encodeCSID($dirid)));
            }
            if(is_array($_FILES['resfile']['error'])){
                for ($i=0; isset($_FILES['resfile']['error'][$i]); $i++){
                    $msg.= $cRes->BindFile(array(
                            'error'    => $_FILES['resfile']['error']   [$i],
                            'name'     => $_FILES['resfile']['name']    [$i],
                            'size'     => $_FILES['resfile']['size']    [$i],
                            'type'     => $_FILES['resfile']['type']    [$i],
                            'tmp_name' => $_FILES['resfile']['tmp_name'][$i],
                        ), $dirid
                    ) ."\n";
                }
            }else{
                $msg.= $cRes->BindFile($_FILES['resfile'], $dirid)."\n";
            }
        }
        //得到图片列表
        if(isset($_FILES['resimg']) && isset($_FILES['resimg']['error'])) {
            if(is_array($_FILES['resimg']['error'])){
                for ($i=0; isset($_FILES['resimg']['error'][$i]); $i++){
                    $msg .= $cRes->BindImg(array(
                            'error'    => $_FILES['resimg']['error']   [$i],
                            'name'     => $_FILES['resimg']['name']    [$i],
                            'size'     => $_FILES['resimg']['size']    [$i],
                            'type'     => $_FILES['resimg']['type']    [$i],
                            'tmp_name' => $_FILES['resimg']['tmp_name'][$i],
                        )
                    ) ."\n";
                }
            }else{
                $msg.= $cRes->BindFile($_FILES['resimg'], $dirid)."\n";
            }
        }
        $msg .= '上传结束'."\n";
        setcookie("msg",$msg,time()+20);
        header('location:./res.php?action=edit&res='.$res);
        exit();
    case 'CreateComment':
        $cmt = $cRes->CreateComment($_POST['content'], $_POST['vote']);
        header('location:./view/?res='.$res.'#rescomment');
        exit();
    case 'CreateReview':
        $cRes->CreateReview($_POST['content'], $_POST['vote']);
        header('location:./view/?res='.$res);
        exit();
    }
}else{
    switch($action = DStr($_POST['action'], 'upload')){
    case 'newdir':
        CheckLogin("DIE");
        if(!isset($_POST['dirname'][0])) PageError('目录名字过短警告');
        $dirid = CRes::NewDir($_POST['pdir'], $_POST['dirname']);
        $row = CRes::QRow("SELECT PathDir($dirid) path", '读取目录');
        header('Location:./res.php?action=dirmanage');
        header('ssrcinfo:'.encodeCSID($dirid));
        header('ssrcint:' .$dirid);
        header('ssrcpath:'.DStr($row['path']));
        exit('创建目录成功/'.encodeCSID($dirid).'|'.$row['path']);
    case 'moddir':
        CheckLogin("DIE");
        if(!isset($_POST['dirname'][0])) PageError('目录名字过短警告');
        CRes::ModDir($_POST['dir'], $_POST['pdir'], $_POST['dirname']);
        $row = CRes::QRow("SELECT PathDir($dirid) path", '读取目录');
        header('Location:./res.php?action=dirmanage');
        header('ssrcinfo:'.encodeCSID($dirid));
        header('ssrcint:' .$dirid);
        header('ssrcpath:'.DStr($row['path']));
        exit('修改目录成功/'.encodeCSID($dirid).'|'.$row['path']);
    case 'deldir':
        CheckLogin("DIE");
        CRes::DelDir($_POST['dir']);
        header('Location:./res.php?action=dirmanage');
        exit('删除目录成功');
    }
}
/**


    资源管理：前端


**/


// CSID translate Insider//_
    define('CSIDInsider'   , '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!-._~');
    define('CSIDInsiderLEN', strlen(CSIDInsider));
    function decodeCSIDInsider($csid){//_
        $s = CSIDInsider;
        $l = CSIDInsiderLEN;
        $n = 0;
        for($i = 0; $i < strlen($csid); $i++){
            $p = strpos($s, $csid[$i]);
            if(false===$p) return 0;
            $n += $p * pow($l, $i);
        }
        return $n;
    }/* return: (int) */
    function encodeCSIDInsider($n   ){//_
        $s = CSIDInsider;
        $l = CSIDInsiderLEN;
        $csid = '';
        $n = (int)$n;
        if(!($n>=0))          $n = 0;
        if(!($n<=4294967297)) $n = 0;
        while($n !== 0){
            $csid .= $s[fmod($n, $l)];
            $n /= $l;
            $n = (int)$n;
        }
        if(!isset($csid[0])) $csid = $s[0];
        return (string)$csid;
    }/* return: str   */


$bbsid = IInt($_SESSION['bbs_uid']);
if (CRes::GlobalPermissionQ("Manage")) {
    define('SQL_EACHDIR',       "SELECT id, PathDir(id) dirpath FROM resdir ORDER BY dirpath");
    //define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update FROM resdir ORDER BY dirpath");
    define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update, (SELECT COUNT(*) FROM resdir tmp WHERE pid=resdir.id) csdir, (SELECT COUNT(*) FROM resfile WHERE dirid=resdir.id) cfile  FROM resdir");
}else{
    define('SQL_EACHDIR',       "SELECT id, PathDir(id) dirpath FROM resdir WHERE $bbsid=author_bbsid OR 0=author_bbsid ORDER BY dirpath");
    define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update FROM resdir WHERE $bbsid=author_bbsid OR 0=author_bbsid ORDER BY dirpath");
}
if(isset($_GET['action'])){
    switch($_GET['action']){
    case 'dirmanage':
        CheckLogin("DIE");
        if(!CRes::GlobalPermissionQ("ManageDir")) PageError('无权管理目录');
        mkHeader($title = '管理目录'); ?>
            <div class="steps">
                <form class="form" action="./res.php" method="post">
                    <h2>添加目录</h2>
                    <?php //<br>* 这个页面只有管理员可以访问 *</br></br> ?>
                    目录名称：<br /><input name="dirname" type="text"/><br />
                    上层目录：<br /><select name="pdir">
                        <option value="<?=encodeCSID(0)?>">/</option>
<?php
        $result = CRes::Q(SQL_EACHDIR, '检索目录');
        while($rowdir = mysql_fetch_array($result)){
            $dir      = encodeCSID($rowdir['id']);
            $dirpath  = $rowdir['dirpath']; ?>
                        <option value="<?=$dir?>"><?=htmlspecialchars($dirpath)?></option>
<?php   } ?>
                    </select><br />
                    <input type="hidden" name="action" value="newdir" /><br />
                    <input type="submit" value="新建目录"/>
                </form>
                <form class="form" >
                    <h2>管理目录</h2>
                    <b>注意: 这个表格不能承受超过100次访问/秒的流量</b>
                    <table class="ls">
                        <thead>
                            <tr><th>路径</th><th>创建时间</th><th>创建者</th><th>子目录</th><th>含文件</th><th>操作</th></tr>
                        </thead>
                        <tbody>
<?php   $result=CRes::Q(SQL_EACHDIRDETEAL, '检索目录'); ?>
<?php   while($rowdir = mysql_fetch_array($result)){
            $dir      = encodeCSID($rowdir['id']);
            $dirpath  = $rowdir['dirpath'];
            $uid      = (int)$rowdir['author_bbsid'];
            $t_update = (int)$rowdir['t_update'];
            $time     = $t_update == 0 ? "系统自带目录" : date("Y年M月d日 h:i:s",$t_update);
            $csdir    = (int)$rowdir['csdir'];
            $cfile    = (int)$rowdir['cfile'];
            $delurl   = "./res.php?action=deldir&dir=$dir";?>
                            <tr>
                                <td><?=htmlspecialchars($dirpath)?></td>
                                <td><?=$time?></td>
                                <td>
                                    <a href="<?=htmlspecialchars("http://www.hawkaoc.net/bbs/space-uid-$uid.html")?>" class="avatar" target="_BLANK" <?=$uid==0?'':'style="background-image: url('.htmlspecialchars('\''."http://www.hawkaoc.net/bbs/uc_server/avatar.php?size=large&uid=$uid".'\'').')"'?>>
                                </td>
                                <td><?=$csdir?></td>
                                <td><?=$cfile?></td>
                                <td><a href="<?=htmlspecialchars($delurl)?>" class="del">删除</a></td></tr>
<?php   } ?>
                        </tbody>
                    </table>
                </form>
            </div>
<?php   mkFooter();
        exit();
    case 'deldir':
        CheckLogin("DIE");
        $dirid = decodeCSID($_GET['dir']);
        $dir   = encodeCSID($dirid);
        $row = CRes::QRow("SELECT id, PathDir(id) dirpath, author_bbsid FROM resdir WHERE id=$dirid", '冇这目录');
        if(!CRes::ResDirPermissionQ("Delete", $row)) PageError('你不能删除这个目录。');
        $dirpath = $row['dirpath'];
        mkHeader('删除目录'); ?>
                <form class="form" action="./res.php" method="post">
                    <h1>确认删除目录？[<?=htmlspecialchars(mb_strimwidth($dir,0,45,'...','utf-8'))?>]</h1>
                    目录名称：<br /><input name="dirname" type="text" disabled value="<?=htmlspecialchars($dirpath)?>"/><br />
                    <input type="hidden" name="dir" value="<?=encodeCSID($dirid)?>" />
                    <input type="hidden" name="action" value="deldir" /><br />
                    <input type="submit" class="del" value="删除目录"/><br />
                </form>
<?php   mkFooter();
        exit();
    }
}
if(isset($_GET['res'])){
    $resid   = decodeCSID($_GET['res']);
    $cRes    = new CRes($resid);
    $res     = encodeCSID($cRes->id);
    $row     = $cRes->row;
    $resname = $row['name'];
    switch($action = DStr($_GET['action'], 'view') ){
    case 'view':
        header("location: ./view/?res=$res");
        exit();
    case 'download':
        $cRes->IncDownloads();
        /*
        $url = "amt://?a=dl&res=$res&cutf=%E5%A6%82%E6%9E%9C%E4%BD%A0%E7%9C%8B%E5%88%B0%E4%BA%86%E8%BF%99%E5%8F%A5%E8%AF%9D%EF%BC%8C%E8%AF%B4%E6%98%8E%E4%BD%A0%E9%9C%80%E8%A6%81%E5%AE%89%E8%A3%85%E5%B8%9D%E5%9B%BD%E6%97%B6%E4%BB%A3%E7%AE%A1%E5%AE%B6%EF%BC%8C%E8%AF%B7%E5%89%8D%E5%BE%80www.hawkaoc.net%E4%B8%8B%E8%BD%BD";
        //header("location: $url");
        echo('<!--<SCRIPT LANGUAGE="javascript">');
        echo("location.href='$url'");
        echo("</SCRIPT>-->");
        */
        exit();
    case 'b_gamebase':
        $b_gamebase_array = CRes::Gamebase();
        foreach($b_gamebase_array as $value){
            echo $value.';';
        }
        header('Location:./res.php?action=view&res='.$res);
        exit();
    case 'del':
        CheckLogin("DIE");
        if(!$cRes->PermissionQ("Delete")) pageError('你不能删除这个资源', encodeCSID($resid));
        mkHeader($title = '删除资源 ' . !isset($resname[0]) ? '未命名资源'.$res
        : htmlspecialchars(mb_strimwidth($resname,0, 45,'...','utf-8') ) ); ?>
        <form class="form" action="./res.php" method="post">
            <h1>确认删除资源？<br /><?=htmlspecialchars(mb_strimwidth($resname,0,45,'...','utf-8'))?></h1>
            <input type="hidden" name="res" value="<?=encodeCSID($resid)?>" />
            <input type="hidden" name="action" value="del" /><br />
            <input type="submit" class="del" value="删除资源"/><br />
        </form>
<?php   mkFooter();
        exit();
    case 'edit':
        CheckLogin("DIE");
        if(!$cRes->PermissionQ("Edit")) pageError('你不能编辑这个资源', encodeCSID($resid));
        $canedit   = $cRes->PermissionQ("Edit");
        $canmanage = $cRes->PermissionQ("Manage");
        $cansee    = $cRes->PermissionQ("See");
        $status    = $cRes->status   ;
        $editing   = $cRes->editing  ;
        $checking  = $cRes->checking ;
        $published = $cRes->published;
        $lsstatus = array('[正在编辑]','[正在审核]','[已发布]');
        $b_gamebase_array = CRes::Gamebase((int)$cRes->row['b_gamebase']);
        $title    = !isset($resname[0]) ? '创建资源'
        : htmlspecialchars(mb_strimwidth($resname,0, 45,'...','utf-8') ) . $lsstatus[$status];
        mkHeader($title); ?>
            <h1><?=$title?></h1>
<?php   if( isset($_COOKIE['msg'])){ ?>
            <input type="checkbox" id="known"><label class='msg' for="known"><?=htmlspecialchars($_COOKIE['msg'])?></label>
<?php       unset($_COOKIE['msg']);
        }
        if( $canmanage && !$published){ ?>
            <form class="form" action="./res.php" method="post" enctype="multipart/form-data">
                <h2><?=$canedit?'6':2?>. 最后审核资源</h2>
                <input type="checkbox" name="published" id="published"><label for="published">我<?=$editing?'非常确定这资源完整了':'也确认资源完整'?></label><br />
                <input type="hidden" name="res" value="<?=$res?>" />
                <input type="hidden" name="action" value="publish" />
                <input type="submit" <?=$editing?'title="不等用户编辑完成"':''?> value="<?=$editing?'强行':''?>通过审核" class="btn-large" /><br />
            </form>
<?php   } ?>
            <div class="steps">
                <form class="form" action="./res.php" method="post">
                    <h2>1. 编辑资源信息</h2>
                    资源名称：<br /><input name="resname" type="text" value='<?=htmlspecialchars($resname)?>'<?=$canedit?'':' disabled'?> /><br />
                    资源简介 + 资源说明：<br />
                    <br /><input name="summary" type="text" value='<?=htmlspecialchars($row['summary'])?>'<?=$canedit?'':' disabled'?> /><br />
                    <textarea name="rescontent" type="text"<?=$canedit?'':' disabled'?>><?=htmlspecialchars($row['content'])?></textarea><br />
                    资源类型：<br /><select name="e_res_type"<?=$canedit?'':' disabled'?>>
                        <option value="cpx" <?=      'cpx'      ==$row['e_type']?'selected="selected"':''?>>战役</option>
                        <option value="scx" <?=      'scx'      ==$row['e_type']?'selected="selected"':''?>>场景</option>
                        <option value="mgx" <?=      'mgx'      ==$row['e_type']?'selected="selected"':''?>>录像</option>
                        <option value="gax" <?=      'gax'      ==$row['e_type']?'selected="selected"':''?>>存档</option>
                        <option value="rms" <?=      'rms'      ==$row['e_type']?'selected="selected"':''?>>随机地图</option>
                        <option value="drs" <?=      'drs'      ==$row['e_type']?'selected="selected"':''?>>图像MOD</option>
                        <option value="mod" <?=      'mod'      ==$row['e_type']?'selected="selected"':''?>>游戏MOD</option>
                        <option value="hki" <?=      'hki'      ==$row['e_type']?'selected="selected"':''?>>键位表</option>
                        <option value="tau" <?=      'tau'      ==$row['e_type']?'selected="selected"':''?>>嘲讽包</option>
                        <option value="ai" <?=       'ai'       ==$row['e_type']?'selected="selected"':''?>>AI</option>
                        <option value="avi" <?=      'avi'      ==$row['e_type']?'selected="selected"':''?>>动画</option>
                        <option value="mp3" <?=      'mp3'      ==$row['e_type']?'selected="selected"':''?>>音乐</option>
                        <option value="tool" <?=     'tool'     ==$row['e_type']?'selected="selected"':''?>>工具</option>
                        <option value="undefined" <?='undefined'==$row['e_type']?'selected="selected"':''?>>未知资源</option>
                    </select><br />
                    <input type="hidden" name="res" value="<?=$res?>" />
                    <input type="hidden" name="action" value="edit" />
                    <input type="submit" value="修改资源" /><br />
                </form>
<?php   if($canedit){ ?>
                <div class="form-split">
                    <form class="form" action="./res.php" method="post" enctype="multipart/form-data">
                        <h2>2. 上传效果图</h2>
                        选择文件(如超过 2MB 请使用客户端上传)：<br /><input type="file" name="resimg[]" multiple="multiple" accept="image/*" /><br />
                        <input type="hidden" name="res" value="<?=$res?>" />
                        <input type="submit" value="上传图片" /><br />
                    </form>
<?php       if($lsdir=CRes::Q(SQL_EACHDIR, '检索目录') ){ ?>
<?          /*if((int)$_SESSION['bbs_uid']==103896) var_dump($lsdir); */?>
                    <form class="form" action="./res.php" method="post" enctype="multipart/form-data">
                        <h2>3. 上传资源文件</h2>
                        选择文件(如超过 2MB 请使用客户端上传)：<br /><input type="file" name="resfile[]" multiple="multiple" /><br />
                        所在目录：<br /><select name="dir" <?=$canedit?'':'disabled'?>>
                            <option value="<?=encodeCSID(0)?>"><?=htmlspecialchars("/")?></option>
<?php           while($rowdir = mysql_fetch_array($lsdir)){
                    //神奇的魔法, 勿动
                    echo "啊啊啊啊啊啊啊啊啊啊啊啊啊啊";
                    
                    $dir            = encodeCSIDInsider($rowdir['id']);
                    $dirpath        = $rowdir['dirpath']; ?>
                            <option value="<?=$dir?>"><?=htmlspecialchars($dirpath)?></option>
<?php           } ?>
                        </select><br />
                        <input type="hidden" name="res" value="<?=$res?>" />
                        <input type="submit" value="上传文件" /><br />
                    </form>
                </div>
<?php       } ?>
                <form class="form" action="./res.php" method="post" enctype="multipart/form-data">
                    <h2>4. 确定游戏版本</h2>
                    <ul class="checklist">
                        <li><input type="checkbox" id="cbr" name="r"<?=$b_gamebase_array['r']?' checked':''?>/><label for="cbr">红帽子</label></li>
                        <li><input type="checkbox" id="cba" name="a"<?=$b_gamebase_array['a']?' checked':''?>/><label for="cba">蓝帽子 1.0a</label></li>
                        <li><input type="checkbox" id="cbc" name="c"<?=$b_gamebase_array['c']?' checked':''?>/><label for="cbc">蓝帽子 1.0c</label></li>
                        <li><input type="checkbox" id="cb4" name="4"<?=$b_gamebase_array['4']?' checked':''?>/><label for="cb4">蓝帽子 1.4</label></li>
                        <li><input type="checkbox" id="cbf" name="f"<?=$b_gamebase_array['f']?' checked':''?>/><label for="cbf">绿帽子 遗忘的帝国</label></li>
                        <li><input type="checkbox" id="cbm" name="m"<?=$b_gamebase_array['m']?' checked':''?>/><label for="cbm">带mod的帝国</label></li>
                    </ul>
                    <input type="hidden" name="action" value="b_gamebase" />
                    <input type="hidden" name="res" value="<?=$res?>" />
                    <input type="submit" value="就是这些" /><br />
                </form>
                <form class="form" action="./res.php" method="post" enctype="multipart/form-data">
                    <h2>5. 最后发布资源</h2>
                    出品人/组织签名: <br /><input name="author_name" type="text" value="<?=htmlspecialchars($cRes->row['author_name'])?>" /><br />
                    来源(若在别处发过可填网址): <br /><input name="fromurl" type="text" value="<?=htmlspecialchars($cRes->row['fromurl'])?>" title="若你在本站论坛 或别的论坛发布过这个资源, 请填入网址。&#13;没有则留空。"/><br />
                    <input type="checkbox" name="checked" id="checked"<?=!$editing?' checked':''?>><label for="checked">我已确认资源完整</label><br />
                    <input type="hidden" name="res" value="<?=$res?>" />
                    <input type="hidden" name="action" value="check" />
                    <input type="submit" value="<?=$editing?'':'重新'?>发布资源" /><br />
                    (发布后经过短时间的审核才可下载)
                </form>
<?php   } ?>
            </div>
            <h2>资源文件列表</h2>
            <div class="lsres">
<?php
        if($result=mysql_query("SELECT f.id, PathFile(f.id) path, d.size FROM resfile f INNER JOIN resdat d ON f.datid=d.id AND f.resid=$resid ORDER BY path") )
        while($row = mysql_fetch_array($result)){
            $csid    = encodeCSID($row['id']);
            $path    = $row['path'];
            $size    = $row['size'].'Byte';
            $urlsee = './res.php?file='.$csid;
            $urldel = './res.php?file='.$csid.'&'.'action=del'; ?>
                <div class="res res-file">
                    <div class="name"><?=$path?></div>
                    <div class="ctrl">
                        <a class="btn info"><?=$size?> 大小</a>
                        <a class="btn" target="_blank" href="<?=htmlspecialchars($urlsee)?>">下载文件</a>
                        <a class="btn del" href="<?=htmlspecialchars($urldel)?>">删除文件</a>
                    </div>
                </div>
<?php   } ?>
            </div>
            <h2>资源图片列表</h2>
            <div class="lsres">
<?php   if($result=mysql_query('SELECT * FROM resimg WHERE resid='.$resid) )
        while($row = mysql_fetch_array($result)){
            $csid           = encodeCSID((int)$row['id'   ]);
            $res            = encodeCSID((int)$row['resid']);
            $comment        = $row['comment'];
            $w              = (int)$row['w'];
            $h              = (int)$row['h'];
            $urlsee         = './res.php?img='.$csid;
            $urldel         = './res.php?img='.$csid.'&'.'action=del'; ?>
                <div class="res" style="background-image: url(&quot;<?=htmlspecialchars($urlsee)?>&quot;)">
                    <div class="name"><?=$comment?></div>
                    <div class="ctrl">
                        <a class="btn info"><?=$w.'*'.$h?> 尺寸</a>
                        <a class="btn" target="_blank" href="<?=htmlspecialchars($urlsee)?>">查看大图</a>
                        <a class="btn del" href="<?=htmlspecialchars($urldel)?>">删除图片</a>
                        <a class="btn info">_</a>
                        <a class="btn info">_</a>
                        <a class="btn info">_</a>
                    </div>
                </div>
<?php   } ?>
            </div>
<?php   mkFooter();
        exit();
    }
}
/**


    无效访问


**/
header('Location:./');
exit('无效访问');