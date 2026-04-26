<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->after('id')->constrained();
            $table->string('org_role')->default('viewer')->after('password');
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organisation_id');
            $table->dropColumn('org_role');
        });
        Schema::dropIfExists('organisations');
    }
};
