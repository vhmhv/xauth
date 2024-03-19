<?php

namespace vhmhv\Xauth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\HttpException;

class XAuthLoginController extends Controller
{
    use AuthenticatesUsers;

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    public const REDIRECT_URI_SESSION_KEY = 'login-redirect-uri';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest', 'web'])->except('logout');
    }

    public function setCustomSocialiteConfig(): void
    {
        config(
            [
                'services.microsoft.client_id' => config('xauth.graph.key'),
                'services.microsoft.client_secret' => config('xauth.graph.secret'),
                'services.microsoft.redirect' => config('xauth.graph.callback_url'),
                'services.microsoft.tenant' => config('xauth.graph.tenant_id'),
                'services.microsoft.include_tenant_info' => config('xauth.graph.include_tenant_info'),
                'services.apple.client_id' => config('xauth.apple.key'),
                'services.apple.client_secret' => config('xauth.apple.secret'),
                'services.apple.redirect' => config('xauth.apple.callback_url'),
            ]
        );
    }

    public function redirectToProvider(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        $this->setCustomSocialiteConfig();
        $this->storeRedirectURIIfSet($request);
        return Socialite::driver('microsoft')->redirect();
    }

    public function redirectToApple(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        $this->setCustomSocialiteConfig();
        $this->storeRedirectURIIfSet($request);
        return Socialite::driver('apple')->redirect();
    }

    public function chooseMethod(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        $this->storeRedirectURIIfSet($request);
        return redirect(config('xauth.uri.login-choosemethod'));
    }

    public function authApiUser(): array
    {
        $user = Auth::user();
        return ['first_name' => $user->first_name, 'last_name' => $user->last_name, 'email' => $user->email];
    }

    /**
     * Obtain the user information from Office365.
     */
    public function handleProviderCallback(): \Illuminate\Http\RedirectResponse
    {
        $this->setCustomSocialiteConfig();
        $user = null;
        try {
            $user = Socialite::driver('microsoft')->user();
        } catch (\Throwable $th) {
            // state, saved in the session cookie differs the state retreived from oauth2-provider
            // maybe the cookie, used to store the session is bound to another domain?
            // it looks like it's only happening locally.
            $user = Socialite::driver('microsoft')->stateless()->user();
        }

        if ($this->endsWith(strtolower($user->email), 'vhmhv.de') !== true) {
            abort(403);
        }
        $dbUser = User::where(['email' => $user->email])->first();
        if ($dbUser === null) {
            $dbUser = new User();
            $dbUser->email = $user->email;
            $dbUser->first_name = $user->user['surname'];
            $dbUser->last_name = $user->user['givenName'];
            $dbUser->password = md5($user->token); //Nur wegen null=false
            $dbUser->qr_login = 'HVAUTH_' . md5(md5($user->email));
        }
        $dbUser->auth_token = $user->token;
        $dbUser->save();
        Auth::login($dbUser, true);
        if (config('xauth.options.get_avatars', true)) {
            XAuthAvatarHelper::resizeAvatars(XAuthAvatarHelper::createFromO365($user));
        }
        return $this->redirectToSessionRedirectURIOrIntendedURI(config('xauth.uri.login-success'));
    }

    /**
     * Obtain the user information from Office365.
     */
    public function handleAppleCallback(): \Illuminate\Http\RedirectResponse
    {
        $this->setCustomSocialiteConfig();
        $user = null;
        try {
            $user = Socialite::driver('apple')->user();
        } catch (\Throwable $th) {
            // state, saved in the session cookie differs the state retreived from oauth2-provider
            // maybe the cookie, used to store the session is bound to another domain?
            // it looks like it's only happening locally.
            $user = Socialite::driver('apple')->stateless()->user();
        }

        if ($this->endsWith(strtolower($user->email), 'vhmhv.de') === true) {
            $dbUser = User::where(['email' => $user->email])->first();
            if($dbUser === null) {
                $dbUser = new User();
                $dbUser->email = $user->email;
                $firstName = trim(substr($user->name, 0, strrpos($user->name, ' ')));
                $lastName = trim(substr($user->name, strrpos($user->name, ' ')));
                $dbUser->first_name = $firstName;
                $dbUser->last_name = $lastName;
                $dbUser->password = md5($user->token); //Nur wegen null=false
                $dbUser->apple_id = $user->id;
            }
        } else {
            $dbUser = User::where(['apple_id' => $user->id])->first();
            if ($dbUser === null) {
                abort(403);
            }
        }
        $dbUser->auth_token = $user->token;
        $dbUser->save();
        Auth::login($dbUser, true);
        return $this->redirectToSessionRedirectURIOrIntendedURI(config('xauth.uri.login-success'));
    }

    public function qrLogin(): \Illuminate\Http\RedirectResponse
    {
        return redirect(config('xauth.uri.login-choosemethod'));
    }

    public function loginByQR(Request $request): \Illuminate\Http\RedirectResponse
    {
        $requestData = null;
        try {
            $requestData = json_decode($request->getContent());
            if (json_last_error() != JSON_ERROR_NONE) {
                $requestData = json_decode(json_encode($request->post()));
            }
            if($requestData == null) throw new HttpException(412);
        } catch (\Throwable $th) {
            abort(406);
        }
        $dbUser = User::where('qr_login', $requestData->qr_login)->firstOr(function()
        {
            throw new HttpException(403);
        });
        Auth::login($dbUser, true);
        return $this->redirectToSessionRedirectURIOrIntendedURI(config('xauth.uri.login-success'));
}

    public static function getRedirectToLoginWithCurrentURI(bool $urlOnly = false): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        $url = route('login', ['redirect_uri' => request()->getRequestUri()]);
        if ($urlOnly) {
            return $url;
        }
        return redirect($url);
    }

    /**
     * The user has logged out of the application.
     */
    protected function loggedOut(): mixed
    {
        return redirect()->route('login');
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

    private function redirectToSessionRedirectURIOrIntendedURI(string $defaultURL = ''): \Illuminate\Http\RedirectResponse
    {
        // intended url cannot be used because it cannot be set by the pwa (popup with login)
        $redirectUri = Session::pull(self::REDIRECT_URI_SESSION_KEY, null);
        $user = auth()->user();
        unset($user['auth_token']);
        if ($redirectUri && strpos($redirectUri, '/') === 0) {
            return redirect($redirectUri, 302, ['X-Auth-User' => json_encode($user->toArray())]);
        }
        return redirect()->intended($defaultURL, 302, ['X-Auth-User' => json_encode($user->toArray())]);
    }

    private function storeRedirectURIIfSet(Request $request): void
    {
        $redirectUri = $request->get('redirect_uri', null);
        if ($redirectUri) {
            Session::put(self::REDIRECT_URI_SESSION_KEY, $redirectUri);
        }
    }
}
