<?php

namespace YiiRoute\models;

use yii\db\ActiveQuery;
use yii\db\StaleObjectException;
use YiiHelper\abstracts\Model;

/**
 * This is the model class for table "{{%route_interfaces}}".
 *
 * @property int $id 自增ID
 * @property string $system_code 系统别名
 * @property string $url_path 接口的path
 * @property string $name 接口名称
 * @property string $source 接口来源
 * @property int $is_operate 是否操作类[0:否; 1:是]
 * @property string $description 描述
 * @property int $record_field_type 接口是否记录新字段[0:随系统; 1:强制开启；2:强制关闭]
 * @property int $validate_type 接口校验方式[0:随系统; 1:强制开启；2:强制关闭]
 * @property int $strict_validate_type 开启严格校验[0:随系统; 1:强制开启；2:强制关闭]
 * @property int $is_open_route_log 是否开启路由日志[0:否; 1:是]
 * @property string $route_log_message 路由操作提示
 * @property string $route_log_key_fields 路由关键字
 * @property int $is_open_mocking 路由响应是否mock[0:否; 1:是]
 * @property int $is_use_custom_mock 是否使用自定义mock
 * @property string|null $mock_response 开启mock时的响应json
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property-read RouteInterfaceFields[] $interfaceFields
 * @property-read int $fieldCounts
 */
class RouteInterfaces extends Model
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%route_interfaces}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['system_code'], 'required'],
            [['is_operate', 'record_field_type', 'validate_type', 'strict_validate_type', 'is_open_route_log', 'is_open_mocking', 'is_use_custom_mock'], 'integer'],
            [['mock_response', 'created_at', 'updated_at'], 'safe'],
            [['system_code'], 'string', 'max' => 50],
            [['url_path', 'route_log_key_fields'], 'string', 'max' => 200],
            [['name'], 'string', 'max' => 100],
            [['source'], 'string', 'max' => 20],
            [['description', 'route_log_message'], 'string', 'max' => 255],
            [['url_path'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                   => '自增ID',
            'system_code'          => '系统别名',
            'url_path'             => '接口的path',
            'name'                 => '接口名称',
            'source'               => '接口来源',
            'is_operate'           => '是否操作类[0:否; 1:是]',
            'description'          => '描述',
            'record_field_type'    => '接口是否记录新字段[0:随系统; 1:强制开启；2:强制关闭]',
            'validate_type'        => '接口校验方式[0:随系统; 1:强制开启；2:强制关闭]',
            'strict_validate_type' => '开启严格校验[0:随系统; 1:强制开启；2:强制关闭]',
            'is_open_route_log'    => '是否开启路由日志[0:否; 1:是]',
            'route_log_message'    => '路由操作提示',
            'route_log_key_fields' => '路由关键字',
            'is_open_mocking'      => '路由响应是否mock[0:否; 1:是]',
            'is_use_custom_mock'   => '是否使用自定义mock',
            'mock_response'        => '开启mock时的响应json',
            'created_at'           => '创建时间',
            'updated_at'           => '更新时间',
        ];
    }

    const SOURCE_AUTH   = "auto";
    const SOURCE_MANUAL = "manual";

    /**
     * 接口创建来源
     *
     * @return array
     */
    public static function sources()
    {
        return [
            self::SOURCE_AUTH   => '自动记录', // auto
            self::SOURCE_MANUAL => '手动添加', // manual
        ];
    }

    const RECORD_FIELD_TYPE_AUTO  = 0;
    const RECORD_FIELD_TYPE_OPEN  = 1;
    const RECORD_FIELD_TYPE_CLOSE = 2;

    /**
     * 字段记录方式
     *
     * @return array
     */
    public static function recordFieldTypes()
    {
        return [
            self::RECORD_FIELD_TYPE_AUTO  => '随系统', // 0
            self::RECORD_FIELD_TYPE_OPEN  => '强制开启', // 1
            self::RECORD_FIELD_TYPE_CLOSE => '强制关闭', // 2
        ];
    }

    const VALIDATE_TYPE_TYPE_AUTO  = 0;
    const VALIDATE_TYPE_TYPE_OPEN  = 1;
    const VALIDATE_TYPE_TYPE_CLOSE = 2;

    /**
     * 接口校验方式
     *
     * @return array
     */
    public static function validateTypes()
    {
        return [
            self::VALIDATE_TYPE_TYPE_AUTO  => '随系统', // 0
            self::VALIDATE_TYPE_TYPE_OPEN  => '强制开启', // 1
            self::VALIDATE_TYPE_TYPE_CLOSE => '强制关闭', // 2
        ];
    }

    const STRICT_VALIDATE_TYPE_AUTO  = 0;
    const STRICT_VALIDATE_TYPE_OPEN  = 1;
    const STRICT_VALIDATE_TYPE_CLOSE = 2;

    /**
     * 严格校验方式
     *
     * @return array
     */
    public static function strictValidateTypes()
    {
        return [
            self::STRICT_VALIDATE_TYPE_AUTO  => '随系统', // 0
            self::STRICT_VALIDATE_TYPE_OPEN  => '强制开启', // 1
            self::STRICT_VALIDATE_TYPE_CLOSE => '强制关闭', // 2
        ];
    }

    /**
     * 关联 : 字段关联
     *
     * @return ActiveQuery
     */
    public function getInterfaceFields()
    {
        return $this->hasMany(RouteInterfaceFields::class, [
            'url_path' => 'url_path',
        ]);
    }

    /**
     * 关联 : 获取接口拥有字段数量
     *
     * @return int|string|null
     */
    public function getFieldCounts()
    {
        return $this->hasMany(RouteInterfaceFields::class, [
            'url_path' => 'url_path',
        ])->count();
    }

    /**
     * 删除接口的时候需要将关联的字段一并删除
     *
     * @return bool
     * @throws \Throwable
     * @throws StaleObjectException
     */
    public function beforeDelete()
    {
        foreach ($this->interfaceFields as $interfaceField) {
            $interfaceField->delete();
        }
        return parent::beforeDelete();
    }

    /**
     *  toArray 默认导出的字段
     *
     * @return array|false
     */
    public function fields()
    {
        return array_merge([
            'fieldCounts' => 'fieldCounts',
        ], parent::fields());
    }
}
