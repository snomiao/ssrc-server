<?php //ssrc/

require_once('cres.php');

$action = DStr($_GET['action'], 'view');
switch ($action) {
case 'view':
    $title='帝国资源中心';
    mkHeader($title);

    /* 获取查询 */
    $q = DStr($_GET["q"]);
    
    /* 35字限制 */
    $qLimited = substr($q, 0, 35);
    
    /* 查询排序 */
    $qArray = split(" ", $qLimited);

    $SQLWhere = "";
    $SQLWhere = "WHERE 1";
    
    ini_set("display_errors", "On");
    error_reporting(E_ALL | E_STRICT);

    /* 关键词搜索 */
    $SQLWhere_E_TYPE   = "0";
    $SQLWhere_KEYWORDS_INNAME = "1";
    $SQLWhere_KEYWORDS_INCONTENT = "1";
    foreach ($qArray as $key => $value) {
        /* 判断搜索资源类型 */
        $e_typeAbbr = GetE_TypeAbbr($value);
        if($e_typeAbbr !== FALSE){
            $SQLWhere_E_TYPE .= ' OR (`e_type`=' . IStr($e_typeAbbr) . ')';
        }else{
            /* 搜索资源名称 */
            $searchPattern = "%" . DStr($value) . "%";
            $SQLWhere_KEYWORDS_INNAME .= ' AND (`name` LIKE ' . IStr($searchPattern) . ')';

            /* 搜索资源介绍 */
            $searchPattern = "%" . DStr($value) . "%";
            $SQLWhere_KEYWORDS_INCONTENT .= ' AND (`content` LIKE ' . IStr($searchPattern) . ')';
        }
    }
    
    if($SQLWhere_E_TYPE === "0"){
        $SQLWhere_E_TYPE = "1"; /* 如果没有提到资源类型，则不作限制 */
    }
    $SQLWhere_KEYWORDS = "($SQLWhere_KEYWORDS_INNAME OR $SQLWhere_KEYWORDS_INCONTENT)";
    $SQLWhere          = "WHERE (($SQLWhere_E_TYPE) AND ($SQLWhere_KEYWORDS))";

    // DEBUG_TagLog($SQLWhere);
    DEBUG_TagLog($q);
    DEBUG_TagLog($qLimited);
    DEBUG_TagLog($q != $qLimited);
    /* 筛选器 */

    // /* 搜索结果排序 */
    $SQLOrderBy = "";

    // if( isset($_GET["byDownload"]) ){
    //     $SQLOrderBy = "ORDER BY `count_download` DESC";
    // }
    //"$count_download 人安装"

    /* 使用分页查询 */
    $page           = IInt($_GET["page"], 1);
    if($page < 1)
        $page = 1;
    $page_itemcount = 60;
    $page_offset    = (($page) - 1) * 60;
    $SQLLimit       = "LIMIT $page_offset, $page_itemcount";
    $result         = CRes::Q("SELECT SQL_CALC_FOUND_ROWS * FROM res $SQLWhere $SQLOrderBy $SQLLimit", '检索资源');
    $rows_row       = CRes::QRow("SELECT CEIL(FOUND_ROWS() / $page_itemcount) AS pages", '检索页数');
    $page_count     = (int)$rows_row["pages"];
    /* 搜索框 */
?>          <h1><?=htmlspecialchars($title)?></h1>
            <div class="resSearch">
                <form class="form">
                    <div>
                        <input type="text" name="q" class="resSearchInput" value="<?= HStr($q) ?>"> <input type="submit" value="搜索">
                    </div>
                    <?php if( $q != $qLimited ) {?>
                        <div>
                            <span>你的搜索关键词太长，己为你截取前 35 字</span>
                        </div>
                    <?php }?>
                </form>
            </div>
            <br>
            <div class="lsres">
<?php
    if(CheckLogin()){ ?>
                <div class="res me" style="background-image: url(&quot;http://www.hawkaoe.net/bbs/uc_server/avatar.php?size=large&amp;uid=<?=$_SESSION['bbs_uid']?>&quot;)">
                    <div class="name"><?=$_SESSION['bbs_uname']?></div>
                    <div class="ctrl">
                        <a class="btn new" href="./res.php?action=new">创建资源</a>
                        <a class="btn link" href="http://www.hawkaoe.net">论坛首页</a>
                        <a class="btn del" href="./login.php?logout=">登出账号</a>
                    </div>
                    <div class="content"></div>
                </div>
<?php }

    while($row = mysql_fetch_array($result)){
        $status         = (int)$row['status'];
        $canedit        = CRes::ResPermissionQ("Edit", $row);
        $canmanage      = CRes::ResPermissionQ("Manage", $row);
        $cansee         = CRes::ResPermissionQ("See", $row);
        $isauthor       = CRes::ResPermissionQ("Author", $row);
        if($cansee){
            $res            = encodeCSID($row['id']);
            $name           = $row['name'];
            $author_name    = $row['author_name'];
            $txt_e_type     = GetE_Type($row['e_type']);
            $filesize_fmt   = (int)$row['totalsize'].'Byte';
            $count_download = (int)$row['count_download'];
            $downloadurl    = "amt://?a=dl&res=$res&cutf=%E5%A6%82%E6%9E%9C%E4%BD%A0%E7%9C%8B%E5%88%B0%E4%BA%86%E8%BF%99%E5%8F%A5%E8%AF%9D%EF%BC%8C%E8%AF%B4%E6%98%8E%E4%BD%A0%E9%9C%80%E8%A6%81%E5%AE%89%E8%A3%85%E5%B8%9D%E5%9B%BD%E6%97%B6%E4%BB%A3%E7%AE%A1%E5%AE%B6%EF%BC%8C%E8%AF%B7%E5%89%8D%E5%BE%80www.hawkaoe.net%E4%B8%8B%E8%BD%BD";
            $mainimg        = (int)$row['mainimgid'];
            $imgurl         = './res.php?img='.encodeCSID($mainimg);
            $fromurl        = $row['fromurl'];
            $viewurl        = "./view/?res=$res"; ?>
                <div class="res"<?=$mainimg?' style="background-image: url('.htmlspecialchars('\''.$imgurl.'\'').')"':''?>>
                    <div class="name"><span class="e_type">[<?=$txt_e_type?>]</span><?=$name?></div>
                    <div class="ctrl">
<?php       if(!$cansee){ ?>
                        <a class="btn del">!<?=$isauthor?>如果你看到这句话请截图给雪星(@Snowstar)////////</a>
<?php       } ?>
                        <a class="btn info"><?=htmlspecialchars($author_name)?>出品</a>
<?php       if(STATUS_EDITING == $status){ ?>
                        <a class="btn disabled">发布等待</a>
<?php       }else if(STATUS_CHECKING == $status && $canmanage){ ?>
                        <a class="btn waiting" target="_BLANK" title="文件大小: <?=$filesize_fmt?>" href="./res.php?action=edit&amp;res=<?=$res?>">审核资源</a>
<?php       }else if(STATUS_CHECKING == $status){ ?>
                        <a class="btn disabled" target="_BLANK" title="文件大小: <?=$filesize_fmt?>">正在审核</a>
<?php       }else if(STATUS_PUBLISHED == $status){ ?>
                        <a class="btn download" target="_BLANK" href="<?=$downloadurl?>"
                       title="文件大小: <?=$filesize_fmt?>&#13;下载人次: <?=$count_download?>">安装资源</a>
<?php       } ?>
                        <a class="btn"     target="_BLANK" href="<?=$viewurl?>">查看详情</a>
<?php       if($canedit){?>
                        <a class="btn"     target="_BLANK" href="./res.php?action=edit&amp;res=<?=$res?>">编辑资源</a>
                        <a class="btn del" target="_BLANK" href="./res.php?action=del&amp;res=<?=$res?>">删除资源</a>
<?php       } ?>
                    </div>
                    <a alt="查看详情" target="_BLANK" href="<?=$viewurl?>" class="content<?=$mainimg?'':' content-textonly'?>"><?=isset($row['content'][0]) ? htmlspecialchars($row['content']) : '未填写资源简介'?></a>
                </div>
<?php   }
    } ?>
            </div>
            <script type="text/javascript">
                function goToPage(page){
                    document.getElementById("page").value = page;
                    document.getElementById("pagelist").submit();
                }
            </script>
            <form class="form" id="pagelist" class="pagelist">
                <input type="hidden" name="q" class="resSearchInput" value="<?= HStr($q) ?>">
                <input type="button" value="<" onclick="goToPage(<?=$page-1?>)" <?=$page <= 1           ? 'disabled' : ''?>>
                <span class="page-number">第 <?= "$page / $page_count" ?> 页</span>
                <input type="button" value=">" onclick="goToPage(<?=$page+1?>)" <?=$page >= $page_count ? 'disabled' : ''?>>
                <input type="hidden" name="page" id="page" value="<?=$page?>">
            </form>
<?php
    mkFooter();
    exit();
default:

}
?>