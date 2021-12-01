<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\bootstraps\logic;


use yii\base\BaseObject;
use yii\base\Event;
use yii\web\Response;

/**
 * 抽象类 ： 路由日志自定义
 *
 * Class RouteLogBase
 * @package YiiRoute\bootstraps\logic
 */
abstract class BaseRouteLog extends BaseObject
{
    /**
     * @var RouteManager 路由日志组件
     */
    protected $routeManager;

    /**
     * 路由日志构造函数
     *
     * @param RouteManager $routeManager
     * @param array $config
     */
    public function __construct(RouteManager $routeManager, $config = [])
    {
        parent::__construct($config);
        $this->routeManager = $routeManager;
    }

    /**
     * yii.web.EVENT_BEFORE_REQUEST 时执行
     *
     * @param Event $event
     */
    abstract public function beforeRequest(Event $event);

    /**
     * yii.web.response.EVENT_AFTER_SEND 时执行
     *
     * @param Response $response
     * @return array
     */
    abstract public function afterResponse(Response $response): array;
}