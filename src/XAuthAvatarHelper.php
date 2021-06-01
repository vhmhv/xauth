<?php

/**
 * Created by IntelliJ IDEA.
 * User: andreasvratny
 * Date: 23.09.18
 * Time: 21:34.
 */

namespace vhmhv\Xauth;

use Intervention\Image\Image;
use Microsoft\Graph\Graph;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

use function PHPUnit\Framework\directoryExists;

class XAuthAvatarHelper
{
    public static function createFromO365($user): Image
    {
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }
        $graph = new Graph();
        $graph->setBaseUrl('https://graph.microsoft.com/')->setApiVersion('beta')->setAccessToken($user->token);
        try {
            $meta = $graph->createRequest('GET', '/me/photo')->execute();
            $meta = $meta->getBody();
            $photo = $graph->createRequest('GET', '/me/photo/$value')->execute();
            $photo = $photo->getRawBody();
            if ($meta['@odata.mediaContentType'] == 'image/jpeg') {
                Storage::disk('public')->put('avatars/' . md5($user->email) . '_360.jpg', $photo);
            }
            $img = new ImageManager();
            $i = $img->make(Storage::disk('public')->get('avatars/' . md5($user->email) . '_360.jpg'));
            return $i;
        } catch (\Exception $e) {
            $img = new ImageManager();
            $canvas = $img->canvas(360, 360, '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT));
            $canvas->text(substr($user->givenName, 0, 1) . substr($user->surname, 0, 1), 180, 180, function ($font) {
                if (file_exists(public_path('/fonts/Avenir Next LT Pro Demi.ttf'))) {
                    $font->file(public_path('/fonts/Avenir Next LT Pro Demi.ttf'));
                } else {
                    $font->file('/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf');
                }
                $font->size(180);
                $font->color('#fff');
                $font->align('center');
                $font->valign('middle');
            });
            Storage::disk('public')->put('avatars/' . md5($user->email) . '_360.jpg', (string) $canvas->encode('jpg', 80));
            return $canvas;
        }
    }

    public static function resizeAvatars(Image $originalImage): void
    {
        $userMailMD5 = md5(Auth::user()->email);

        $img = clone $originalImage;
        $img->resize(128, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img = $img->sharpen(5);
        Storage::disk('public')->put('avatars/' . $userMailMD5 . '_128.jpg', (string) $img->encode('jpg', 80));

        $img = clone $originalImage;
        $img->resize(72, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img = $img->sharpen(5);
        Storage::disk('public')->put('avatars/' . $userMailMD5 . '_72.jpg', (string) $img->encode('jpg', 80));

        $img = clone $originalImage;
        $img->resize(46, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img = $img->sharpen(5);
        Storage::disk('public')->put('avatars/' . $userMailMD5 . '_46.jpg', (string) $img->encode('jpg', 80));

        $img = clone $originalImage;
        $img->resize(32, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img = $img->sharpen(5);
        Storage::disk('public')->put('avatars/' . $userMailMD5 . '_32.jpg', (string) $img->encode('jpg', 80));
    }
}
