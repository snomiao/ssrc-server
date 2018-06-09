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
    data: pmd5=密码&u=用户
@ 登录
    post: login.php
    data: pmd5=密码&username=用户名
@ 登录
    post: login.php
    data: pmd5=密码&uid=论坛号
@ 登录
    post: login.php
    data: pmd5=密码&email=邮箱
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
@ 下载资源
    get : res.php
        data: action=ls&res=资源编号
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
@ 设置兼容版本
    post: res.php
    data: res=资源编号&action=b_gamebase
        &o=兼容red  其它版本   /?????.exe
        &r=兼容red  红帽子     /empires2.exe
        &a=兼容1.0a 蓝帽子1.0a /age2_x1.exe
        &c=兼容1.0c 蓝帽子1.0c /age2_x1/age2_x1.exe
        &4=兼容1.4  蓝帽子1.4  /age2_x1/???
        &f=兼容forg 遗忘的帝国 绿帽子
        &m=兼容mod  带mod的帝国 黑帽子 /????
    以上选项
        填=Yes为勾选
        不填为取消勾选
@ 获取兼容版本
    post: res.php
    data: res=资源编号&action=b_gamebase
    返回值
        o; 兼容red  其它版本   /?????.exe
        r; 兼容red  红帽子     /empires2.exe
        a; 兼容1.0a 蓝帽子1.0a /age2_x1.exe
        c; 兼容1.0c 蓝帽子1.0c /age2_x1/age2_x1.exe
        4; 兼容1.4  蓝帽子1.4  /age2_x1/???
        f; 兼容forg 遗忘的帝国 绿帽子
        m; 兼容mod  带mod的帝国 黑帽子 /????
    返回值例: "a;c;4;"
    注: 以后版本标号不一定只有一个字母
/**
    权限管理系统
**/
@ 可编辑
    post: res.php
    data: res=资源编号&action=canEdit
@ 可审核
    post: res.php
    data: res=资源编号&action=canManage
@ 可视
    post: res.php
    data: res=资源编号&action=canSee
/**
    文件管理系统
**/
@ 上传文件(256M以内)
    post: res.php
    data: res=资源编号&action=upload&resfile[]=文件列表&dir=目录编号
    *resfile[]不能直接传值
    返回cookie: msg=上传结果
@ 上传图片(256M以内)
    post: res.php
    data: res=资源编号&action=upload&resimg[]=图片列表
    *resimg[]不能直接传值
    返回cookie: msg=上传结果
@ 新建目录
    post: res.php
    data: action=newdir&pdir=父目录编号&dirname=目录名称
@ 修改目录
    post: res.php
    data: action=moddir&pdir=父目录编号&dirname=目录名称&dir=目录编号
@ 删除目录
    post: res.php
    data: action=deldir&dir=目录编号
/**
    社交管理系统
**/
@ 发表评论
    post: res.php
    data: res=资源编号&action=CreateComment&content=评论理由&vote=评论
    评论(1~5)整数 数字传入双精度类型
@ 删除评论
    post: res.php
    data: res=资源编号&action=DeleteComment&cmt=评论编号
@ OO评论
    post: res.php
    data: res=资源编号&action=CommentOO&cmt=评论编号
    评论(1.00~5.00)
@ XX评论
    post: res.php
    data: res=资源编号&action=CommentXX&cmt=评论编号

@ 发表评分
    post: res.php
    data: res=资源编号&action=CreateReview&content=评分理由&vote=评分
    评分(1.00~5.00)整数 数字传入双精度类型
@ 删除评分
    post: res.php
    data: res=资源编号&action=DeleteReview&rvw=评分编号
@ OO评分
    post: res.php
    data: res=资源编号&action=ReviewOO&rvw=评分编号
    评分(1.00~5.00)
@ XX评分
    post: res.php
    data: res=资源编号&action=ReviewXX&rvw=评分编号
/**
    查询索引系统
**/
暂未开放: @ 贴标签
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=newtag&tagname=标签名称
暂未开放: @ 撕标签
暂未开放:     post: res.php
暂未开放:     data: res=资源编号&action=deltag&tag=标签编号