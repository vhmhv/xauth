<?php

namespace vhmhv\Xauth;

use vhmhv\Xauth\XAuthAvatarHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

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

    public function setCustomSocialiteConfig()
    {
        config([
            'services.graph.client_id' => config('xauth.graph.key'),
            'services.graph.client_secret' => config('xauth.graph.secret'),
            'services.graph.redirect' => config('xauth.graph.callback_url')
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest', 'web'])->except('logout');
    }

    public function xauthLogin(Request $request)
    {
        $this->setCustomSocialiteConfig();
        return view("xauth::redirect", ["uri" => Socialite::with('graph')->redirect()->getTargetUrl()]);
        $this->redirectToProvider($request);
    }

    public function redirectToProvider(Request $request)
    {

        $this->setCustomSocialiteConfig();
        $this->storeRedirectURIIfSet($request);
        return Socialite::driver('graph')->redirect();
    }

    private function storeRedirectURIIfSet(Request $request)
    {
        $redirectUri = $request->get('redirect_uri', null);
        if ($redirectUri) {
            Session::put(self::REDIRECT_URI_SESSION_KEY, $redirectUri);
        }
    }

    public function authApiUser(Request $request)
    {
        $user = Auth::user();
        return ['first_name' => $user->first_name, 'last_name' => $user->last_name, 'email' => $user->email];
    }

    /**
     * Obtain the user information from Office365.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $this->setCustomSocialiteConfig();
        $user = null;
        try {
            $user = Socialite::driver('graph')->user();
        } catch (\Throwable $th) {
            // state, saved in the session cookie differs the state retreived from oauth2-provider
            // maybe the cookie, used to store the session is bound to another domain?
            // it looks like it's only happening locally.
            $user = Socialite::driver('graph')->stateless()->user();
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
        }
        $dbUser->auth_token = $user->token;
        $dbUser->save();
        Auth::login($dbUser, true);
        if (config('xauth.options.get_avatars', true)) {
            XAuthAvatarHelper::resizeAvatars(XAuthAvatarHelper::createFromO365($user));
        }
        return $this->redirectToSessionRedirectURIOrIntendedURI(config('xauth.uri.login-success'));
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

    private function redirectToSessionRedirectURIOrIntendedURI($defaultURL)
    {
        // intended url cannot be used because it cannot be set by the pwa (popup with login)
        $redirectUri = Session::pull(self::REDIRECT_URI_SESSION_KEY, null);
        $user = auth()->user();
        unset($user['auth_token']);
        if ($redirectUri && strpos($redirectUri, '/') == 0) {
            return redirect($redirectUri, 302, ["X-Auth-User" => json_encode($user->toArray())]);
        }
        return redirect()->intended($defaultURL, 302, ["X-Auth-User" => json_encode($user->toArray())]);
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }

    public static function getRedirectToLoginWithCurrentURI($urlOnly = false)
    {
        $url = route('login', ['redirect_uri' => request()->getRequestUri()]);
        if ($urlOnly) {
            return $url;
        } else {
            return redirect($url);
        }
    }
}
