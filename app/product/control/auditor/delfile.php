<?php
!defined('IN_SUPU') && exit('Forbidden');
//删除自己上传的审核文件

$aid = getgp('id');
$ct_id = getgp('ct_id');
$tid = getgp('tid');
$att=load( 'attachment');
    if ($aid) {		
       load( 'attachment')->del($aid);
    }	
	$REQUEST_URI = "?m=product&c=auditor&a=task_edit&tid=$tid&ct_id=$ct_id";
    showmsg('success', 'success', $REQUEST_URI);