# yii2-task
- yii2实现组件:路由管理，可开启路由日志，接口访问日志
- 必须使用 qingbing/yii2-helper 的 {{%systems}} 表(\YiiHelper\features\system\models\Systems)

# 使用
## 一、配置

### 1.1 配置控制器 web.php
```php
'controllerMap' => [
    // 路由管理
    'interface'       => \YiiRoute\controllers\InterfaceController::class,
    'interface-field' => \YiiRoute\controllers\InterfaceFieldController::class,
    'route-log'       => \YiiRoute\controllers\RouteLogController::class,
]
```


### 1.2 配置路由组件启动 web.php
```php
'bootstrap'  => [
    'bootRoute',
],
'components' => [
    'bootRoute'      => [
        'class'                 => \YiiRoute\boots\RouteBootstrap::class,
        'openRoute'             => define_var('COM_BOOT_ROUTE_OPEN_ROUTE', true), // 开启路由记录
        'acceptNewInterface'    => define_var('COM_BOOT_ROUTE_ACCEPT_NEW_INTERFACE', true), // 记录新接口信息
        'throwIfSystemNotExist' => define_var('COM_BOOT_ROUTE_THROW_IF_SYSTEM_NOT_EXIST', true), // 不存在的系统是否抛出异常终止
    ],
],
```

### 1.3 配置组件常量 define-local.php
```php
// bootRoute 组件(路由接口管理)
defined('COM_BOOT_ROUTE_OPEN_ROUTE') or define('COM_BOOT_ROUTE_OPEN_ROUTE', true); // 开启路由记录
defined('COM_BOOT_ROUTE_ACCEPT_NEW_INTERFACE') or define('COM_BOOT_ROUTE_ACCEPT_NEW_INTERFACE', true); // 记录新接口信息
defined('COM_BOOT_ROUTE_THROW_IF_SYSTEM_NOT_EXIST') or define('COM_BOOT_ROUTE_THROW_IF_SYSTEM_NOT_EXIST', true); // 不存在的系统是否抛出异常终止
```

## 二、对外 action
- \YiiRoute\actions\SystemOptions::class(接口系统选项)
- \YiiRoute\actions\SystemTypeOptions::class(接口系统类型选项)
