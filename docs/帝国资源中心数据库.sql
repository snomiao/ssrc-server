/** Update
#2015-06-11
创建
**/
-- 请务必把字体设成中英文等宽字体 例 宋体，黑体 等

DROP DATABASE  IF EXISTS `ssrc`;
CREATE DATABASE IF NOT EXISTS `ssrc` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `ssrc`;
SET CHARSET 'utf8';

################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
########    资源相关表
DROP TABLE IF EXISTS `res`       ;
CREATE TABLE IF NOT EXISTS `res`         (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '资源id',
    `t_create`       bigint unsigned NOT NULL default '0' COMMENT '创建时间戳',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '更新时间戳, 受评论影响',
    `t_fileup`       bigint unsigned NOT NULL default '0' COMMENT '文件修改时间',

    `totalsize`      bigint unsigned NOT NULL default '0' COMMENT '文件大小',
    `mainimgid`      int    unsigned NOT NULL default '0' COMMENT '首图id',
    `votereview`     int    unsigned NOT NULL default '0' COMMENT '计算评分',
    `votecomment`    int    unsigned NOT NULL default '0' COMMENT '计算推荐度',

    `count_download` int    unsigned NOT NULL default '0' COMMENT '下载次数',
    `author_bbsid`   int    unsigned NOT NULL default '0' COMMENT '作者/上传者',
    `author_name`    char(30)        NOT NULL default '0' COMMENT '作者名, 组织名',
    `name`           char(63)        NOT NULL default ''  COMMENT '资源名, 应包括版本号',
    `content`        text            NOT NULL             COMMENT '资源描述',
    `summary`        varchar(255)    NOT NULL default ''  COMMENT '简介',

    `b_notallow`     int    unsigned NOT NULL default '0' COMMENT '位, 禁止事项(禁评, ...)',
    `b_gamebase`     int    unsigned NOT NULL default '0' COMMENT '位, 帝国版本(1.0c, ...)',

    `status`         int    unsigned NOT NULL default '0' COMMENT '状态, 见下',
    `checkerid`      int    unsigned NOT NULL default '0' COMMENT '审批人id',
    `fromurl`        varchar(512)    NOT NULL default ''  COMMENT '来源URL 没有则留空',

    `e_type`         enum(
        'cpx'   ,     -- 战役包      //带音乐,带MOD等
        'scx'   ,     -- 场景包      //主要指联机用途
        'mgx'   ,     -- 录像包      //录像包
        'gax'   ,     -- 存档包      //存档包
        'rms'   ,     -- 随机地图    //
        -- 游戏资源
        'drs'   ,     -- drsmod      //图像mod, 音效mod等
        'mod'   ,     -- MOD包       //MOD包
        'hki'   ,     -- 键位表      //
        'tau'   ,     -- 嘲讽包      //语音嘲讽包
        -- 开发者资源
        'ai'    ,     -- AI包        //
        'avi'   ,     -- 动画包      //战役开局动画
        'mp3'   ,     -- 音乐包      //战役曲包
        -- 高危险资源(需要审核)
        'tool'  ,     -- 工具        //例: GE, AGE, IPX补丁
        'undefined')  NOT NULL DEFAULT 'undefined'        COMMENT '资源类型, 参考用',

    KEY `b_gamebase`        (`b_gamebase`)                 ,#COMMENT '版本索引',
    KEY `author_bbsid`      (`author_bbsid`)               ,#COMMENT '作者索引',
    KEY `e_type`            (`e_type`)                     ,#COMMENT '资源类型索引',
    FULLTEXT KEY `name`     (`name`)                       ,#COMMENT '名称索引',
    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源表';
DROP TABLE IF EXISTS `resreview`   ;
CREATE TABLE IF NOT EXISTS `resreview`  (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '评论id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '创建时间戳',

    `resid`          int    unsigned NOT NULL             COMMENT '资源id',
    `author_bbsid`   int    unsigned NOT NULL default '0' COMMENT '作者id',
    `author_name`    char(30)        NOT NULL default '0' COMMENT '作者名缓存',
    `content`        text            NOT NULL             COMMENT '理由',
    `vote`           int    unsigned NOT NULL default '3' COMMENT '评分(1.0~5.0, 存千倍)',
    `oo`             int    unsigned NOT NULL default '0' COMMENT '赞数',
    `xx`             int    unsigned NOT NULL default '0' COMMENT '踩数',

    KEY `author_bbsid`      (`author_bbsid`)              ,#COMMENT '作者索引',
    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源评分表';
DROP TABLE IF EXISTS `rescomment`;
CREATE TABLE IF NOT EXISTS `rescomment`  (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '评论id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '创建时间戳',

    `resid`          int    unsigned NOT NULL             COMMENT '资源id',
    `author_bbsid`   int    unsigned NOT NULL default '0' COMMENT '作者id',
    `author_name`    char(15)        NOT NULL default '0' COMMENT '作者名缓存',
    `content`        varchar(512)    NOT NULL             COMMENT '评论(5字以上, 256字以内)',
    `vote`           int    unsigned NOT NULL default '3' COMMENT '推薦度(1~5整数, 存千倍)',
    `oo`             int    unsigned NOT NULL default '0' COMMENT '赞数',
    `xx`             int    unsigned NOT NULL default '0' COMMENT '踩数',

    KEY `author_bbsid`      (`author_bbsid`)              ,#COMMENT '作者索引',
    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源评论表';
DROP TABLE IF EXISTS `resdat`    ;
CREATE TABLE IF NOT EXISTS `resdat`     (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '文件id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '更新时间戳',

    `size`           bigint unsigned NOT NULL             COMMENT '文件大小',
    `sha1`           blob(20)        NOT NULL             COMMENT 'SHA1, 作为文件名',

    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源文件数据表';
DROP TABLE IF EXISTS `resfile`   ;
CREATE TABLE IF NOT EXISTS `resfile`  (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '文件关系id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '更新时间戳',

    `resid`          int    unsigned NOT NULL             COMMENT '资源id',
    `datid`          int    unsigned NOT NULL             COMMENT '资源文件id',
    `filename`       varchar(512)    NOT NULL             COMMENT '资源文件名',
    `dirid`          int    unsigned NOT NULL             COMMENT '资源文件所在目录id',
    `size`           int    unsigned NOT NULL             COMMENT '文件大小, 缓存',

    KEY `resid`             (`resid`)                     ,#COMMENT '资源索引',
    KEY `dirid`             (`dirid`)                     ,#COMMENT '目录索引',
    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源文件表';
DROP TABLE IF EXISTS `resdir`    ;
CREATE TABLE IF NOT EXISTS `resdir`   (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '目录id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '更新时间戳',
    `author_bbsid`   int    unsigned NOT NULL default '0' COMMENT '作者id',

    `pid`            int    unsigned NOT NULL default '0' COMMENT '父目录id',
    `dirname`        varchar(512)    NOT NULL             COMMENT '目录名称',
    `filter`         varchar(128)    NOT NULL             COMMENT '这个目录允许的文件名，格式 (*.ai|*.per)',

    FULLTEXT KEY `dirname`      (`dirname`)                       ,#COMMENT '目录索引',
    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源目录表（注意安全）';
DROP TABLE IF EXISTS `resimg`    ;
CREATE TABLE IF NOT EXISTS `resimg`    (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '图片id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '更新时间戳',

    `resid`          int    unsigned NOT NULL             COMMENT '资源id',
    `w`              int    unsigned NOT NULL             COMMENT '图片宽度',
    `h`              int    unsigned NOT NULL             COMMENT '图片高度',
    `datid`          int    unsigned NOT NULL             COMMENT '资源数据id',
    `comment`        char(32)        NOT NULL default '#' COMMENT '图片描述',

    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源图片表';
DROP TABLE IF EXISTS `restag`    ;
CREATE TABLE IF NOT EXISTS `restag`      (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '标签id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '创建时间戳',

    `name`           char(32)        NOT NULL             COMMENT '标签名',
    `count`          int    unsigned NOT NULL default '0' COMMENT '标签引用数量//缓存',

    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源标签表';
DROP TABLE IF EXISTS `restagrel` ;
CREATE TABLE IF NOT EXISTS `restagrel`   (
    `id`             int    unsigned AUTO_INCREMENT       COMMENT '标签关系id',
    `t_update`       bigint unsigned NOT NULL default '0' COMMENT '更新时间戳',

    `resid`          int    unsigned NOT NULL             COMMENT '资源id',
    `tagid`          int    unsigned NOT NULL             COMMENT '标签id',

    KEY `resid`             (`resid`)                     ,#COMMENT '资源索引',
    KEY `tagid`             (`tagid`)                     ,#COMMENT '标签索引',
    PRIMARY KEY(`id`)
)ENGINE=MyISAM CHARSET=utf8 COMMENT='资源标签关系表';

################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
########    函数定义
DROP FUNCTION IF EXISTS `PathDir` ;
CREATE FUNCTION `PathDir`(`dirid` int unsigned)
    RETURNS varchar(512) CHARSET utf8
    READS SQL DATA
    COMMENT '由目录id获取路径'
    SQL SECURITY INVOKER
BEGIN
    DECLARE spath VARCHAR(512) DEFAULT '/';
    DECLARE pdir int unsigned DEFAULT 0;
    #拼接路径
    WHILE dirid != 0 DO
        SET pdir  = 0;
        SELECT CONCAT(dirname, spath, '/'), pid INTO spath, pdir FROM resdir WHERE id = dirid LIMIT 1;
        SET dirid = pdir;
    END WHILE;
    RETURN spath;
END;
#GRANT EXECUTE ON FUNCTION PathDir TO 'amtclient'@'%';
#SELECT PathDir(id) FROM resdir;

DROP FUNCTION IF EXISTS `PathFile`;
CREATE FUNCTION `PathFile`(`fileid` int unsigned)
    RETURNS varchar(512) CHARSET utf8
    READS SQL DATA
    COMMENT '由文件关系id获取路径'
    SQL SECURITY INVOKER
BEGIN
    DECLARE spath VARCHAR(512) DEFAULT '';
    DECLARE pdir int unsigned DEFAULT 0;
    SELECT CONCAT(PathDir(dirid), filename) INTO spath FROM resfile WHERE id = fileid LIMIT 1;
    RETURN spath;
END;
#GRANT EXECUTE ON FUNCTION PathFile TO 'amtclient'@'%';
#SELECT PathFile(id) FROM resfile;

################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
########    字段解释
#### res.b_gamebase -- 位标志_版本和兼容性
# #00~15bit MASK = 0x0000FFFF  //
# 0x0000 == '不知'
# 0x0001 |= '其它'
# 0x0002 |= 'r'        -- red  红帽子 /empires2.exe
# 0x0004 |= 'a'        -- 1.0a 蓝帽子 /age2_x1.exe
# 0x0008 |= 'c'        -- 1.0c 蓝帽子 /age2_x1/age2_x1.exe
# 0x0010 |= '4'        -- 1.4  蓝帽子 /age2_x1/???
# 0x0020 |= 'f'        -- forg 遗忘的帝国 绿帽子
# 0x0040 |= 'm'        -- mod  带mod的帝国 黑帽子 /????
# 0xFFFF |= '对帝国全面兼容'
# #................... -- 可随时扩展

#### res.status 状态:
# 值 状态名称 对用户 对管理
# 0  编辑状态 不可见 不可见
# 1  待审状态 不可见   可见
# 2  发布状态   可见   可见
# 任何编辑行为会强制把资源设为编辑

#### res.b_notallow -- 位标志_版本和兼容性
# #00~15bit MASK = 0x0000FFFF  //
# 0x0001 |= '禁评'
# 0x0002 |= '禁'
# 0xFFFF |= '对帝国全面兼容'
# #................... -- 可随时扩展

################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
########    初始数据

#目录
INSERT INTO resdir SET id=1 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.ai|*.per',  dirname='AI'      ;
INSERT INTO resdir SET id=2 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.avi',       dirname='Avi'     ;
INSERT INTO resdir SET id=3 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.cpx|*.cpn', dirname='Campaign';
INSERT INTO resdir SET id=4 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.dat|*.drs', dirname='Data'    ;
INSERT INTO resdir SET id=5 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.txt',       dirname='History' ;
INSERT INTO resdir SET id=6 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.uh',        dirname='Learn'   ;
INSERT INTO resdir SET id=7 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.rms',       dirname='Random'  ;
INSERT INTO resdir SET id=8 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.gax|*.mgx', dirname='SaveGame';
INSERT INTO resdir SET id=9 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.scx',       dirname='Scenario';
INSERT INTO resdir SET id=10,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.m3u',       dirname='Sound'   ;
INSERT INTO resdir SET id=11,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.mp3',       dirname='Campaign', pid=10;
INSERT INTO resdir SET id=12,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.mp3',       dirname='music'   , pid=10;
INSERT INTO resdir SET id=13,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.mp3|*.wav', dirname='Scenario', pid=10;
INSERT INTO resdir SET id=14,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.mp3',       dirname='Stream'  , pid=10;
INSERT INTO resdir SET id=15,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.wav',       dirname='Terrain' , pid=10;
INSERT INTO resdir SET id=16,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),filter='*.mp3',       dirname='Taunt'   ;
#标签
#游戏类型
INSERT INTO restag SET id=1 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='建筑毁灭';
INSERT INTO restag SET id=2 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='定量过关';
INSERT INTO restag SET id=3 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='角色扮演';
INSERT INTO restag SET id=4 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='存档/模组';
INSERT INTO restag SET id=5 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='帝国电影';
INSERT INTO restag SET id=6 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='混合型';
INSERT INTO restag SET id=7 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='小游戏';
INSERT INTO restag SET id=8 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='解谜';
INSERT INTO restag SET id=9 ,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='休闲经营';
INSERT INTO restag SET id=10,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='战役合集';
INSERT INTO restag SET id=11,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='夺城';
INSERT INTO restag SET id=12,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='防守';
INSERT INTO restag SET id=13,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='逃亡';
INSERT INTO restag SET id=14,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='血堡';
INSERT INTO restag SET id=15,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='角色对抗';
INSERT INTO restag SET id=16,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='场景合集';
#游戏版本
INSERT INTO restag SET id=17,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='官方征服者1.0a';
INSERT INTO restag SET id=18,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='官方征服者1.0c';
INSERT INTO restag SET id=19,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='旧版被遗忘的帝国（绿版）';
INSERT INTO restag SET id=20,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='征服者1.4（Z系列）';
INSERT INTO restag SET id=21,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='官方帝国时代2 HD：征服者';
INSERT INTO restag SET id=22,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='官方帝国时代2 HD：被遗忘的帝国';
#作品类型
INSERT INTO restag SET id=23,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='火箭筒杯参赛作品';
INSERT INTO restag SET id=24,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='翻译作品';
INSERT INTO restag SET id=25,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='电影节参赛作品';
INSERT INTO restag SET id=26,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='其他比赛参赛作品';
#作品标记
INSERT INTO restag SET id=27,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='作品合集';
INSERT INTO restag SET id=28,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='必玩作品';
INSERT INTO restag SET id=29,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='优质作品';
INSERT INTO restag SET id=30,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='原创';
INSERT INTO restag SET id=31,t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP), name='修改场景';


################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
########    集成查询