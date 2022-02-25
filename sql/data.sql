
-- 路由日志 - 表头类型
insert into `configure_header_category`
( `key`, `name`, `description`, `sort_order`, `is_open`)
values
( 'backend-logs-route', '后管系统-路由日志', '后管系统-路由日志', '102', '0');

-- 路由日志 - 表头选项
insert into `configure_header_option`
( `key`, `field`, `label`, `width`, `fixed`, `default`, `align`, `is_tooltip`, `is_resizable`, `is_editable`, `component`, `options`, `params`, `description`, `sort_order`, `is_required`, `is_default`, `is_enable`, `operate_ip`, `operate_uid`)
values
( 'backend-logs-route', '_idx', '序号', '50', 'left', '', '', '0', '0', '0', '', '\"\"', '\"\"', '', '1', '0', '1', '1', '192.168.1.1', '100000000'),
( 'backend-logs-route', 'url_path', '接口path', '200', 'left', '', 'left', '0', '1', '0', '', '\"\"', '\"\"', '', '2', '1', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'id', 'ID', '50', '', '', 'left', '0', '1', '0', '', '\"\"', '\"\"', '', '3', '1', '1', '1', '192.168.1.1', '100000000'),
( 'backend-logs-route', 'system_name', '接口名称', '100', '', '', 'left', '0', '1', '0', '', '\"\"', '\"\"', '', '4', '0', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'is_success', '是否成功', '80', '', '', '', '1', '0', '0', '', '[\"否\", \"是\"]', '\"\"', '', '5', '1', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'use_time', '接口耗时', '80', '', '', 'left', '1', '0', '0', '', '\"\"', '\"\"', '', '6', '1', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'trace_id', '链路ID', '280', '', '', 'left', '1', '0', '0', '', '\"\"', '\"\"', '', '7', '0', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'keyword', '关键字', '100', '', '', 'left', '1', '1', '0', '', '\"\"', '\"\"', '', '8', '0', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'method', '请求类型', '80', '', '', '', '1', '1', '0', '', '\"\"', '\"\"', '', '9', '0', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'message', '操作消息', '120', '', '', 'left', '1', '1', '0', '', '\"\"', '\"\"', '', '10', '0', '0', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'ip', '操作IP', '120', '', '', 'left', '1', '1', '0', '', '\"\"', '\"\"', '', '11', '0', '0', '0', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'uid', '操作UID', '100', '', '', 'left', '0', '1', '0', '', '\"\"', '\"\"', '', '12', '0', '0', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'created_at', '创建时间', '160', '', '', '', '0', '0', '0', '', '\"\"', '\"\"', '', '13', '1', '1', '1', '127.0.0.1', '100000000'),
( 'backend-logs-route', 'operate', '操作', '', 'right', '', 'left', '0', '0', '0', 'operate', '\"\"', '[]', '', '14', '1', '1', '1', '127.0.0.1', '100000000');

-- 路由日志 - 表单类型
insert into `configure_form_category`
( `key`, `name`, `description`, `sort_order`, `is_setting`, `is_open`)
values
( 'backend-logs-route', '后管系统-路由日志', '后管系统-路由日志', '127', '0', '0');

-- 路由日志 - 表单选项
insert into `configure_form_option`
( `key`, `field`, `label`, `input_type`, `default`, `description`, `sort_order`, `is_enable`, `exts`, `rules`, `is_required`, `required_msg`)
values
( 'backend-logs-route', 'system_code', '系统代码', 'view-text', '', '', '1', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'system_name', '系统', 'view-text', '', '', '2', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'url_path', '接口path', 'view-text', '', '', '3', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'id', 'ID', 'view-text', '', '', '4', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'trace_id', 'Trace-ID', 'view-text', '', '', '5', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'method', '请求方式', 'view-text', '', '', '6', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'use_time', '接口耗时', 'view-text', '', '', '7', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'is_success', '是否成功', 'input-select', '', '', '8', '1', '{\"options\": [\"否\", \"是\"]}', '\"\"', '0', ''),
( 'backend-logs-route', 'keyword', '关键字', 'view-text', '', '', '9', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'message', '消息', 'view-text', '', '', '10', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'input', '请求数据', 'json-editor', '0', '', '11', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'output', '响应数据', 'json-editor', '0', '', '12', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'exts', '扩展数据', 'json-editor', '0', '', '13', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'uid', 'UID', 'view-text', '', '', '14', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'ip', 'IP', 'view-text', '', '', '15', '1', '\"\"', '\"\"', '0', ''),
( 'backend-logs-route', 'created_at', '创建时间', 'view-text', '', '', '16', '1', '\"\"', '\"\"', '0', '');
