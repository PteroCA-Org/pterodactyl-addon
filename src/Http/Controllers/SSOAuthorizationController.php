<?php

namespace Pteroca\PterodactylAddon\Http\Controllers;

use Pteroca\PterodactylAddon\Services\JwtService;
use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Container\Container;
use Pterodactyl\Events\Auth\DirectLogin;
use Pterodactyl\Http\Controllers\Auth\AbstractLoginController;

class SSOAuthorizationController extends AbstractLoginController
{
    private $jwtService;

    public function __construct(JwtService $jwtService)
    {
        parent::__construct();

        $this->jwtService = $jwtService;
    }

    public function index(Request $request)
    {
        $token = $request->post('pteroca_sso_token');
        $decodedToken = $this->jwtService->decodeToken($token);

        if (empty($decodedToken) || empty($decodedToken->user->id)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $pterodactylUserId = $decodedToken->user->id;
        $user = User::find($pterodactylUserId);
        if (empty($user)) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user['2fa']) {
            return response()->json(['error' => 'Cannot login with 2FA enabled'], 403);
        }

        $this->sendLoginResponse($user, $request);

        return redirect()->intended($this->safeRedirectPath($request->post('pteroca_sso_redirect')));
    }

    /**
     * Only allow same-origin relative paths as post-login redirect targets.
     */
    private function safeRedirectPath($path): string
    {
        if (!is_string($path) || $path === '' || $path[0] !== '/' || str_starts_with($path, '//') || str_contains($path, '\\')) {
            return '/';
        }

        return $path;
    }
}
