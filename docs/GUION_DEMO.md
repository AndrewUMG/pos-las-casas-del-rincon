# Guion de la demostración (10–12 minutos)

Recorrido sugerido para presentar el sistema en vivo y mostrar **cada requerimiento del enunciado**.
Antes de empezar, deje los datos listos y abra el sistema:

```powershell
powershell -ExecutionPolicy Bypass -File scripts\instalar.ps1 -Demo
powershell -ExecutionPolicy Bypass -File scripts\iniciar.ps1 -Red     # -Red solo si va a leer el QR con el celular
```

Tenga a mano estos códigos de barras (se escriben en el lector del despacho y se presiona Enter):

| Código | Producto | Promoción que se ve |
|--------|----------|---------------------|
| `7401006000016` | Coca-Cola 600 ml | 2.ª a mitad de precio (lleve 2) |
| `7401001000028` | Corbatitas 200 g | Mayoreo en Pastas (lleve 3) |
| `7401004000018` | Crema pura 450 ml | Gana puntos (Lácteos) |
| `7401003000019` | Jamón de pavo 250 g | — |

---

## Parte 1 · Cajero (requerimientos 1, 3, 5, 6, 8, 9 y 10)

1. **Acceso** (req. 1) — Inicie sesión con `cajero / cajero123`. Explique que el usuario, la contraseña cifrada y
   los permisos se validan contra la base de datos.
2. **Menú interactivo por rol** (req. 9) — Señale que el cajero solo ve *Inicio, Punto de venta, Caja y
   Devoluciones*, y el indicador **Caja abierta** en la barra superior.
3. **Apertura de caja** (req. 10) — Entre a *Caja*: la caja ya está abierta con Q 300.00. Muestre los indicadores
   (monto inicial, ventas, devoluciones, efectivo esperado).
4. **Despacho** (req. 3) — *Punto de venta › Despacho*: hay una venta en proceso. Escriba `7401006000016` y
   Enter: la línea suma la cantidad, aparece el descuento de la promoción y se recalculan el subtotal por línea y el
   total.
5. **Cobro** (req. 5) — Presione **Cobrar (F9)**. Muestre las tres formas de pago. En efectivo use el teclado
   numérico o los montos rápidos y vea el **cambio**. Confirme.
6. **Facturación** (req. 6) — Ingrese el NIT `7894561-2` (Familia Castillo) o use *CF*. Genere la factura: datos
   del cliente, todos los productos, descuentos, total y **puntos ganados**.
7. **Código QR** (req. 8) — Escanee el QR de la factura con el celular (o haga clic en el código): se abre la
   página pública con la promoción ganada, su vigencia y que es de un solo uso.

## Parte 2 · Devolución y corte (requerimientos 4 y 10)

8. **Devolución** (req. 4) — *Devoluciones › Nueva devolución*: busque la factura recién emitida, elija 1 unidad,
   motivo *Daño* y registre. Explique: el producto sale como **baja** en el kardex y el reembolso sale de la caja.
9. **Corte de caja** (req. 10) — *Caja*: ingrese el efectivo contado (el mismo que *Efectivo esperado*) y cierre.
   Se muestra esperado, contado y **diferencia**.

## Parte 3 · Administrador (requerimientos 2, 7 y 10a)

10. Cierre sesión y entre con `admin / admin123`: el menú ahora muestra todos los módulos.
11. **Catálogos** (req. 2) — *Catálogos › Productos*: filtre por familia con los chips; abra una ficha (existencia,
    kardex, activo). Muestre *Clientes*, *Proveedores* y *Precios* (edición en línea de precio normal y de mayoreo).
12. **Core de Consumo Masivo** — *Catálogos › Sincronizar con el Core*: sincronice y muestre el resultado (sistema
    externo existente, conectado mediante un puerto y un adaptador).
13. **Inventario** — *Inventario › Compras*: registre una compra; luego *Existencias y kardex* para ver la entrada.
14. **Promociones** (req. 10a) — *Promociones*: los cinco tipos (producto gratuito, vale de regalo, 2.º a mitad de
    precio, mayoreo y puntos), automáticas o entregadas por QR. Muestre *Cupones QR emitidos*.
15. **Reportes** (req. 7) — *Reportes*: corte de caja (cierre del día), devoluciones, clientes y compras; ábralos en
    pantalla y en **PDF**.
16. **Seguridad** — *Administración › Roles y permisos*: los interruptores definen qué módulos ve cada rol.

## Cierre (1 minuto)

- Mostrar las pruebas automáticas: `php -d extension=pdo_pgsql -d extension=pgsql vendor\phpunit\phpunit\phpunit`
  → 17 pruebas en verde.
- Repositorio: <https://github.com/AndrewUMG/pos-las-casas-del-rincon> · Kanban:
  <https://github.com/users/AndrewUMG/projects/1>
