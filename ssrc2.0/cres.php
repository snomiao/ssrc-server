<?
namespace SSRC2;

class CRes{
	static function PermQ(){
		
	}
    static function Create(){
        if(!self::PermQ("CreateRes"))
            PageError('没有权限: 创建资源');
        $query   = "INSERT INTO res SET"
        $query  .= " t_create=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ",t_update=UNIX_TIMESTAMP(CURRENT_TIMESTAMP)";
        $query  .= ',author_bbsid='.IStr($_SESSION['bbs_uid'  ]);
        $query  .= ',author_name=' .IStr($_SESSION['bbs_uname']);
        $query  .= ',content='     .IStr('');
        CSSRC2::Q($query, '创建资源');
        return mysql_insert_id();
    }
    private function Exist       (                 ){//_
        $this->row       = self::QRow("SELECT * FROM res WHERE id={$this->id} LIMIT 1", '定位资源', $this->id);
        if($this->row === false) PageError('该资源不存在', $this->id);
        return $this->row;
    }
}
class CResComment{
	
}
class CResDat{
	
}
class CResDir{
	
}
class CResFile{
	
}
class CResImg{
	
}
class CResRate{
	
}
class CResReview{
	
}
class CResTag{
	
}
class CResTagrel{
	
}