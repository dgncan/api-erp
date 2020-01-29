<?php

use Common\ApiException;
use League\OAuth2\Server\ResourceServer;
use Common\AccessTokenRepository;

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        $uid = @$_SESSION['profile']['UID'];
        $withStatus = 500;

        /** @var Monolog\Logger $logger */
        $logger = $c->get('logger');
        /** @var Exception $exception */

        if ($exception instanceof ApiException) {
            /** @var ApiException $exception */
            $withStatus = $exception->getHttpStatusCode();

            if ($exception->getErrorType() == 'client_error') {
                $logger->warning("uid:" . $uid . ", " . $exception->getMessage() .
                    " detail:" . $exception->getErrorDetail() . ' trace:' . $exception->getBackTrace());
            }

            if ($exception->getErrorType() == 'server_error') {
                $logger->error("uid:" . $uid . ", " . $exception->getMessage() .
                    " detail:" . $exception->getErrorDetail() . ' trace:' . $exception->getBackTrace());
            }

            if ($exception->getErrorType() == 'db_error') {
                $logger->critical("uid:" . $uid . ", " . $exception->getMessage() .
                    " detail:" . $exception->getErrorDetail() . ' trace:' . $exception->getBackTrace());
            }

            $data = [
                'status'  => $exception->getHttpStatusCode(),
                'code'    => $exception->getCode(),
                'message' => $exception->getMessage(),
                'helpUri' => $exception->getHelpUri(),
            ];
        } elseif ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException) {
            $data = [
                'status'  => 500,
                'helpUri' => '',
                'message' => $exception->getMessage() . " : " . $exception->getHint(),
                'code'    => $exception->getCode()
            ];
        } else {
            $data = [
                'status'  => 500,
                'helpUri' => '',
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode()
            ];
        }

        return $c->get('response')->withStatus($withStatus)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($data));
    };
};

$container['notFoundHandler'] = function ($c) {
    throw ApiException::notFound(4400, "Böyle bir kaynak yok");
};

$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

$container['redis'] = function ($c) {
    $settings = $c->get('settings')['redis'];
    try {
        $redis = new Predis\Client($settings);
        if (@$settings['password'] != '') {
            $redis->auth($settings['password']);
        }
    } catch (\Exception $e) {
        throw new Exception("Önbellek sorunu |" . $e->getMessage(), 5000);
    }

    return $redis;
};

# --------------------------------------------------------------------------
$container['db'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $dsn = $settings['driver'] . ":host=" . $settings['host'] . ";dbname=" . $settings['database'] . ";charset=" . $settings['charset'];
    try {
        $db = new \PDO($dsn, $settings['user'], $settings['password']);
    } catch (\Exception $e) {
        throw ApiException::dbError(5100, $e->getMessage());
    }

    return $db;
};

$container['resourceServer'] = function ($c) {
    $settings = $c->get('settings');

    $accessTokenRepository = new AccessTokenRepository($c->get('db'));

    // Path to authorization server's public key
    $publicKeyPath = 'file://' . __DIR__ . '/../public.key';

    // Setup the authorization server
    $server = new ResourceServer(
        $accessTokenRepository,
        $publicKeyPath
    );

    return $server;
};
