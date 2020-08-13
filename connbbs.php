<?php


require_once('user-config/config.inc.php');

if (!mysql_connect(BBS_DB_HOST, BBS_DB_USERNAME, BBS_DB_PASSWORD))
    die('论坛服务器忙，请稍候再试');
mysql_select_db(BBS_DB_NAME);
mysql_set_charset(BBS_DB_CHARSET);
