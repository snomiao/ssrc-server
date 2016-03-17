<?php //ssrc/

require_once('cres.php');

$action = DStr($_GET['action'], 'view');
switch ($action) {
case 'view':
    $title='帝国资源中心';
    mkHeader($title); ?>
            <h1><?=htmlspecialchars($title)?></h1>
            <div class="lsres">
<?php if(CheckLogin()){ ?>
                <div class="res me" style="background-image: url(&quot;http://www.hawkaoc.net/bbs/uc_server/avatar.php?size=large&amp;uid=<?=$_SESSION['bbs_uid']?>&quot;)">
                    <div class="name"><?=$_SESSION['bbs_uname']?></div>
                    <div class="ctrl">
                        <a class="btn new" href="./res.php?action=new">创建资源</a>
                        <a class="btn link" href="http://www.hawkaoc.net">论坛首页</a>
                        <a class="btn del" href="./login.php?logout=">登出账号</a>
                    </div>
                    <div class="content"></div>
                </div>
<?php }
    $result = CRes::Q('SELECT * FROM res', '检索资源');
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
            $filesize_fmt   = (int)$row['totalsize'].'Byte';
            $count_download = (int)$row['count_download'];
            $downloadurl    = "amt://?a=dl&res=$res&cutf=%E5%A6%82%E6%9E%9C%E4%BD%A0%E7%9C%8B%E5%88%B0%E4%BA%86%E8%BF%99%E5%8F%A5%E8%AF%9D%EF%BC%8C%E8%AF%B4%E6%98%8E%E4%BD%A0%E9%9C%80%E8%A6%81%E5%AE%89%E8%A3%85%E5%B8%9D%E5%9B%BD%E6%97%B6%E4%BB%A3%E7%AE%A1%E5%AE%B6%EF%BC%8C%E8%AF%B7%E5%89%8D%E5%BE%80www.hawkaoc.net%E4%B8%8B%E8%BD%BD";
            $mainimg        = (int)$row['mainimgid'];
            $imgurl         = './res.php?img='.encodeCSID($mainimg);
            $fromurl        = $row['fromurl'];
            $viewurl        = "./view/?res=$res"; ?>
                <div class="res"<?=$mainimg?' style="background-image: url('.htmlspecialchars('\''.$imgurl.'\'').')"':''?>>
                    <div class="name"><?=$name?></div>
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
<?php
    mkFooter();
    exit();
default:

}
?>