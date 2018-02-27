<?php
!defined('IN_SUPU') && exit('Forbidden');
if ($step) { //保存人员信息
    $uid = (int)getgp('uid');
    $is_hire = (int)getgp('is_hire');
    
    $name = trim(getgp('name'));
    $code = trim(getgp('code'));	//人员编号
    $easycode = getFirstNameChar($name); //易记码
	if(!$code)
	{
		$num = $db->find_num('hr'," AND easycode = '$easycode'");
	    $num++;
        $code = $easycode . sprintf("%03d",$num);
	}
    $user_value = array(
	    'code' => $code,
	    'filenum' => getgp("filenum"),
		'easycode' => $easycode,
        'name' => $name , //姓名
        'ename' => getgp('ename') , //别名
        'sex' => getgp('sex') , //性别
        'birthday' => getgp('birthday') , //生日
        'cts_date' => getgp('cts_date') , //合同开始日期
        'cte_date' => getgp('cte_date') , //合同结束
        'agreement_s_date' => getgp('agreement_s_date') , //协议开始日期
        'agreement_e_date' => getgp('agreement_e_date') , //协议结束
        'tel' => getgp('tel') , //手机
        'areacode' => getgp('areacode') , //区划地址
        'areacode_str' => getgp('areacode_str') , //区划地址
        'ctfrom' => getgp('ctfrom') , //合同来源
        'department' => getgp('department') , //部门
        'card_type' => getgp('card_type') , //证件类型
        'card_no' => getgp('card_no') , //证件号码
        'bank' => getgp('bank') , //
        'account' => getgp('account') , //
        'is_hire' => max(1, $is_hire) , //在聘情况 在职 离职
        'is_office' => getgp('is_office') ? getgp('is_office') : NULL, //是否办公人员 1：是 0否
        'job_type' => getgp('job_type') , //人员性质
        'technical' => getgp('technical') , //人员职称
        'audit_job' => getgp('audit_job') , //审核性质 专职 兼职 无
        'urgent_person' => getgp('urgent_person') ,
        'urgent_tel' => getgp('urgent_tel') ,
        'unit' => getgp('unit') ,
        'agreement_s_date' => getgp('agreement_s_date') ,
        'agreement_e_date' => getgp('agreement_e_date') ,
        //'post' => implode("；", getgp('post')) , //岗位
        'retire' => getgp('retire') ,  				//退休
        //'major' => getgp('major') , //专业特长
        'm_separate' => getgp('m_separate'), //人员分层
        //'day_quota' => getgp('day_quota'), //定额天数
    	//'fee_regiest' => getgp('fee_regiest'), //
    	'is_secret' => getgp('is_secret'), //人员分层
    	//'invoice_title' => getgp('invoice_title'), //
    	'ccaa_code' => getgp('ccaa_code'), //ccaa档案号
    	'note_account' => getgp('note_account') //ccaa账号
    );
    if ($user_value['job_type'] !== NULL) {
        $user_value['job_type'] = join('|', $user_value['job_type']);
        //判断是否是审核员
        if (in_array('1004-1', getgp('job_type'))) {
            $u_info = $user->get($uid);
            if (!$u_info['username']) {
                $t_arr['username'] = trim(getgp('tel'));
                $t_arr['password'] = md5('123456');
                $user->edit($uid, $t_arr);
            }
        }
    }
    if ($uid) {
    	
        $bf_str = serialize($user->get($uid));
        $user->edit($uid, $user_value);
        log_add(0, $uid, "修改人员", $bf_str, serialize($user->get($uid)));
    } else {
        $user_value['username'] = substr($user_value['card_no'],-6);
        $user_value['password'] = md5('123456');
        $uid = $user->add($user_value);
        //日志
        $af_info = serialize($user->get($uid));
        log_add(0, $uid, "新增人员", NULL, $af_info);
    }
    $REQUEST_URI = '?c=hr&a=edit&uid=' . $uid . '#' . $_POST['jump'];
    showmsg('success', 'success', $REQUEST_URI);
} else { 
	
    if ($_GET['uid']) { //编辑时候显示需要编辑的信息
 
		
		$upload_hr_photo_dir=get_option('upload_hr_photo_dir').$_GET['uid'].'.jpg'; //人员上传头像路径
		if(!file_exists($upload_hr_photo_dir)){
			$upload_hr_photo_dir=get_option('upload_hr_photo_dir').'nophoto.jpg';	//默认没有头像的照片
 		}
	 
 		
        $uid = (int)getgp('uid');
        $row = $user->get($uid);
        extract(chk_arr($row), EXTR_SKIP);

		$is_hire = $row['is_hire'];
        $audit_job = (int)$row['audit_job'];		//是否专职 无、专职、兼职
        //人员来源  *用f_select 会去掉前空
        $ctfrom_select = str_replace("value=\"$ctfrom\"", "value=\"$ctfrom\" selected", $ctfrom_select);

        //人员性质
        $arr_job_type = explode('|', $job_type);
        foreach ($arr_job_type as $key => $value) {
            $job_type_checkbox = str_replace("value=\"$value\">", "value=\"$value\" checked>", $job_type_checkbox);
        }


        //性别
        if ($sex == '1') {
            $sex_M = 'checked';
            $sex_F = '';
        } elseif ($sex == '2') {
            $sex_M = '';
            $sex_F = 'checked';
        }

		//在聘情况
		if ($is_hire == 1) {
			$is_hire_Y = 'checked';
			$is_hire_N = '';
			$is_hire_T = '';
		} else if ($is_hire == 2) {
			$is_hire_Y = '';
			$is_hire_N = 'checked';
			$is_hire_T = '';
		} else if ($is_hire == 3) {
			$is_hire_Y = '';
			$is_hire_N = '';
			$is_hire_T = 'checked';
		}

		//读取个人经历列表
		if ($uid) {
			$query = '';
			$sql = "SELECT * FROM sp_hr_experience he where he.deleted='0' AND he.add_hr_id=$uid";
			$query = $db->query($sql);
			while ($rt = $db->fetch_array($query)) {
				if ($rt['type'] == 'g') { //读取工作经历
					$glist[] = $rt;
				} elseif ($rt['type'] == 'j') { //读取教育经历
					$jlist[] = $rt;
				} elseif ($rt['type'] == 'p') { //读取培训经历
					$plist[] = $rt;
				}
			};
		}
    }
	//if结束
	
    //提示信息
    $j_tip_msg = '添加教育经历';
    $g_tip_msg = '添加工作经历';
    $p_tip_msg = '添加培训经历';
    if ($_GET['jid']) { //要修改的培训信息
        $j_tip_msg = '编辑教育经历';
        $jExpInfo = $exp->get($_GET['jid']);
    } else if ($_GET['gid']) { //要修改的培训信息
        $g_tip_msg = '编辑工作经历';
        $gExpInfo = $exp->get($_GET['gid']);
    } else if ($_GET['pid']) { //要修改的培训信息
        $p_tip_msg = '编辑培训经历';
        $pExpInfo = $exp->get($_GET['pid']);
    }
    //人员文档
    $hr_archives = array();
    if ($uid) {
        $query = $db->query("SELECT * FROM sp_hr_archives WHERE uid = '$uid'"); // LIMIT 10
        while ($rt = $db->fetch_array($query)) {
            $rt['ftype_V'] = f_atachtype($rt['ftype']);
            $hr_archives[] = $rt;
        }
    }
    $startYear = date('Y') - 70;
    $endYear = date('Y') + 5;
    $mt_rand = mt_rand(1, 999999);
   
    tpl();
}
?>

