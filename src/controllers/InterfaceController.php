<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\controllers;


use Exception;
use YiiHelper\abstracts\RestController;
use YiiHelper\features\system\models\Systems;
use YiiHelper\validators\JsonValidator;
use YiiRoute\interfaces\IInterfaceService;
use YiiRoute\models\RouteInterfaces;
use YiiRoute\services\InterfaceService;
use Zf\Helper\Traits\Models\TLabelYesNo;

/**
 * 控制器 ： 接口管理
 *
 * Class InterfaceController
 * @package YiiRoute\controllers
 *
 * @property-read IInterfaceService $service
 */
class InterfaceController extends RestController
{
    public $serviceInterface = IInterfaceService::class;
    public $serviceClass     = InterfaceService::class;

    /**
     * 接口列表
     *
     * @return array
     * @throws Exception
     */
    public function actionList()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'string', 'label' => '接口ID'],
            ['system_code', 'exist', 'label' => '系统代码', 'targetClass' => Systems::class, 'targetAttribute' => 'code'],
            ['url_path', 'string', 'label' => '接口path'],
            ['name', 'string', 'label' => '接口名称'],

            ['source', 'in', 'label' => '接口来源', 'range' => array_keys(RouteInterfaces::sources())],

            ['is_operate', 'in', 'label' => '是否操作类', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['is_open_route_log', 'in', 'label' => '开启路由日志', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['is_open_mocking', 'in', 'label' => '开启mock', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['is_use_custom_mock', 'in', 'label' => '自定义mock', 'range' => array_keys(TLabelYesNo::isLabels())],

            ['record_field_type', 'in', 'label' => '字段记录方式', 'range' => array_keys(RouteInterfaces::recordFieldTypes())],
            ['validate_type', 'in', 'label' => '接口校验方式', 'range' => array_keys(RouteInterfaces::validateTypes())],
            ['strict_validate_type', 'in', 'label' => '开启严格校验', 'range' => array_keys(RouteInterfaces::strictValidateTypes())],

            ['start_at', 'datetime', 'label' => '访问开始时间', 'format' => 'php:Y-m-d H:i:s'],
            ['end_at', 'datetime', 'label' => '访问结束时间', 'format' => 'php:Y-m-d H:i:s'],
        ], null, true);
        // 业务处理
        $res = $this->service->list($params);
        // 渲染结果
        return $this->success($res, '接口列表');
    }

    /**
     * 添加接口
     *
     * @return array
     * @throws Exception
     */
    public function actionAdd()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['name', 'system_code', 'url_path'], 'required'],
            ['name', 'string', 'label' => '接口名称'],
            ['system_code', 'exist', 'label' => '系统代码', 'targetClass' => Systems::class, 'targetAttribute' => 'code'],
            ['url_path', 'unique', 'label' => '接口path', 'targetClass' => RouteInterfaces::class, 'targetAttribute' => 'url_path'],
            ['is_operate', 'in', 'label' => '是否操作类', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['description', 'string', 'label' => '接口描述'],

            ['record_field_type', 'in', 'label' => '字段记录方式', 'range' => array_keys(RouteInterfaces::recordFieldTypes())],
            ['validate_type', 'in', 'label' => '接口校验方式', 'range' => array_keys(RouteInterfaces::validateTypes())],
            ['strict_validate_type', 'in', 'label' => '开启严格校验', 'range' => array_keys(RouteInterfaces::strictValidateTypes())],

            ['is_open_route_log', 'in', 'label' => '开启路由日志', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['route_log_message', 'string', 'label' => '路由操作提示'],
            ['route_log_key_fields', 'string', 'label' => '路由关键字'],
            ['is_open_mocking', 'in', 'label' => '开启mock', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['is_use_custom_mock', 'in', 'label' => '自定义mock', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['mock_response', JsonValidator::class, 'label' => '自定义mock响应'],
        ]);

        // 业务处理
        $res = $this->service->add($params);
        // 渲染结果
        return $this->success($res, '添加接口成功');
    }

    /**
     * 编辑接口
     *
     * @return array
     * @throws Exception
     */
    public function actionEdit()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id', 'name'], 'required'],
            ['id', 'exist', 'label' => '接口ID', 'targetClass' => RouteInterfaces::class, 'targetAttribute' => 'id'],

            ['name', 'string', 'label' => '接口名称'],
            ['is_operate', 'in', 'label' => '是否操作类', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['description', 'string', 'label' => '接口描述'],

            ['record_field_type', 'in', 'label' => '字段记录方式', 'range' => array_keys(RouteInterfaces::recordFieldTypes())],
            ['validate_type', 'in', 'label' => '接口校验方式', 'range' => array_keys(RouteInterfaces::validateTypes())],
            ['strict_validate_type', 'in', 'label' => '开启严格校验', 'range' => array_keys(RouteInterfaces::strictValidateTypes())],

            ['is_open_route_log', 'in', 'label' => '开启路由日志', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['route_log_message', 'string', 'label' => '路由操作提示'],
            ['route_log_key_fields', 'string', 'label' => '路由关键字'],
            ['is_open_mocking', 'in', 'label' => '开启mock', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['is_use_custom_mock', 'in', 'label' => '自定义mock', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['mock_response', JsonValidator::class, 'label' => '自定义mock响应'],
        ]);

        // 业务处理
        $res = $this->service->edit($params);
        // 渲染结果
        return $this->success($res, '编辑接口成功');
    }

    /**
     * 删除接口
     *
     * @return array
     * @throws Exception
     */
    public function actionDel()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '接口ID', 'targetClass' => RouteInterfaces::class, 'targetAttribute' => 'id'],
        ]);
        // 业务处理
        $res = $this->service->del($params);
        // 渲染结果
        return $this->success($res, '删除接口成功');
    }

    /**
     * 查看接口详情
     *
     * @return array
     * @throws Exception
     */
    public function actionView()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '接口ID', 'targetClass' => RouteInterfaces::class, 'targetAttribute' => 'id'],
        ]);
        // 业务处理
        $res = $this->service->view($params);
        // 渲染结果
        return $this->success($res, '接口详情');
    }
}