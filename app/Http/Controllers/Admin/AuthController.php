<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View
    {
        $captcha = Str::upper(Str::random(5));

        $request->session()->put([
            'admin_login_captcha_hash' => Hash::make(Str::lower($captcha)),
            'admin_login_captcha_issued_at' => now()->timestamp,
        ]);

        return view('admin.login', [
            'captchaSvg' => $this->captchaSvg($captcha),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Coba kembali dalam {$seconds} detik.",
            ]);
        }

        $captchaHash = $request->session()->pull('admin_login_captcha_hash');
        $captchaIssuedAt = (int) $request->session()->pull('admin_login_captcha_issued_at', 0);
        $captchaIsExpired = $captchaIssuedAt === 0 || now()->timestamp - $captchaIssuedAt > 600;

        if ($captchaIsExpired || ! is_string($captchaHash) || ! Hash::check(Str::lower($request->string('captcha')->toString()), $captchaHash)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'captcha' => 'Kode verifikasi tidak sesuai atau sudah kedaluwarsa. Silakan muat kode baru.',
            ]);
        }

        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower((string) $request->input('email')).'|'.$request->ip());
    }

    private function captchaSvg(string $captcha): string
    {
        $characters = collect(str_split($captcha))->map(function (string $character, int $index): string {
            $x = 30 + ($index * 38);
            $y = random_int(43, 56);
            $rotation = random_int(-16, 16);

            return "<text x=\"{$x}\" y=\"{$y}\" transform=\"rotate({$rotation} {$x} {$y})\">{$character}</text>";
        })->implode('');

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 230 74" role="img" aria-label="Kode verifikasi CAPTCHA">
            <rect width="230" height="74" rx="10" fill="#f8ecd4"/>
            <path d="M8 20 C48 68 92 2 142 47 S204 64 224 18" fill="none" stroke="#bd9150" stroke-width="2" opacity=".55"/>
            <path d="M3 55 C57 12 113 72 169 22 S216 18 229 48" fill="none" stroke="#582308" stroke-width="1.5" opacity=".25"/>
            <g font-family="Georgia,serif" font-size="31" font-weight="700" fill="#582308">{$characters}</g>
        </svg>
        SVG;
    }
}
