<?php //ssrc/res.php

//require_once('debug.php'); // 开启调试
set_time_limit(600); //For Uploading File, 10分钟
require_once('cres.php');
/**


    资源引用


 **/
if (isset($_GET['img'])) {
    $id_file     = decodeCSID($_GET['img']);
    $action = DStr($_GET['action'], 'ls');
    switch ($action) {
        case 'del':
            CheckLogin("DIE");
            $msg = CRes::DelImg($id_file);
            setcookie("msg", $msg, time() + 20);
            if (isset($_SERVER['HTTP_REFERER'])) header('location:' . $_SERVER['HTTP_REFERER']);
            echo $msg;
            exit();
        case 'ls':
            header('location:' . CRes::UrlImg($id_file));
            exit();
    }
}
if (isset($_GET['file'])) {
    $id_file = decodeCSID($_GET['file']);
    $action = DStr($_GET['action'], 'ls');
    $filename = DStr($_GET['filename'], 'hawkaoc');
    switch ($action) {
        case 'del':
            CheckLogin("DIE");
            $msg = CRes::DelFile($id_file);
            setcookie("msg", $msg, time() + 20);
            if (isset($_SERVER['HTTP_REFERER'])) header('location:' . $_SERVER['HTTP_REFERER']);
            echo $msg;
            exit();
        case 'ls':
            header('location:' . CRes::UrlFile($id_file));
            exit();
        case 'download':
            //
            $filePath = CRes::UrlFile($id_file);

            $fileHandle = fopen($filePath, "rb");
            if ($fileHandle === false) {
                PageError('文件不存在，请联系翔鹰制作组管理员 QQ群：434763036');
            }

            //die(urlencode($filename));
            //文件类型是二进制流。设置为utf8编码（支持中文文件名称）
            header('Content-type: application/octet-stream; charset=utf-8');
            header("Content-Transfer-Encoding: binary");
            header("Accept-Ranges: bytes");

            //文件大小
            header("Content-Length: " . filesize($filePath));

            //触发浏览器文件下载功能
            // header('Content-Disposition:attachment;filename="' . $filename.'"');

            header('Content-Disposition:attachment;filename="' . str_replace('"', '\\"', $filename) . '"');

            //循环读取文件内容，并输出
            while (!feof($fileHandle)) {
                //从文件指针 handle 读取最多 length 个字节（每次输出8k）
                print(fread($fileHandle, 1024 * 8));
                ob_flush();
                flush();
            }

            //关闭文件流
            fclose($fileHandle);

            exit();
    }
}
/**


    资源管理：后端


 **/
if (
    isset($_REQUEST['action'])
    &&  isset($_REQUEST['res'])
) {
    $res   = encodeCSID(decodeCSID($_REQUEST['res']));
    switch ($_REQUEST['action']) {
        case 'DeleteComment':
            CheckLogin("DIE");
            if (!$_REQUEST['cmt']) break;
            CRes::DeleteComment($_REQUEST['cmt']);
            header('location:./view/?res=' . $res);
            exit();
        case 'CommentOO':
            CheckLogin("DIE");
            if (!$_REQUEST['cmt']) break;
            CRes::CommentOO($_REQUEST['cmt']);
            header('location:./view/?res=' . $res);
            exit();
        case 'CommentXX':
            CheckLogin("DIE");
            if (!$_REQUEST['cmt']) break;
            CRes::CommentXX($_REQUEST['cmt']);
            header('location:./view/?res=' . $res);
            exit();
        case 'DeleteReview':
            CheckLogin("DIE");
            if (!$_REQUEST['rvw']) break;
            CRes::DeleteReview($_REQUEST['rvw']);
            header('location:./view/?res=' . $res);
            exit();
        case 'ReviewOO':
            CheckLogin("DIE");
            if (!$_REQUEST['rvw']) break;
            CRes::ReviewOO($_REQUEST['rvw']);
            header('location:./view/?res=' . $res);
            exit();
        case 'ReviewXX':
            CheckLogin("DIE");
            if (!$_REQUEST['rvw']) break;
            CRes::ReviewXX($_REQUEST['rvw']);
            header('location:./view/?res=' . $res);
            exit();
    }
}
if (
    isset($_GET['action'])
    &&  $_GET['action'] == 'new'
) {
    CheckLogin("DIE");
    $resid = CRes::Create();
    header('Location:./res.php?action=edit&res=' . encodeCSID($resid));
    header('ssrcinfo:' . encodeCSID($resid));
    exit();
}
if (isset($_POST['res'])) {
    CheckLogin("DIE");
    $resid = decodeCSID($_POST['res']);
    $cRes  = new CRes($resid);
    $res   = encodeCSID($cRes->id);
    switch ($action = DStr($_POST['action'], 'upload')) {
        case 'del':
            $cRes->Delete();
            header('Location:./');
            exit();
        case 'edit':
            $cRes->Edit($_POST['resname'], $_POST['summary'], $_POST['rescontent'], $_POST['e_res_type']);
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'check':
            if (isset($_POST['checked']))
                $cRes->Check($_POST['author_name'], $_POST['fromurl']);
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'publish':
            if (isset($_POST['published']))
                $cRes->Publish($_SESSION['bbs_uid']);
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'b_gamebase':
            $b_gamebase = CRes::Gamebase($_POST);
            $cRes->SetGamebase($b_gamebase);
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'canEdit':
            echo $cRes->canEdit   ? 'Yes' : 'No';
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'canManage':
            echo $cRes->canManage ? 'Yes' : 'No';
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'canSee':
            echo $cRes->canSee    ? 'Yes' : 'No';
            header('Location:./res.php?action=edit&res=' . $res);
            exit();
        case 'upload':
            ini_set('max_file_uploads', '200');
            $msg = '';
            //得到文件列表
            if (
                isset($_FILES['resfile']) && isset($_FILES['resfile']['error'])
                && isset($_POST['dir'])
            ) {
                $dirid = decodeCSID($_POST['dir']);
                //确认目录存在
                if ($dirid != 0) {
                    $row = CRes::QRow("SELECT 1 FROM resdir WHERE id=$dirid LIMIT 1", '该目录不存在', array('目录:' => encodeCSID($dirid)));
                }
                if (is_array($_FILES['resfile']['error'])) {
                    for ($i = 0; isset($_FILES['resfile']['error'][$i]); $i++) {
                        $msg .= $cRes->BindFile(array(
                            'error'    => $_FILES['resfile']['error'][$i],
                            'name'     => $_FILES['resfile']['name'][$i],
                            'size'     => $_FILES['resfile']['size'][$i],
                            'type'     => $_FILES['resfile']['type'][$i],
                            'tmp_name' => $_FILES['resfile']['tmp_name'][$i],
                        ), $dirid) . "\n";
                    }
                } else {
                    $msg .= $cRes->BindFile($_FILES['resfile'], $dirid) . "\n";
                }
            }
            //得到图片列表
            if (isset($_FILES['resimg']) && isset($_FILES['resimg']['error'])) {
                if (is_array($_FILES['resimg']['error'])) {
                    for ($i = 0; isset($_FILES['resimg']['error'][$i]); $i++) {
                        $msg .= $cRes->BindImg(array(
                            'error'    => $_FILES['resimg']['error'][$i],
                            'name'     => $_FILES['resimg']['name'][$i],
                            'size'     => $_FILES['resimg']['size'][$i],
                            'type'     => $_FILES['resimg']['type'][$i],
                            'tmp_name' => $_FILES['resimg']['tmp_name'][$i],
                        )) . "\n";
                    }
                } else {
                    $msg .= $cRes->BindFile($_FILES['resimg'], $dirid) . "\n";
                }
            }
            $msg .= '上传结束' . "\n";
            setcookie("msg", $msg, time() + 20);
            header('location:./res.php?action=edit&res=' . $res);
            exit();
        case 'CreateComment':
            $cmt = $cRes->CreateComment($_POST['content'], $_POST['vote']);
            header('location:./view/?res=' . $res . '#rescomment');
            exit();
        case 'CreateReview':
            $cRes->CreateReview($_POST['content'], $_POST['vote']);
            header('location:./view/?res=' . $res);
            exit();
    }
} else {
    switch ($action = DStr($_POST['action'], 'upload')) {
        case 'newdir':
            CheckLogin("DIE");
            if (!isset($_POST['dirname'][0])) PageError('目录名字过短警告');
            $dirid = CRes::NewDir($_POST['pdir'], $_POST['dirname']);
            $row = CRes::QRow("SELECT PathDir($dirid) path", '读取目录');
            header('Location:./res.php?action=dirmanage');
            header('ssrcinfo:' . encodeCSID($dirid));
            header('ssrcint:' . $dirid);
            header('ssrcpath:' . DStr($row['path']));
            exit('创建目录成功/' . encodeCSID($dirid) . '|' . $row['path']);
        case 'moddir':
            CheckLogin("DIE");
            if (!isset($_POST['dirname'][0])) PageError('目录名字过短警告');
            CRes::ModDir($_POST['dir'], $_POST['pdir'], $_POST['dirname']);
            $row = CRes::QRow("SELECT PathDir($dirid) path", '读取目录');
            header('Location:./res.php?action=dirmanage');
            header('ssrcinfo:' . encodeCSID($dirid));
            header('ssrcint:' . $dirid);
            header('ssrcpath:' . DStr($row['path']));
            exit('修改目录成功/' . encodeCSID($dirid) . '|' . $row['path']);
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
define('CSIDInsider', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!-._~');
define('CSIDInsiderLEN', strlen(CSIDInsider));
function decodeCSIDInsider($csid)
{ //_
    $s = CSIDInsider;
    $l = CSIDInsiderLEN;
    $n = 0;
    for ($i = 0; $i < strlen($csid); $i++) {
        $p = strpos($s, $csid[$i]);
        if (false === $p) return 0;
        $n += $p * pow($l, $i);
    }
    return $n;
}/* return: (int) */
function encodeCSIDInsider($n)
{ //_
    $s = CSIDInsider;
    $l = CSIDInsiderLEN;
    $csid = '';
    $n = (int)$n;
    if (!($n >= 0))          $n = 0;
    if (!($n <= 4294967297)) $n = 0;
    while ($n !== 0) {
        $csid .= $s[fmod($n, $l)];
        $n /= $l;
        $n = (int)$n;
    }
    if (!isset($csid[0])) $csid = $s[0];
    return (string)$csid;
}/* return: str   */


$bbsid = IInt($_SESSION['bbs_uid']);
if (CRes::GlobalPermissionQ("Manage")) {
    define('SQL_EACHDIR',       "SELECT id, PathDir(id) dirpath FROM resdir ORDER BY CONVERT(dirpath USING gbk)");
    //define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update FROM resdir ORDER BY dirpath");
    define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update, (SELECT COUNT(*) FROM resdir tmp WHERE pid=resdir.id) csdir, (SELECT COUNT(*) FROM resfile WHERE dirid=resdir.id) cfile FROM resdir");
} else {
    define('SQL_EACHDIR',       "SELECT id, PathDir(id) dirpath FROM resdir WHERE $bbsid=author_bbsid OR 0=author_bbsid ORDER BY CONVERT(dirpath USING gbk)");
    define('SQL_EACHDIRDETEAL', "SELECT id, PathDir(id) dirpath, author_bbsid, t_update, (SELECT COUNT(*) FROM resdir tmp WHERE pid=resdir.id) csdir, (SELECT COUNT(*) FROM resfile WHERE dirid=resdir.id) cfile FROM resdir WHERE $bbsid=author_bbsid OR 0=author_bbsid ORDER BY CONVERT(dirpath USING gbk)");
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'dirmanage':
            CheckLogin("DIE");
            if (!CRes::GlobalPermissionQ("ManageDir")) PageError('无权管理目录');
            mkHeader($title = '管理目录'); ?>
            <div class="steps">
                <form class="form" action="./res.php" method="post">
                    <h2>添加目录</h2>
                    <?php //<br>* 这个页面只有管理员可以访问 *</br></br> 
                    ?>
                    目录名称：<br /><input name="dirname" type="text" /><br />
                    上层目录：<br /><select name="pdir">
                        <option value="<?= encodeCSID(0) ?>">/</option>
                        <?php
                        $result = CRes::Q(SQL_EACHDIR, '检索目录');
                        while ($rowdir = mysql_fetch_array($result)) {
                            $dir      = encodeCSID($rowdir['id']);
                            $dirpath  = $rowdir['dirpath']; ?>
                            <option value="<?= $dir ?>"><?= htmlspecialchars($dirpath) ?></option>
                        <?php   } ?>
                    </select><br />
                    <input type="hidden" name="action" value="newdir" /><br />
                    <input type="submit" value="新建目录" />
                </form>
                <form class="form">
                    <h2>管理目录</h2>
                    <b>注意: 这个表格不能承受太大流量</b>
                    <table class="ls">
                        <thead>
                            <tr>
                                <th>路径</th>
                                <th>创建时间</th>
                                <th>创建者</th>
                                <th>子目录</th>
                                <th>含文件</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $result = CRes::Q(SQL_EACHDIRDETEAL, '检索目录'); ?>
                            <?php while ($rowdir = mysql_fetch_array($result)) {
                                $dir      = encodeCSID($rowdir['id']);
                                $dirpath  = $rowdir['dirpath'];
                                $uid      = (int)$rowdir['author_bbsid'];
                                $t_update = (int)$rowdir['t_update'];
                                $time     = $t_update == 0 ? "系统自带目录" : date("Y年M月d日 h:i:s", $t_update);

                                $csdir    = (int)$rowdir['csdir'];
                                $cfile    = (int)$rowdir['cfile'];
                                $delurl   = "./res.php?action=deldir&dir=$dir"; ?>
                                <tr>
                                    <td><?= htmlspecialchars($dirpath) ?></td>
                                    <td><?= $time ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars("//www.hawkaoe.net/bbs/space-uid-$uid.html") ?>" class="avatar" target="_BLANK" <?= $uid == 0 ? '' : 'style="background-image: url(' . htmlspecialchars('\'' . "//www.hawkaoe.net/bbs/uc_server/avatar.php?size=large&uid=$uid" . '\'') . ')"' ?>>
                                    </td>
                                    <td><?= $csdir ?></td>
                                    <td><?= $cfile ?></td>
                                    <td><a href="<?= htmlspecialchars($delurl) ?>" class="del">删除</a></td>
                                </tr>
                            <?php   } ?>
                        </tbody>
                    </table>
                </form>
            </div>
        <?php mkFooter();
            exit();
        case 'deldir':
            CheckLogin("DIE");
            $dirid = decodeCSID($_GET['dir']);
            $dir   = encodeCSID($dirid);
            $row = CRes::QRow("SELECT id, PathDir(id) dirpath, author_bbsid FROM resdir WHERE id=$dirid", '冇这目录');
            if (!CRes::ResDirPermissionQ("Delete", $row)) PageError('你不能删除这个目录。');
            $dirpath = $row['dirpath'];
            mkHeader('删除目录'); ?>
            <!-- TODO: 显示目录下子文件数 -->
            <form class="form" action="./res.php" method="post">
                <h1>确认删除目录？[<?= htmlspecialchars(mb_strimwidth($dir, 0, 45, '...', 'utf-8')) ?>]</h1>
                目录名称：<br /><input name="dirname" type="text" disabled value="<?= htmlspecialchars($dirpath) ?>" /><br />
                <input type="hidden" name="dir" value="<?= encodeCSID($dirid) ?>" />
                <input type="hidden" name="action" value="deldir" /><br />
                <input type="submit" class="del" value="删除目录" /><br />
            </form>
        <?php mkFooter();
            exit();
    }
}
if (isset($_GET['res'])) {
    $resid   = decodeCSID($_GET['res']);
    $cRes    = new CRes($resid);
    $res     = encodeCSID($cRes->id);
    $row     = $cRes->row;
    $resname = $row['name'];
    switch ($action = DStr($_GET['action'], 'view')) {
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
            foreach ($b_gamebase_array as $value) {
                echo $value . ';';
            }
            header('Location:./res.php?action=view&res=' . $res);
            exit();
        case 'del':
            CheckLogin("DIE");
            if (!$cRes->PermissionQ("Delete")) pageError('你不能删除这个资源', encodeCSID($resid));
            mkHeader($title = '删除资源 ' . !isset($resname[0]) ? '未命名资源' . $res
                : htmlspecialchars(mb_strimwidth($resname, 0, 45, '...', 'utf-8'))); ?>
            <form class="form" action="./res.php" method="post">
                <h1>确认删除资源？<br /><?= htmlspecialchars(mb_strimwidth($resname, 0, 45, '...', 'utf-8')) ?></h1>
                <input type="hidden" name="res" value="<?= encodeCSID($resid) ?>" />
                <input type="hidden" name="action" value="del" /><br />
                <input type="submit" class="del" value="删除资源" /><br />
            </form>
<?php mkFooter();
            exit();
        case 'edit':
            // 资源编辑页面转移到 res_edit.php
            header("location: ./res_edit.php?res=" . $_GET['res']);
            exit();
    }
}
/**


    无效访问


 **/
header('Location:./');
exit('无效访问');
