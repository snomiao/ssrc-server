<?php
/**


                                                    基本函数


**/

// WWW Redirect   //_
    $host = strtolower($_SERVER["HTTP_HOST"]);
    if(in_array($host, array('hawkaoc.net'))) {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location:http://www.'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
        exit();
    }

// User Verify     //_
    @session_start();
    function CheckLogin($die = false){
        if(basename($_SERVER['PHP_SELF']) != 'login.php'){
            if(!isset($_SESSION['bbs_uid']) OR !isset($_SESSION['bbs_uname'])){
                if($die){
                    header('Location: ./login.php');
                    die('校验失败/请先登录'.$htmNavLogin);
                }
                return false;
            }else{
                return true;
            }
        }
        header("content-type:text/html; charset=utf-8");
    }
// gzip           //_
    if( !headers_sent() // 如果页面头部信息还没有输出
    &&  extension_loaded("zlib") // 而且php已经加载了zlib扩展
    &&  strstr(DStr($_SERVER["HTTP_ACCEPT_ENCODING"]),"gzip")){ //而且浏览器接受GZIP
        ini_set('zlib.output_compression', 'On');
        ini_set('zlib.output_compression_level', '4');
    }
// Safe SQL       //_
    function DStr     (&$val, $def=''){//_
        return isset($val) ? $val : $def;
    }/* return: str*/
    function IInt     (&$val, $def=0 ){//_
        if(!isset($val)) $val = $def;
        return intval(is_numeric($val) ? $val : $def);
        // intval: 限制数字范围 -> signed int
    }/* return: (int)*/
    function IStr     (&$val, $def=''){//_
        $ret = DStr($val, $def);
        if (get_magic_quotes_gpc()) // 去除斜杠
            $ret = stripslashes($ret);
        return '\'' . mysql_real_escape_string($ret) . '\'';
    }/* return: str*/
    function IStrQuery($column, &$val){//_
        return !isset($val) ? '' : ','.$column.'='.IStr($val);
    }/* return: str*/
// CSID translate //_
    define('CSID'   , '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!-._~');
    define('CSIDLEN', strlen(CSID));
    function decodeCSID($csid){//_
        $s = CSID;
        $l = CSIDLEN;
        $n = 0;
        for($i = 0; $i < strlen($csid); $i++){
            $p = strpos($s, $csid[$i]);
            if(false===$p) return 0;
            $n += $p * pow($l, $i);
        }
        return $n;
    }/* return: (int) */
    function encodeCSID($n   ){//_
        $s = CSID;
        $l = CSIDLEN;
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
// Size Translate
    function GetUnit($unit){
        $ls_unit = array('B','K','M','T');
        if($unit == 0)
            return $ls_unit[0];
        elseif($unit < count($ls_unit))
            return $ls_unit[$unit] . $ls_unit[0];
        else
            return $ls_unit[count($ls_unit)-1] . GetUnit($unit - count($ls_unit) + 1);
    }/* return: str*/
    function TextSize($size, $unit=0){
      return $size < 1024 ? round($size,1) . GetUnit($unit) : TextSize($size/1024, $unit+1);
    }/* return: str*/

// template
    function MkHeader($title, $suffix = '帝国资源中心'){//_
?>
<!DOCTYPE html>
<html lang="cn">
    <head>
        <meta name="description" content="帝国时代2资源中心是一个帝国时代2的资源库，其中包括但不限于录像、战役、场景、MOD、等资源，并拥有自主研发的一站式安装工具。">
        <meta name="keywords" content="age,empires,ageofempires2,AOC,AOK,下载,世紀帝國,帝国,帝国2,帝国时代,帝国时代2,帝国时代2,帝国时代2下载,帝国时代2中文版下载,帝国时代2修改器,帝国时代2攻略,帝国时代2汉化,帝国时代2秘籍,帝国时代2配置,帝国时代2高清版,帝国时代2资源,帝國時代,帝王世纪,征服者,资源,资源中心,遗忘的帝国,遗忘帝国,遗朝">
        <meta name="author" content="Snowstar">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body::before{
                content: "正在加载";
                text-align: center;
                display: block;
                font-size: 10em;
                height: 10em;
                width: 100%;
                background-color: white;
                z-index: 10;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="rc.css">
        <title><?=$title?> - <?=$suffix?></title>
    </head>
    <body>
        <div id="header">
            <ul class="nav">
                <li><a class="btn-mini" href="./">资源中心</a></li>
                <li><a class="btn-mini" href="http://www.hawkaoc.net">论坛首页</a></li>
                <li><a class="btn-mini" href="./res.php?action=dirmanage">管理目录</a></li>
            </ul>
        </div>
        <div id="content">
<?php
    }/* return: OUTPUT */
    function MkFooter($content = ''                  ){//_
?>
        </div>
        <div id="footer">
<?=$content?>
        </div>
    </body>
</html>
<?php
    }/* return: OUTPUT */

// error page
    function PageError($error, $info = ''){//_
        MkHeader($error); ?>
        <div class="form">
            <h1><?=$error?> - 帝国资源中心</h1>
<?php   if(!empty($info))
        if(is_string($csid = $info)){?>
            资源号：<input type="text" disabled value="<?=$csid?>" /><br />
<?php   }else
        if(is_array($info))
        foreach($info as $name => $csid){?>
            <?=$name?>：<input type="text" disabled value="<?=$csid?>" /><br />
<?php   } ?>
            <a class="btn" href=".">返回首页</a><br/>
<?php   if(isset($_SERVER['HTTP_REFERER'])){ ?>
            <a class="btn" href="<?=$_SERVER['HTTP_REFERER']?>">返回上页</a>
<?php   } ?>
        </div>
<?php   MkFooter();
        exit();
    }/* return: DIE */
    function PageError404()               {//_
        header('HTTP/1.1 404 Not Found');
        PageError('404 页面未找到');
    }/* return: DIE */