<?php

//require_once('./debug.php');

set_time_limit(600); //For Uploading File, 10分钟
require_once('./func.php');
require_once('./cres.php');

if(!isset($_GET['res'])){
    header("Location: ./");
    exit;
}

// 检查权限
$bbsid = IInt($_SESSION['bbs_uid']);
if (CRes::GlobalPermissionQ("Manage")) {
    define('SQL_EACHDIR',       "SELECT id, PathDir(id) dirpath FROM resdir ORDER BY CONVERT(dirpath USING gbk)");
    //define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update FROM resdir ORDER BY dirpath");
    define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update, (SELECT COUNT(*) FROM resdir tmp WHERE pid=resdir.id) csdir, (SELECT COUNT(*) FROM resfile WHERE dirid=resdir.id) cfile FROM resdir");
}else{
    define('SQL_EACHDIR',       "SELECT id, PathDir(id) dirpath FROM resdir WHERE $bbsid=author_bbsid OR 0=author_bbsid ORDER BY CONVERT(dirpath USING gbk)");
    define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update, (SELECT COUNT(*) FROM resdir tmp WHERE pid=resdir.id) csdir, (SELECT COUNT(*) FROM resfile WHERE dirid=resdir.id) cfile FROM resdir WHERE $bbsid=author_bbsid OR 0=author_bbsid ORDER BY CONVERT(dirpath USING gbk)");
}


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


$resid   = decodeCSID($_GET['res']);
$cRes    = new CRes($resid);
$res     = encodeCSID($cRes->id);
$row     = $cRes->row;
$resname = $row['name'];

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

mkHeader($title);
?>


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
                        <option value="lang" <?=     'lang'     ==$row['e_type']?'selected="selected"':''?>>语言</option>
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
                    $dir            = encodeCSIDInsider($rowdir['id']);
                    $dirpath        = $rowdir['dirpath']; ?>
                            <option value="<?=$dir?>"<?=
                                isset($_POST['dir']) && ($_POST['dir'] == $dir ) ? ' selected="selected"':''
                                ?>><?=htmlspecialchars($dirpath)?></option>
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
                        <li><input type="checkbox" id="cb5" name="5"<?=$b_gamebase_array['5']?' checked':''?>/><label for="cb5">蓝帽子 1.5</label></li>
                        <li><input type="checkbox" id="cbw" name="w"<?=$b_gamebase_array['w']?' checked':''?>/><label for="cbw">WololoKingdoms</label></li>
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
            <table  class="form">
<?php
        if($result = mysql_query("SELECT f.id, PathFile(f.id) path, f.filename, d.size FROM resfile f INNER JOIN resdat d ON f.datid=d.id AND f.resid=$resid AND f.deleted=0 ORDER BY CONVERT(path USING gbk)") )
        while($row = mysql_fetch_array($result)){
            $csid     = encodeCSID($row['id']);
            $path     = $row['path'];
            $size     = $row['size'].'B';
            $filename = $row['filename'];
            $urlsee   = './res.php?file='.$csid."&filename=$filename";
            $urldel   = './res.php?file='.$csid.'&'.'action=del'; ?>
                <tr class="res res-file" style="text-align: left">
                    <td><a class="name"><?=$path?></a></td>
                    <td><a class="info"><?=$size?></a></td>
                    <td><a class="btn-small" target="_blank" href="<?=htmlspecialchars($urlsee)?>">下载文件</a></td>
                    <td><a class="btn-small del" href="<?=htmlspecialchars($urldel)?>">删除文件</a></td>
                </tr>
<?php   } ?>
            </table>
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
                        <a class="btn-floating info"><?=$w.'*'.$h?> 尺寸</a>
                        <a class="btn-floating" target="_blank" href="<?=htmlspecialchars($urlsee)?>">查看大图</a>
                        <a class="btn-floating del" href="<?=htmlspecialchars($urldel)?>">删除图片</a>
                        <a class="btn-floating info">　</a>
                        <a class="btn-floating info">　</a>
                        <a class="btn-floating info">　</a>
                    </div>
                </div>
<?php   } ?>
            </div>
<?php   mkFooter();
        exit();
