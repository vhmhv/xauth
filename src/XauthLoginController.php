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

class XauthLoginController extends Controller
{
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

    use AuthenticatesUsers;

    public const REDIRECT_URI_SESSION_KEY = 'login-redirect-uri';

    public function setCustomSocialiteConfig()
    {
        config([
            'services.graph.client_id' => config('xauth.graph.key'),
            'services.graph.client_secret'=> config('xauth.graph.secret'),
            'services.graph.redirect'=> env('APP_URL').'/login/graph/callback'
        ]);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function xauthlogin(Request $request)
    {
        $this->setCustomSocialiteConfig();
        return view("redirect", ["uri" => Socialite::with('graph')->redirect()->getTargetUrl()]);
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

    function AuthApiUser(Request $request)
    {
        $user = Auth::user();
        return ['name' => $user->name, 'email' => $user->email];
    }

    /**
     * Obtain the user information from Office365.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $this->setCustomSocialiteConfig();
        $user = Socialite::driver('graph')->user();

        if ($this->endsWith(strtolower($user->email), 'vhmhv.de') !== true) {
            abort(403);
        }

        $dbUser = User::where(['email' => $user->email])->first();
        if ($dbUser === null) {
            $dbUser = new User();
            $dbUser->email = $user->email;
            $dbUser->name = $user->displayName;
            $dbUser->password = md5($user->token); //Nur wegen null=false
        }
        $dbUser->auth_token = $user->token;
        $dbUser->save();
        Auth::login($dbUser, true);
        XAuthAvatarHelper::resizeAvatars(XauthAvatarHelper::createFromO365($user));


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
        if ($redirectUri && strpos($redirectUri, '/') == 0) {
            return redirect($redirectUri);
        }
        return redirect()->intended($defaultURL);
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
