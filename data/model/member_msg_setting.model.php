<?php
/**
 * 用户消息模板模型
 *
 * @copyright  Copyright (c) 2007-2016 shopec Inc. (http://www.shopec.net)
 * @license    http://www.shopec.net
 * @link       http://www.shopec.net
 * @since      File available since Release v1.1
 */
defined('Inshopec') or exit('Access Invalid!');
class member_msg_settingModel extends Model{
    public function __construct() {
        parent::__construct('member_msg_setting');
    }

    /**
     * 用户消息模板列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getMemberMsgSettingList($condition, $field = '*', $page = 0, $order = 'mmt_code asc') {
        return $this->field($field)->where($condition)->order($order)->page($page)->select();
    }

    /**
     * 用户消息模板详细信息
     * @param array $condition
     * @param string $field
     */
    public function getMemberMsgSettingInfo($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
    }

    /**
     * 编辑用户消息模板
     * @param array $condition
     * @param unknown $update
     */
    public function addMemberMsgSettingAll($insert) {
        return $this->pk(array('mmt_code', 'member_id'))->insertAll($insert, true);
    }
}
