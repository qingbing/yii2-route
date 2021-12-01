<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\services;


use Exception;
use YiiHelper\abstracts\Service;
use YiiRoute\interfaces\IInterfaceFieldService;
use YiiRoute\models\RouteInterfaceFields;
use YiiRoute\models\RouteInterfaces;
use Zf\Helper\Exceptions\BusinessException;
use Zf\Helper\Exceptions\CustomException;

/**
 * 服务 ： 接口字段管理
 *
 * Class InterfaceFieldService
 * @package YiiRoute\services
 */
class InterfaceFieldService extends Service implements IInterfaceFieldService
{
    /**
     * 字段按类型和区域过滤
     *
     * @param array $fields
     * @param string $type
     * @param string $dataArea
     * @return array
     */
    protected function filterFields(array $fields, string $type, string $dataArea)
    {
        return array_values(array_filter($fields, function ($field) use ($type, $dataArea) {
            /* @var RouteInterfaceFields $field */
            return $field->type == $type && $field->data_area == $dataArea;
        }));
    }

    /**
     * 接口字段列表
     *
     * @param array|null $params
     * @return array
     * @throws Exception
     */
    public function list(array $params = []): array
    {
        $interface = RouteInterfaces::findOne([
            'url_path' => $params['url_path'],
        ]);
        if (null === $interface) {
            throw new CustomException("不存在的接口");
        }
        $options = $interface->interfaceFields;
        return [
            'input'  => [
                'headers' => $this->filterFields($options, RouteInterfaceFields::TYPE_INPUT, RouteInterfaceFields::DATA_AREA_HEADER),
                'files'   => $this->filterFields($options, RouteInterfaceFields::TYPE_INPUT, RouteInterfaceFields::DATA_AREA_FILE),
                'get'     => $this->filterFields($options, RouteInterfaceFields::TYPE_INPUT, RouteInterfaceFields::DATA_AREA_GET),
                'post'    => $this->filterFields($options, RouteInterfaceFields::TYPE_INPUT, RouteInterfaceFields::DATA_AREA_POST),
            ],
            'output' => [
                'headers'  => $this->filterFields($options, RouteInterfaceFields::TYPE_OUTPUT, RouteInterfaceFields::DATA_AREA_HEADER),
                'response' => $this->filterFields($options, RouteInterfaceFields::TYPE_OUTPUT, RouteInterfaceFields::DATA_AREA_RESPONSE),
            ],
        ];
    }

    /**
     * 添加接口字段
     *
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public function add(array $params): bool
    {
        throw new CustomException("未开放该功能");
    }

    /**
     * 编辑接口字段
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
     * 删除接口字段
     *
     * @param array $params
     * @return bool
     * @throws \Throwable
     * @throws Exception
     */
    public function del(array $params): bool
    {
        $model = $this->getModel($params);
        return $model->delete();
    }

    /**
     * 查看接口字段详情
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function view(array $params)
    {
        return $this->getModel($params);
    }

    /**
     * 获取当前操作模型
     *
     * @param array $params
     * @return RouteInterfaceFields
     * @throws BusinessException
     */
    protected function getModel(array $params): RouteInterfaceFields
    {
        $model = RouteInterfaceFields::findOne([
            'id' => $params['id'] ?? null
        ]);
        if (null === $model) {
            throw new BusinessException("接口字段不存在");
        }
        return $model;
    }
}