<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\controllers;


use Exception;
use YiiHelper\abstracts\RestController;
use YiiRoute\interfaces\IRouteLogService;
use YiiRoute\models\RouteLogs;
use YiiRoute\services\RouteLogService;

/**
 * 控制器 ： 路由日志查询
 *
 * Class RouteLogController
 * @package YiiRoute\controllers
 *
 * @property-read IRouteLogService $service
 */
class RouteLogController extends RestController
{
    public $serviceInterface = IRouteLogService::class;
    public $serviceClass     = RouteLogService::class;

    /**
     * 路由访问日志列表
     *
     * @return array
     * @throws Exception
     */
    public function actionList()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['system_code', 'string', 'label' => '系统别名'],
            ['trace_id', 'string', 'label' => 'Trace ID'],
            ['url_path', 'string', 'label' => '接口路径'],
            ['method', 'in', 'label' => '请求方法', 'range' => array_keys(RouteLogs::methods())],
            ['is_success', 'boolean', 'label' => '是否成功'],
            ['message', 'string', 'label' => '消息关键字'],
            ['keyword', 'string', 'label' => '关键字'],
            ['ip', 'string', 'label' => '操作IP'],
            ['uid', 'string', 'label' => 'UID'],
            ['start_at', 'datetime', 'label' => '开始时间', 'format' => 'php:Y-m-d H:i:s'],
            ['end_at', 'datetime', 'label' => '结束时间', 'format' => 'php:Y-m-d H:i:s'],
        ], null, true);

        // 业务处理
        $res = $this->service->list($params);
        // 渲染结果
        return $this->success($res, '路由访问列表');
    }

    /**
     * 查看路由访问日志详情
     *
     * @return array
     * @throws Exception
     */
    public function actionView()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            ['id', 'required'],
            [
                'id', 'exist', 'label' => '路由日志', 'targetClass' => RouteLogs::class, 'targetAttribute' => 'id'
            ],
        ]);
        // 业务处理
        $res = $this->service->view($params);
        // 渲染结果
        return $this->success($res, '路由日志详情');
    }
}