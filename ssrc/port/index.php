<?php
/**

** This is a old file.


                                                    主类


**/
require_once('../func.php');

define('UPDIR', dirname(__FILE__).'/../rcdat');
if('127.0.0.1' == $_SERVER['SERVER_NAME']){
    define('DB_NAME'    , 'ssrc');
    define('DB_CHARSET' , 'utf8');
    define('DB_HOST'    , '127.0.0.1');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', 'root');
}else
if('www.hawkaoc.net' == $_SERVER['SERVER_NAME']){
    define('DB_NAME'    , 'amt');
    define('DB_CHARSET' , 'utf8');
    define('DB_HOST'    , '121.199.22.226:5899');
    define('DB_USERNAME', 'con');
    define('DB_PASSWORD', 'con@con.con');
}else
//if('121.42.152.168' == $_SERVER['SERVER_NAME']){
if('localhost' == $_SERVER['SERVER_NAME']){
    define('DB_NAME'    , 'ssrc');
    define('DB_CHARSET' , 'utf8');
    define('DB_HOST'    , '121.42.152.168');
    define('DB_USERNAME', 'ssrcadmin');
    define('DB_PASSWORD', 'ssrc@ssrc.ssrc');
}
if(!mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD)) PageError('资源中心服务器忙，请稍候再试！');
mysql_select_db(DB_NAME); mysql_set_charset(DB_CHARSET);

class CRes{
// Define        //_
    public $id;
    public $row      ;
// MySql         //_
    static function QRow      ($query, $err, $resid=null){//_
        $result = mysql_query($query);
        if(!$result && in_array((int)$_SESSION['bbs_uid'], array(103896,70794))) {
            die('ERROR Q: '.$query);
        }
        if(!$result )      PageError('服务器忙，请稍候再试！', $resid);
        if(!$row    = mysql_fetch_array($result))      PageError($err                  , $resid);
        return $row;
    }/* return: DIE  |$row    */
    static function QRowNoErr ($query                   ){//_
        $result = mysql_query($query);
        if(!$result && in_array((int)$_SESSION['bbs_uid'], array(103896,70794))) {
            die('ERROR Q: '.$query);
        }
        if(!$result ) return false;
        return mysql_fetch_array($result);
    }/* return: false|$row    */
    static function Q         ($query, $act, $resid=null){//_
        $result = mysql_query($query);
        if(!$result && in_array((int)$_SESSION['bbs_uid'], array(103896,70794))) {
            die('ERROR Q: '.$query);
        }
        return $result ? $result : PageError($act.'失败，服务器忙，请稍候再试！', $resid);
    }/* return: DIE  |$result */
    static function QNoErr    ($query                   ){//_
        return    mysql_query($query);
    }/* return: false|$result */
// DatFile       //_
    static function ErrorInfo   ($err         ){//_
        switch($err){
            case UPLOAD_ERR_OK:         return '错误 '.$err.'，没有错误发生，文件上传成功。';
            case UPLOAD_ERR_INI_SIZE:   return '错误 '.$err.'，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。';
            case UPLOAD_ERR_FORM_SIZE:  return '错误 '.$err.'，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。';
            case UPLOAD_ERR_PARTIAL:    return '错误 '.$err.'，文件只有部分被上传。';
            case UPLOAD_ERR_NO_FILE:    return '错误 '.$err.'，没有文件被上传。';
            case UPLOAD_ERR_NO_TMP_DIR: return '错误 '.$err.'，找不到临时文件夹。';
            case UPLOAD_ERR_CANT_WRITE: return '错误 '.$err.'，文件写入失败。';
            default:                    return '错误 '.$err.'，未知错误。';
        }
    }/* return: error        */
    static function MkDir       ($dir         ){//_
        if(is_file($dir))
            unlink($dir);
        if(!file_exists($dir))
            mkdir($dir);
        return realpath($dir);
    }/* return: path     */
    static function PathDat     ($sha1, $size ){//_
        $sha1 = strtolower($sha1);
        $size = (int)$size;
        return self::MkDir(UPDIR)."/$sha1.$size.png";
    }/* return: path     */
    static function PathDatUser ($sha1, $size ){//_
        $sha1 = strtolower($sha1);
        $size = (int)$size;
        return './rcdat'."/$sha1.$size.png";
    }/* return: uri         */
    static function GetDatId    ($sha1, $size ){//_
        $row        = self::QRowNoErr("SELECT id FROM resdat WHERE sha1=X'$sha1' AND size=$size LIMIT 1");
        return $row ? (int)$row['id'] : (int)0;
    }/* return: $datid|0     */
    static function UpDat       ($f, &$img = null){//_
        if(!($f['error'] === UPLOAD_ERR_OK ))                         return '上传失败，'.self::ErrorInfo($f['error']);
        if(!file_exists(     $f['tmp_name']))                         return '上传失败，文件找不到了';
        if(!is_uploaded_file($f['tmp_name']))                         return '上传失败，文件路径错了';
        // 图片检测
        if($img){
            if(($img = getimagesize($f['tmp_name'])) === FALSE)       return '上传失败，文件格式不对';
            $w       = (int)$img[0];
            $h       = (int)$img[1];
            $type    = (int)$img[2];
            if($type == 6)                                            return '这里不能上传bmp格式的图片！';
        }
        // 去重
        $size = IInt     ($f['size'    ]);
        $sha1 = sha1_file($f['tmp_name']);
        if(FALSE === $sha1)                                           return '上传失败，没能识别文件';
        if(FALSE === $datid = self::GetDatId($sha1, $size))           return '上传失败，服务器不理我';
        $path = self::PathDat($sha1, $size);
        if(0 == $datid){
            // 上传
            if(!move_uploaded_file($f['tmp_name'], $path))            return '上传失败，文件被锁定了';
            if(!file_exists($path))                                   return '上传失败，文件消失了！';
            if(!self::QNoErr("INSERT INTO resdat SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),sha1=X'$sha1',size=$size")){
                unlink($path);                                        return '上传失败，服务器突然忙了起来';
            }
            $datid = mysql_insert_id();
        }
        return (int)$datid;
    }/* return: $datid|error */
    static function DelDat      ($id          ){//_
        // 获取链接数量
        $lsresid = "";
        if(!$row = self::QRowNoErr("SELECT COUNT(c.resid) count FROM (SELECT resid FROM resfile a WHERE a.datid=$id LIMIT 1 UNION ALL SELECT resid FROM resimg b WHERE b.datid=$id LIMIT 1) c"))
            return '该文件不曾存在，'.encodeCSID($id);
        if(0 != (int)$row['count']) return (int)0;
        // 获取文件名
        if(!$row = self::QRowNoErr("SELECT HEX(sha1) sha1, size FROM resdat WHERE id=$id LIMIT 1"))
            return '该文件已不存在，'.encodeCSID($id);
        $sha1 = $row['sha1'];
        $size = (int)$row['size'];
        $path = self::PathDat($sha1, $size);
        // 删除
        if(!self::QNoErr("DELETE FROM resdat WHERE id=$id"))
            return '服务器忙，请稍候再试，'.encodeCSID($id);
        unlink($path);
        return (int)$id;
    }/* return: error|$datid */
// ResPermission //_
    static function HasPermissionQ($permission, $row){//_
        $status         = (int)$row['status'];
        $is_author      = (int)$row['author_bbsid'] == (int)$_SESSION['bbs_uid'];
        $is_manager     = in_array((int)$_SESSION['bbs_groupid'], array(1,2,3));// || (int)$_SESSION['bbs_uid']==103896;

        switch($permission){
            case "CreateRes":
                return $is_manager;
                break;
            case "DeleteRes":
                return $is_manager || $is_author;
                break;
            case "EditRes":
                return $is_author || $is_manager;
                break;
            case "SeeRes":
                return STATUS_EDITING   == $status && $is_author
                    || STATUS_CHECKING  == $status && ($is_author || $is_manager)
                    || STATUS_PUBLISHED == $status && true;
                break;
            case "ManageRes":
                return self::IsManager($row);
                break;
            case "CreateDir":
                return self::IsManager($row);
                break;
            case "EditDir":
                return self::IsManager($row);
                break;
            case "CommentCreate":
                return self::IsManager($row);
                break;
            case "CommentEdit":
                return self::IsManager($row);
                break;
            case "CommentDelete":
                return self::IsManager($row);
                break;
            default:
                return false;
                break;
        }
        //return (int)$row['author_bbsid'] == (int)$_SESSION['bbs_uid'];
    }/* return: boolean */
    static function IsAuthor  ($row){//_
        print("WARNNING: ashy code");
        return false;
    }/* return: boolean */
    static function IsManager ($row=null){//_
        print("WARNNING: ashy code");
        return false;
    }/* return: boolean */
    static function CanEdit   ($row){//_
        print("WARNNING: ashy code");
        return false;
    }/* return: boolean */
    static function CanManage ($row=null){//_
        print("WARNNING: ashy code");
        return false;
    }/* return: boolean */
    static function CanSee    ($row){//_
        print("WARNNING: ashy code");
        return false;
    }/* return: boolean */
// Res           //_
    static function Create      (                 ){//_
        if(!self::IsManager())                   PageError('内测期间不允许创建资源');
        $query   = "INSERT INTO res SET t_create=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid='.IStr($_SESSION['bbs_uid'  ]);
        $query  .= ',author_name=' .IStr($_SESSION['bbs_uname']);
        $query  .= ',content='     ."''";
        self::Q($query, '创建资源');
        return mysql_insert_id();
    }/* return: DIE|$resid  */
    public function __construct ($id              ){//_
        $this->id  = (int)$id;
        $this->Exist();
    }/* return: DIE|null    */
    public function Exist       (                 ){//_
        $this->row       = self::QRow("SELECT * FROM res WHERE id={$this->id} LIMIT 1", '该资源不存在', $this->id);
        $this->canEdit   = self::CanEdit(  $this->row);
        $this->canManage = self::CanManage($this->row);
        $this->canSee    = self::CanSee(   $this->row);
        $this->status    = (int)$this->row['status'  ];
        $this->editing   = STATUS_EDITING == $this->status;
        $this->checking  = STATUS_CHECKING == $this->status;
        $this->published = STATUS_PUBLISHED == $this->status;
        return $this->row;
    }/* return: DIE|$row    */
    public function Update     ($ex = ',status=0'){//_
        return self::Q("UPDATE res SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)$ex WHERE id={$this->id}", '更新资源', $this->id);
    }/* return: DIE|$result */
    public function Edit        (&$name, &$summary, &$content, &$e_type){//_
        if(!$this->canEdit)                      PageError('无权编辑该资源', encodeCSID($this->id));
        $ex = ',status=0';
        $ex.= ',name='   .IStr($name   );
        $ex.= ',summary='.IStr($summary);
        $ex.= ',content='.IStr($content);
        $ex.= ',e_type=' .IStr($e_type );
        return $this->Update($ex);
    }/* return: DIE|$result */
    public function Delete      (                 ){//_
        if(!$this->canEdit && !$this->canManage) PageError('无权删除该资源', encodeCSID($this->id));
        return self::Q("DELETE FROM res WHERE id={$this->id}", '删除资源', $this->id);
    }/* return: DIE|$result */
    public function Check       (&$author_name, &$fromurl=''){//_
        if(!$this->canEdit)                      PageError('无权编辑该资源', encodeCSID($this->id));
        return $this->Update(',status='.STATUS_CHECKING .',author_name='.IStr($author_name).',fromurl='.IStr($fromurl));
    }/* return: DIE|$result */
    public function Publish     (&$checkerid      ){//_
        if(!$this->canManage)                    PageError('无权审核该资源', encodeCSID($this->id));
        return $this->Update(',status='.STATUS_PUBLISHED.',checkerid='  .IInt($checkerid  ));
    }/* return: DIE|$result */
// Upload        //_
    public function BindFile    ($f, $dirid){//_
        if(!is_int($datid = $this->UpDat($f)))             return $datid.'，'.$f['name'];
        // SQL安全检查
        $filename = IStr($f['name']);
        $dirid    = IInt($dirid    );
        $size     = (int)$f['size'];
        // 綁定文件
        $query = "INSERT INTO resfile SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query.= ",datid=$datid,dirid=$dirid,filename=$filename,size=$size";
        if(!self::QNoErr($query))                          return '上传没有完成，请再试试吧，'.$f['name'];
        $this->Update(",status=0,totalsize=(SELECT SUM(size) size FROM (SELECT resdat.size FROM resdat JOIN resfile ON resid={$this->id} WHERE resdat.id=resfile.datid) lssize)");
                                                           return '上传成功，'.$f['name'];
    }/* return: DIE|error */
    public function BindImg  ($f        ){//_
        $img = 1;
        if(!is_int($datid = $this->UpDat($f, $img)))      return $datid.'，'.$f['name'];
        // 取宽高
        $w       = (int)$img[0];
        $h       = (int)$img[1];
        $comment = IStr($f['name']);
        // 綁定文件
        $query = "INSERT INTO resimg SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query.= ",datid=$datid,w=$w,h=$h,comment=$comment";
        if(!self::QNoErr($query))                          return '上传未完成，请重试，'.$f['name'];
        $this->Update(',mainimgid='.mysql_insert_id());    return '上传成功，'.$f['name'];
    }/* return: DIE|error */
    static function DelFile  ($id       ){//_
        //SQL安全检查
        $id = (int)$id;
        // 取文件关系
        $row = self::QRow("SELECT datid, resid, filename FROM resfile WHERE id=$id", '该文件不曾存在，'.encodeCSID($id));
        $resid = (int)$row['resid'];
        $datid = (int)$row['datid'];
        $self  = new self($resid)  ;
        if(!$self->canEdit)                          PageError('无权编辑该资源', $resid);
        //删除
        self::Q("DELETE FROM resfile WHERE id=$id", "删除文件".$id);
        $self->Update(",status=0,totalsize=(SELECT SUM(size) size FROM (SELECT resdat.size FROM resdat JOIN resfile ON resid={$self->id} WHERE resdat.id=resfile.datid) lssize)");
        if(!is_int($errinfo = self::DelDat($datid))) return $errinfo;
                                                     return '删除成功，'.$row['filename'];
    }/* return: DIE|error */
    static function DelImg   ($id       ){//_
        //SQL安全检查
        $id = (int)$id;
        // 取文件关系
        $row=self::QRow("SELECT datid, resid, comment FROM resimg WHERE id=$id", '该文件不曾存在，'.encodeCSID($id));
        //检查权限
        $resid = (int)$row['resid'];
        $datid = (int)$row['datid'];
        $self  = new self($resid)  ;
        if(!$self->canEdit)                          PageError('无权编辑该资源', $resid);
        //删除
        self::Q("DELETE FROM resimg WHERE id=$id", "删除图片".$id);
        if(!is_int($errinfo = self::DelDat($datid))) return $errinfo;
                                                     return '删除成功，'.$row['comment'];
    }/* return: DIE|error */
    static function NewDir   (&$pid='', &$dirname=''){//_
        if(!self::IsManager())                   PageError('公测期间不能创建目录');
        $pid = IInt(decodeCSID($pid));
        if($pid != 0){
            self::QRow("SELECT id FROM resdir WHERE id=$pid LIMIT 1", '目录不存在');
        }
        $query   = "INSERT INTO resdir SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid='  .IStr($_SESSION['bbs_uid']);
        $query  .= ',pid='           .$pid.'';
        $query  .= ',dirname='       .IStr($dirname);
        $query  .= ',filter='        ."'*.*'";
        self::Q($query, '创建目录');
        return mysql_insert_id();
    }/* return: DIE|$dirid  */
    static function ModDir   ($id,&$pid, &$dirname){
        if(!self::IsManager())                   PageError('公测期间不能修改目录');
        $pid = IInt(decodeCSID($pid));
        if($pid != 0){
            self::QRow("SELECT id FROM resdir WHERE id=$pid LIMIT 1", '目录不存在');
        }
        $query  = "UPDATE resdir SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid='  .IStr($_SESSION['bbs_uid']);
        $query  .= ',pid='           .$pid.'';
        $query  .= ',dirname='       .IStr($dirname);
        $query  .= " WHERE id=$id";
        self::Q($query, '修改目录');
        if(!mysql_affected_rows())  PageError('你不能修改这个目录');
    }/* return: DIE|null */
    static function DelDir   (&$dir){
        $id     = decodeCSID($dir);
        if(!self::IsManager())                   PageError('公测期间不能删除目录');
        $query  = "DELETE FROM resdir WHERE id=$id";
        self::Q($query, '删除目录');
        if(!mysql_affected_rows())  PageError('你不能修改这个目录');
    }/* return: DIE|null */
    static function UrlFile  ($id       ){//_
        //确认文件存在
        if(!($result = self::QNoErr("SELECT HEX(sha1) sha1,size FROM resdat WHERE id=(SELECT datid id FROM resfile WHERE id=$id)"))
        OR !($row    = mysql_fetch_array($result)))
            PageError404();
        $sha1 = $row['sha1'];
        $size = $row['size'];
        //返回相对路径(用户)
        return self::PathDatUser($sha1, $size);
    }
    static function UrlImg   ($id       ){//_
        //确认文件存在
        if(!($result = self::QNoErr("SELECT HEX(sha1) sha1,size FROM resdat WHERE id=(SELECT datid id FROM resimg WHERE id=$id)"))
        OR !($row   = mysql_fetch_array($result))){
            header('HTTP/1.1 404 Not Found');
            return './image/'.'imgnoexist.png';
        }
        //返回相对路径(用户)
        $sha1 = $row['sha1'];
        $size = $row['size'];
        return self::PathDatUser($sha1, $size);
    }
    static function Gamebase($post = ''){
        if(is_int($post)){
            return array(
                "o" => ($post & 0x0001) ? 'Yes' : null,
                "r" => ($post & 0x0002) ? 'Yes' : null,
                "a" => ($post & 0x0004) ? 'Yes' : null,
                "c" => ($post & 0x0008) ? 'Yes' : null,
                "4" => ($post & 0x0010) ? 'Yes' : null,
                "f" => ($post & 0x0020) ? 'Yes' : null,
                "m" => ($post & 0x0040) ? 'Yes' : null,
            );
        }else{
            $b_gamebase = 0;
            if(isset($post["o"])) $b_gamebase |= 0x0001; //red  其它版本   /?????.exe
            if(isset($post["r"])) $b_gamebase |= 0x0002; //red  红帽子     /empires2.exe
            if(isset($post["a"])) $b_gamebase |= 0x0004; //1.0a 蓝帽子1.0a /age2_x1.exe
            if(isset($post["c"])) $b_gamebase |= 0x0008; //1.0c 蓝帽子1.0c /age2_x1/age2_x1.exe
            if(isset($post["4"])) $b_gamebase |= 0x0010; //1.4  蓝帽子1.4  /age2_x1/???
            if(isset($post["f"])) $b_gamebase |= 0x0020; //forg 遗忘的帝国 绿帽子
            if(isset($post["m"])) $b_gamebase |= 0x0040; //mod  带mod的帝国 黑帽子 /????
            return $b_gamebase;
        }
    }
// Comment       //_
    static function CreateComment($comment, $vote){
        if(!self::IsManager())                   PageError('内测期间不允许评论');
        $author_bbsid = IInt($_SESSION['bbs_uid'  ]);
        $author_name  = IStr($_SESSION['bbs_uname']);
        $comment      = IStr($comment              );
        $vote         = IInt($vote                 );
        $query ="INSERT INTO rescomment SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query.=",author_bbsid=$author_bbsid,author_name=$author_name";
        $query.=",comment=$comment,vote=$vote";
        return self::Q($query, '评论', $this->id);
    }
    static function DeleteComment($id){
        $row = self::QRow("SELECT author_bbsid FROM rescomment WHERE id=$id", '该评论不曾存在，'.encodeCSID($id));
        if(!self::IsAuthor($row))                    PageError('无权删除该评论', $resid);
        return self::Q("DELETE FROM rescomment WHERE id=$id", '删除评论');
    }
    static function CommentOO($id){
        return self::Q("UPDATE rescomment SET oo=oo+1 WHERE id=$id", '字字珠玑');
    }
    static function CommentXX($id){
        return self::Q("UPDATE rescomment SET xx=xx+1 WHERE id=$id", '信口胡言');
    }
// Rate          //_
    //........
// Tag           //_
    public function CreateTag($comment, $vote){
        $author_bbsid = IInt($_SESSION['bbs_uid'  ]);
        $author_name  = IStr($_SESSION['bbs_uname']);
        $comment      = IStr($comment              );
        $vote         = IInt($vote                 );
        $query ="INSERT INTO rescomment SET t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP),resid={$this->id}";
        $query.=",author_bbsid=$author_bbsid,author_name=$author_name";
        $query.=",comment=$comment,vote=$vote";
        return self::Q($query, '评论', $this->id);
    }
    static function DeleteTag($id){
        $row = self::QRow("SELECT author_bbsid FROM rescomment WHERE id=$id", '该评论不曾存在，'.encodeCSID($id));
        if(!self::IsAuthor($row))                    PageError('无权删除该评论', $resid);
        return self::Q("DELETE FROM rescomment WHERE id=$id", '删除评论');
    }
    public function BindTag($id){
        return self::Q("UPDATE rescomment SET oo=oo+1 WHERE id=$id", '字字珠玑');
    }
    static function DeleteTagRel($id){
        return self::Q("UPDATE rescomment SET xx=xx+1 WHERE id=$id", '信口胡言');
    }
}


/**


                                                    请求


**/