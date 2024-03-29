<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendUsersTable extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'auth_token') === false) {
            Schema::table(
                'users',
                static function (Blueprint $table): void {
                    $table->text('auth_token')->nullable()->after('password');
                }
            );
        }
    }
}
