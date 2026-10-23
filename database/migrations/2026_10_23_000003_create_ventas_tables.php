<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulos Promociones, Despacho, Cobros, Facturación y Devoluciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            // producto_gratuito | vale_regalo | segundo_mitad | mayoreo | puntos
            $table->string('tipo', 20);
            // automatica: se aplica sola en el despacho | qr: se entrega con el QR de la factura
            $table->string('modo', 12)->default('automatica');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->foreignId('familia_id')->nullable()->constrained('familias')->nullOnDelete();
            $table->integer('cantidad_minima')->default(1);
            $table->decimal('valor', 10, 2)->default(0); // monto del vale o puntos por unidad
            $table->string('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->timestamp('fecha');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('descuento_vale', 12, 2)->default(0);
            $table->decimal('descuento_puntos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->integer('puntos_canjeados')->default(0);
            $table->integer('puntos_ganados')->default(0);
            $table->string('estado', 15)->default('en_proceso'); // en_proceso | pagada | facturada
        });

        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->string('promocion')->nullable();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas')->cascadeOnDelete();
            $table->string('metodo', 10); // efectivo | credito | debito
            $table->decimal('monto', 12, 2);
            $table->decimal('monto_recibido', 12, 2)->nullable();
            $table->decimal('cambio', 12, 2)->default(0);
            $table->string('referencia_voucher', 40)->nullable();
            $table->timestamp('fecha');
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->unique()->constrained('ventas');
            $table->string('serie', 5);
            $table->integer('numero');
            $table->timestamp('fecha');
            $table->string('nit', 20);
            $table->string('nombre', 150);
            $table->string('direccion')->nullable();
            $table->decimal('total', 12, 2);
            $table->unique(['serie', 'numero']);
        });

        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->foreignId('promocion_id')->constrained('promociones');
            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('estado', 10)->default('emitido'); // emitido | canjeado
            $table->date('fecha_vencimiento');
            $table->foreignId('venta_canje_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->timestamp('canjeado_en')->nullable();
            $table->timestamps();
        });

        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('caja_id')->constrained('cajas');
            $table->timestamp('fecha');
            $table->string('motivo', 12); // dano | vencimiento
            $table->string('observacion')->nullable();
            $table->decimal('total', 12, 2);
        });

        Schema::create('detalle_devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devolucion_id')->constrained('devoluciones')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_devoluciones');
        Schema::dropIfExists('devoluciones');
        Schema::dropIfExists('cupones');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('promociones');
    }
};
