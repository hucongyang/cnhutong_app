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

        'MSG_ERR_INVALID_MEMBER'        => array('20016', '学员memberId已绑定'),

        'MSG_ERR_FAIL_MEMBER'         => array('20017', '传入的学员memberId错误'),

        'MSG_ERR_NULL_SALT'                  => array('20018', '口令为NULL'),

        'MSG_MOBILE_PASSWORD_LOGIN'       => array('20019', '账号/密码登录'),

        'MSG_ERR_LESSON_ARRANGE_ID'       => array('20020', '课程的唯一排课编号错误'),

        'MSG_ERR_LESSON_STUDENT_ID'       => array('20021', '课时唯一编号错误'),

        'MSG_ERR_SCORE'                     => array('20022', '评分不在范围内(0-5分)'),

        'MSG_ERR_QUESTION'                  => array('20023', '题目顺序错误'),

        'MSG_ERR_INVALID_EVAL'                  => array('20024', '该学员ID对应该课时ID已评价'),

        'MSG_ERR_FEEDBACK'                  => array('20025', '反馈内容字数不在范围内(1-200字以内)'),

        'MSG_ERR_INVALID_SIGN'             => array('20026', '今日已签到 '),

        'MSG_ERR_INVALID_PRIZE'             => array('20027', '连续签到7天才可抽奖'),

        'MSG_ERR_SIGN_TYPE'             => array('20028', '签到类型错误'),

        'MSG_ERR_HISTORY_ID'             => array('20029', '积分历史ID错误'),

        'MSG_ERR_NULL_HISTORY'             => array('20030', '没有积分历史'),

        'MSG_ERR_LEAVE_TYPE'             => array('20031', '请假类型错误'),

        'MSG_ERR_NO_LEAVE'             => array('20032', '正在请假中'),

        'MSG_ERR_NO_CANCEL_LEAVE'      => array('20033', '当前不能取消请假'),

        'MSG_ERR_FAIL_TEACHER_ID'       => array('20034', '教师ID错误'),

        'MSG_ERR_NULL_TEACHER'       => array('20035', '没有教师信息'),

        'MSG_ERR_FAIL_PRODUCE_ID'       => array('20036', '作品ID错误'),

        'MSG_ERR_FAIL_PRODUCE'       => array('20037', '没有作品信息'),

		// 其它
		'MSG_ERR_UNKOWN'			=> array('99999', '未知错误')
);

// return $ErrorCode;
