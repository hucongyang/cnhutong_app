<?php
/*********************************************************
 * 错误码列表
 * 
 * @author  Lujia
 * @version 1.0 by Lujia @ 2013.12.23 创建错误列表
 ***********************************************************/
 
$_error_code = array(
		// 基本错误码
		'MSG_SUCCESS' 				=> array('10000', '成功'),
		'MSG_ERR_LESS_PARAM' 		=> array('10001', '请求缺少必要的参数'),
		'MSG_ERR_FAIL_PARAM' 		=> array('10002', '请求参数错误'),

        'MSG_ERR_INVALID_MOBILE'    => array('20001', '该手机号码已被注册'),
	    'MSG_SUCCESS_MOBILE'         => array('20002', '该手机号码可用'),

        'MSG_ERR_CODE_OVER_TIME'    => array('20003', '验证码已过期'),

        'MSG_ERR_UN_REGISTER_MOBILE'    => array('20004', '该手机号码未被注册,请先注册'),

        'MSG_ERR_FAIL_PASSWORD' 		=> array('20005', '密码错误'),

        'MSG_ERR_FAIL_DEPARTMENT'   => array('20006', '没有该校区信息'),

        'MSG_ERR_FAIL_TOKEN'     => array('20007', '传入的token错误'),

        'MSG_ERR_FAIL_USER'         => array('20008', '传入的用户ID错误'),

        'MSG_ERR_FAIL_MOBILE'       => array('20009', '手机号码错误'),

        'MSG_ERR_FAIL_SUBJECT'       => array('20010', '课程错误'),

        'MSG_ERR_FAIL_REFEREE_MOBILE'       => array('20011', '推荐人手机号码错误'),

        'MSG_ERR_FAIL_TESTID'       => array('20012', '测试编号错误'),

        'MSG_ERR_OVER_THREE_MEMBERID'     => array('20013', '该用户已经绑定3个学员ID'),

        'MSG_ERR_INVALID_BIND_MOBILE'       => array('20014', '该手机号码已被绑定'),

        'MSG_ERR_SALT'                  => array('20015', '口令错误'),

        'MSG_ERR_INVALID_SALT'        => array('20016', '口令已被使用，失效'),

        'MSG_ERR_FAIL_MEMBER'         => array('20017', '传入的学员MEMBER_ID错误'),

        'MSG_ERR_NULL_SALT'                  => array('20018', '口令为NULL'),

        'MSG_MOBILE_PASSWORD_LOGIN'       => array('20019', '账号/密码登录'),

        'MSG_ERR_LESSON_ARRANGE_ID'       => array('20020', '课程的唯一排课编号错误'),

        'MSG_ERR_LESSON_STUDENT_ID'       => array('20021', '课时唯一编号错误'),

        'MSG_ERR_SCORE'                     => array('20022', '评分不在范围内(0-5分)'),

        'MSG_ERR_QUESTION'                  => array('20023', '题目顺序错误'),

        'MSG_ERR_INVALID_EVAL'                  => array('20024', '该学员ID对应该课时ID已评价'),

		// 其它
		'MSG_ERR_UNKOWN'			=> array('99999', '未知错误')
);

// return $ErrorCode;
