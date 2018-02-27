<?php
!defined('IN_SUPU') && exit('Forbidden');
/*
*应暂停项目列表
*/
	extract( $_GET, EXTR_SKIP );
	$mark_select=f_select('mark');
	$audit_ver_select = f_select('audit_ver');//体系版本
	$certstate_select=f_select('certstate');
	$audit_type_select=f_select('audit_type', '', array('1003','1004-1','1004-2','1004-3','1007'));
	$iso_select=f_select('iso');

	$ctfrom_select = f_ctfrom_select();//合同来源

	$fields = $join = $where = '';
	//企业名称
	$ep_name = trim($ep_name);
	if( $ep_name ){
		$_eids = array();
		$_query = $db->query("SELECT eid FROM sp_enterprises WHERE ep_name LIKE '%".str_replace('%','\%',$ep_name)."%' and deleted=0");
		while( $rt = $db->fetch_array( $_query ) ){
			$_eids[] = $rt['eid'];
		}

		if( $_eids ){
			$where .= " AND p.eid IN (".implode(',',$_eids).")";
		} else {
			$where .= " AND p.id < -1";
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
	$where .= " AND p.eid IN (".implode(',',$_eids).")";
	unset( $_eids, $_query, $rt, $_eids );
	
	$province_select = str_replace( "value=\"$pcode\">", "value=\"$pcode\" selected>" , $province_select );
}


	//企业编号
	if( $ep_code=trim($ep_code) ){
		$eid = $db->get_var("SELECT eid FROM sp_enterprises WHERE code = '$code' and deleted=0");
		if( $eid ){
			$where .= " AND p.eid = '$eid'";
		} else {
			$where .= " AND p.id < -1";
		}
	}

	//合同来源限制
	$len = get_ctfrom_level( current_user( 'ctfrom' ) );

	if( $ctfrom && substr( $ctfrom, 0, $len ) == substr( current_user( 'ctfrom' ), 0, $len ) ){
		$len =  get_ctfrom_level( $ctfrom );
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
	$where .= " AND p.ctfrom >= '$ctfrom' AND p.ctfrom < '$ctfrom_e'";
	$ctfrom_select = str_replace( "value=\"$ctfrom\"", "value=\"$ctfrom\" selected" , $ctfrom_select );
	unset( $len );


	if( $audit_type ){ //审核类型
		$where .= " AND p.audit_type = '$audit_type'";
	}

	if( $pre_date_start ){ //注册时间 起
		$where .= " AND p.pre_date  >= '$pre_date_start'";
	}
	if( $pre_date_end){ //注册时间 止
		$where .= " AND p.pre_date  <= '$pre_date_end'";
	}

	if( $final_date_start ){ //到期时间 起
		$where .= " AND p.final_date >= '$final_date_start'";
	}
	if( $final_date_end ){ //到期时间 止
		$where .= " AND p.final_date <= '$final_date_end'";
	}

	if( $ct_code=trim($ct_code) ){ //合同编码
		$ct_id = $db->get_var("SELECT ct_id FROM sp_contract WHERE ct_code = '$ct_code' and deleted=0");
		if( $ct_id ){
			$where .= " AND p.ct_id = '$ct_id'";
		} else {
			$where .= " AND p.id < -1";
		}
	}

	if( $cti_code=trim($cti_code) ){ //合同项目编码
		$cti_ids=array(-1);
	$query = $db->query("SELECT cti_id FROM sp_contract_item WHERE cti_code like '%$cti_code%' and deleted=0");
	while($rt=$db->fetch_array($query)){
		$cti_ids[]=$rt[cti_id];
		}
	$where .= " AND p.cti_id in (".implode(",",$cti_ids).")";
	}

 	if( $iso ){ //认证体系
		$where .= " AND p.iso = '$iso'";
	}


	$where .= " AND p.deleted = 0";

	$day_date = date('Y-m-d');
	$join .= " left join sp_enterprises e on e.eid=p.eid ";
	$join .= " left join sp_certificate z on (z.cti_id=p.cti_id AND z.eid = p.eid) ";
	$where .= "  and p.status in('0','5') and final_date<'$day_date' and p.audit_type not in ('1001','1002','1003') AND z.status = '01'";

	if( !$export ){
		$total = $db->get_var("select COUNT(*) from sp_project p $join where 1 $where ");
		$pages = numfpage( $total );
	}
	$sql = "select e.ep_name,p.eid,p.cti_id,p.cti_code,p.ct_code, p.ctfrom,p.pre_date,p.final_date,p.status,p.audit_ver,p.audit_type from sp_project p $join where 1 $where  $pages[limit] ";
	$res = $db->query($sql);
	while($row = $db->fetch_array($res)){
		$sql = "select id as zsid ,certno,e_date from  sp_certificate where eid='$row[eid]' and cti_id='$row[cti_id]' order by e_date desc limit 1";
		$c_info= $db->get_row($sql);//证书id，编辑的链接使用
		$c_info && $row=array_merge($c_info,$row);
		$datas[] = chk_arr($row);
	}
	if( !$export ){
		tpl('certificate/pushed');
	} else {
		ob_start();
		tpl( 'xls/list_pushed' );
		$data = ob_get_contents();
		ob_end_clean();

		export_xls( '应暂停项目', $data );
	}