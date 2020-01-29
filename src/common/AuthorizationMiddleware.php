<?php

namespace Common;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Lcobucci\JWT\Parser;
use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * Class AuthorizationMiddleware
 *
 * @author  Doğan Can <dgncan@gmail.com>
 * @package Common
 */
final class AuthorizationMiddleware
{
    private $scopes;
    private $app;

    public function __construct($app, $options = [])
    {
        $this->app = $app;
    }


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     * @return static
     * @throws OAuthServerException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $token = $this->getToken($request);

        $requestScopes = $token->getClaim('scopes');
        if (isset($this->scopes) && count($this->scopes) && count($requestScopes)) {
            foreach ($this->scopes as $scope) {
                if (in_array($scope, $requestScopes)) {
                    $request
                        ->withAttribute('oauth_access_token_id', $token->getClaim('jti'))
                        ->withAttribute('oauth_client_id', $token->getClaim('aud'))
                        ->withAttribute('oauth_user_id', $token->getClaim('sub'))
                        ->withAttribute('oauth_scopes', $token->getClaim('scopes'));
                    unset($token);

                    return $next($request, $response);
                }
            }
        }
        throw OAuthServerException::accessDenied('Scope Authorization Problem. Your Request Scopes: \'' . implode(",",
                $requestScopes) . '\' Required Scopes:\'' . implode(",", $this->scopes) . '\'');
    }


    public function getToken(ServerRequestInterface $request)
    {
        $header = $request->getHeader('authorization');
        $jwt = trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header[0]));

        try {
            // Attempt to parse and validate the JWT
            $token = (new Parser())->parse($jwt);

            return $token;
        } catch (\RuntimeException $exception) {
            throw OAuthServerException::accessDenied('Error while decoding to JSON');
        }
    }


    public function withRequiredScope(array $scopes)
    {
        $clone = clone $this;
        $clone->scopes = $clone->formatScopes($scopes);

        return $clone;
    }


    private function formatScopes(array $scopes)
    {
        if (empty($scopes)) {
            return [null]; //use at least 1 null scope
        }
        array_walk(
            $scopes,
            function (&$scope) {
                if (is_array($scope)) {
                    $scope = implode(' ', $scope);
                }
            }
        );

        return $scopes;
    }
}
