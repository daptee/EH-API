<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Seed de los tipos necesarios para tests
        DB::table('users_types')->insert([
            ['id' => 1, 'name' => 'Superadmin',      'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Admin EH',         'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Admin Sukha',      'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Admin Cafeteria',  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('users_types');
    }
};
