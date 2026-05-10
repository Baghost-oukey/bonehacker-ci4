<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLogin')) {
            // Check Remember Me Cookie
            helper('cookie');
            $rememberToken = get_cookie('remember_me');

            if ($rememberToken) {
                $decoded = base64_decode($rememberToken);
                if (strpos($decoded, ':') !== false) {
                    list($userId, $token) = explode(':', $decoded, 2);

                    $authModel = new \App\modules\auth\Models\MAuth();
                    $user = $authModel->find($userId);

                    if ($user && $user->remember_token && password_verify($token, $user->remember_token)) {
                        $sessionData = $authModel->getUserSessionData($user);
                        session()->set($sessionData);
                        return; // Successfully logged in
                    }
                }
            }

            if ($request->isAJAX()) {
                return service('response')->setStatusCode(401, 'Session Expired');
            }
            return redirect()->to(base_url('auth'));
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
