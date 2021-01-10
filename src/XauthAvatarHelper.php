<?php
/**
 * Created by IntelliJ IDEA.
 * User: andreasvratny
 * Date: 23.09.18
 * Time: 21:34.
 */

namespace vhmhv\Xauth;

use Microsoft\Graph\Graph;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

use function PHPUnit\Framework\directoryExists;

class XAuthAvatarHelper
{
    public static function createFromO365($user)
    {
        if(!directoryExists(public_path('/img/avatars'))) {
            mkdir(public_path('/img/avatars'), true);
        }
        $graph = new Graph();
        $graph->setBaseUrl('https://graph.microsoft.com/')->setApiVersion('beta')->setAccessToken($user->token);
        try {
            $meta = $graph->createRequest('GET', '/me/photo')->execute();
            $meta = $meta->getBody();
            $photo = $graph->createRequest('GET', '/me/photo/$value')->execute();
            $photo = $photo->getRawBody();
            if ($meta['@odata.mediaContentType'] == 'image/jpeg') {
                file_put_contents(public_path('/img/avatars/'.md5($user->email).'_360.jpg'), $photo);
            }

            return Image::make(public_path('/img/avatars/'.md5($user->email).'_360.jpg'));
        } catch (\Exception $e) {
            $img = Image::canvas(360, 360, '#'.str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT));
            $img->text(substr($user->givenName, 0, 1).substr($user->surname, 0, 1), 180, 180, function ($font) {
                $font->size(180);
                $font->color('#fff');
                $font->align('center');
                $font->valign('middle');
            });
            $img->save(public_path('/img/avatars/'.md5($user->email).'_360.jpg'));
            return $img;
        }
    }

    public static function resizeAvatars($originalImage)
    {
        $storagePrefix = public_path('/img/avatars');
        $userMailMD5 = md5(Auth::user()->email);

        $img = clone $originalImage;
        $img->resize(128, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->sharpen(5);
        $img->save($storagePrefix.'/'.$userMailMD5.'_128.jpg');

        $img = clone $originalImage;
        $img->resize(72, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->sharpen(5);
        $img->save($storagePrefix.'/'.$userMailMD5.'_72.jpg');

        $img = clone $originalImage;
        $img->resize(46, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->sharpen(5);
        $img->save($storagePrefix.'/'.$userMailMD5.'_46.jpg');

        $img = clone $originalImage;
        $img->resize(32, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->sharpen(5);
        $img->save($storagePrefix.'/'.$userMailMD5.'_32.jpg');
    }
}
