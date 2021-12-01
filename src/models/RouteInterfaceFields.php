<?php

namespace YiiRoute\models;

use YiiHelper\abstracts\Model;

/**
 * This is the model class for table "{{%route_interface_fields}}".
 *
 * @property int $id 自增ID
 * @property string $url_path 接口的path
 * @property string $parent_alias 上级字段别名
 * @property string $alias 字段别名
 * @property string $field 字段名
 * @property string $name 字段意义
 * @property string|null $default 默认值
 * @property string $type 字段类型[input,output]
 * @property string $data_area 字段区域[header,file,get,post]
 * @property string $data_type 数据类型[integer,double,boolean,string,object,array,items,compare,date,datetime,time,email,in,url,ip,number,default,match,safe,file,image,safe]
 * @property string $description 描述
 * @property int $is_required 是否必填[0:否; 1:是]
 * @property int $is_last_level 最后级别，子字段不记录
 * @property string|null $rules 额外验证规则
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class RouteInterfaceFields extends Model
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%route_interface_fields}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alias', 'field'], 'required'],
            [['is_required', 'is_last_level'], 'integer'],
            [['rules', 'created_at', 'updated_at'], 'safe'],
            [['url_path'], 'string', 'max' => 200],
            [['parent_alias', 'alias', 'description'], 'string', 'max' => 255],
            [['field', 'data_type'], 'string', 'max' => 50],
            [['name', 'default'], 'string', 'max' => 100],
            [['type', 'data_area'], 'string', 'max' => 20],
            [['url_path', 'parent_alias', 'field'], 'unique', 'targetAttribute' => ['url_path', 'parent_alias', 'field']],
            [['alias'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => '自增ID',
            'url_path'      => '接口的path',
            'parent_alias'  => '上级字段别名',
            'alias'         => '字段别名',
            'field'         => '字段名',
            'name'          => '字段意义',
            'default'       => '默认值',
            'type'          => '字段类型[input,output]',
            'data_area'     => '字段区域[header,file,get,post]',
            'data_type'     => '数据类型[integer,double,boolean,string,object,array,items,compare,date,datetime,time,email,in,url,ip,number,default,match,safe,file,image,safe]',
            'description'   => '描述',
            'is_required'   => '是否必填[0:否; 1:是]',
            'is_last_level' => '最后级别，子字段不记录',
            'rules'         => '额外验证规则',
            'created_at'    => '创建时间',
            'updated_at'    => '更新时间',
        ];
    }

    const TYPE_INPUT  = 'input';
    const TYPE_OUTPUT = 'output';

    /**
     * 字段输入类型
     *
     * @return array
     */
    public static function types()
    {
        return [
            self::TYPE_INPUT  => '输入', // input
            self::TYPE_OUTPUT => '输出', // output
        ];
    }

    const DATA_AREA_HEADER   = 'header';
    const DATA_AREA_FILE     = 'file';
    const DATA_AREA_GET      = 'get';
    const DATA_AREA_POST     = 'post';
    const DATA_AREA_RESPONSE = 'response';

    /**
     * 字段来源
     * @return array
     */
    public static function dataAreas()
    {
        return [
            self::DATA_AREA_HEADER   => 'header', // header
            self::DATA_AREA_FILE     => 'file', // file
            self::DATA_AREA_GET      => 'get', // get
            self::DATA_AREA_POST     => 'post', // post
            self::DATA_AREA_RESPONSE => 'response', // response
        ];
    }

    // 基本数据类型
    const DATA_TYPE_INTEGER = 'integer'; // integer
    const DATA_TYPE_DOUBLE  = 'double'; // double
    const DATA_TYPE_BOOLEAN = 'boolean'; // boolean
    const DATA_TYPE_STRING  = 'string'; // string
    const DATA_TYPE_OBJECT  = 'object';
    const DATA_TYPE_ARRAY   = 'array'; // 可扩展为each
    const DATA_TYPE_ITEMS   = 'items';
    // 扩展数据类型
    const DATA_TYPE_COMPARE  = 'compare';
    const DATA_TYPE_DATE     = 'date';
    const DATA_TYPE_DATETIME = 'datetime';
    const DATA_TYPE_TIME     = 'time';
    const DATA_TYPE_EMAIL    = 'email';
    const DATA_TYPE_IN       = 'in';
    const DATA_TYPE_URL      = 'url';
    const DATA_TYPE_IP       = 'ip';
    const DATA_TYPE_NUMBER   = 'number';
    const DATA_TYPE_DEFAULT  = 'default';
    const DATA_TYPE_MATCH    = 'match';
    const DATA_TYPE_SAFE     = 'safe';
    // 上传数据类型
    // const DATA_TYPE_FILE  = 'file';
    // const DATA_TYPE_IMAGE = 'image';

    /**
     * 字段数据类型
     *
     * @return array
     */
    public static function dataTypes()
    {
        return [
            self::DATA_TYPE_INTEGER  => '整数', // integer
            self::DATA_TYPE_DOUBLE   => '浮点数', // double
            self::DATA_TYPE_BOOLEAN  => 'boolean值', // boolean
            self::DATA_TYPE_STRING   => '字符串', // string
            self::DATA_TYPE_OBJECT   => 'JSON对象', // object
            self::DATA_TYPE_ARRAY    => '数组', // array
            self::DATA_TYPE_ITEMS    => '列表', // items
            self::DATA_TYPE_COMPARE  => '对比', // compare
            self::DATA_TYPE_DATE     => '日期', // date
            self::DATA_TYPE_DATETIME => '日期时间', // datetime
            self::DATA_TYPE_TIME     => '时间', // time
            self::DATA_TYPE_EMAIL    => '邮箱', // email
            self::DATA_TYPE_IN       => '限定范围', // in
            self::DATA_TYPE_URL      => 'URL', // url
            self::DATA_TYPE_IP       => 'IP地址', // ip
            self::DATA_TYPE_NUMBER   => '数字', // number
            self::DATA_TYPE_DEFAULT  => '默认', // default
            self::DATA_TYPE_MATCH    => '匹配', // match
            self::DATA_TYPE_SAFE     => '安全', // safe
            // self::DATA_TYPE_FILE     => '文件', // file
            // self::DATA_TYPE_IMAGE    => '图片', // image
        ];
    }
}
