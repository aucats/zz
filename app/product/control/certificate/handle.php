<?php
!defined('IN_SUPU') && exit('Forbidden');
/*
*应恢复证书
*/

$iso_select=f_select('iso'); //体系
$mark_select=f_select('mark');
$audit_type_select=f_select('audit_type','',array('1003','1004-1','1004-2','1004-3','1007'));
$certstate_select=f_select('certstate');

$fields = $join = $where = '';
extract( $_GET, EXTR_SKIP );

$certstatus = ($certstatus)?1:$certstatus;
//合同来源
$ctfrom_select = f_ctfrom_select();

//企业名称
$ep_name = trim($ep_name);
if( $ep_name ){
	$_eids = array();
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%' and deleted=0");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}

	if( $_eids ){
		$where .= " AND cert.eid IN (".implode(',',$_eids).")";
	} else {
		$where .= " AND cert.id < -1";
	}
	unset( $_eids, $_query, $rt, $_eids );
}


//省份
if( $areacode=getgp("areacode") ){
	$pcode = substr($areacode,0,2) . '0000';
	$_eids = array(-1);
	$_query = $db->query("SELECT eid FROM sp_enterprises WHERE LEFT(areacode,2) = '".substr($areacode,0,2)."'");
	while( $rt = $db->fetch_array( $_query ) ){
		$_eids[] = $rt['eid'];
	}
	$where .= " AND cert.eid IN (".implode(',',$_eids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


//企业编号
if( $ep_code=trim($ep_code) ){
	$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code' and deleted=0");
	if( $eid ){
		$where .= " AND cert.eid = '$eid'";
	} else {
		$where .= " AND cert.id < -1";
	}
}

//合同来源限制
$len = get_ctfrom_level( current_user( 'ctfrom' ) );

if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
	$_len = get_ctfrom_level( $ctfrom );
	$len = $_len;
} else {
	$ctfrom = current_user( 'ctfrom' );
}
switch( $len ){
	case 2	: $add = 1000000; break;
	case 4	: $add = 10000; break;
	case 6	: $add = 100; break;
	case 8	: $add = 1; break;
}
$ctfrom_e = sprintf("%08d",$ctfrom+$add);
$where .= " AND cert.ctfrom >= '$ctfrom' AND cert.ctfrom < '$ctfrom_e'";
$ctfrom_select = str_replace( "value=\"$ctfrom\"", "value=\"$ctfrom\" selected" , $ctfrom_select );
unset( $len, $_len );


if( $audit_type ){ //审核类型
	$where .= " AND cert.audit_type = '$audit_type'";
	$audit_type_select = str_replace( "value=\"$audit_type\">", "value=\"$audit_type\" selected>" , $audit_type_select );
}

if( $s_dates ){ //注册时间 起
	$where .= " AND cert.s_date >= '$s_dates'";
}
if( $s_dates ){ //注册时间 止
	$where .= " AND cert.s_date <= '$s_dates'";
}

if( $e_dates ){ //到期时间 起
	$where .= " AND cert.e_date >= '$e_dates'";
}
if( $e_datee ){ //到期时间 止
	$where .= " AND cert.e_date <= '$e_datee'";
}

if( $ct_code=trim($ct_code) ){ //合同编码
	$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code' and deleted=0");
	if( $ct_id ){
		$where .= " AND cert.ct_id = '$ct_id'";
	} else {
		$where .= " AND cert.id < -1";
	}
}

if( $cti_code=trim($cti_code) ){ //合同项目编码
	$cti_ids=array(-1);
	$query = $db->query("SELECT cti_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$cti_ids[]=$rt[cti_id];
		}
	$where .= " AND cert.cti_id in (".implode(",",$cti_ids).")";
	unset( $cti_id );
}
if($fac_code=trim($fac_code)){
    $res = $db->find_one("enterprises",array('fac_code'=>$fac_code),"eid");
    $where .= "AND ep_prod_id = '$res[eid]'";
}


if( $iso ){ //认证体系
	$where .= " AND cert.iso = '$iso'";
	$iso_select = str_replace("value=\"$iso\">","value=\"$iso\" selected>",$iso_select);
}

//专业代码
if( $audit_code ){
	$where .= " AND cert.audit_code LIKE '%$audit_code%'";
}
//认可标志
if( $mark ){
	$where .= " AND cert.mark = '$mark'";
	$mark_select = str_replace("value=\"$mark\">","value=\"$mark\" selected>",$mark_select);
}
if( $certno=trim($certno) ){
	$where .= " AND cert.certno = '$certno'";
}

// $join .= " LEFT JOIN sp_enterprises  e ON e.eid = cert.eid ";
// $join .= " LEFT JOIN sp_contract ct ON ct.ct_id = cert.ct_id";
// $join .= " LEFT JOIN sp_contract_item cti ON cti.cti_id = cert.cti_id";
//$join .= " LEFT JOIN sp_assess a ON a.id = cert.pd_id";
$join .= " LEFT JOIN sp_certificate_change c ON cert.id = c.zsid";
$day_time = date('Y-m-d');
$day_time1 = thedate_add($day_time,70,'day');
$where .= " and cert.status='02' ";
$where .= " and cge_date>='$day_time' and cge_date<='$day_time1'";
$where .= " and cert.deleted=0 AND cert.iso_prod_type=1";
if( !$export ){
	$total = $db->get_var("SELECT COUNT(cert.id) FROM sp_certificate cert $join WHERE 1 $where");
	$pages = numfpage( $total, 20, $url_param );
}
$sql = "SELECT cert.*,c.cge_date FROM sp_certificate cert $join WHERE 1 $where ORDER BY cert.id DESC $pages[limit]";

$query = $db->query( $sql );
while( $rt = $db->fetch_array( $query ) ){
	$datas[] = chk_arr($rt);
}

if( !$export ){
tpl();
} else {//应撤销证书列表
ob_start();
tpl( 'xls/list_certificate_restore' );
$data = ob_get_contents();
ob_end_clean();
export_xls( '应撤销证书列表', $data );
}