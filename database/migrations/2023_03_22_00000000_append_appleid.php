<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppendAppleid extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'apple_id') === false) {
            Schema::table(
                'users',
                static function (Blueprint $table): void {
                    $table->text('apple_id')->nullable()->after('password');
                    $table->unique('apple_id', 'users_apple_id_unique');
                }
            );
        }
    }
}
