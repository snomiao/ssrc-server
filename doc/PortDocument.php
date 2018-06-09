<?php die('这都找得到…少年你可以联系雪星了(997596@gmail.com)');
/**


    账号管理系统


**/
@ 登录
    post: login.php
    data: p=密码&u=用户
    用户可以是username/uid/email, 按从左到右的优先级识别
    以下3种方式效果相同
@ 登录
    post: login.php
    data: p=密码&username=用户名
@ 登录
    post: login.php
    data: p=密码&uid=论坛号
@ 登录
    post: login.php
    data: p=密码&email=邮箱
@ 登录
    post: login.php
    data: pmd5=加密密码&u=用户
    用户可以是username/uid/email, 按从左到右的优先级识别
    以下3种方式效果相同
@ 登录
    post: login.php
    data: pmd5=加密密码&username=用户名
@ 登录
    post: login.php
    data: pmd5=加密密码&uid=论坛号
@ 登录
    post: login.php
    data: pmd5=加密密码&email=邮箱
@ 登出
    post: login.php
    data: logout=
@ 登出
    get : login.php
    data: logout
/**


    资源管理系统


**/
@ 下载图片
    get : res.php
        data: action=ls&img=图片编号
@ 下载文件
    get : res.php
        data: action=ls&file=文件编号
@ 创建资源
    get : res.php
    data: action=new
@ 删除资源
    post: res.php
    data: res=资源编号&action=del
@ 编辑资源
    post: res.php
    data: res=资源编号&action=edit&resname=资源名称&rescontent=资源描述&e_res_type=$e_res_type
    资源描述: 以后打算支持少量html标签
@ 发布资源
    post: res.php
    data: res=资源编号&action=check&author_name=$author_name&fromurl=$fromurl
@ 审核资源
    post: res.php
    data: res=资源编号&action=publish
/**
    文件管理系统
**/
@ 上传文件(2M以内)
    post: res.php
    data: res=资源编号&action=upload&resfile[]=文件列表&dir=目录编号
    返回cookie: msg=上传结果
@ 上传图片(2M以内)
    post: res.php
    data: res=资源编号&action=upload&resimg[]=图片列表
    返回cookie: msg=上传结果
@ 新建目录
    post: res.php
    data: action=moddir&pdir=父目录编号&dirname=目录名称
@ 修改目录
    post: res.php
    data: action=moddir&pdir=父目录编号&dirname=目录名称&dir=目录编号
@ 删除目录
    post: res.php
    data: action=deldir&dir=目录编号
/**
    社交管理系统
**/
暂未开放: @ 发表评分
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=newreview&content=评分理由&vote=评分
暂未开放:     评分(1.00~5.00)
暂未开放: @ 删除评分
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=delreview&cmt=评论编号
暂未开放: 
暂未开放: @ 发表评论
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=newcmt&content=评论内容&vote=玩家推荐度
暂未开放:     玩家推荐度(12345星)
暂未开放: @ 删除评论
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=newcmt&cmt=评论编号
暂未开放: 
暂未开放: @ 贴标签
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=newtag&tagname=标签名称
暂未开放: @ 撕标签
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=deltag&tag=标签编号
/**

**/


@狂兄
登录试试


@yty
我们的交叉宣传力度不够....
比如  管家里没有按钮that点一下就能加我们主群的

