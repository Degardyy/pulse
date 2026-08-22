<?php

namespace Modules\Core\Services\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Core\Services\Audit\AuditService;

/**
 * Single entry point for signing users in and out (ADR-006).
 *
 * Today only local credentials are supported; an SSO callback will later call
 * loginUser() with a user resolved from auth_provider/provider_id, reusing the
 * same session handling, activity gate, and last-login bookkeeping.
 */
class AuthenticationService
{
    /**
     * Attempt a local email/password login. Inactive accounts never authenticate.
     */
    public function attemptLocal(string $email, string $password, bool $remember, Request $request): bool
    {
        $ok = Auth::attempt([
            'email' => $email,
            'password' => $password,
            'auth_provider' => User::PROVIDER_LOCAL,
            'is_active' => true,
        ], $remember);

        if ($ok) {
            $this->onLoggedIn($request);
        }

        return $ok;
    }

    public function logout(Request $request): void
    {
        if ($user = Auth::user()) {
            app(AuditService::class)->record(AuditLog::EVENT_LOGOUT, $user);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    protected function onLoggedIn(Request $request): void
    {
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        app(AuditService::class)->record(AuditLog::EVENT_LOGIN, $user);
    }
}
