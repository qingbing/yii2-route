<?php

namespace YiiRoute\models;

use YiiHelper\abstracts\Model;

/**
 * This is the model class for table "{{%route_logs}}".
 *
 * @property int $id 自增ID
 * @property string $system_code 系统别名
 * @property string $url_path 接口的path
 * @property string $trace_id 客户端日志ID
 * @property string $method 请求方法[get post put...]
 * @property int $is_success 是否成功[0:失败; 1:成功]
 * @property string $keyword 关键字，用于后期筛选
 * @property string $message 操作消息
 * @property string|null $input 请求内容
 * @property string|null $output 响应内容
 * @property string|null $exts 扩展信息
 * @property float $use_time 路由耗时
 * @property string $ip 登录IP
 * @property int $uid 用户ID
 * @property string $created_at 创建时间
 */
class RouteLogs extends Model
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%route_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system_code'], 'required'],
            [['is_success', 'uid'], 'integer'],
            [['input', 'output', 'exts', 'created_at'], 'safe'],
            [['use_time'], 'number'],
            [['system_code'], 'string', 'max' => 50],
            [['url_path'], 'string', 'max' => 200],
            [['trace_id'], 'string', 'max' => 32],
            [['method'], 'string', 'max' => 10],
            [['keyword'], 'string', 'max' => 100],
            [['message'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => '自增ID',
            'system_code' => '系统别名',
            'url_path'    => '接口的path',
            'trace_id'    => '客户端日志ID',
            'method'      => '请求方法[get post put...]',
            'is_success'  => '是否成功[0:失败; 1:成功]',
            'keyword'     => '关键字，用于后期筛选',
            'message'     => '操作消息',
            'input'       => '请求内容',
            'output'      => '响应内容',
            'exts'        => '扩展信息',
            'use_time'    => '路由耗时',
            'ip'          => '登录IP',
            'uid'         => '用户ID',
            'created_at'  => '创建时间',
        ];
    }

    const METHOD_GET  = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT  = 'PUT';

    /**
     * 获取所有请求方式
     *
     * @return array
     */
    public static function methods()
    {
        return [
            self::METHOD_GET  => 'GET',
            self::METHOD_POST => 'POST',
            self::METHOD_PUT  => 'PUT',
        ];
    }
}
