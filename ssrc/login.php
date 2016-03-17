<?php // /ssrc/login.php
/*
输入
    身份：「'u'」或「'u'之下3个之1」
    $_POST['u']           // 用户名或ID或邮箱
    $_POST['uid']
    $_POST['username']
    $_POST['email']

    密码：以下2个其中一个
    $_POST['p']           // password
    $_POST['pmd5']        // password_md5

输出
    $_SESSION['bbs_uid']
    $_SESSION['bbs_uname']
*/


// 登出
@session_start();
header("content-type:text/html; charset=utf-8");

require_once('func.php');

function endWith($haystack, $needle)
{
    $length = strlen($needle);
    if($length == 0)
        return true;
    return (substr($haystack, -$length) === $needle);
}
function GoReferer(){
    if(isset($_SERVER['HTTP_REFERER']))  $referer = $_SERVER['HTTP_REFERER'];
    if(isset($_REQUEST['HTTP_REFERER'])) $referer = $_REQUEST['HTTP_REFERER'];

    if(isset($referer) && isset($referer{1}) && !endWith($referer, $_SERVER['PHP_SELF'])){
        header("Referer: http://www.hawkaoc.net");
        header("Location:".$referer);
        exit();
    }
}
if(isset($_REQUEST['logout'])){
    unset($_SESSION['bbs_uid']);
    unset($_SESSION['bbs_uname']);
    header('location: ./login.php');
    exit();
}

// 已登录
if(isset($_SESSION['bbs_uname']) AND isset($_SESSION['bbs_uid'])){
    $session_name = $_SESSION['bbs_uname'];
    $session_uid  = $_SESSION['bbs_uid'];
    GoReferer();
    $title='己登錄到翔鷹論壇';
    mkHeader($title);
?>
        <form class="form">
            <h1>己登錄到翔鷹論壇</h1>
            账号：<input type="text" disabled value="<?=$session_uid?>"/><br />
            昵称：<input type="text" disabled value="<?=$session_name?>"/><br />
            <a href="login.php?logout" class="btn-large del">登出</a><br />
        </form>
<?php
    mkFooter();
    exit();
}

$_regEmail = '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/u';
$_regMD5   = '/^[0-9a-f]{32}$/u';

// 取用户名
if(isset($_POST['u'])){
    $u = $_POST['u'];
    if(is_numeric($u))                         $uid          = $u;
    if(is_string($u))                          $username     = $u;
    if(preg_match($_regEmail, $u))             $email        = $u;
}
if(isset($_POST['username']))                  $username     = $_POST['username'];
if(isset($_POST['uid']     ))                  $uid          = $_POST['uid'];
if(isset($_POST['email']   ))                  $email        = $_POST['email'];
if(!isset($uid) AND !isset($username) AND !isset($email)){

    $title='登錄到翔鷹論壇';
    mkHeader($title);
//不给用户名就问丫要！
?>
        <form action="login.php" method="post" class="form">
            <h1>登錄到翔鷹論壇</h1>
            账号：<input name="u" type="text" placeholder="uid|用户名|邮箱|" /><br />
            密码：<input name="p" type="password" /><br />
            <?php if(isset($_SERVER['HTTP_REFERER'])){ ?>
            <input type="hidden" name="HTTP_REFERER" value="<?=$_SERVER['HTTP_REFERER']?>" />
            <?php } ?>
            <input type="submit" value="登录" class="btn-large"/>
        </form>
<?php
    mkFooter();
}else{
    ///////////////////////////////////////////////////////////////
// 取密码
if(isset($_POST['p'])                        ) $password     = $_POST['p'];
if(isset($_POST['pmd5'])                     ) $password_md5 = strtolower($_POST['pmd5']);
if(!isset($password_md5) AND isset($password)) $password_md5 = md5($password);

// 测试
/*
if(isset($uid)         ) echo 'uid = '          . $uid          . '<br />';
if(isset($username)    ) echo 'username = '     . $username     . '<br />';
if(isset($email)       ) echo 'email = '        . $email        . '<br />';

if(isset($password)    ) echo 'password = '     . $password     . '<br />';
if(isset($password_md5)) echo 'password_md5 = ' . $password_md5 . '<br />';
*/

// 连接数据库
require_once('connbbs.php');

// 用户信息
if(isset($username))                    $username       = iconv("utf-8", "GBK//IGNORE", $username);
if(isset($uid) AND is_numeric($uid)   ) $uid_s          = $uid;
if(isset($username)                   ) $username_s     = "'".mysql_real_escape_string($username    )."'";
if(isset($email)                      ) $email_s        = "'".mysql_real_escape_string($email       )."'";
if(preg_match($_regMD5, $password_md5)) $password_md5_s = "'".mysql_real_escape_string($password_md5)."'";
if(!isset($uid_s)AND!isset($username_s)AND!isset($email_s))            die('登录失败/用户名或密码错误');
if(!isset($password_md5_s))                                            die('登录失败/用户名或密码错误');


$lsParam   = array();
if(isset($username_s)) $lsParam[] = "username=$username_s";
if(isset($uid_s))      $lsParam[] = "uid=$uid_s";
if(isset($email_s))    $lsParam[] = "email=$email_s";

$strParams = implode(  $lsParam, ' OR ');
//$query     = "SELECT uid,username FROM x15_ucenter_members WHERE md5(concat('$password_md5_s',  salt))=password AND ($strParams)";
$query     = "SELECT uid,username,groupid FROM x15_common_member WHERE uid=(SELECT uid FROM x15_ucenter_members WHERE md5(concat($password_md5_s,  salt))=password AND ($strParams))";
$result                = mysql_query($query)                             OR die("登录失败/用户名或密码错误");
$row                   = mysql_fetch_array($result)                      OR die("登录失败/用户名或密码错误");
$result_uid            = isset($row['uid'])      ? (int)$row['uid']      :  die("登录失败/数据库设置错误，请联系管理员");
$result_groupid        = isset($row['groupid'])  ? (int)$row['groupid']  :  die("登录失败/数据库设置错误，请联系管理员");
$result_username       = isset($row['username']) ?      $row['username'] :  die("登录失败/数据库设置错误，请联系管理员");
$result_username       = iconv("GBK", "utf-8//IGNORE", $result_username);



$_SESSION['bbs_groupid'] = $result_groupid;
$_SESSION['bbs_uid']     = $result_uid;
$_SESSION['bbs_uname']   = $result_username;


// 勋章
//110 创意工坊通行证 协助完成创意工坊资源维护的证明

$query                 = "SELECT medalid FROM x15_common_member_medal WHERE uid=$result_uid";
$result                = mysql_query($query)                             OR die("登录失败/用户名或密码错误");

$user_medals = array();
while ($row = mysql_fetch_array($result)) {
    array_push($user_medals, $row['medalid']);
}

$_SESSION['bbs_umedals']   = $user_medals;


if(isset($_SERVER['HTTP_REFERER'])) header("location:".$_SERVER['HTTP_REFERER']);
exit("登录成功/$result_uid|$result_username");
}?>