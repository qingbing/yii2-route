<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\services;

use Exception;
use YiiHelper\abstracts\Service;
use YiiHelper\helpers\Pager;
use YiiRoute\interfaces\IInterfaceService;
use YiiRoute\models\RouteInterfaces;
use Zf\Helper\Exceptions\BusinessException;

/**
 * 服务 ： 接口管理
 *
 * Class InterfaceService
 * @package YiiRoute\services
 */
class InterfaceService extends Service implements IInterfaceService
{
    /**
     * 接口列表
     *
     * @param array|null $params
     * @return array
     */
    public function list(array $params = []): array
    {
        $query = RouteInterfaces::find()
            ->orderBy('updated_at DESC');
        // 等于查询
        $this->attributeWhere($query, $params, [
            'id',
            'system_code',
            'source',
            'type',
            'is_operate',
            'is_open_route_log',
            'is_open_mocking',
            'is_use_custom_mock',
            'record_field_type',
            'validate_type',
            'strict_validate_type',
        ]);
        // like 查询
        $this->likeWhere($query, $params, ['name', 'url_path']);
        // 开始时间
        if (!empty($params['start_at'])) {
            $query->andWhere(['>=', 'created_at', $params['start_at']]);
        }
        // 结束时间
        if (!empty($params['end_at'])) {
            $query->andWhere(['<=', 'created_at', $params['end_at']]);
        }
        return Pager::getInstance()->pagination($query, $params['pageNo'], $params['pageSize']);
    }

    /**
     * 添加接口
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function add(array $params): bool
    {
        $model = new RouteInterfaces();
        $model->setFilterAttributes($params);
        $model->source = RouteInterfaces::SOURCE_MANUAL;
        return $model->saveOrException();
    }

    /**
     * 编辑接口
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function edit(array $params): bool
    {
        $model = $this->getModel($params);
        unset($params['id']);
        $model->setFilterAttributes($params);
        return $model->saveOrException();
    }

    /**
     * 删除接口
     *
     * @param array $params
     * @return bool
     * @throws BusinessException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function del(array $params): bool
    {
        $model = $this->getModel($params);
        return $model->delete();
    }

    /**
     * 查看接口详情
     *
     * @param array $params
     * @return mixed|RouteInterfaces
     * @throws BusinessException
     */
    public function view(array $params)
    {
        return $this->getModel($params);
    }

    /**
     * 获取当前操作模型
     *
     * @param $params
     * @return RouteInterfaces
     * @throws BusinessException
     */
    protected function getModel(array $params): RouteInterfaces
    {
        $model = RouteInterfaces::findOne([
            'id' => $params['id'] ?? null,
        ]);
        if (null === $model) {
            throw new BusinessException("接口不存在");
        }
        return $model;
    }
}