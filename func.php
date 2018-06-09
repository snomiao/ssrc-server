<?php
/**


                                                    基本函数


**/

// 安全转义 SQL 和 HTML       //_
    function DStr        (&$val, $def=''               ){//_
        return isset($val) ? $val : $def;
    }/* 返回: str */ /* 返回 字串 或 $def 的值 */
    function IInt        (&$val, $def=0                ){//_
        if(!isset($val)) $val = $def;
        return intval(is_numeric($val) ? $val : $def);
        // intval: 限制数字范围 -> signed int
    }/* 返回: int */ /* 返回 SQL安全数字 或 $def 的值。返回值会限制到 (int) 的范围。 */
    function IStr        (&$val, $def=''               ){//_
        $ret = DStr($val, $def);
        if (get_magic_quotes_gpc()) // 去除斜杠
            $ret = stripslashes($ret);
        return '\'' . mysql_real_escape_string($ret) . '\'';
    }/* 返回: str */ /* 返回 SQL安全字串, 自动使用 isset */
    function IStrQuery   ($prefix, &$val               ){//_
        return !isset($val) ? '' : ','.$prefix.'='.IStr($val);
    }/* 返回: str */ /* 此函数己不推荐使用。返回一个 UPDATE 的子 SQL安全字串，或空字串 */
    function IStrOptional(&$val, $prefix='', $suffix=''){//_
        return !isset($val) ? '' : $prefix . IStr($val) . $suffix;
    }/* 返回: str */ /* 如果 val 有被设定，则返回一个带前后缀的SQL安全字串 */
    function HStr        (&$val, $def=''               ){//_
        return !isset($val) ? $def : htmlspecialchars($val);
    }/* 返回: str */ /* 返回 HTML安全字串, 自动使用 isset，如未定义，返回 $def 的值 */
    function HStrOptional(&$val, $prefix='', $suffix=''){//_
        return !isset($val) ? '' : $prefix . htmlspecialchars($val) . $suffix;
    }/* 返回: str */ /* 如果 val 有被设定，则返回一个带前后缀的 HTML安全字串 */


// WWW 重定向   //_
    $host = strtolower($_SERVER["HTTP_HOST"]);
    if(in_array($host, array('hawkaoc.net'))) {
        header('HTTP/1.1 301 Moved Permanently'); // 301 页面永久移动
        header('Location:http://www.'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
        exit();
    }

// 用户验证     //_
    @session_start();
    function CheckLogin($die = false){//_
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
    } /* 返回: boolean */ /* 返回用户是否己登录，如设置了 die=ture 且用户未登录，则自动跳转到登录页面 */
// 使用 gzip 压缩网页         //_
    if( !headers_sent() // 如果页面头部信息还没有输出
    &&  extension_loaded("zlib") // 而且php已经加载了zlib扩展
    &&  strstr(DStr($_SERVER["HTTP_ACCEPT_ENCODING"]),"gzip")){ //而且浏览器接受GZIP
        ini_set('zlib.output_compression', 'On');
        ini_set('zlib.output_compression_level', '4');
    }
// CSID 翻译 //_
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
    }/* 返回: (int) */
    function encodeCSID($n   ){//_
        $s = CSID;
        $l = CSIDLEN;
        $csid = '';
        $n = (int)$n;
        if(!($n>=0))          $n = 0;
        if(!($n<=4294967297)) $n = 0;
        while($n !== 0){
            $csid .= $s[(int)fmod($n, $l)];
            $n /= $l;
            $n = (int)$n;
        }
        if(!isset($csid[0])) $csid = $s[0];
        return (string)$csid;
    }/* 返回: str   */
// Size 翻译 //_
    function GetUnit($unit){//_
        $ls_unit = array('B','K','M','T');
        if($unit == 0)
            return $ls_unit[0];
        elseif($unit < count($ls_unit))
            return $ls_unit[$unit] . $ls_unit[0];
        else
            return $ls_unit[count($ls_unit)-1] . GetUnit($unit - count($ls_unit) + 1);
    }/* 返回: str*/ /* 获取表达文件大小级别所需的单位，支持复合单位 */
    function TextSize($size, $unit=0){//_
      return $size < 1024 ? round($size,1) . GetUnit($unit) : TextSize($size/1024, $unit+1);
    }/* 返回: str*/
// 一些模板
    function MkHeader($title, $suffix = '帝国资源中心'){//_
?>
<!DOCTYPE html>
<html lang="cn">
    <head>
        <base href="/ssrc/">
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
        <link rel="stylesheet" type="text/css" href="css/rc.css">
        <title><?=$title?> - <?=$suffix?></title>
    </head>
    <body>
        <div id="header">
            <ul class="nav">
                <li><a class="btn-mini" href="./">资源中心</a></li>
                <li><a class="btn-mini" href="http://www.hawkaoe.net">论坛首页</a></li>
                <li><a class="btn-mini" href="./login.php">登录论坛</a></li>
                <li><a class="btn-mini" href="./res.php?action=dirmanage">管理目录</a></li>
            </ul>
        </div>
        <div id="content">
<?php
    }/* 返回: OUTPUT */
    function MkFooter($content = ''                  ){//_
?>
        </div>
        <div id="footer">
<?=$content?>
        </div>
    </body>
</html>
<?php
    }/* 返回: OUTPUT */
// 显示错误页面
    function PageError($error, $info = ''){//_
        MkHeader($error); ?>
<div class="form">
    <h1><?=$error?> - 帝国资源中心</h1>
<?php   if(!empty($info)){
            if(is_string($csid = $info)){ ?>
    资源号：<input type="text" disabled value="<?=$csid?>" /><br />
<?php       }else if(is_array($info)){
                foreach($info as $name => $csid){ ?>
    <?=$name?>：<input type="text" disabled value="<?=$csid?>" /><br />
<?php           } ?>
    <a class="btn" href=".">返回首页</a><br/>
<?php           if(isset($_SERVER['HTTP_REFERER'])){ ?>
    <a class="btn" href="<?=$_SERVER['HTTP_REFERER']?>">返回上页</a>
<?php           } ?>
<?php       } ?>
</div>
<?php   }
        MkFooter();
        exit();
    }/* 返回: DIE */
    function PageError404()               {//_
        header('HTTP/1.1 404 Not Found');
        PageError('404 页面未找到');
    }/* 返回: DIE */
// e_type 翻译
    function ListE_Type(){
        return array(
            'cpx'       => '战役',
            'scx'       => '场景',
            'mgx'       => '录像',
            'gax'       => '存档',
            'rms'       => '随机地图',
            'drs'       => 'MOD.drs',
            'mod'       => 'MOD',
            'hki'       => '键位表',
            'tau'       => '嘲讽',
            'ai'        => 'AI',
            'avi'       => '过场动画',
            'mp3'       => '音乐',
            'tool'      => '工具',
            'undefined' => '未知',
            'lang'      => '语言',
        );
    }
    function GetE_Type($e_type){//_
      return DStr(ListE_Type()[$e_type], ListE_Type()['undefined']);
    }/* 返回: str */
    function GetE_TypeAbbr($e_type_name){//_
      return array_search($e_type_name, ListE_Type());
    }/* 返回: str */
// DEBUG 相关函数
    function DEBUG_TagLog($var){ 
?>      <script type="text/debug_log">
            <?php var_dump($var); ?>
        </script>
<?php
    }