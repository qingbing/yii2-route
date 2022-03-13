<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\services;


use YiiHelper\abstracts\Service;
use YiiHelper\features\system\models\Systems;
use YiiHelper\helpers\Pager;
use YiiRoute\interfaces\IRouteLogService;
use YiiRoute\models\RouteLogs;

/**
 * 服务 ： 路由日志查询
 *
 * Class RouteLogService
 * @package YiiRoute\services
 */
class RouteLogService extends Service implements IRouteLogService
{
    /**
     * 路由访问日志列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array
    {
        // 构建查询query
        $query = RouteLogs::find()
            ->andFilterWhere(['=', 'system_code', $params['system_code']])
            ->andFilterWhere(['=', 'trace_id', $params['trace_id']])
            ->andFilterWhere(['=', 'method', $params['method']])
            ->andFilterWhere(['=', 'is_success', $params['is_success']])
            ->andFilterWhere(['=', 'ip', $params['ip']])
            ->andFilterWhere(['=', 'uid', $params['uid']])
            ->andFilterWhere(['=', 'keyword', $params['keyword']])
            ->andFilterWhere(['like', 'url_path', $params['url_path']])
            ->andFilterWhere(['like', 'message', $params['message']])
            ->andFilterWhere(['>=', 'created_at', $params['start_at']])
            ->andFilterWhere(['<=', 'created_at', $params['end_at']]);
        // 分页查询返回
        return Pager::getInstance()
            ->setAsArray(true)
            ->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 查看路由访问日志详情
     *
     * @param array $params
     * @return mixed
     */
    public function view(array $params)
    {
        // 构建查询query
        return RouteLogs::find()
            ->andWhere(['=', 'id', $params['id']])
            ->asArray()
            ->one();
    }
}