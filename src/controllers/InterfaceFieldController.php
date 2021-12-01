<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\controllers;


use Exception;
use YiiHelper\abstracts\RestController;
use YiiHelper\validators\JsonValidator;
use YiiRoute\interfaces\IInterfaceFieldService;
use YiiRoute\models\RouteInterfaceFields;
use YiiRoute\models\RouteInterfaces;
use YiiRoute\services\InterfaceFieldService;
use Zf\Helper\Exceptions\CustomException;
use Zf\Helper\Traits\Models\TLabelYesNo;

/**
 * 控制器 ： 接口字段管理
 *
 * Class InterfaceFieldController
 * @package YiiRoute\controllers
 *
 * @property-read IInterfaceFieldService $service
 */
class InterfaceFieldController extends RestController
{
    public $serviceInterface = IInterfaceFieldService::class;
    public $serviceClass     = InterfaceFieldService::class;

    /**
     * 接口字段列表
     *
     * @return array
     * @throws Exception
     */
    public function actionList()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['url_path'], 'required'],
            ['url_path', 'exist', 'label' => '接口路径', 'targetClass' => RouteInterfaces::class, 'targetAttribute' => 'url_path'],
        ]);
        // 业务处理
        $res = $this->service->list($params);
        // 渲染结果
        return $this->success($res, '接口字段列表');
    }

    /**
     * 添加接口字段
     *
     * @throws Exception
     */
    public function actionAdd()
    {
        throw new CustomException("未开放该功能");
    }

    /**
     * 编辑接口字段
     *
     * @return array
     * @throws Exception
     */
    public function actionEdit()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '字段ID', 'targetClass' => RouteInterfaceFields::class, 'targetAttribute' => 'id'],
            ['name', 'string', 'label' => '字段意义'],
            ['default', 'string', 'label' => '默认值'],
            ['data_type', 'in', 'label' => '数据类型', 'range' => array_keys(RouteInterfaceFields::dataTypes())],
            ['description', 'string', 'label' => '字段描述'],
            ['is_required', 'in', 'label' => '是否必填', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['is_last_level', 'in', 'label' => '最后级别', 'range' => array_keys(TLabelYesNo::isLabels())],
            ['rules', JsonValidator::class, 'label' => '验证规则'],
        ]);
        // 业务处理
        $res = $this->service->edit($params);
        // 渲染结果
        return $this->success($res, '编辑接口字段');
    }

    /**
     * 删除接口字段
     *
     * @return array
     * @throws Exception
     */
    public function actionDel()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '接口字段', 'targetClass' => RouteInterfaceFields::class, 'targetAttribute' => 'id'],
        ]);
        // 业务处理
        $res = $this->service->del($params);
        // 渲染结果
        return $this->success($res, '删除接口字段');
    }

    /**
     * 查看接口字段详情
     *
     * @return array
     * @throws Exception
     */
    public function actionView()
    {
        // 参数验证和获取
        $params = $this->validateParams([
            [['id'], 'required'],
            ['id', 'exist', 'label' => '接口字段', 'targetClass' => RouteInterfaceFields::class, 'targetAttribute' => 'id'],
        ]);
        // 业务处理
        $res = $this->service->view($params);
        // 渲染结果
        return $this->success($res, '查看接口字段详情');
    }
}