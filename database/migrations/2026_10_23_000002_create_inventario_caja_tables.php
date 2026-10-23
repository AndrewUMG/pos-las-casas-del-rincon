<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulos Inventario y Compras + Caja.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->string('numero_documento', 40);
            $table->date('fecha');
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2);
        });

        // Kardex: toda entrada o salida de producto queda registrada.
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('tipo', 20); // entrada | salida | baja
            $table->integer('cantidad');
            $table->integer('existencia_resultante');
            $table->string('referencia', 60)->nullable();
            $table->string('descripcion')->nullable();
            $table->timestamp('fecha');
        });

        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->timestamp('fecha_apertura');
            $table->decimal('monto_inicial', 12, 2);
            $table->timestamp('fecha_cierre')->nullable();
            $table->decimal('monto_esperado', 12, 2)->nullable();
            $table->decimal('monto_contado', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();
            $table->string('estado', 10)->default('abierta'); // abierta | cerrada
        });

        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->cascadeOnDelete();
            $table->string('tipo', 10); // ingreso | egreso
            $table->string('metodo_pago', 10); // efectivo | credito | debito
            $table->decimal('monto', 12, 2);
            $table->string('descripcion');
            $table->string('referencia', 60)->nullable();
            $table->timestamp('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
        Schema::dropIfExists('cajas');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('detalle_compras');
        Schema::dropIfExists('compras');
    }
};
