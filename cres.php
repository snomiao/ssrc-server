<?php

/**


                                                    主类


 **/

/*
代码折叠说明：
    1. 在 Sublime Text 下
    2. 选中所有的这个："{//_"（不带引号）
    3. 然后按 Ctrl+Shift+M 选中函数体
    4. 再按 Ctrl+Shift+[
    5. 就可以折叠所有函数了，赶快试试吧！
    （po主强迫症晚期系列）

对应 ahk 代码：

    SetTitleMatchMode, RegEx

    Return

    #IfWinActive .*\(ssrc\) - Sublime Text
        ^e::
            Clipboard := "{//_"
            Send ^f^v!{Enter}^+m^+[
            Return
        F12:: ExitApp

*/
require_once('func.php');
require_once('user-config/config.inc.php');

define('UPDIR', dirname(__FILE__) . '/rcdat');

define('STATUS_EDITING', 0);
define('STATUS_CHECKING', 1);
define('STATUS_PUBLISHED', 2);
define('STATUS_DELETED', 3);

if (!mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD)) PageError('资源中心服务器忙，请稍候再试！');
mysql_select_db(DB_NAME);
mysql_set_charset(DB_CHARSET);

class CRes
{
    // Define        //_
    public $id; /* 资源id */
    public $row;
    // MySql         //_
    public  static function QRow($query, $act = "查询", $resid = null)
    { //_
        /* 查询一行, 返回 $row */
        $result = mysql_query($query);
        if (!$result && in_array(IInt($_SESSION['bbs_uid']), array(103896, 70794))) {
            die('ERROR Q: ' . $query); /* 对开发人员显示错误页面 */
        }
        if (!$result)      PageError($act . '失败，服务器忙，请稍候再试！', $resid);
        if (!$row    = mysql_fetch_array($result))
            return false;
        return $row;
    }/* 返回: DIE  | false | $row    */
    public  static function QRowNoErr($query)
    { //_
        /* 查询一行, 返回 $row，不报错 */
        $result = mysql_query($query);
        if (!$result && in_array(IInt($_SESSION['bbs_uid']), array(103896, 70794))) {
            die('ERROR Q: ' . $query);
        }
        if (!$result) return false;
        return mysql_fetch_array($result);
    }/* 返回: false|$row    */
    public  static function Q($query, $act = "查询", $resid = null)
    { //_
        /* 查询, 返回 $result */
        $result = mysql_query($query);
        if (!$result && in_array(IInt($_SESSION['bbs_uid']), array(103896, 70794))) {
            die('ERROR Q: ' . $query);
        }
        return $result ? $result : PageError($act . '失败，服务器忙，请稍候再试！', $resid);
    }/* 返回: DIE   | $result */
    public  static function QNoErr($query)
    { //_
        /* 查询, 返回 $result，不报错 */
        return    mysql_query($query);
    }/* 返回: false | $result */
    // DatFile       //_
    private static function ErrorInfo($err)
    { //_
        /* 返回错误代码对应的错误信息 */
        switch ($err) {
            case UPLOAD_ERR_OK:
                return '错误 ' . $err . '，没有错误发生，文件上传成功。';
            case UPLOAD_ERR_INI_SIZE:
                return '错误 ' . $err . '，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。';
            case UPLOAD_ERR_FORM_SIZE:
                return '错误 ' . $err . '，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。';
            case UPLOAD_ERR_PARTIAL:
                return '错误 ' . $err . '，文件只有部分被上传。';
            case UPLOAD_ERR_NO_FILE:
                return '错误 ' . $err . '，没有文件被上传。';
            case UPLOAD_ERR_NO_TMP_DIR:
                return '错误 ' . $err . '，找不到临时文件夹。';
            case UPLOAD_ERR_CANT_WRITE:
                return '错误 ' . $err . '，文件写入失败。';
            default:
                return '错误 ' . $err . '，未知错误。';
        }
    }/* 返回: error          */ /* */
    private static function MkDir($dir)
    { //_
        /* 创建目录，返回目录路径 */
        if (is_file($dir))
            unlink($dir);
        if (!file_exists($dir))
            mkdir($dir);
        return realpath($dir);
    }/* 返回: path           */ /* */
    private static function PathDat($sha1, $size)
    { //_
        /* 未知 */
        $sha1 = strtolower($sha1);
        $size = (int) $size;
        return self::MkDir(UPDIR) . "/$sha1.$size.png";
    }/* 返回: path           */ /* */
    private static function PathDatUser($sha1, $size)
    { //_
        $sha1 = strtolower($sha1);
        $size = (int) $size;
        return './rcdat' . "/$sha1.$size.png";
    }/* 返回: uri            */ /* */
    private static function GetDatId($sha1, $size)
    { //_
        $row        = self::QRowNoErr("SELECT id FROM resdat WHERE sha1=X'$sha1' AND size=$size LIMIT 1");
        return $row ? (int) $row['id'] : (int) 0;
    }/* 返回: $datid|0       */ /* 取得数据文件id */
    private static function UpDat($f, &$img = null)
    { //_
        // $f 是浏览器交上来的文件表单。
        if (!($f['error'] === UPLOAD_ERR_OK))                         return '上传失败，' . self::ErrorInfo($f['error']);
        if (!file_exists($f['tmp_name']))                         return '上传失败，文件找不到了';
        if (!is_uploaded_file($f['tmp_name']))                         return '上传失败，文件路径不对';
        // 图片检测
        if ($img) {
            if (($img = getimagesize($f['tmp_name'])) === false)       return '上传失败，文件格式不对';
            $w       = (int) $img[0];
            $h       = (int) $img[1];
            $type    = (int) $img[2];
            if ($type == 6)                                            return '这里不能上传bmp格式的图片！';
        }
        // 去重
        $size = IInt($f['size']);
        $sha1 = sha1_file($f['tmp_name']);
        if (false === $sha1)                                           return '上传失败，没能识别文件';
        if (false === $datid = self::GetDatId($sha1, $size))           return '上传失败，服务器不理我5';
        $path = self::PathDat($sha1, $size);
        if (0 == $datid) {
            // 上传
            if (!move_uploaded_file($f['tmp_name'], $path))            return '上传失败，文件被锁定了';
            if (!file_exists($path))                                   return '上传失败，文件消失了！';
            if (!self::QNoErr("INSERT INTO resdat SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),sha1=X'$sha1',size=$size")) {
                unlink($path);
                return '上传失败，服务器突然忙了起来';
            }
            $datid = mysql_insert_id();
        }
        return (int) $datid;
    }/* 返回: $datid | error */ /* 上传数据文件   */
    private static function DelDat($id)
    { //_
        // 获取链接数量
        $lsresid = "";
        if (!$row = self::QRowNoErr("SELECT COUNT(c.resid) count FROM (SELECT resid FROM resfile a WHERE a.datid=$id LIMIT 1 UNION ALL SELECT resid FROM resimg b WHERE b.datid=$id LIMIT 1) c"))
            return '该文件不曾存在，' . encodeCSID($id);
        if (0 != (int) $row['count']) return (int) 0;
        // 获取文件名
        if (!$row = self::QRowNoErr("SELECT HEX(sha1) sha1, size FROM resdat WHERE id=$id LIMIT 1"))
            return '该文件已不存在，' . encodeCSID($id);
        $sha1 = $row['sha1'];
        $size = (int) $row['size'];
        $path = self::PathDat($sha1, $size);
        // 删除
        if (!self::QNoErr("DELETE FROM resdat WHERE id=$id"))
            return '服务器忙，请稍候再试，' . encodeCSID($id);
        unlink($path);
        return (int) $id;
    }/* 返回: error|$datid   */ /* 删除数据文件   */
    // ResPermission //_
    public  static function GlobalPermissionQ($permission)
    { //_
        //$is_manager     = false;
        $is_manager     = in_array((int) $_SESSION['bbs_groupid'], array(1, 2, 3));
        //$zyzx_medal = true;
        $zyzx_medal     = in_array(110, $_SESSION['bbs_umedals']);
        switch ($permission) {
            case "CreateRes":
                return $is_manager || $zyzx_medal;
            case "NewDir":
                return $is_manager || $zyzx_medal;
            case "ManageDir":
                return $is_manager || $zyzx_medal;
            case "Manage":
                return $is_manager;
            case "EditDir":
                return $is_manager;
            case "CommentCreate":
                return $is_manager;
            case "CommentEdit":
                return $is_manager;
            case "CommentDelete":
                return $is_manager;
            default:
                print("WARNNING: UNKNOWN PERMISSION[" . $permission . "]");
                return false;
        }
    }/* 返回: boolean */
    public  static function ResPermissionQ($permission, $row)
    { //_
        $status         = (int) $row['status'];
        $is_author      = !isset($row['author_bbsid']) || !isset($_SESSION['bbs_uid']) ? false : (int) $row['author_bbsid']  ==   (int) $_SESSION['bbs_uid'];
        $is_manager     = !isset($_SESSION['bbs_groupid']) ? false : in_array((int) $_SESSION['bbs_groupid'], array(1, 2, 3)); // || (int)$_SESSION['bbs_uid']==103896;
        $zyzx_medal     = !isset($_SESSION['bbs_umedals']) ? false : in_array(110, $_SESSION['bbs_umedals']);

        $permissions = array();

        $permissions["Delete"]        = $is_manager || $is_author;
        $permissions["Edit"]          = $is_author  || $is_manager;
        $permissions["Manage"]        = $is_manager;
        $permissions["See"]           = STATUS_EDITING   == $status && $is_author
            || STATUS_CHECKING  == $status && ($is_author || $is_manager)
            || STATUS_PUBLISHED == $status && true;
        $permissions["Author"]        = $is_author;
        $permissions["CreateComment"] = $is_manager;
        $permissions["CreateReview"]  = $is_manager || $zyzx_medal;

        if (array_key_exists($permission, $permissions)) {
            return $permissions[$permission];
        } else {
            print("WARNNING: ashy code[UNKNOWN PERMISSION: " . $permission . "];");
            return false;
        }
    }/* 返回: boolean */
    public         function PermissionQ($permission)
    { //_
        return self::ResPermissionQ($permission, $this->row);
    }/* 返回: boolean */
    public  static function ResReviewPermissionQ($permission, $row)
    { //_
        switch ($permission) {
            case "Delete":
                return (int) $row['author_bbsid'] == (int) $_SESSION['bbs_uid'];
        }
    }/* 返回: boolean */
    public  static function ResCommentPermissionQ($permission, $row)
    { //_
        $is_author = (int) $row['author_bbsid'] == (int) $_SESSION['bbs_uid'];
        switch ($permission) {
            case "Edit":
                return $is_author;
            case "Delete":
                return $is_author; //| $is_manager;
        }
    }/* 返回: boolean */
    public  static function ResDirPermissionQ($permission, $row)
    { //_
        $is_author = (int) $row['author_bbsid'] == (int) $_SESSION['bbs_uid'];
        $is_manager     = in_array((int) $_SESSION['bbs_groupid'], array(1, 2, 3)); // || (int)$_SESSION['bbs_uid']==103896;
        switch ($permission) {
            case "Edit":
                return $is_author;
            case "Delete":
                return $is_author | $is_manager;
        }
    }/* 返回: boolean */
    public  static function IsAuthor($row)
    { //_
        print("WARNNING: ashy code[IsAuthor];");
        return false;
    }/* 返回: boolean */
    public  static function IsManager($row = null)
    { //_
        print("WARNNING: ashy code[IsManager];");
        return false;
    }/* 返回: boolean */
    public  static function CanEdit($row)
    { //_
        print("WARNNING: ashy code[CanEdit];");
        return false;
    }/* 返回: boolean */
    public  static function CanManage($row = null)
    { //_
        print("WARNNING: ashy code[CanManage];");
        return false;
    }/* 返回: boolean */
    public  static function CanSee($row)
    { //_
        print("WARNNING: ashy code[CanSee];");
        return false;
    }/* 返回: boolean */
    // Res           //_
    public  static function Create()
    { //_
        if (!self::GlobalPermissionQ("CreateRes")) PageError('没有权限: 创建资源');
        $query   = "INSERT INTO res SET t_create=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid=' . IStr($_SESSION['bbs_uid']);
        $query  .= ',author_name=' . IStr($_SESSION['bbs_uname']);
        $query  .= ',content='     . "''";
        self::Q($query, '创建资源');
        return mysql_insert_id();
    }/* 返回: DIE|$resid  */
    public         function __construct($id)
    { //_
        $this->id          = (int) $id;
        $this->exist       = $this->Exist();
        //$this->permissions = $this->ResPermissions();
    }/* 返回: DIE|null    */
    private        function Exist()
    { //_
        $this->row       = self::QRow("SELECT * FROM res WHERE id={$this->id} LIMIT 1", '定位资源', $this->id);
        if ($this->row === false) PageError('该资源不存在', $this->id);

        // $this->canEdit   = self::CanEdit(  $this->row);
        // $this->canManage = self::CanManage($this->row);
        // $this->canSee    = self::CanSee(   $this->row);

        // 这段不知道为啥曾经被注释掉过
        $this->status    = (int) $this->row['status'];
        $this->editing   = STATUS_EDITING == $this->status;
        $this->checking  = STATUS_CHECKING == $this->status;
        $this->published = STATUS_PUBLISHED == $this->status;
        // 现在给它恢复了(20180616)

        return $this->row;
    }/* 返回: DIE|$row    */
    private        function Update($ex = ',status=0')
    { //_
        return self::Q("UPDATE res SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)$ex WHERE id={$this->id}", '更新资源', $this->id);
    }/* 返回: DIE|$result */
    public         function IncDownloads()
    { //_
        return self::Q("UPDATE res SET count_download=count_download+1 WHERE id={$this->id}", '下载资源', $this->id);
    }/* 返回: | error 下载资源计数 */
    public         function SetGamebase($b_gamebase)
    { //_
        if (!$this->PermissionQ("Edit")) PageError('无权编辑该资源', encodeCSID($this->id));
        $ex = ",status=" . STATUS_EDITING . ",b_gamebase=$b_gamebase";
        return $this->Update($ex);
    }/* 未知 */
    public         function Edit(&$name, &$summary, &$content, &$e_type)
    { //_
        if (!$this->PermissionQ("Edit")) PageError('无权编辑该资源', encodeCSID($this->id));
        $ex = ',status=' . STATUS_EDITING;
        $ex .= ',name='   . IStr($name);
        $ex .= ',summary=' . IStr($summary);
        $ex .= ',content=' . IStr($content);
        $ex .= ',e_type=' . IStr($e_type);
        return $this->Update($ex);
    }/* 返回: DIE|$result */
    public         function Delete()
    { //_
        if (!$this->PermissionQ("Delete")) PageError('无权删除该资源', encodeCSID($this->id));

        $result = self::Q("SELECT id FROM resfile WHERE resid={$this->id}", '定位资源文件', $this->id);
        while ($row = mysql_fetch_array($result)) {
            $id_file    = (int) $row['id'];
            self::DelFile($id_file);
        }
        return self::Q('UPDATE res SET status=' . STATUS_DELETED . " WHERE id={$this->id}", '删除资源', $this->id);
        //return self::Q("DELETE FROM res WHERE id={$this->id}", '删除资源', $this->id);
    }/* 返回: DIE|$result */
    public         function Check(&$author_name, &$fromurl = '')
    { //_
        if (!$this->PermissionQ("Edit")) PageError('无权编辑该资源', encodeCSID($this->id));
        return $this->Update(',status=' . STATUS_CHECKING . ',author_name=' . IStr($author_name) . ',fromurl=' . IStr($fromurl));
    }/* 返回: DIE|$result */
    public         function Publish(&$checkerid)
    { //_
        if (!$this->PermissionQ("Manage")) PageError('无权审核该资源', encodeCSID($this->id));
        return $this->Update(',status=' . STATUS_PUBLISHED . ',checkerid='  . IInt($checkerid));
    }/* 返回: DIE|$result */
    // Upload        //_
    private        function DelFileDuplicate($dirid, $filename)
    { //_
        // MySql默认查询并不区分大小写
        $result = self::Q("SELECT id FROM resfile WHERE resid={$this->id} AND dirid=$dirid AND filename=$filename", '检查重复文件', $this->id);
        $count_del   = 0;
        while ($row = mysql_fetch_array($result)) {
            $id_file    = (int) $row['id'];
            self::DelFile($id_file);
            $count_del++;
        }
        return $count_del;
    }/* 返回: DIE|$count_del */ /* 不明 */
    public         function BindFile($f, $dirid)
    { //_
        // $f 是浏览器交上来的文件表单
        if (!$this->PermissionQ("Edit")) PageError('无权编辑该资源', encodeCSID($this->id));
        // SQL安全检查
        $filename = IStr($f['name']);
        $dirid    = IInt($dirid);
        $size     = (int) $f['size'];
        // 重名检查
        $creplace = $this->DelFileDuplicate($dirid, $filename);
        // 上传文件
        if (!is_int($datid = $this->UpDat($f)))             return $datid . '，' . $f['name'];
        // 綁定文件
        $query = "INSERT INTO resfile SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query .= ",dirid=$dirid,filename=$filename,size=$size,datid=$datid";
        if (!self::QNoErr($query))                          return '上传没有完成，请再试试吧，' . $f['name'];
        $this->Update(",status=" . STATUS_EDITING . ",t_fileup=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),totalsize=(SELECT SUM(size) size FROM (SELECT resdat.size FROM resdat JOIN resfile ON resid={$this->id} WHERE resdat.id=resfile.datid) lssize)");
        return '上传成功，' . ($creplace == 0 ? '' : "覆盖了 $creplace 个文件，") . $f['name'];
    }/* 返回: DIE|error */ /* 给资源绑定文件 */
    public         function BindImg($f)
    { //_
        $img = 1;
        if (!is_int($datid = $this->UpDat($f, $img)))      return $datid . '，' . $f['name'];
        // 取宽高
        $w       = (int) $img[0];
        $h       = (int) $img[1];
        $comment = IStr($f['name']);
        // 綁定文件
        $query = "INSERT INTO resimg SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query .= ",datid=$datid,w=$w,h=$h,comment=$comment";
        if (!self::QNoErr($query))                          return '上传未完成，请重试，' . $f['name'];
        $this->Update(',mainimgid=' . mysql_insert_id());
        return '上传成功，' . $f['name'];
    }/* 返回: DIE|error */ /* 给资源绑定图片 */
    public  static function DelFile($id)
    { //_
        // SQL安全检查
        $id = (int) $id;
        // 取文件关系
        $row = self::QRow("SELECT datid, resid, filename FROM resfile WHERE id=$id", '定位文件' . encodeCSID($id));
        if ($row === false)                           PageError('该文件不曾存在，' . encodeCSID($id));
        $resid = (int) $row['resid'];
        $datid = (int) $row['datid'];
        // $self为 CRes类
        $self  = new self($resid);
        if (!$self->PermissionQ("Edit")) PageError('无权编辑该资源', encodeCSID($resid));
        // 删除
        self::Q("UPDATE resfile SET deleted = 1 WHERE id=$id", "删除文件" . $id);
        //self::Q("DELETE FROM resfile WHERE id=$id", "删除文件".$id);
        $self->Update(",status=" . STATUS_EDITING . ",t_fileup=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),totalsize=(SELECT SUM(size) size FROM (SELECT resdat.size FROM resdat JOIN resfile ON resid={$self->id} WHERE resdat.id=resfile.datid) lssize)");
        if (!is_int($errinfo = self::DelDat($datid))) return $errinfo;
        return '删除成功，' . $row['filename'];
    }/* 返回: DIE|error */
    public  static function DelImg($id)
    { //_
        //SQL安全检查
        $id = (int) $id;
        // 取文件关系
        $row = self::QRow("SELECT datid, resid, comment FROM resimg WHERE id=$id", '定位图片' . encodeCSID($id));
        if ($row === false) PageError('该图片不曾存在，' . encodeCSID($id));
        //检查权限
        $resid = (int) $row['resid'];
        $datid = (int) $row['datid'];
        $self  = new self($resid);
        if (!$self->PermissionQ("Edit")) PageError('无权编辑该资源', encodeCSID($resid));
        //删除
        self::Q("DELETE FROM resimg WHERE id=$id", "删除图片" . $id);
        if (!is_int($errinfo = self::DelDat($datid))) return $errinfo;
        return '删除成功，' . $row['comment'];
    }/* 返回: DIE|error */
    public  static function NewDir(&$pid = '', &$dirname = '')
    { //_
        if (!self::GlobalPermissionQ("NewDir")) PageError('无权创建目录');
        $pid = IInt(decodeCSID($pid));
        if ($pid != 0) {
            $row = self::QRow("SELECT id FROM resdir WHERE id=$pid LIMIT 1", "查找父目录");
            if ($row === false) PageError('父目录不存在' . encodeCSID($pid));
        }
        $query   = "INSERT INTO resdir SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid='  . IStr($_SESSION['bbs_uid']);
        $query  .= ',pid='           . $pid . '';
        $query  .= ',dirname='       . IStr($dirname);
        $query  .= ',filter='        . "'*.*'";
        self::Q($query, '创建目录');
        return mysql_insert_id();
    }/* 返回: DIE|$dirid  */
    public  static function ModDir($id, &$pid, &$dirname)
    { //_

        $row = self::QRow("SELECT author_bbsid FROM resdir WHERE id=$id LIMIT 1", "查找目录");
        if (!self::ResDirPermissionQ("Edit", $row))   PageError('无权修改目录');

        $pid = IInt(decodeCSID($pid));
        if ($pid != 0) {
            $row = self::QRow("SELECT id FROM resdir WHERE id=$pid LIMIT 1", "查找父目录");
            if ($row === false) PageError('父目录不存在' . encodeCSID($pid));
        }
        $query  = "UPDATE resdir SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid='  . IStr($_SESSION['bbs_uid']);
        $query  .= ',pid='           . $pid . '';
        $query  .= ',dirname='       . IStr($dirname);
        $query  .= " WHERE id=$id";
        self::Q($query, '修改目录');
        if (!mysql_affected_rows())  PageError('你不能修改这个目录');
    }/* 返回: DIE|null */
    public  static function DelDir(&$dir)
    { //_
        $id     = decodeCSID($dir);


        $row = self::QRow("SELECT author_bbsid FROM resdir WHERE id=$id LIMIT 1", "查找目录");
        if (!self::ResDirPermissionQ("Delete", $row))   PageError('无权删除目录');
        $query  = "DELETE FROM resdir WHERE id=$id";
        self::Q($query, '删除目录');
        if (!mysql_affected_rows())  PageError('你不能修改这个目录');
    }/* 返回: DIE|null */
    public  static function UrlFile($id)
    { //_
        //确认文件存在
        if (
            !($result = self::QNoErr("SELECT HEX(sha1) sha1,size FROM resdat WHERE id=(SELECT datid id FROM resfile WHERE id=$id)"))
            or !($row    = mysql_fetch_array($result))
        )
            PageError404();
        $sha1 = $row['sha1'];
        $size = $row['size'];
        //返回相对路径(用户)
        return self::PathDatUser($sha1, $size);
    }/*return url*/
    public  static function UrlImg($id)
    { //_
        //确认文件存在
        if (
            !($result = self::QNoErr("SELECT HEX(sha1) sha1,size FROM resdat WHERE id=(SELECT datid id FROM resimg WHERE id=$id)"))
            or !($row   = mysql_fetch_array($result))
        ) {
            header('HTTP/1.1 404 Not Found');
            return './image/' . 'imgnoexist.png';
        }
        //返回相对路径(用户)
        $sha1 = $row['sha1'];
        $size = $row['size'];
        return self::PathDatUser($sha1, $size);
    }/*return url*/
    public  static function Gamebase($post = '')
    { //_
        if (is_int($post)) {
            return array(
                "o" => ($post & 0x0001) ? 'Yes' : null,
                "r" => ($post & 0x0002) ? 'Yes' : null,
                "a" => ($post & 0x0004) ? 'Yes' : null,
                "c" => ($post & 0x0008) ? 'Yes' : null,
                "4" => ($post & 0x0010) ? 'Yes' : null,
                "f" => ($post & 0x0020) ? 'Yes' : null,
                "m" => ($post & 0x0040) ? 'Yes' : null,
                "5" => ($post & 0x0080) ? 'Yes' : null,
                "w" => ($post & 0x0100) ? 'Yes' : null,
            );
        } else {
            $b_gamebase = 0;
            if (isset($post["o"])) $b_gamebase |= 0x0001; //red  其它版本   /?????.exe
            if (isset($post["r"])) $b_gamebase |= 0x0002; //red  红帽子     /empires2.exe
            if (isset($post["a"])) $b_gamebase |= 0x0004; //1.0a 蓝帽子1.0a /age2_x1.exe
            if (isset($post["c"])) $b_gamebase |= 0x0008; //1.0c 蓝帽子1.0c /age2_x1/age2_x1.exe
            if (isset($post["4"])) $b_gamebase |= 0x0010; //1.4  蓝帽子1.4  /age2_x1/???
            if (isset($post["f"])) $b_gamebase |= 0x0020; //forg 遗忘的帝国 绿帽子
            if (isset($post["m"])) $b_gamebase |= 0x0040; //mod  带mod的帝国 黑帽子 /????
            if (isset($post["5"])) $b_gamebase |= 0x0080; //1.5  蓝帽子1.5  /age2_x1/???
            if (isset($post["w"])) $b_gamebase |= 0x0100; //WK   WololoKingdoms  /age2_x1/???
            return $b_gamebase;
        }
    }/* 返回: array()|(int)$b_gamebase */
    // Comment       //_
    public         function UpdateVoteComment()
    { //_
        $query = "UPDATE res SET votecomment=(SELECT AVG(vote) votecomment FROM rescomment WHERE resid=res.id) WHERE id={$this->id}";
        return self::Q($query, '计算评分', $this->id);
    }/* 返回: DIE|true  */
    public         function CreateComment(&$content, &$vote)
    { //_
        if (!$this->PermissionQ("CreateComment"))       PageError('无权进行评论');
        $author_bbsid = IInt($_SESSION['bbs_uid']);
        $author_name  = IStr($_SESSION['bbs_uname']);
        $content      = IStr($content); //512字节
        $vote         = (doubleval($vote) * 1000);
        $vote         = IInt($vote); //推薦度(1~5整数, 存千倍)
        $query = "INSERT INTO rescomment SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query .= ",author_bbsid=$author_bbsid,author_name=$author_name";
        $query .= ",content=$content,vote=$vote";
        self::Q($query, '评论', $this->id);
        return encodeCSID(mysql_insert_id());
    }/* 返回: DIE|CSID  */
    public  static function DeleteComment(&$cmt)
    { //_
        $id = decodeCSID($cmt);
        $row = self::QRow("SELECT author_bbsid FROM rescomment WHERE id=$id", "删除评分");
        if ($row === false)        PageError('该评论不曾存在，' . encodeCSID($id));
        if (!self::ResCommentPermissionQ("Delete", $row)) PageError('无权删除该评论');
        return self::Q("DELETE FROM rescomment WHERE id=$id", '删除评论');
    }/* 返回: DIE|true  */
    public  static function CommentOO(&$cmt)
    { //_
        $id = decodeCSID($cmt);
        return self::Q("UPDATE rescomment SET oo=oo+1 WHERE id=$id", '评论不存在');
    }/* 返回: DIE|true  */
    public  static function CommentXX(&$cmt)
    { //_
        $id = decodeCSID($cmt);
        return self::Q("UPDATE rescomment SET xx=xx+1 WHERE id=$id", '评论不存在');
    }/* 返回: DIE|true  */
    // Review        //_
    public         function UpdateVoteReview()
    { //_
        $query = "UPDATE res SET votereview=(SELECT AVG(vote) votereview FROM resreview WHERE resid=res.id) WHERE id={$this->id}";
        return self::Q($query, '计算评分', $this->id);
    }/* 返回: DIE|true  */
    public         function CreateReview(&$content, &$vote)
    { //_
        if (!$this->PermissionQ("CreateReview"))       PageError('无权进行评分');
        $author_bbsid = IInt($_SESSION['bbs_uid']);
        $author_name  = IStr($_SESSION['bbs_uname']);
        $content      = IStr($content); //字数65534字节
        $vote         = (doubleval($vote) * 1000);
        $vote         = IInt($vote); //评分(1.0~5.0, 存千倍)
        $query = "INSERT INTO resreview SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query .= ",author_bbsid=$author_bbsid,author_name=$author_name";
        $query .= ",content=$content,vote=$vote";
        self::Q($query, '评分', $this->id);
        $re = encodeCSID(mysql_insert_id());
        $this->UpdateVoteReview();
        return $re;
    }/* 返回: DIE|CSID  */
    public  static function DeleteReview(&$rvw)
    { //_
        $id = decodeCSID($rvw);
        $row = self::QRow("SELECT author_bbsid FROM resreview WHERE id=$id", '查找作者');
        if ($row === false)        PageError('该评分不曾存在，' . encodeCSID($id));
        if (!self::ResReviewPermissionQ("Delete", $row))                    PageError('无权删除该评分');
        $re = self::Q("DELETE FROM resreview WHERE id=$id", '删除评分');
        $this->UpdateVoteReview();
        return $re;
    }/* 返回: DIE|true  */
    public  static function ReviewOO(&$rvw)
    { //_
        $id = decodeCSID($rvw);
        $re = self::Q("UPDATE resreview SET oo=oo+1 WHERE id=$id", '评分不存在');
        $this->UpdateVoteReview();
        return $re;
    }/* 返回: DIE|true  */
    public  static function ReviewXX(&$rvw)
    { //_
        $id = decodeCSID($rvw);
        $re = self::Q("UPDATE resreview SET xx=xx+1 WHERE id=$id", '评分不存在');
        $this->UpdateVoteReview();
        return $re;
    }/* 返回: DIE|true  */
    // Tag           //_
    public  static function CreateTag($comment, $vote)
    {
        /* 填坑？ */ }
    public  static function DeleteTag($id)
    {
        /* 填坑？ */ }
    public         function BindTag($id)
    {
        /* 填坑？ */ }
    public  static function DeleteTagRel($id)
    {
        /* 填坑？ */ }
}
