<?php
/**
 * NormalRoute
 *
 * @author camera360_server@camera360.com
 * @copyright Chengdu pinguo Technology Co.,Ltd.
 */

namespace PG\MSF\Rest;

/**
 * Class Route
 * @package PG\MSF\Route
 */
class Route extends \PG\MSF\Route\NormalRoute
{
    /**
     * @var string
     * The name of the POST parameter that is used to indicate if a request is a PUT, PATCH or DELETE
     */
    public $methodParam = '_method';
    /**
     * @var string
     */
    public $verb;
    /**
     * @var array
     * support verb
     */
    public static $verbs = [
        'GET',      // 从服务器取出资源（一项或多项）
        'POST',     // 在服务器新建一个资源
        'PUT',      // 在服务器更新资源（客户端提供改变后的完整资源）
        'PATCH',    // 在服务器更新资源（客户端提供改变的属性）
        'DELETE',   // 从服务器删除资源
        'HEAD',     // 获取 head 元数据
        'OPTIONS',  // 获取信息，关于资源的哪些属性是客户端可以改变的
    ];
    /**
     * @var array
     */
    public $patterns = [
        'PUT,PATCH {id}' => 'update', // 更新资源，如：/users/<id>
        'DELETE {id}' => 'delete', // 删除资源，如：/users/<id>
        'GET,HEAD {id}' => 'view', // 查看资源单条数据，如：/users/<id>
        'POST' => 'create', // 新建资源，如：/users
        'GET,HEAD' => 'index', // 查看资源列表数据（可分页），如：/users
        '{id}' => 'options', // 查看资源所支持的HTTP动词，如：/users/<id> | /users
        '' => 'options',
    ];

    /**
     * 处理http request
     * @param $request
     */
    public function handleClientRequest($request)
    {
        $this->clientData->path = rtrim($request->server['path_info'], '/');
        $this->verb = $this->getVerb($request);
        $this->parsePath($this->clientData->path);
    }

    /**
     * 解析path
     *
     * @param $path
     */
    public function parsePath($path)
    {
        if (isset($this->routeCache[$path])) {
            $this->clientData->controllerName = $this->routeCache[$path][0];
            $this->clientData->methodName = $this->routeCache[$path][1];
        } else {
            $route = explode('/', $path);
            $route = array_map(function ($name) {
                $name = ucfirst($name);
                return $name;
            }, $route);
            $methodName = array_pop($route);
            $this->clientData->controllerName = ltrim(implode("\\", $route), "\\")??null;
            $this->clientData->methodName = $methodName;
        }
    }

    /**
     * get request verb
     * @param Object $request
     */
    public function getVerb($request)
    {
        if (isset($request->post[$this->methodParam])) {
            return strtoupper($request->post[$this->methodParam]);
        }
        if (isset($request->server['http_x_http_method_override'])) {
            return strtoupper($request->server['http_x_http_method_override']);
        }
        if (isset($request->server['request_method'])) {
            return strtoupper($request->server['request_method']);
        }

        return 'GET';
    }

    /**
     * Returns whether this is a GET request.
     * @return bool whether this is a GET request.
     */
    public function getIsGet()
    {
        return $this->verb === 'GET';
    }

    /**
     * Returns whether this is an OPTIONS request.
     * @return bool whether this is a OPTIONS request.
     */
    public function getIsOptions()
    {
        return $this->verb === 'OPTIONS';
    }

    /**
     * Returns whether this is a HEAD request.
     * @return bool whether this is a HEAD request.
     */
    public function getIsHead()
    {
        return $this->verb === 'HEAD';
    }

    /**
     * Returns whether this is a POST request.
     * @return bool whether this is a POST request.
     */
    public function getIsPost()
    {
        return $this->verb === 'POST';
    }

    /**
     * Returns whether this is a DELETE request.
     * @return bool whether this is a DELETE request.
     */
    public function getIsDelete()
    {
        return $this->verb === 'DELETE';
    }

    /**
     * Returns whether this is a PUT request.
     * @return bool whether this is a PUT request.
     */
    public function getIsPut()
    {
        return $this->verb === 'PUT';
    }

    /**
     * Returns whether this is a PATCH request.
     * @return bool whether this is a PATCH request.
     */
    public function getIsPatch()
    {
        return $this->verb === 'PATCH';
    }

    /**
     * parse rule
     */
    protected function parseRule()
    {
        $rules = getInstance()->config->get('route.rest.rules', []);
        if (empty($rules)) {
            return;
        }
    }
}
