<?php
error_reporting(E_ALL);
function GetE_Type($e_type){
  $ls_e_type = array(
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
  );
  return isset($ls_e_type[$e_type]) ? $ls_e_type[$e_type] : $ls_e_type['undefined'];
}
////////////////////
function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}
function GetComeFrom(&$url){
    if(!isset($url[0])) return '';
    if( startsWith($url, 'http://amt.hawkaoc.net/'            ) ) return "帝国资源中心";
    if( startsWith($url, 'http://www.hawkaoc.net/'            ) ) return "翔鹰帝国论坛";
    if( startsWith($url, 'http://hawkaoc.net/'                ) ) return "翔鹰帝国论坛";
    if( startsWith($url, 'http://aoczone.net/'                ) ) return "AoCZone\nVoobly官方论坛";
    if( startsWith($url, 'http://aoe.heavengames.com/'        ) ) return "AoE Heaven\n帝国时代天堂";
    if( startsWith($url, 'http://aoe3.heavengames.com/'       ) ) return "AoE3 Heaven\n帝国时代3天堂";
    if( startsWith($url, 'http://aok.heavengames.com/'        ) ) return "AoK Heaven\n帝国时代2天堂";
    if( startsWith($url, 'http://aom.heavengames.com/'        ) ) return "AoM Heaven\n神话时代天堂";
    if( startsWith($url, 'http://diguobbs.com/'               ) ) return "帝国战网";
    if( startsWith($url, 'http://forum.gamer.com.tw/'         ) ) return "巴哈姆特";
    if( startsWith($url, 'http://tieba.baidu.com/'            ) ) return "百度贴吧";
    if( startsWith($url, 'http://userpatch.aiscripters.net/'  ) ) return "UserPatch\nUserpatch官网";
    if( startsWith($url, 'http://www.1mod.org/'               ) ) return "中华MOD网";
    if( startsWith($url, 'http://www.33hudong.com/'           ) ) return "33互动";
    if( startsWith($url, 'http://www.ageofempires.com/'       ) ) return "帝国时代官网";
    if( startsWith($url, 'http://www.ageofempires.com/forums/') ) return "帝国官方论坛";
    if( startsWith($url, 'http://www.ensemblestudios.com/'    ) ) return "全效工作室";
    if( startsWith($url, 'http://www.forgottenempires.net/'   ) ) return "AoE2: FE\n被遗忘的帝国MOD官网";
    if( startsWith($url, 'http://www.voobly.com/'             ) ) return "Voobly\nVoobly 国际联机平台";
    return '查看来源';
}
////////////////////
require_once('../func.php');
require_once('../cres.php');
if(!isset($_GET['res'])) header("location: ../");
function DefaultString(&$str, $default){
    return !isset($str) ? $default : !isset($str[0]) ? $default : $str;
}

$resid = decodeCSID($_GET['res']);
$row = CRes::QRow("SELECT * FROM res WHERE id=$resid", "定位资源", $resid);
$res = encodeCSID($resid);
if($row === false) PageError('该资源不存在', $resid);
$txt_author_bbsid = (int)$row['author_bbsid'];
$txt_author_name  = $row['author_name'];
$txt_come_from    = GetComeFrom($row['fromurl']);
$txt_downlaods    = $row['count_download'];
$txt_e_type       = GetE_Type($row['e_type']);
$txt_size         = TextSize((int)$row['totalsize']);
$txt_title        = $row['name'];
$txt_content      = DefaultString($row['content'], '未填写资源说明');
$txt_update_time  = date('Y年m月d日', (int)$row['t_fileup'] ? (int)$row['t_fileup'] : (int)$row['t_update'] ? (int)$row['t_update'] : (int)$row['t_create']);
$url_install      = "安装\n$txt_size";
$url_come_from    = $row['fromurl'];
$url_install      = "./../res.php?action=download&res=$res";
$url_install      = "amt://?a=dl&res=$res&cutf=%E5%A6%82%E6%9E%9C%E4%BD%A0%E7%9C%8B%E5%88%B0%E4%BA%86%E8%BF%99%E5%8F%A5%E8%AF%9D%EF%BC%8C%E8%AF%B4%E6%98%8E%E4%BD%A0%E9%9C%80%E8%A6%81%E5%AE%89%E8%A3%85%E5%B8%9D%E5%9B%BD%E6%97%B6%E4%BB%A3%E7%AE%A1%E5%AE%B6%EF%BC%8C%E8%AF%B7%E5%89%8D%E5%BE%80www.hawkaoc.net%E4%B8%8B%E8%BD%BD";
//imagelist
$ls_img_url = array();
$ls_img_txt = array();
$ls_img_rnd = array();
$ls_img_cx  = array();
$ls_img_cy  = array();
$k = 0;
$result = CRes::Q('SELECT * FROM resimg WHERE resid='.$resid, '检索图片', $resid);
while($row = mysql_fetch_array($result)){
  $csid           = encodeCSID($row['id'   ]);
  $ls_img_txt[$k] = $row['comment'];
  $ls_img_url[$k] = './../res.php?img='.$csid;
  $ls_img_rnd[$k] = encodeCSID(mt_rand());
  $ls_img_cx[$k]  = (int)$row['w'];
  $ls_img_cy[$k]  = (int)$row['h'];
  $k++;
}

///////////
$ls_resreview_txt_content   = array();
$ls_resreview_author_bbsid  = array();
$ls_resreview_author_name   = array();
$ls_resreview_vote          = array();
$ls_resreview_oo            = array();
$ls_resreview_xx            = array();
$ls_resreview_txt_time      = array();
$k = 0;
$result = CRes::Q("SELECT * FROM resreview WHERE resid=$resid", '检索评分', $resid);
while($row = mysql_fetch_array($result)){
  $csid           = encodeCSID($row['id'   ]);
  $ls_resreview_csid[$k]         = $csid;
  $ls_resreview_txt_content[$k]  = $row['content'];
  $ls_resreview_author_bbsid[$k] = $row['author_bbsid'];
  $ls_resreview_author_name[$k]  = $row['author_name'];
  $ls_resreview_vote[$k]         = (int)$row['vote'] / 1000;
  $ls_resreview_oo[$k]           = $row['oo'];
  $ls_resreview_xx[$k]           = $row['xx'];
  $ls_resreview_txt_time[$k]     = date('Y/m/d H:i:s', $row['t_update']);
  $k++;
}
///////////
$ls_rescomment_txt_content   = array();
$ls_rescomment_author_bbsid  = array();
$ls_rescomment_author_name   = array();
$ls_rescomment_vote          = array();
$ls_rescomment_oo            = array();
$ls_rescomment_xx            = array();
$ls_rescomment_txt_time      = array();
$j = 0;
$result = CRes::Q("SELECT * FROM rescomment WHERE resid=$resid", '检索评论', $resid);
while($row = mysql_fetch_array($result)){
  $csid           = encodeCSID($row['id'   ]);
  $ls_rescomment_csid[$j]         = $csid;
  $ls_rescomment_txt_content[$j]  = $row['content'];
  $ls_rescomment_author_bbsid[$j] = $row['author_bbsid'];
  $ls_rescomment_author_name[$j]  = $row['author_name'];
  $ls_rescomment_vote[$j]         = (int)$row['vote'] / 1000;
  $ls_rescomment_oo[$j]           = $row['oo'];
  $ls_rescomment_xx[$j]           = $row['xx'];
  $ls_rescomment_txt_time[$j]     = date('Y/m/d H:i:s', $row['t_update']);
  $k++;
}
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
    <link rel="stylesheet" type="text/css" href="../rc.css">
    <link rel="stylesheet" type="text/css" href="./view.css">
    <!-- <script type="text/javascript" src="http://cssrefresh.frebsite.nl/js/cssrefresh.js"></script> -->
    <title><?=htmlspecialchars($txt_title)?> - 帝国资源中心</title>
  </head><body>
    <div id="header">
      <ul class="nav">
        <li>
          <a class="btn-mini" href="./../">资源中心</a>
        </li><li>
          <a class="btn-mini" href="http://www.hawkaoc.net">论坛首页</a>
        </li><li>
          <a class="btn-mini" href="./../res.php?action=dirmanage">管理目录</a>
        </li>
      </ul>
    </div><div id="content">
      <iframe name="ssrcAsync" style="display: none"></iframe>
      <div class="resview">
        <div class="header">
          <span class="e_type"><?=htmlspecialchars($txt_e_type)?></span><h1 class="title"><?=htmlspecialchars($txt_title)?></h1>
        </div>
        <div class="content">
          <ul class="main">
            <li id="rescontent" class="content"><?=htmlspecialchars($txt_content)?></li>
<?php   foreach ($ls_img_rnd as $k => $rnd) { ?>
            <li id="<?=$rnd?>"><img src="<?=htmlspecialchars($ls_img_url[$k])?>" width="$ls_img_cx[$k]" height="$ls_img_cy[$k]"/></li>
<?php   } ?>
          </ul><div class="side">
            <ul class="info">
              <li><a id="install" title="点击安装" href="<?=$url_install?>" target="_BLANK">安装<br /><?= htmlspecialchars($txt_size) ?></a></li>
<?php if(isset($txt_come_from[0])){ ?>
              <li><a id="source" title="来源" href="<?=$url_come_from?>"><?= htmlspecialchars($txt_come_from) ?></a></li>
<?php } ?>
              <li><a id="author" title="作者" href=""><?= htmlspecialchars($txt_author_name) ?></a></li>
              <li><a id="update" title="更新时间"><?= htmlspecialchars($txt_update_time) ?>更新</a></li>
              <li><a id="downloads" title="安装数"><?= "安装数 ".htmlspecialchars($txt_downlaods) ?></a></li>
            </ul><ul class="index-img">
              <li><a title="描述" href="#rescontent" class="content">描述</a></li>
<?php   foreach ($ls_img_rnd as $k => $rnd) { ?>
              <li><a href="#<?=$rnd?>" style="background-image: url(&quot;<?=htmlspecialchars($ls_img_url[$k])?>&quot;)"></a></li>
<?php   } ?>
            </ul>
          </div>
        </div>
<?php //        <ul class="tags">                                                                                           ?>
<?php //          <li><a href="#标签1">标签1</a></li><li><a href="#标签2">标签2</a></li><li><a href="#标签3">标签3</a></li>             ?>
<?php //        </ul>                                                                                                       ?>
        <ul class="review review-comment">
<?php   foreach ($ls_resreview_vote as $k => $vote) { ?>
          <li>
            <div class="header">
              <span class="update"><?=htmlspecialchars($ls_resreview_txt_time[$k])?> 的</span>
              <span class="author"><?=htmlspecialchars($ls_resreview_author_name[$k])?></span>
              <span class="vote"><?=htmlspecialchars($ls_resreview_vote[$k])?>分</span>
            </div>
            <div class="main">
              <div class="content"><?=htmlspecialchars($ls_resreview_txt_content[$k])?></div>
              <ul class="btnbar">
                <li>
<?php     if(CheckLogin()){ ?>
                  <form action="../res.php" method="post">
                    <input type="hidden" name="rvw" value="<?=$ls_resreview_csid[$k]?>" />
                    <input type="hidden" name="res" value="<?=$res?>" />
                    <input type="hidden" name="action" value="DeleteReview" />
                    <input type="submit" value="删除评分" />
                    </form>
                </li>
                <li><a href="#">有理。</a></li>
                <li><a href="#">胡言！</a></li>
<?php     } ?>
              </ul>
            </div>
          </li>
<?php   }
        if(CheckLogin()){ ?>
          <li class="mynew">
            <form action="../res.php" method="post">
              <input type="hidden" name="res" value="<?=$res?>" />
              <input type="hidden" name="action" value="CreateReview" />
              <div class="header">
                <span class="update">现在 的</span>
                <span class="author"><?=htmlspecialchars($_SESSION['bbs_uname'])?></span>
                <input type="text" name="vote" class="vote" value="5.00分" />
              </div>
              <div class="main">
                <textarea class="content" name="content"></textarea>
                <ul class="btnbar">
                  <li><input type="submit" value="评分！" /></li>
                </ul>
              </div>
            </form>
          </li>
<?php } ?>
        </ul>
        <ul class="comment review-comment">
<?php   foreach ($ls_rescomment_vote as $k => $vote) { ?>
          <li>
            <div class="header">
              <span class="update"><?=htmlspecialchars($ls_rescomment_txt_time[$k])?> 的</span>
              <span class="author"><?=htmlspecialchars($ls_rescomment_author_name[$k])?></span>
              <span class="vote"><?=htmlspecialchars($ls_rescomment_vote[$k])?></span>
            </div>
            <div class="main">
              <div class="content"><?=htmlspecialchars($ls_rescomment_txt_content[$k])?></div>
              <ul class="btnbar">
                <li><a href="#">同意。</a></li>
                <li><a href="#">反对！</a></li>
              </ul>
            </div>
          </li>
<?php   }
        if(CheckLogin()){ ?>
          <li class="mynew">
            <form action="../res.php" method="post">
              <input type="hidden" name="res" value="<?=$res?>" />
              <input type="hidden" name="action" value="CreateComment" />
              <div class="header">
                <span class="update">现在 的</span>
                <span class="author"><?=htmlspecialchars($_SESSION['bbs_uname'])?></span>
                <div class="vote">
                  <input type="radio" name="vote" value="5" />5
                  <input type="radio" name="vote" value="4" />4
                  <input type="radio" name="vote" value="3" />3
                  <input type="radio" name="vote" value="2" />2
                  <input type="radio" name="vote" value="1" />1
                </div>
              </div>
              <div class="main">
                <textarea class="content" name="content"></textarea>
                <ul class="btnbar">
                  <li><input type="submit" value="评论！" /></li>
                </ul>
              </div>
            </form>
          </li>
<?php } ?>
        </ul>
      </div>
    </div>
    <div id="footer">
    </div>
  </body>
</html>
