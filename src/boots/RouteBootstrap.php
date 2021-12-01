<?php
/**
 * @link        http://www.phpcorner.net
 * @author      qingbing<780042175@qq.com>
 * @copyright   Chengdu Qb Technology Co., Ltd.
 */

namespace YiiRoute\boots;


use Exception;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\web\HeaderCollection;
use yii\web\Response;
use YiiHelper\features\system\models\Systems;
use YiiHelper\helpers\AppHelper;
use YiiHelper\helpers\Req;
use YiiHelper\traits\TResponse;
use YiiRoute\boots\logic\BaseRouteLog;
use YiiRoute\boots\logic\RouteManager;
use YiiRoute\models\RouteInterfaces;
use Zf\Helper\DataStore;
use Zf\Helper\Exceptions\CustomException;
use Zf\Helper\ReqHelper;
use Zf\Helper\Timer;

/**
 * bootstrap组件 : 路由及路由日志记录入库
 *
 * Class Bootstrap
 * @package YiiRoute\boots
 */
class RouteBootstrap implements BootstrapInterface
{
    use TResponse;
    const TIMER_KEY_BEFORE_REQUEST = __CLASS__ . ':beforeRequest';
    /**
     * @var int 响应无效
     */
    public $errorCodeResponseInvalid = -999999;
    /**
     * @var int 参数无效:验证不通过
     */
    public $errorCodeParamsInvalid = -999998;
    /**
     * @var array 路由日志类集合
     */
    public $routeLogClasses = [];
    /**
     * @var bool 开启路由记录
     */
    public $openRoute = false;
    /**
     * @var bool 开启mock
     */
    public $openMock = false;
    /**
     * @var bool 记录新接口信息
     */
    public $acceptNewInterface = false;
    /**
     * @var bool 不存在的系统是否抛出异常终止
     */
    public $throwIfSystemNotExist = true;
    /**
     * @var string mock的消息标识
     */
    public $mockMsg = "__ROUTE__MOCK__";
    /**
     * @var array 不被记入参数的 header 头
     */
    public $ignoreHeaders = [
        'x-forwarded-for',
        'x-trace-id',
        'x-system',
    ];
    /**
     * @var yii\web\Request
     */
    protected $request;
    /**
     * @var Systems 系统模型
     */
    public $system;
    /**
     * @var RouteInterfaces|null 接口模型
     */
    public $interface;
    /**
     * @var BaseRouteLog
     */
    protected $routeLogInstance;
    // 请求路由
    protected $realPathInfo;
    // 接口返回是否mock
    protected $isMock = false;
    // 接口是否严格验证
    protected $strictValidate = false;

    /**
     * 获取参数保存的 dataStore 的key
     * @return string
     */
    protected function getStoreKey()
    {
        return __CLASS__ . ":store";
    }

    /**
     * 获取以"x-"透传的header参数
     * @param HeaderCollection|null $headers
     * @return array
     */
    public function getCustomHeaders(?HeaderCollection $headers = null)
    {
        $res     = [];
        $headers = $headers ?? $this->request->getHeaders();
        foreach ($headers as $key => $val) {
            if (in_array($key, $this->ignoreHeaders)) {
                continue;
            }
            if (0 !== strpos($key, 'x-')) {
                continue;
            }
            $res[$key] = $val;
        }
        return $res;
    }

    /**
     * 记录接口预定义
     */
    protected function initAcceptNewInterface()
    {
        if (!$this->acceptNewInterface) {
            // 组件拒绝记录新接口
            return;
        }
        if (!$this->system->is_record_field) {
            // 系统不接受新接口记录
            return;
        }
        if (!$this->getResultType('is_record_field', 'record_field_type')) {
            // 接口不允许记录接口文档
            return;
        }
        Yii::$app->getResponse()->on(Response::EVENT_AFTER_SEND, [$this, "handleRecordInterface"]);
    }

    /**
     * 路由日志预处理
     *
     * @throws CustomException
     * @throws InvalidConfigException
     */
    protected function initOpenRoute()
    {
        if (!$this->openRoute) {
            // 组件拒绝路由记录
            return;
        }
        if (empty($this->interface)) {
            // 接口尚未注册，不记录
            return;
        }
        if ($this->interface->is_open_route_log) {
            Yii::$app->getResponse()->on(Response::EVENT_AFTER_SEND, [$this, "handleRouteLog"]);
            // 检查是否有指定路由操作类
            $routeLogClass = $this->routeLogClasses[$this->realPathInfo] ?? null;
            if (null !== $routeLogClass) {
                // 对于自定义日志路由，在请求前执行一次
                $this->routeLogInstance = Yii::createObject($routeLogClass, [$this]);
                if (!$this->routeLogInstance instanceof BaseRouteLog) {
                    throw new CustomException(replace('日志路由"{routeClass}"必须继承路由抽象类"{baseRouteLog}"', [
                        '{routeClass}'   => $routeLogClass,
                        '{baseRouteLog}' => '\YiiRoute\boots\logic\BaseRouteLog',
                    ]));
                }
                Yii::$app->on(\yii\web\Application::EVENT_BEFORE_REQUEST, [$this->routeLogInstance, 'beforeRequest']);
            }
        }
    }

    /**
     * init 后执行，这里主要进行必要的验证的数据参数覆盖
     */
    protected function afterBootstrap()
    {
        if ($this->getResultType('is_strict_validate', 'strict_validate_type')) {
            // 开启强制校验
            $this->strictValidate = true;
        }
        if ($this->getResultType('is_open_validate', 'validate_type')) {
            // 开启校验
            Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, "handleValidate"]);
        }
        if ($this->interface->is_open_mocking) {
            // 接口mock
            $this->isMock = true;
            Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, "handleMockData"]);
        }
    }

    /**
     * 获取接口最终是否执行某个处理的结果
     *
     * @param string $systemField
     * @param string $interfaceField
     * @return bool
     */
    protected function getResultType(string $systemField, string $interfaceField)
    {
        $resultType = false;
        if (empty($this->interface)) {
            // 接口未定义，使用系统的处理方式
            if ($this->system->{$systemField}) {
                $resultType = true;
            }
        } else if (1 == $this->interface->{$interfaceField}) {
            // 接口中字段为 1 表示强制开启
            $resultType = true;
        } else if ($this->system->{$systemField} && 0 == $this->interface->{$interfaceField}) {
            // 系统配置为开启，接口为0 随系统时，表示开启处理
            $resultType = true;
        }
        return $resultType;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     * @throws CustomException
     * @throws Exception
     */
    public function bootstrap($app)
    {
        $this->request = $app->getRequest();
        // 接口记录只在 web 应用上有效
        if ($this->request->getIsConsoleRequest()) {
            return;
        }
        // 请求开始时间
        Timer::begin(self::TIMER_KEY_BEFORE_REQUEST);
        // 获取访问系统信息
        $this->system = RouteManager::getSystem(AppHelper::app()->getSystemAlias());
        if (empty($this->system) || !$this->system->is_enable) {
            if ($this->throwIfSystemNotExist) {
                throw new CustomException(replace('访问不存在的系统{system}', [
                    '{system}' => AppHelper::app()->getSystemAlias(),
                ]));
            } else {
                return;
            }
        }
        // 构建访问 url_path
        $this->realPathInfo = $this->system->code . '/' . $this->request->getPathInfo();
        // 获取
        $this->interface = RouteManager::getInterface($this->realPathInfo)['info'];
        if (empty($this->interface) && !$this->system->is_allow_new_interface) {
            // 对于未注册接口，系统不允许访问新接口
            throw new CustomException(replace('访问不存在的接口{interface}', [
                '{interface}' => $this->realPathInfo,
            ]));
        }
        // 参数记录
        DataStore::set($this->getStoreKey(), [
            'header' => $this->getCustomHeaders(),
            'get'    => $this->request->get(),
            'post'   => $this->request->post(),
            'file'   => $_FILES,
        ]);
        // 接口记录
        $this->initAcceptNewInterface();
        // 路由记录
        $this->initOpenRoute();
        // 访问检查
        $this->afterBootstrap();
    }

    /**
     * 请求前参数验证
     *
     * @param Event $event
     * @throws CustomException
     * @throws InvalidConfigException
     */
    public function handleValidate(Event $event)
    {
        $validRes = RouteManager::validateData($this->realPathInfo, DataStore::get($this->getStoreKey()));
        if (!$validRes['isValidSuccess']) {
            // 验证失败，直接抛出异常
            throw new CustomException($validRes['errorMsg'], $this->errorCodeParamsInvalid);
        }
        if ($this->strictValidate) {
            // 如果为严格检查，将只会接受字段中指定的值
            $this->request->setQueryParams($validRes['validData']['get']);
            $this->request->setBodyParams($validRes['validData']['post']);
        }
    }

    /**
     * 接口定位为mock时返回mock数据
     *
     * @param Event $event
     * @throws Exception
     */
    public function handleMockData(Event $event)
    {
        // mock 数据构建
        if ($this->interface->is_use_custom_mock) {
            $data = json_decode($this->interface->mock_response);
        } else {
            $data = RouteManager::getMockData($this->realPathInfo);
        }
        $response         = AppHelper::app()->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data   = $this->success($data, $this->mockMsg);
        $response->send();
        Yii::$app->end();
    }

    /**
     * 获取访问日志数据
     *
     * @param Response $response
     * @return array
     */
    protected function getLogData(Response $response)
    {
        static $inData;
        if (null === $inData) {
            $inData = [
                // 'id'          => '', // 自增ID
                'system_code' => $this->system->code, // 系统别名
                'url_path'    => $this->realPathInfo, // 接口的path
                'trace_id'    => ReqHelper::getTraceId(), // 客户端日志ID
                'method'      => $this->request->getMethod(), // 请求方法[get post put...]
                'is_success'  => '', // 是否成功[0:失败; 1:成功]
                // 'keyword'     => '', // 关键字，用于后期筛选
                'message'     => '', // 操作消息
                // 'input'       => $input, // 请求内容
                // 'output'      => $response->data, // 响应内容
                'exts'        => null, // 扩展信息
                'use_time'    => Timer::end(self::TIMER_KEY_BEFORE_REQUEST), // 路由耗时
                'ip'          => Req::getUserIp(), // 登录IP
                'uid'         => AppHelper::app()->getUser()->getIsGuest() ? 0 : AppHelper::app()->getUser()->getId(), // 用户ID
                //'created_at'  => '', // 创建时间
            ];
            // 正常响应
            if (is_array($response->data)) {
                if (isset($response->data['code'])) {
                    $inData['is_success'] = 0 == $response->data['code'] ? 1 : 0;
                } else {
                    $inData['is_success'] = 1;
                }
                $inData['message'] = isset($response->data['msg']) ? $response->data['msg'] : '';
            } else {
                $inData['is_success'] = 1;
                $inData['message']    = '';
            }
        }
        return $inData;
    }

    /**
     * response.afterSend 事件
     *
     * @param Event $event
     * @throws \Throwable
     */
    public function handleRecordInterface(Event $event)
    {
        $response = $event->sender;
        /* @var Response $response */
        if ($this->isMock) {
            // 返回数据为mock时，不记录接口信息
            return;
        }
        $logData = $this->getLogData($response);
        if (0 == $logData['is_success']) {
            // 当接口失败时，不记录接口信息
            return;
        }
        if (is_string($response->data)) {
            // 防止接口传递传递的响应数据是字符串(string)
            $json = json_decode($response->data, true);
            if (isset($json['code']) && isset($json['data'])) {
                $response->data = $json;
            } else {
                $response->data = [
                    'code' => $this->errorCodeResponseInvalid,
                    'data' => $response->data,
                ];
            }
        }
        RouteManager::saveInterface(
            $this->system->code,
            $this->realPathInfo,
            DataStore::get($this->getStoreKey()),
            [
                'header'   => $this->getCustomHeaders($response->getHeaders()),
                'response' => (0 == $response->data['code'] && $response->data['data']) ? $response->data['data'] : null,
            ],
            RouteInterfaces::SOURCE_AUTH
        );
    }

    /**
     * 路由日志入库
     *
     * @param Event $event
     * @throws InvalidConfigException
     */
    public function handleRouteLog(Event $event)
    {
        $response = $event->sender;
        $request  = $this->request;
        /* @var Response $response */
        $logData            = $this->getLogData($response);
        $input              = array_merge($request->getQueryParams(), $request->getBodyParams());
        $logData['input']   = $input; // 请求内容
        $logData['output']  = $response->data; // 响应内容
        $logData['keyword'] = $this->getKeyword($input, $this->interface->route_log_key_fields); // 关键字，用于后期筛选

        if ($this->routeLogInstance) {
            $data = call_user_func([$this->routeLogInstance, 'afterResponse'], $response);
            // 消息覆盖
            if (isset($data['message']) && !empty($data['message'])) {
                $logData['message'] = $data['message'];
            }
            // 扩展信息必须来自自定义日志路由
            if (isset($data['exts'])) {
                $logData['exts'] = $data['exts'];
            }
            // 关键字以自定义日志路由优先
            if (isset($data['keyword']) && !empty($data['keyword'])) {
                $logData['keyword'] = $data['keyword'];
            }
        }
        $routeLogModel = RouteManager::getRouteLogModel();
        $routeLogModel->setAttributes($logData);
        $routeLogModel->save();
    }

    /**
     * 获取关键字
     *
     * @param array $input
     * @param $fields
     * @return string
     */
    protected function getKeyword(array $input, $fields): string
    {
        if (empty($fields)) {
            return '';
        }
        if (false !== strpos($fields, '->')) {
            $delimiter = '->';
        } elseif (false !== strpos($fields, ':')) {
            $delimiter = ':';
        } elseif (false !== strpos($fields, '|')) {
            $delimiter = '|';
        } else {
            $delimiter = '';
        }
        $fields   = explode_data($fields, $delimiter);
        $keywords = [];
        foreach ($fields as $field) {
            if (false === strpos($field, '.')) {
                $keywords[$field] = $input[$field] ?? '';
            } else {
                $_fields = explode_data($field, '.');
                $_input  = $input;
                while (count($_fields) > 0) {
                    $_field = array_shift($_fields);
                    if (isset($_input[$_field])) {
                        $_input = $_input[$_field];
                    } else {
                        break;
                    }
                }
                $keywords[$field] = count($_fields) > 0 ? '' : $_input;
            }
        }
        foreach ($keywords as $field => $keyword) {
            if (is_array($keyword)) {
                $keywords[$field] = json_encode($keyword, JSON_UNESCAPED_UNICODE);
            }
        }

        // 构造字符串返回
        $_count = count($keywords);
        if (0 === $_count) {
            return '';
        } elseif (1 === $_count) {
            $keyword = array_pop($keywords);
            return null === $keyword ? '' : $keyword;
        }
        return implode($delimiter, $keywords);
    }
}