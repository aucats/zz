<?php
!defined('IN_SUPU') && exit('Forbidden');
//系统下载操作，企业，人员
$class = $_GET['class']? $_GET['class'] : 'enterprise';

$att = load("pro");
if($class=='enterprise'){
 	$file_dir=get_option( 'upload_ep_dir' );
}elseif($class=='zz'){
 	$file_dir=get_option( 'upload_zz_dir' );
}elseif($class == 'pro'){
	$file_dir = get_option( 'upload_pro_dir' );
}else{
	$file_dir=get_option( 'upload_hr_dir' );
	$att->table='hr_archives';
}
$att->attachdir=$file_dir; //配置下载路径

if( 'down' == $a ){ //单个文档下载
	$aid = getgp( 'aid' );

	$att->down( $aid );
} elseif( 'batdown' == $a ){ //批量下载文档
 	$aids = getgp( 'aid' );
	$aids = array_unique( $aids );
 	if( $aids ){
		$att->batdown( $aids );
	}
}else if('del' == $a ){ //删除上传的文档
	$aid = getgp( 'aid' );
	$att->del( $aid );
	echo "<script>history.go(-1);</script>";
}

?>