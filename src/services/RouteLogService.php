<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\services;


use yii\db\Exception;
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
            ->alias('logs')
            ->leftJoin(Systems::tableName() . ' AS system', 'system.code=logs.system_code')
            ->select(['logs.*', 'system_name' => 'system.name'])
            ->andFilterWhere(['=', 'system.code', $params['system_code']])
            ->andFilterWhere(['=', 'logs.trace_id', $params['trace_id']])
            ->andFilterWhere(['=', 'logs.method', $params['method']])
            ->andFilterWhere(['=', 'logs.is_success', $params['is_success']])
            ->andFilterWhere(['=', 'logs.ip', $params['ip']])
            ->andFilterWhere(['=', 'logs.uid', $params['uid']])
            ->andFilterWhere(['=', 'logs.keyword', $params['keyword']])
            ->andFilterWhere(['like', 'logs.url_path', $params['url_path']])
            ->andFilterWhere(['like', 'logs.message', $params['message']])
            ->andFilterWhere(['>=', 'logs.created_at', $params['start_at']])
            ->andFilterWhere(['<=', 'logs.created_at', $params['end_at']]);
        // 分页查询返回
        return Pager::getInstance()
            ->setAsArray(true)
            ->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 查看路由访问日志详情
     *
     * @param array $params
     * @return array|false|mixed
     * @throws Exception
     */
    public function view(array $params)
    {
        // 构建查询query
        $query = RouteLogs::find()
            ->alias("logs")
            ->leftJoin(Systems::tableName() . ' AS system', 'system.code=logs.system_code')
            ->select(['logs.*', 'system_name' => 'system.name'])
            ->andWhere(['=', 'logs.id', $params['id']]);
        return $query->createCommand()->queryOne();
    }
}