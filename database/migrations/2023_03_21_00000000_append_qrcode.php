<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppendQrcode extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'qr_login') === false) {
            Schema::table(
                'users',
                static function (Blueprint $table): void {
                    $table->text('qr_login')->nullable()->after('password');
                    $table->unique('qr_login', 'users_qr_login_unique');
                }
            );
        }
    }
}
