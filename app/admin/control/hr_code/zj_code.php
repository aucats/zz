<?php
!defined('IN_SUPU') && exit('Forbidden');
$arr_data=array("A02"=>array("王石"=>array("E-12-02","E-12-03","E-12-04"),
			"沈文钢"=>array("E-28-01","E-28-03","E-39-01"),
			"温建安"=>array('E-35-01'),
			),
			"A03"=>array(
			"朱成昌"=>array("S-04-02","S-05-01","S-05-02"),
			"沈文钢"=>array("S-28-01","S-28-03","S-39-01")
			),
			"A01"=>array("李成秀"=>array("Q-04-01","Q-04-02","Q-04-03","Q-04-04","Q-04-05","Q-04-06"),
						"温建安"=>array("Q-04-06","Q-22-01","Q-23-01","Q-30-00","Q-35-07"),
						"朱成昌"=>array("Q-05-01","Q-05-02","Q-05-03"),
						"张子健"=>array("Q-12-01","Q-12-02","Q-12-03","Q-12-04","Q-12-05","Q-12-06","Q-12-07","Q-12-08","Q-12-09","Q-12-10","Q-12-11","Q-39-01","Q-39-02"),
						"王石"=>array("Q-12-01","Q-12-02","Q-12-03","Q-12-04","Q-12-05","Q-12-06","Q-12-07","Q-12-08","Q-12-09","Q-12-10","Q-12-11"),
						"郭京洲"=>array("Q-15-01","Q-15-02","Q-15-03","Q-15-04"),
						"侯蔚兴"=>array("Q-17-01","Q-17-03"),
						"索军力"=>array("Q-18-02","Q-18-03","Q-18-04","Q-18-05"),
						"崔丰刚"=>array("Q-19-01","Q-19-02","Q-19-03","Q-19-04","Q-19-05","Q-19-06"),
						"郑培东"=>array("Q-22-01"),
						"张涛"=>array("Q-28-01","Q-28-04"),
						"沈文钢"=>array("Q-28-01","Q-28-03","Q-39-01"),
						"余桂军"=>array("Q-29-01","Q-29-02"),
						"曹宏斌"=>array("Q-30-00"),
						"张淑珍"=>array("Q-32-08"),
						"陈蔓悦"=>array("Q-33-00"),
						"温致平"=>array("Q-34-00","Q-35-04","Q-35-05","Q-36-01"),
						"彭冬秀"=>array("Q-35-02"),
						"徐雁"=>array("Q-35-06"),
						"陈喜群"=>array("Q-39-02")));
$hacs=array();					
foreach($arr_data as $iso=>$arr){
foreach($arr as $name=>$item){
foreach($item as $code){
	$h=array();
	$temp=$db->get_col("SELECT shangbao FROM `sp_settings_audit_code` WHERE `code` = '$code'");
	$h[use_code]=$code;
	$h[name]=$name;
	$h[audit_code]=@join(";",$temp);
	$hacs[]=$h;
}}}
ob_start();
	tpl( 'xls/list_hr_code' );
	$data = ob_get_contents();
	ob_end_clean();
	export_xls( '人员专业代码列表', $data );

die;	
					
?>