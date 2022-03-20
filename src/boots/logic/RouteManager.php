<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\boots\logic;


use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use YiiHelper\features\system\models\Systems;
use YiiHelper\helpers\AppHelper;
use YiiHelper\helpers\DynamicModel;
use YiiHelper\validators\JsonValidator;
use YiiRoute\models\RouteInterfaceFields;
use YiiRoute\models\RouteInterfaces;
use YiiRoute\models\RouteLogs;
use Zf\Helper\Business\DeepTree;
use Zf\Helper\Util;

/**
 * 接口参数信息管理工具
 *
 * Class RouteManager
 * @package YiiRoute\boots\logic
 */
class RouteManager
{
    /**
     * 获取系统模型 cache-key
     *
     * @param string|array $system
     * @return string
     */
    public static function cacheKeySystem($system)
    {
        if (is_array($system)) {
            return __CLASS__ . ":system:" . AppHelper::app()->id;
        } else {
            return __CLASS__ . ":system:{$system}";
        }
    }

    /**
     * 获取接口模型 cache-key
     *
     * @param string $urlPath
     * @return string
     */
    public static function cacheKeyInterface(string $urlPath)
    {
        return __CLASS__ . ":interface:{$urlPath}";
    }

    /**
     * 通过系统代码获取系统模型
     *
     * @param string|array $system
     * @return bool|Systems
     */
    public static function getSystem($system)
    {
        AppHelper::app()->cacheHelper->delete(self::cacheKeySystem($system));
        return AppHelper::app()->cacheHelper->get(self::cacheKeySystem($system), function () use ($system) {
            if (is_array($system)) {
                $systemModel = array_merge([
                    'code'                   => '-',
                    'is_enable'              => 1,
                    'is_allow_new_interface' => 1,
                    'is_record_field'        => 0,
                    'is_open_validate'       => 0,
                    'is_strict_validate'     => 0,
                ], $system);
                return json_decode(json_encode($systemModel));
            }
            $systemModel = self::getSystemModel();
            return $systemModel
                ->find()
                ->andWhere(['=', 'code', $system])
                ->one();
        }, 300);
    }

    /**
     * 通过接口 url_path 获取接口模型
     *
     * @param string $urlPath
     * @return bool|mixed
     */
    public static function getInterface(string $urlPath)
    {
        return AppHelper::app()->cacheHelper->get(self::cacheKeyInterface($urlPath), function () use ($urlPath) {
            $interface = self::getInterfaceModel()
                ->find()
                ->andWhere(['=', 'url_path', $urlPath])
                ->one();
            if (!$interface) {
                return ['info' => null, 'fields' => []];
            }
            $fields = self::getInterfaceFieldModel()
                ->find()
                ->andWhere(['=', 'url_path', $urlPath])
                ->asArray()
                ->all();
            return ['info' => $interface, 'fields' => $fields];
        }, 600);
    }

    /**
     * 保存接口及参数信息
     *
     * @param string $systemCode
     * @param string $urlPath
     * @param array|null $input
     * @param array|null $output
     * @param string $source
     * @throws \Throwable
     */
    public static function saveInterface(string $systemCode, string $urlPath, ?array $input = [], ?array $output = [], $source = RouteInterfaces::SOURCE_AUTH)
    {
        // 利用事务的形式，写入接口数据
        AppHelper::app()->getDb()->transaction(function () use ($systemCode, $urlPath, $input, $output, $source) {
            // 写入接口主体信息
            $data = [
                'system_code' => $systemCode,
                'url_path'    => $urlPath,
                'source'      => $source,
            ];

            // 接口信息写入
            $interfaceModel = self::saveInterfaceInfo($data);
            // 写入请求信息
            self::saveHeaderFields($interfaceModel, 'input', $input['header'] ?? null);
            self::saveFileFields($interfaceModel, $input['file'] ?? null);
            self::saveParamFields($interfaceModel, 'input', 'get', self::releaseParams($input['get'] ?? null)['sub']);
            self::saveParamFields($interfaceModel, 'input', 'post', self::releaseParams($input['post'] ?? null)['sub']);
            // 写入响应信息
            self::saveHeaderFields($interfaceModel, 'output', $output['header'] ?? null);
            self::saveParamFields($interfaceModel, 'output', 'response', self::releaseParams($output['response'] ?? null)['sub']);
        });
    }

    /**
     * 保存接口信息
     *
     * @param array $data
     * @return array|ActiveRecord|RouteInterfaces|void
     * @throws InvalidConfigException
     */
    protected static function saveInterfaceInfo(array $data)
    {
        $model = self::getInterfaceModel()
            ->find()
            ->andWhere(['=', 'url_path', $data['url_path']])
            ->one();
        if (null !== $model) {
            return $model;
        }
        $model = self::getInterfaceModel();
        $model->setAttributes($data);
        if ($model->save()) {
            return $model;
        }
        Yii::warning([
            'message' => '接口信息写入失败',
            'file'    => __FILE__,
            'line'    => __LINE__,
            'model'   => get_class($model),
            'data'    => $data,
        ], 'interface');
    }

    /**
     * 添加 header 接口字段信息
     *
     * @param RouteInterfaces $interface
     * @param string $type
     * @param array|null $params
     * @throws InvalidConfigException
     */
    protected static function saveHeaderFields(RouteInterfaces $interface, string $type = 'input', ?array $params = null)
    {
        if (empty($params)) {
            return;
        }
        foreach ($params as $key => $val) {
            self::saveInterfaceField([
                'url_path'     => $interface->url_path, // 接口的path
                'parent_alias' => '', // 上级字段别名
                'alias'        => "{$interface->url_path}|{$type}|header|{$key}", // 字段别名
                'field'        => $key, // 字段名
                'type'         => $type, // 字段类型[input,output]
                'data_area'    => 'header', // 字段区域[header,file,get,post]
                'data_type'    => 'string', // 数据类型[integer,double,boolean,string,object,array,items,compare,date,datetime,time,email,in,url,ip,number,default,match,file,image,safe]
            ]);
        }
    }

    /**
     * 添加 file 接口字段信息
     *
     * @param RouteInterfaces $interface
     * @param array|null $params
     * @throws InvalidConfigException
     */
    protected static function saveFileFields(RouteInterfaces $interface, ?array $params = null)
    {
        if (empty($params)) {
            return;
        }
        foreach ($params as $key => $val) {
            if (is_string($val['name'])) {
                $data_type = "string";
            } elseif (is_real_array($val['name'])) {
                $data_type = "items";
            } else {
                $data_type = "object";
            }
            self::saveInterfaceField([
                'url_path'     => $interface->url_path, // 接口的path
                'parent_alias' => '', // 上级字段别名
                'field'        => $key, // 字段名
                'alias'        => "{$interface->url_path}|input|file|{$key}", // 字段别名
                'type'         => 'input', // 字段类型[input,output]
                'data_area'    => 'file', // 字段区域[header,file,get,post]
                'data_type'    => $data_type, // 数据类型[integer,double,boolean,string,object,array,items,compare,date,datetime,time,email,in,url,ip,number,default,match,file,image,safe]
            ]);
            // file 的 object 数组
            if ("object" === $data_type) {
                self::saveParamFields($interface, 'input', 'file', self::releaseParams($val['name'])['sub'], $key);
            }
        }
    }

    /**
     * 添加 get post 接口字段信息
     *
     * @param RouteInterfaces $interface
     * @param string $type
     * @param string $dataArea
     * @param array|null $params
     * @param string $parentField
     * @throws InvalidConfigException
     */
    protected static function saveParamFields(RouteInterfaces $interface, string $type, string $dataArea, ?array $params, $parentField = '')
    {
        foreach ($params as $val) {
            $alias = $parentField ? "{$parentField}.{$val['field']}" : $val['field'];
            $model = self::saveInterfaceField([
                'url_path'     => $interface->url_path, // 接口的path
                'parent_alias' => $parentField, // 上级字段别名
                'field'        => $val['field'], // 字段名
                'alias'        => "{$interface->url_path}|{$type}|{$dataArea}|{$alias}", // 字段别名
                'type'         => $type, // 字段类型[input,output]
                'data_area'    => $dataArea, // 字段区域[header,file,get,post]
                'data_type'    => $val['type'], // 数据类型[integer,double,boolean,string,object,array,items,compare,date,datetime,time,email,in,url,ip,number,default,match,file,image,safe]
            ]);
            if (!$model->is_last_level && !empty($val['sub'])) {
                // 非最后级别并且 sub 不为空表示有子项目
                self::saveParamFields($interface, $type, $dataArea, $val['sub'], $alias);
            }
        }
    }

    /**
     * 保存接口字段信息
     *
     * @param array $data
     * @return array|object|ActiveRecord[]|RouteInterfaceFields|void
     * @throws InvalidConfigException
     */
    protected static function saveInterfaceField(array $data)
    {
        $model = self::getInterfaceFieldModel()
            ->find()
            ->andWhere(['=', 'alias', $data['alias']])
            ->one();
        if (null !== $model) {
            // 对于db中已经存在的接口字段不再记录
            return $model;
        }
        $model = self::getInterfaceFieldModel();
        $model->setAttributes($data);
        if ($model->save()) {
            return $model;
        }
        Yii::warning([
            'message' => '接口信息写入失败',
            'file'    => __FILE__,
            'line'    => __LINE__,
            'model'   => get_class($model),
            'data'    => $data,
        ], 'interface');
    }

    /**
     * 解析参数的各级数据类型
     *
     * @param mixed $data
     * @return array
     */
    public static function releaseParams($data)
    {
        $data = is_string($data) ? json_decode($data) : json_decode(json_encode($data));
        return self::_releaseParams($data);
    }

    private static function _releaseParams($data, $field = "root")
    {
        $item = [
            "field" => $field,
            "type"  => 'safe',
            'sub'   => [],
        ];
        if (is_object($data)) {
            $item['type'] = "object";
            // 子字段分析
            foreach ($data as $field => $datum) {
                $item['sub'][$field] = self::_releaseParams($datum, $field);
            }
        } elseif (is_array($data) && count($data) > 0) {
            if (is_object($data[0])) {
                $item['type'] = 'items';
                // 子数组合并field，值以最后次出现的为准
                $fields = [];
                foreach ($data as $datum) {
                    $fields = array_merge($fields, (array)$datum);
                }
                // 子字段分析
                foreach ($fields as $field => $datum) {
                    $item['sub'][$field] = self::_releaseParams($datum, $field);
                }
            }
        }
        return $item;
    }

    /**
     * 过滤配置字段
     *
     * @param array $fields
     * @param string $type
     * @param string $area
     * @return array
     */
    private static function _filterFields(array $fields, string $type, ?string $area = null)
    {
        return array_filter($fields, function ($val) use ($type, $area) {
            if (null === $area) {
                return $type == $val['type'];
            }
            return $type == $val['type'] && $area == $val['data_area'];
        });
    }

    /**
     * 验证数据字段
     *
     * @param array $fields
     * @param array $data
     * @return DynamicModel
     * @throws InvalidConfigException
     */
    private static function _validateData(array $fields, array $data)
    {
        $rules         = [];
        $requireFields = [];
        foreach ($fields as $field) {
            $name = $field['parent_alias'] ? "{$field['parent_alias']}.{$field['field']}" : "{$field['field']}";
            if ($field['is_required']) {
                array_push($requireFields, $name);
            }
            $extRules = [];
            if (!empty($field['rules'])) {
                $extRules = json_decode($field['rules'], true);
                if (!is_array($extRules)) {
                    $extRules = [];
                }
            }
            $label  = $field['name'];
            $ignore = false;
            switch ($field['data_type']) {
                case RouteInterfaceFields::DATA_TYPE_INTEGER : // integer
                case RouteInterfaceFields::DATA_TYPE_DOUBLE  : // double
                case RouteInterfaceFields::DATA_TYPE_NUMBER  : // number
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'max', 'min', 'tooBig', 'tooSmall']);
                    break;
                case RouteInterfaceFields::DATA_TYPE_BOOLEAN : // boolean
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'trueValue', 'falseValue']);
                    break;
                case RouteInterfaceFields::DATA_TYPE_STRING  : // string
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'length', 'max', 'min', 'message', 'tooShort', 'tooLong']);
                    break;
                case RouteInterfaceFields::DATA_TYPE_OBJECT  : // object
                    $ruleType = JsonValidator::class;
                    break;
                case RouteInterfaceFields::DATA_TYPE_ARRAY   : // array
                    $ruleType = JsonValidator::class;
                    break;
                case RouteInterfaceFields::DATA_TYPE_ITEMS   : // items
                    $ruleType = JsonValidator::class;
                    break;
                case RouteInterfaceFields::DATA_TYPE_COMPARE : // compare
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'compareAttribute', 'compareValue']);
                    if (!isset($extRules['compareAttribute'])) {
                        $ignore = true;
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_DATE    : // date
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'format', 'max', 'min', 'tooBig', 'tooSmall', 'maxString', 'minString']);
                    if (!isset($extRules['format'])) {
                        $extRules['format'] = "php:Y-m-d";
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_DATETIME: // datetime
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'format', 'max', 'min', 'tooBig', 'tooSmall', 'maxString', 'minString']);
                    if (!isset($extRules['format'])) {
                        $extRules['format'] = "php:Y-m-d H:i:s";
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_TIME    : // time
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'format', 'max', 'min', 'tooBig', 'tooSmall', 'maxString', 'minString']);
                    if (!isset($extRules['format'])) {
                        $extRules['format'] = "php:H:i:s";
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_IN      : // in
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'range', 'strict', 'not']);
                    if (!isset($extRules['range'])) {
                        $ignore = true;
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_DEFAULT : // default
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'value']);
                    if (!isset($extRules['value'])) {
                        $ignore = true;
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_MATCH   : // match
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message', 'pattern']);
                    if (!isset($extRules['pattern'])) {
                        $ignore = true;
                    }
                    break;
                case RouteInterfaceFields::DATA_TYPE_EMAIL   : // email
                case RouteInterfaceFields::DATA_TYPE_URL     : // url
                case RouteInterfaceFields::DATA_TYPE_IP      : // ip
                    $ruleType = $field['data_type'];
                    $extRules = Util::filterArrayByKeys($extRules, ['message']);
                    break;
                case RouteInterfaceFields::DATA_TYPE_SAFE    : // safe
                default:
                    $ruleType = 'safe';
                    $extRules = Util::filterArrayByKeys($extRules, ['message']);
                    break;
            }
            if ($ignore) {
                continue;
            }
            $rule = [$name, $ruleType];
            if (!empty($label)) {
                $rule['label'] = $label;
            }
            $rule['default'] = $field['default'];
            array_push($rules, array_merge($rule, $extRules));
        }
        if (count($requireFields) > 0) {
            array_unshift($rules, [$requireFields, 'required']);
        }
        return DynamicModel::validateData($data, $rules);
    }

    /**
     * 验证参数是否正确，并返回验证后信息
     *
     * @param string $urlPath
     * @param array|null $data
     * @param string $type
     * @return array
     * @throws InvalidConfigException
     */
    public static function validateData(string $urlPath, ?array $data = null, string $type = RouteInterfaceFields::TYPE_INPUT)
    {
        $interfaceFields = self::getInterface($urlPath)['fields'];
        $isValidSuccess  = true;
        $errorMsg        = [];
        $errors          = [
            'header' => [],
            'get'    => [],
            'post'   => [],
        ];
        $validData       = [
            'header' => [],
            'get'    => [],
            'post'   => [],
        ];
        $headerFields    = self::_filterFields($interfaceFields, $type, RouteInterfaceFields::DATA_AREA_HEADER);
        if (!empty($headerFields)) {
            $headerValid = self::_validateData($headerFields, $data['headers'] ?? []);
            if ($headerValid->hasErrors()) {
                $isValidSuccess   = false;
                $errors['header'] = $headerValid->getErrorSummary(true);
                $errorMsg         = array_merge($errorMsg, $headerValid->getErrorSummary(false));
            } else {
                $validData['header'] = $headerValid->values;
            }
        }
        $postFields = self::_filterFields($interfaceFields, $type, RouteInterfaceFields::DATA_AREA_POST);
        if (!empty($postFields)) {
            $postValid = self::_validateData($postFields, $data['post'] ?? []);
            if ($postValid->hasErrors()) {
                $isValidSuccess = false;
                $errors['post'] = $postValid->getErrorSummary(true);
                $errorMsg       = array_merge($errorMsg, $postValid->getErrorSummary(false));
            } else {
                $validData['post'] = $postValid->values;
            }
        }
        $getFields = self::_filterFields($interfaceFields, $type, RouteInterfaceFields::DATA_AREA_GET);
        if (!empty($getFields)) {
            $getValid = self::_validateData($getFields, $data['get'] ?? []);
            if ($getValid->hasErrors()) {
                $isValidSuccess = false;
                $errors['get']  = $getValid->getErrorSummary(true);
                $errorMsg       = array_merge($errorMsg, $getValid->getErrorSummary(false));
            } else {
                $validData['get'] = $getValid->values;
            }
        }
        if ($isValidSuccess) {
            return [
                "isValidSuccess" => $isValidSuccess,
                "validData"      => $validData,
            ];
        } else {
            return [
                "isValidSuccess" => $isValidSuccess,
                "errors"         => $errors,
                "errorMsg"       => array_shift($errorMsg),
            ];
        }
    }

    /**
     * 获取mock数据
     *
     * @param string $urlPath
     * @param string $type
     * @return array
     */
    public static function getMockData(string $urlPath, string $type = RouteInterfaceFields::TYPE_OUTPUT)
    {
        $interfaceFields = self::getInterface($urlPath)['fields'];

        $tree = DeepTree::getInstance()
            ->setFilter(function ($val) use ($type) {
                return $type == $val['type'];
            })
            ->setSourceData($interfaceFields)
            ->setId("field")
            ->setPid("parent_alias")
            ->setTopTag("")
            ->getTreeData();

        return self::fillParams($tree);
    }

    /**
     * 装填数据样例
     *
     * @param array $fieldTree
     * @return array
     */
    protected static function fillParams(array $fieldTree)
    {
        $R = [];
        foreach ($fieldTree as $data) {
            $datum = $data['attr'];
            switch ($datum['data_type']) {
                // 基本数据类型
                case RouteInterfaceFields::DATA_TYPE_INTEGER: // 'integer';
                case RouteInterfaceFields::DATA_TYPE_DOUBLE: // 'double';
                case RouteInterfaceFields::DATA_TYPE_BOOLEAN: // 'boolean';
                case RouteInterfaceFields::DATA_TYPE_STRING: // 'string';
                    $R[$datum['field']] = $datum['data_type'];
                    break;
                case RouteInterfaceFields::DATA_TYPE_OBJECT: // 'object';
                    $R[$datum['field']] = self::fillParams($data['data']);
                    break;
                case RouteInterfaceFields::DATA_TYPE_ARRAY: // 'array';
                    $R[$datum['field']] = ["options_01", "options_02", "..."];
                    break;
                case RouteInterfaceFields::DATA_TYPE_ITEMS: // 'items';
                    $item               = self::fillParams($data['data']);
                    $rest               = array_combine(array_keys($item), array_fill(0, count($item), '...'));
                    $R[$datum['field']] = [$item, $rest];
                    break;
                // 扩展数据类型
                case RouteInterfaceFields::DATA_TYPE_DATE: // date
                    $R[$datum['field']] = "2000-01-01";
                    break;
                case RouteInterfaceFields::DATA_TYPE_DATETIME: // datetime
                    $R[$datum['field']] = "2000-01-01 01:01:01";
                    break;
                case RouteInterfaceFields::DATA_TYPE_TIME: // time
                    $R[$datum['field']] = "01:01:01";
                    break;
                case RouteInterfaceFields::DATA_TYPE_EMAIL: // email
                    $R[$datum['field']] = "xx@xx.xx";
                    break;
                case RouteInterfaceFields::DATA_TYPE_IN: // in
                    $R[$datum['field']] = "in";
                    break;
                case RouteInterfaceFields::DATA_TYPE_URL: // url
                    $R[$datum['field']] = "http://www.example.com";
                    break;
                case RouteInterfaceFields::DATA_TYPE_IP: // ip
                    $R[$datum['field']] = "127.0.0.1";
                    break;
                case RouteInterfaceFields::DATA_TYPE_NUMBER: // number
                    $R[$datum['field']] = "11.11";
                    break;
                case RouteInterfaceFields::DATA_TYPE_COMPARE: // compare
                case RouteInterfaceFields::DATA_TYPE_DEFAULT: // default
                case RouteInterfaceFields::DATA_TYPE_MATCH: // match
                case RouteInterfaceFields::DATA_TYPE_SAFE: // safe
                    $R[$datum['field']] = $datum['data_type'];
                    break;
            }
        }
        return $R;
    }

    /**
     * 实例化系统模型
     *
     * @return Systems
     * @throws InvalidConfigException
     */
    protected static function getSystemModel()
    {
        return Yii::createObject(Systems::class);
    }

    /**
     * 实例化接口模型
     *
     * @return RouteInterfaces
     * @throws InvalidConfigException
     */
    protected static function getInterfaceModel()
    {
        return Yii::createObject(RouteInterfaces::class);
    }

    /**
     * 实例化接口字段模型
     *
     * @return RouteInterfaceFields
     * @throws InvalidConfigException
     */
    protected static function getInterfaceFieldModel()
    {
        return Yii::createObject(RouteInterfaceFields::class);
    }

    /**
     * 实例化路由日志模型
     *
     * @return RouteLogs
     * @throws InvalidConfigException
     */
    public static function getRouteLogModel()
    {
        return Yii::createObject(RouteLogs::class);
    }
}