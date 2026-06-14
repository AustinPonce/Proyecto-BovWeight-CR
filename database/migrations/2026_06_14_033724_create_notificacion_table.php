<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Notificacion', function (Blueprint $table) {
            $table->integer('id_notificacion')->autoIncrement();
            $table->text('mensaje');
            $table->dateTime('fecha_envio');
            $table->integer('id_recordatorio');

            $table->foreign('id_recordatorio')->references('id_recordatorio')->on('Recordatorio')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('Notificacion');
    }
};