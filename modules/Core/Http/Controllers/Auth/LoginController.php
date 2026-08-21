<?php

namespace Modules\Core\Http\Controllers\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Core\Http\Requests\Auth\LoginRequest;
use Modules\Core\Services\Auth\AuthenticationService;

class LoginController extends Controller
{
    public function __construct(private readonly AuthenticationService $auth) {}

    public function create(): View
    {
        return view('core::auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $ok = $this->auth->attemptLocal(
            $request->string('email'),
            $request->string('password'),
            $request->boolean('remember'),
            $request,
        );

        if (! $ok) {
            $request->hitRateLimiter();

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah, atau akun tidak aktif.',
            ]);
        }

        $request->clearRateLimiter();

        return redirect()->intended(route('core.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout($request);

        return redirect()->route('core.landing');
    }
}
