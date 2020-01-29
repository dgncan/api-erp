<?php

namespace Service;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

/**
 * Class AbstractService
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Service
 */
abstract class AbstractService
{
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NO_CONTENT = 204;

    const STATUS_MULTIPLE_CHOICES = 300;
    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_FOUND = 302;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_USE_PROXY = 305;
    const STATUS_TEMPORARY_REDIRECT = 307;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTED = 406;

    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;

    protected $container;
    protected $request;
    protected $response;

    protected $pageLimit;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function response($status = 200, $data = [], $allow = [])
    {
        if (!isset($this->response)) {
            $this->response = new  Response($status);
        }

        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $this->response->withStatus($status);

        if (!empty($allow)) {
            $response = $response->withHeader('Allow', strtoupper(implode(',', $allow)));
        }

        if (!empty($data)) {
            $response = $response->withJson($data);
        }

        return $response;
    }

    /**
     * Zenginleştirilmiş ve anlamlı dönüş sağlayan response fonksiyonu
     *
     * @param                        $status
     * @param                        $list
     * @param ServerRequestInterface $request
     * @param array                  $args
     * @param array                  $params
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function res($status, ServerRequestInterface $request, $list = [], $args = [], $params = [])
    {
        $message = '';
        $list = (count($list) == 0 ? false : $list);
        if ($status !== self::STATUS_OK) {
            if ($list['message'] == '') {
                $message = 'Hata Oldu';
            }
            $message = $list['message'];
            $list = false;
        }
        $resource = [
            'request' => [
                'method' => (string)$request->getMethod(),
                'path'   => (string)$request->getUri(),
                'args'   => $args,
                'params' => $params
            ],
            "count"   => count($list),
            'list'    => $list,
            'message' => $message
        ];

        return $this->response($status, $resource);
    }
}
