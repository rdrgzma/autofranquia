<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleAndFranchiseToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // role: super_admin, franchise_admin, collaborator, super_collaborator (ajuste conforme seu uso)
            $table->string('role')->default('collaborator')->after('password');
            $table->foreignId('franchise_id')->nullable()->constrained('franchises')->nullOnDelete()->after('role');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('franchise_id');
            $table->dropColumn('role');
        });
    }
}
