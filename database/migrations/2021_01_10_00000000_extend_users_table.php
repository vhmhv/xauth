<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExtendUsersTable extends Migration
{
    public function up()
    {

        if (Schema::hasColumn("users", 'auth_token') == false) {
            Schema::table(
                "users",
                function (Blueprint $table) {
                    $table->text('auth_token')->nullable()->after('password');
                });
        }
    }
}
