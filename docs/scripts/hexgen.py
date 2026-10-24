"""Diagramas de Arquitectura Hexagonal - Sistema POS e Inventario, Las Casas del Rincón Guatemalteco.
Estilo tomado de la presentación de referencia (fondo oscuro, rojo carmesí, Bookman + Calibri)."""
import html, math, os, sys

W, H = 1920, 1080
BG, CARD, LINE, GRAY, RED, LIGHT, PINK = "#121212", "#1F1F1F", "#333333", "#9A9A9A", "#E4032E", "#F4F4F4", "#FFE3E8"
SERIF = "'Bookman Old Style', 'Rockwell', Georgia, serif"
SANS = "Calibri, 'Segoe UI', Arial, sans-serif"
MONO = "'Courier New', Consolas, monospace"
FOOT = "ARQUITECTURA HEXAGONAL · LAS CASAS DEL RINCÓN GUATEMALTECO · LARAVEL + POSTGRESQL"


def esc(t):
    return html.escape(t, quote=True)


def wrap(text, maxw, fs, k=0.5):
    per = max(4, int(maxw / (fs * k)))
    out, cur = [], ""
    for w in text.split():
        if cur and len(cur) + 1 + len(w) > per:
            out.append(cur)
            cur = w
        else:
            cur = (cur + " " + w).strip()
    return out + ([cur] if cur else [])


def hexpts(cx, cy, w, h):
    return [(cx - w / 2, cy), (cx - w / 4, cy - h / 2), (cx + w / 4, cy - h / 2),
            (cx + w / 2, cy), (cx + w / 4, cy + h / 2), (cx - w / 4, cy + h / 2)]


def poly(pts):
    return " ".join(f"{x:.1f},{y:.1f}" for x, y in pts)


# ------------------------------------------------------------------ iconos (glifos blancos en hexágono rojo)
def icon(kind, x, y, r=20, bg=RED, fg="#fff"):
    s = [f'<polygon points="{poly(hexpts(x, y, r*2.2, r*1.95))}" fill="{bg}"/>']
    a = f'stroke="{fg}" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"'
    if kind == "web":
        s += [f'<circle cx="{x}" cy="{y}" r="9" {a}/>', f'<ellipse cx="{x}" cy="{y}" rx="4" ry="9" {a}/>',
              f'<line x1="{x-9}" y1="{y}" x2="{x+9}" y2="{y}" {a}/>']
    elif kind == "db":
        s += [f'<ellipse cx="{x}" cy="{y-6}" rx="8" ry="3.5" {a}/>',
              f'<path d="M{x-8},{y-6} v12 a8,3.5 0 0 0 16,0 v-12" {a}/>', f'<path d="M{x-8},{y} a8,3.5 0 0 0 16,0" {a}/>']
    elif kind == "code":
        s += [f'<path d="M{x-4},{y-7} l-6,7 l6,7 M{x+4},{y-7} l6,7 l-6,7" {a}/>']
    elif kind == "qr":
        for dx, dy in [(-8, -8), (2, -8), (-8, 2)]:
            s.append(f'<rect x="{x+dx}" y="{y+dy}" width="6" height="6" {a}/>')
        s.append(f'<rect x="{x+3}" y="{y+3}" width="4" height="4" fill="{fg}"/>')
    elif kind == "pdf":
        s += [f'<path d="M{x-7},{y-9} h9 l5,5 v13 h-14 Z" {a}/>', f'<line x1="{x-4}" y1="{y+1}" x2="{x+4}" y2="{y+1}" {a}/>',
              f'<line x1="{x-4}" y1="{y+5}" x2="{x+4}" y2="{y+5}" {a}/>']
    elif kind == "phone":
        s += [f'<rect x="{x-6}" y="{y-10}" width="12" height="20" rx="2.5" {a}/>', f'<line x1="{x-2}" y1="{y+6}" x2="{x+2}" y2="{y+6}" {a}/>']
    elif kind == "lock":
        s += [f'<rect x="{x-8}" y="{y-2}" width="16" height="11" rx="2" {a}/>', f'<path d="M{x-5},{y-2} v-4 a5,5 0 0 1 10,0 v4" {a}/>']
    elif kind == "cloud":
        s += [f'<path d="M{x-8},{y+6} a5,5 0 0 1 0,-10 a7,7 0 0 1 13,-1 a5,5 0 0 1 3,11 Z" {a}/>']
    elif kind == "link":
        s += [f'<path d="M{x-9},{y-3} h14 l-4,-4 M{x+9},{y+4} h-14 l4,4" {a}/>']
    elif kind == "key":
        s += [f'<circle cx="{x-4}" cy="{y}" r="4.5" {a}/>', f'<path d="M{x},{y} h9 v4 M{x+5},{y} v3" {a}/>']
    elif kind == "cube":
        s += [f'<path d="M{x},{y-10} l9,5 v10 l-9,5 l-9,-5 v-10 Z M{x-9},{y-5} l9,5 l9,-5 M{x},{y} v10" {a}/>']
    elif kind == "chart":
        s += [f'<path d="M{x-9},{y+8} h18 M{x-6},{y+8} v-6 M{x},{y+8} v-13 M{x+6},{y+8} v-9" {a}/>']
    elif kind == "cash":
        s += [f'<rect x="{x-10}" y="{y-6}" width="20" height="12" rx="2" {a}/>', f'<circle cx="{x}" cy="{y}" r="3" {a}/>']
    return "".join(s)


class Slide:
    def __init__(self, eyebrow, title, subtitle=None, page=1):
        self.p = [f'<rect width="{W}" height="{H}" fill="{BG}"/>']
        # hexágonos decorativos
        self.p.append(f'<polygon points="{poly(hexpts(1830, 40, 300, 260))}" fill="none" stroke="#262626" stroke-width="2"/>')
        self.p.append(f'<polygon points="{poly(hexpts(60, 1010, 170, 150))}" fill="none" stroke="#222" stroke-width="2"/>')
        self.p.append(f'<text x="60" y="66" font-size="17" font-weight="700" letter-spacing="4" fill="{RED}" font-family="{SANS}">{esc(eyebrow.upper())}</text>')
        self.p.append(f'<text x="60" y="122" font-size="46" font-weight="700" fill="#fff" font-family="{SERIF}">{esc(title)}</text>')
        if subtitle:
            self.p.append(f'<text x="60" y="160" font-size="20" font-style="italic" fill="{GRAY}" font-family="{SANS}">{esc(subtitle)}</text>')
        self.p.append(f'<text x="60" y="{H-28}" font-size="13" letter-spacing="2.5" fill="#6d6d6d" font-family="{SANS}">{esc(FOOT)}</text>')
        self.p.append(f'<text x="{W-60}" y="{H-28}" font-size="13" fill="#6d6d6d" text-anchor="end" font-family="{SANS}">{page:02d}</text>')

    def add(self, s):
        self.p.append(s)

    def text(self, x, y, t, fs=16, fill=LIGHT, weight=400, anchor="start", font=SANS, italic=False, ls=0):
        st = ' font-style="italic"' if italic else ""
        lsp = f' letter-spacing="{ls}"' if ls else ""
        self.p.append(f'<text x="{x}" y="{y}" font-size="{fs}" font-weight="{weight}" fill="{fill}" text-anchor="{anchor}"{st}{lsp} font-family="{font}">{esc(t)}</text>')

    def colhead(self, x, y, t, sub=None):
        self.text(x, y, t.upper(), 16, "#fff", 700, ls=1.5)
        if sub:
            self.text(x, y + 22, sub, 14, GRAY, italic=True)

    def card(self, x, y, w, h, ic, title, sub=None, accent=False):
        self.add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="{CARD}" stroke="{RED if accent else LINE}" stroke-width="1.5"/>')
        self.add(icon(ic, x + 34, y + h / 2))
        lines = wrap(title, w - 90, 17, 0.52)
        subl = wrap(sub, w - 90, 14, 0.5) if sub else []
        tot = len(lines) * 21 + len(subl) * 18
        yy = y + h / 2 - tot / 2 + 15
        for l in lines:
            self.text(x + 66, yy, l, 17, "#fff", 700)
            yy += 21
        for l in subl:
            self.text(x + 66, yy, l, 14, GRAY)
            yy += 18

    def port(self, x, y, w, h, name, desc):
        self.add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="4" fill="{CARD}" stroke="{LINE}" stroke-width="1"/>')
        self.add(f'<rect x="{x}" y="{y}" width="5" height="{h}" fill="{RED}"/>')
        self.text(x + 18, y + h / 2 - 3, name, 15, "#fff", 700, font=MONO)
        if desc:
            self.text(x + 18, y + h / 2 + 16, desc, 13, GRAY, italic=True)

    def arrow(self, x1, x2, y, label=None, sub=None):
        hh, tip = 26, 30
        pts = [(x1, y - hh / 2), (x2 - tip, y - hh / 2), (x2 - tip, y - hh), (x2, y), (x2 - tip, y + hh), (x2 - tip, y + hh / 2), (x1, y + hh / 2), (x1 + 14, y)]
        self.add(f'<polygon points="{poly(pts)}" fill="{RED}"/>')
        if label:
            self.text((x1 + x2) / 2, y - 36, label.upper(), 12, RED, 700, "middle", ls=1.2)
        if sub:
            self.text((x1 + x2) / 2, y + 48, sub, 12, GRAY, anchor="middle", italic=True)

    def vline_arrow(self, x, y1, y2):
        self.add(f'<line x1="{x}" y1="{y1}" x2="{x}" y2="{y2-14}" stroke="{RED}" stroke-width="5"/>')
        self.add(f'<polygon points="{x-12},{y2-16} {x+12},{y2-16} {x},{y2}" fill="{RED}"/>')

    def domain_hex(self, cx, cy, w, h, title, lines, sub=None, fs=16):
        self.add(f'<polygon points="{poly(hexpts(cx, cy, w + 46, h + 40))}" fill="none" stroke="#3a3a3a" stroke-width="2"/>')
        self.add(f'<polygon points="{poly(hexpts(cx, cy, w, h))}" fill="{RED}"/>')
        n = len(lines)
        block = 34 + 30 + (22 if sub else 0) + n * (fs + 6)
        y = cy - block / 2 + 10
        self.add(icon("cube", cx, y, 17, bg="#ffffff", fg=RED))
        y += 56
        self.text(cx, y, title, 30, "#fff", 700, "middle", SERIF)
        y += 8
        if sub:
            y += 22
            self.text(cx, y, sub, 14, PINK, anchor="middle", italic=True)
        y += 8
        for l in lines:
            y += fs + 6
            self.text(cx, y, l, fs, "#fff", anchor="middle")

    def bullets(self, x, y, w, items, fs=16, gap=10, color=LIGHT):
        for it in items:
            ls = wrap(it, w - 30, fs, 0.48)
            self.add(f'<polygon points="{poly(hexpts(x + 7, y - 5, 14, 12))}" fill="{RED}"/>')
            for l in ls:
                self.text(x + 26, y, l, fs, color)
                y += fs + 5
            y += gap
        return y

    def panel(self, x, y, w, h, head, red=False):
        self.add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="{RED if red else CARD}" stroke="{RED if red else LINE}" stroke-width="1.5"/>')
        self.text(x + 22, y + 32, head.upper(), 13, "#fff" if red else RED, 700, ls=2)

    def svg(self):
        return f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}" height="{H}" viewBox="0 0 {W} {H}">' + "".join(self.p) + "</svg>"


# ==================================================================== VISTA GENERAL
def general():
    s = Slide("Arquitectura hexagonal · vista general", "Sistema POS e Inventario",
              "Puertos y adaptadores en Laravel: el negocio de Las Casas del Rincón Guatemalteco aislado de la tecnología", 1)
    s.colhead(60, 236, "Adaptadores primarios", "Driving — inician la acción")
    prim = [("web", "Controladores web Laravel", "Interfaz Web del POS (Blade) · cajero y administrador"),
            ("code", "Endpoints AJAX (JSON)", "Lectura de producto por línea en el despacho"),
            ("phone", "Controlador público de Promoción QR", "Celular del cliente al leer el QR de la factura"),
            ("lock", "Middleware de acceso", "Sesión y permisos del menú por rol")]
    for i, (ic, t, sb) in enumerate(prim):
        s.card(60, 290 + i * 132, 360, 112, ic, t, sb)
    s.colhead(1500, 236, "Adaptadores secundarios", "Driven — el dominio los necesita")
    sec = [("db", "Repositorios Eloquent", "PostgreSQL · esquema relacional"),
           ("cloud", "Cliente HTTP del Core", "Core de Empresas de Consumo Masivo (existente)"),
           ("qr", "Generador de código QR", "simple-qrcode · token de promoción"),
           ("pdf", "Generador de PDF", "DomPDF · factura y reportes"),
           ("key", "Hasher de contraseñas", "Bcrypt (Laravel Hash)")]
    for i, (ic, t, sb) in enumerate(sec):
        s.card(1500, 290 + i * 106, 360, 92, ic, t, sb)

    cx, cy = 960, 575
    s.add(f'<polygon points="{poly(hexpts(cx, cy, 680, 600))}" fill="#181818" stroke="{RED}" stroke-width="2.5" stroke-dasharray="12,8"/>')
    s.text(cx, cy - 238, "CAPA DE APLICACIÓN", 15, RED, 700, "middle", ls=2.5)
    s.text(cx, cy - 216, "Casos de uso · servicios de aplicación", 14, GRAY, anchor="middle", italic=True)
    s.text(cx - 290, cy - 8, "Puertos de", 14, "#fff", 700, "middle")
    s.text(cx - 290, cy + 12, "entrada", 14, "#fff", 700, "middle")
    s.text(cx + 290, cy - 8, "Puertos de", 14, "#fff", 700, "middle")
    s.text(cx + 290, cy + 12, "salida", 14, "#fff", 700, "middle")
    s.text(cx, cy + 250, "Inyección de dependencias: HexagonalServiceProvider", 14, GRAY, anchor="middle", italic=True)
    s.add(f'<polygon points="{poly(hexpts(cx, cy, 430, 390))}" fill="{RED}"/>')
    s.add(icon("cube", cx, cy - 140, 17, bg="#fff", fg=RED))
    s.text(cx, cy - 92, "DOMINIO", 32, "#fff", 700, "middle", SERIF)
    s.text(cx, cy - 66, "10 bounded contexts (DDD)", 15, PINK, anchor="middle", italic=True)
    left = ["Seguridad y Accesos", "Catálogos", "Inventario y Compras", "Caja", "Despacho"]
    right = ["Cobros", "Facturación", "Devoluciones", "Promociones", "Reportes"]
    for i, (a, b) in enumerate(zip(left, right)):
        s.text(cx - 12, cy - 28 + i * 28, a, 16, "#fff", anchor="end")
        s.text(cx + 12, cy - 28 + i * 28, b, 16, "#fff")
    s.add(f'<line x1="{cx}" y1="{cy-46}" x2="{cx}" y2="{cy+92}" stroke="#ff8fa3" stroke-width="1"/>')
    s.text(cx, cy + 128, "Entidades · Value Objects · Reglas", 14, PINK, anchor="middle", italic=True)

    s.arrow(440, 610, cy - 60, "Puerto de entrada", "interfaz «inbound»")
    s.arrow(1310, 1480, cy - 60, "Puerto de salida", "interfaz «outbound»")
    s.add(f'<polygon points="{poly(hexpts(76, 990, 14, 12))}" fill="{RED}"/>')
    s.text(96, 995, "La dependencia siempre apunta hacia adentro: los adaptadores (Laravel, Eloquent, DomPDF, HTTP) conocen al dominio; el dominio no conoce a ningún framework.", 16, GRAY, italic=True)
    return s


# ==================================================================== PLANTILLA DE MÓDULO
def module(page, num, title, subtitle, req, prim, inp, ent, out, sec, rules):
    s = Slide(f"Módulo {num:02d} · Bounded context", title, subtitle, page)
    top = 250
    s.colhead(60, top - 14, "Adaptadores primarios", "Driving")
    s.colhead(460, top - 14, "Puertos de entrada", "Casos de uso (interfaces)")
    s.colhead(1150, top - 14, "Puertos de salida", "Lo que el dominio necesita")
    s.colhead(1530, top - 14, "Adaptadores secundarios", "Driven")
    band_y, band_h = top + 40, 440

    def stack(items, x, w, fn, minh=70, maxh=120, gap=14):
        n = len(items)
        h = min(maxh, max(minh, (band_h - gap * (n - 1)) / n))
        tot = n * h + gap * (n - 1)
        y = band_y + (band_h - tot) / 2
        for it in items:
            fn(x, y, w, h, *it)
            y += h + gap

    stack(prim, 60, 330, lambda x, y, w, h, ic, t, sb: s.card(x, y, w, h, ic, t, sb), maxh=118)
    stack(inp, 460, 300, lambda x, y, w, h, n, d: s.port(x, y, w, h, n, d), minh=58, maxh=78, gap=12)
    stack(out, 1150, 300, lambda x, y, w, h, n, d: s.port(x, y, w, h, n, d), minh=58, maxh=78, gap=12)
    stack(sec, 1530, 330, lambda x, y, w, h, ic, t, sb: s.card(x, y, w, h, ic, t, sb), maxh=118)
    cy = band_y + band_h / 2
    s.arrow(395, 455, cy)
    s.domain_hex(955, cy, 300, 400, "DOMINIO", ent, fs=16)
    # flechas entre puertos y hexágono
    s.arrow(768, 800, cy)
    s.arrow(1112, 1146, cy)
    s.arrow(1456, 1526, cy)

    by = band_y + band_h + 40
    s.panel(60, by, 1180, 1000 - by, "Reglas de negocio del dominio")
    s.bullets(84, by + 74, 1140, rules, fs=19, gap=12)
    s.panel(1270, by, 590, 1000 - by, "Requisito del proyecto cubierto", red=True)
    yy = by + 72
    for l in wrap(req, 540, 21, 0.48):
        s.text(1292, yy, l, 21, "#fff")
        yy += 30
    return s


MODULES = [
    dict(num=1, title="Seguridad y Accesos", subtitle="Interfaz de acceso validada en base de datos y menú interactivo por rol",
         req="Punto 1: interfaz de acceso para el usuario (todo a nivel de base de datos). Punto 9: menú interactivo con las opciones de cada módulo.",
         prim=[("web", "LoginController", "Formulario Blade · POST /login"), ("lock", "Middleware VerificarRol", "Protege las rutas de cada opción del menú"),
               ("web", "UsuarioController", "Administración de usuarios y roles")],
         inp=[("IniciarSesionUseCase", "valida credenciales"), ("CerrarSesionUseCase", "finaliza la sesión"),
              ("GestionarUsuariosUseCase", "alta, baja y cambios"), ("AsignarPermisosUseCase", "opciones del menú por rol")],
         ent=["Usuario", "Rol", "Permiso (opción de menú)", "Credencial"],
         out=[("UsuarioRepository", "persistencia de usuarios"), ("RolRepository", "roles y permisos"), ("PasswordHasher", "cifrado de contraseñas")],
         sec=[("db", "EloquentUsuarioRepository", "PostgreSQL · tabla usuarios"), ("db", "EloquentRolRepository", "PostgreSQL · roles y permisos"),
              ("key", "BcryptPasswordHasher", "Laravel Hash")],
         rules=["Las credenciales se validan contra la base de datos; nunca se guardan contraseñas en texto plano.",
                "Un usuario inactivo no puede iniciar sesión.",
                "El menú muestra únicamente las opciones permitidas para el rol (Cajero / Administrador)."]),
    dict(num=2, title="Catálogos", subtitle="Clientes, proveedores, productos por familia y precios",
         req="Punto 2: módulos de catálogos (clientes, proveedores, productos y precios). Integración con el Core de Empresas de Consumo Masivo (existente).",
         prim=[("web", "ClienteController", "CRUD Blade de clientes"), ("web", "ProveedorController", "CRUD Blade de proveedores"),
               ("web", "ProductoController", "Productos, familias y precios")],
         inp=[("GestionarClientesUseCase", "NIT, nombre, dirección"), ("GestionarProveedoresUseCase", "datos del proveedor"),
              ("GestionarProductosUseCase", "código, familia, precio"), ("ActualizarPreciosUseCase", "precio normal y mayoreo"),
              ("SincronizarCatalogoUseCase", "productos del Core")],
         ent=["Cliente", "Proveedor", "Producto", "Familia", "Precio"],
         out=[("ClienteRepository", "persistencia"), ("ProveedorRepository", "persistencia"), ("ProductoRepository", "persistencia"),
              ("CatalogoCorePort", "consulta al Core")],
         sec=[("db", "Repositorios Eloquent", "PostgreSQL · clientes, proveedores, productos"),
              ("cloud", "CoreConsumoHttpAdapter", "Laravel HTTP Client → Core existente")],
         rules=["Familias: Pastas, Cárnicos, Embutidos, Lácteos, Golosinas, Aguas gaseosas y Carbonatados.",
                "El código de producto es único y todo producto pertenece a una familia y a un proveedor.",
                "El precio debe ser mayor a cero y el precio de mayoreo no puede superar al precio normal."]),
    dict(num=3, title="Inventario y Compras", subtitle="Control del inventario que ingresa al negocio y del que se vende",
         req="Control de inventario de ingresos y egresos del negocio; base para el reporte de compras de productos para existencias (punto 7).",
         prim=[("web", "CompraController", "Registro de compras a proveedores"), ("web", "InventarioController", "Existencias y kardex")],
         inp=[("RegistrarCompraUseCase", "ingreso de mercadería"), ("ConsultarExistenciasUseCase", "stock por producto"),
              ("ConsultarKardexUseCase", "historial de movimientos"), ("AjustarExistenciasUseCase", "usado por Despacho y Devoluciones")],
         ent=["Compra", "DetalleCompra", "Existencia", "MovimientoInventario"],
         out=[("CompraRepository", "persistencia de compras"), ("ExistenciaRepository", "stock actual"), ("MovimientoRepository", "kardex")],
         sec=[("db", "EloquentCompraRepository", "PostgreSQL"), ("db", "EloquentExistenciaRepository", "PostgreSQL"),
              ("db", "EloquentMovimientoRepository", "PostgreSQL")],
         rules=["Toda entrada (compra) o salida (venta, devolución por daño o vencimiento) genera un movimiento en el kardex.",
                "La existencia de un producto nunca puede quedar negativa.",
                "Una compra aumenta la existencia y registra el costo unitario por proveedor."]),
    dict(num=4, title="Caja", subtitle="Apertura con monto inicial y cierre con corte de caja",
         req="Punto 10: apertura de caja (con cuánto dinero inicia) y cierre (con cuánto cierra al momento del corte de caja).",
         prim=[("cash", "CajaController", "Pantallas de apertura y corte de caja")],
         inp=[("AbrirCajaUseCase", "monto inicial"), ("RegistrarMovimientoUseCase", "ingresos y egresos"), ("CerrarCajaUseCase", "monto final y corte")],
         ent=["Caja", "AperturaCaja", "MovimientoCaja", "CorteCaja"],
         out=[("CajaRepository", "persistencia de la caja"), ("MovimientoCajaRepository", "ingresos y egresos")],
         sec=[("db", "EloquentCajaRepository", "PostgreSQL"), ("db", "EloquentMovimientoCajaRepository", "PostgreSQL")],
         rules=["No se puede despachar ni cobrar sin una caja abierta; un cajero tiene una sola caja abierta a la vez.",
                "Esperado = monto inicial + ingresos − egresos (devoluciones); el corte registra la diferencia contra el monto contado.",
                "Una caja cerrada no admite nuevos movimientos."]),
    dict(num=5, title="Despacho (Punto de Venta)", subtitle="Lectura por línea de producto, subtotales por línea y total de la compra",
         req="Punto 3: interfaz de despacho (cobro de producto, lectura por línea, cobro por línea, subtotales por línea y total de la compra).",
         prim=[("web", "VentaController", "Pantalla de despacho (Blade)"), ("code", "LecturaProductoController", "AJAX · POST /ventas/{id}/lineas")],
         inp=[("IniciarVentaUseCase", "nueva venta en caja abierta"), ("AgregarProductoUseCase", "lectura por línea"),
              ("QuitarLineaUseCase", "corrige una línea"), ("CalcularTotalesUseCase", "subtotales y total")],
         ent=["Venta (raíz de agregado)", "LineaVenta", "Producto (referencia)", "Total"],
         out=[("VentaRepository", "persistencia de la venta"), ("ProductoRepository", "precio y existencia"),
              ("MotorPromocionesPort", "→ Promociones"), ("InventarioPort", "→ Inventario")],
         sec=[("db", "EloquentVentaRepository", "PostgreSQL"), ("db", "EloquentProductoRepository", "PostgreSQL"),
              ("link", "PromocionesAdapter", "Llamada al módulo Promociones"), ("link", "InventarioAdapter", "Llamada al módulo Inventario")],
         rules=["Subtotal de línea = cantidad × precio unitario − descuento; total = suma de subtotales.",
                "Al agregar un producto se valida su existencia y se aplican las promociones vigentes.",
                "Al finalizar la venta se descuentan las existencias a través del puerto de inventario."]),
    dict(num=6, title="Cobros", subtitle="Pago en efectivo, tarjeta de crédito o tarjeta de débito",
         req="Punto 5: interfaz de cobro, que puede ser por tarjeta de crédito, débito o efectivo.",
         prim=[("cash", "CobroController", "Pantalla de cobro (Blade)")],
         inp=[("CobrarVentaUseCase", "registra el pago"), ("CalcularCambioUseCase", "pago en efectivo")],
         ent=["Pago", "MetodoPago", "(Efectivo · Crédito · Débito)", "Cambio"],
         out=[("PagoRepository", "persistencia del pago"), ("VentaRepository", "venta a cobrar"), ("CajaPort", "→ Caja")],
         sec=[("db", "EloquentPagoRepository", "PostgreSQL"), ("db", "EloquentVentaRepository", "PostgreSQL"),
              ("link", "CajaAdapter", "Registra el ingreso en la caja")],
         rules=["Efectivo: el monto recibido debe ser mayor o igual al total y se calcula el cambio.",
                "Tarjeta de crédito o débito: se registra el tipo de tarjeta y la referencia del voucher.",
                "Todo cobro registra un ingreso en la caja abierta y marca la venta como pagada."]),
    dict(num=7, title="Facturación con Código QR", subtitle="Factura con datos del cliente, detalle de productos y QR de promoción",
         req="Punto 6: facturación con los datos del cliente y todos los productos comprados. Punto 8: código QR en la factura que indica la promoción.",
         prim=[("web", "FacturaController", "Datos del cliente, vista e impresión")],
         inp=[("GenerarFacturaUseCase", "a partir de la venta pagada"), ("ImprimirFacturaUseCase", "PDF imprimible")],
         ent=["Factura", "DetalleFactura", "DatosCliente (NIT)", "CodigoQR"],
         out=[("FacturaRepository", "persistencia"), ("AsignarPromocionPort", "→ Promociones"), ("GeneradorQrPort", "imagen QR"),
              ("GeneradorPdfPort", "documento")],
         sec=[("db", "EloquentFacturaRepository", "PostgreSQL"), ("link", "PromocionesAdapter", "Emite el cupón de promoción"),
              ("qr", "SimpleQrCodeAdapter", "simple-qrcode"), ("pdf", "DomPdfAdapter", "DomPDF")],
         rules=["Solo se factura una venta pagada; si el cliente no da NIT se usa «CF» (consumidor final).",
                "La factura contiene serie, número, fecha, datos del cliente, detalle, subtotales y total.",
                "El QR contiene la URL pública con un token único que identifica la promoción asignada."]),
    dict(num=8, title="Devoluciones", subtitle="Devolución de producto por daño o vencimiento",
         req="Punto 4: interfaz de devoluciones (proceso de devolución de producto por algún tipo de daño o vencimiento).",
         prim=[("web", "DevolucionController", "Búsqueda de factura y registro")],
         inp=[("ProcesarDevolucionUseCase", "registra la devolución"), ("ConsultarFacturaUseCase", "productos devolvibles")],
         ent=["Devolucion", "DetalleDevolucion", "MotivoDevolucion", "(Daño · Vencimiento)"],
         out=[("DevolucionRepository", "persistencia"), ("FacturaRepository", "consulta de la venta"), ("InventarioPort", "→ Inventario"),
              ("CajaPort", "→ Caja")],
         sec=[("db", "EloquentDevolucionRepository", "PostgreSQL"), ("db", "EloquentFacturaRepository", "PostgreSQL"),
              ("link", "InventarioAdapter", "Registra la baja en el kardex"), ("link", "CajaAdapter", "Registra el egreso")],
         rules=["Solo se devuelven productos de una factura existente y la cantidad no puede superar la vendida.",
                "El motivo es obligatorio: daño o vencimiento; el producto devuelto se registra como baja en el kardex.",
                "El reembolso se registra como egreso de la caja abierta."]),
    dict(num=9, title="Promociones y Lectura del QR", subtitle="Descuentos que se obtienen al leer el código QR de la factura con el celular",
         req="Punto 8 y 10a: al leer el QR se indica la promoción: producto gratuito, vale de regalo, segundo a mitad de precio, compra por mayoreo o puntos por producto.",
         prim=[("web", "PromocionController", "Configuración (administrador)"), ("phone", "PromocionQrController", "Pública · GET /promocion/{token}")],
         inp=[("ConfigurarPromocionUseCase", "tipos y vigencia"), ("ConsultarPromocionQrUseCase", "lectura con el celular"),
              ("AplicarPromocionUseCase", "usado por Despacho"), ("CanjearPuntosUseCase", "pago con puntos")],
         ent=["Promocion (5 tipos)", "CuponQR (token)", "PuntosCliente", "ReglaDescuento"],
         out=[("PromocionRepository", "promociones vigentes"), ("CuponRepository", "cupones emitidos"), ("PuntosRepository", "saldo de puntos")],
         sec=[("db", "EloquentPromocionRepository", "PostgreSQL"), ("db", "EloquentCuponRepository", "PostgreSQL"),
              ("db", "EloquentPuntosRepository", "PostgreSQL")],
         rules=["Tipos: producto gratuito, vale de regalo, segundo producto a mitad de precio, compra por mayoreo y puntos por producto.",
                "Mayoreo: 3 o más productos de la misma clase obtienen el precio de mayoreo del producto promocionado.",
                "Un cupón QR es válido una sola vez y dentro de su vigencia; los puntos se usan en la próxima compra."]),
    dict(num=10, title="Reportes", subtitle="Cierre del día, devoluciones, clientes y compras para existencias",
         req="Punto 7: reportes de cierre del día (corte de caja), devoluciones, clientes y compras de productos para existencias.",
         prim=[("chart", "ReporteController", "Filtros por fecha · vista e impresión")],
         inp=[("ReporteCorteCajaQuery", "ventas e ingresos del día"), ("ReporteDevolucionesQuery", "por motivo y fecha"),
              ("ReporteClientesQuery", "clientes y compras"), ("ReporteComprasQuery", "compras para existencias")],
         ent=["ReporteCorteCaja", "ReporteDevoluciones", "ReporteClientes", "RangoFechas"],
         out=[("ReportesQueryPort", "consultas de lectura"), ("ExportadorPdfPort", "impresión / PDF")],
         sec=[("db", "ReportesSqlAdapter", "Query Builder · PostgreSQL"), ("pdf", "DomPdfAdapter", "DomPDF")],
         rules=["Los reportes son de solo lectura: no modifican el estado del negocio.",
                "Todos se filtran por rango de fechas; el corte de caja agrupa las ventas por método de pago.",
                "Se pueden visualizar en pantalla o exportar a PDF para imprimir."]),
]


# ==================================================================== FLUJO DE UNA VENTA
def flujo():
    s = Slide("Mapa de contextos", "Flujo completo de una venta",
              "Cómo colaboran los bounded contexts a través de puertos, sin conocerse entre sí", 12)
    steps = [("Caja", "AbrirCajaUseCase", "El cajero registra el monto inicial."),
             ("Despacho", "AgregarProductoUseCase", "Lectura por línea, subtotales y total."),
             ("Promociones", "AplicarPromocionUseCase", "Descuentos, mayoreo o puntos."),
             ("Inventario", "AjustarExistenciasUseCase", "Descuenta las existencias vendidas."),
             ("Cobros", "CobrarVentaUseCase", "Efectivo, crédito o débito."),
             ("Facturación", "GenerarFacturaUseCase", "Datos del cliente + código QR."),
             ("Cliente", "ConsultarPromocionQrUseCase", "Lee el QR con el celular.")]
    y = 390
    for i, (ctx, uc, d) in enumerate(steps):
        cx = 175 + i * 262
        last = i == len(steps) - 1
        s.add(f'<polygon points="{poly(hexpts(cx, y, 210, 185))}" fill="{CARD if last else RED}" stroke="{RED}" stroke-width="3"/>')
        s.text(cx, y - 32, f"{i+1}", 34, "#fff", 700, "middle", SERIF)
        s.text(cx, y + 10, ctx, 21, "#fff", 700, "middle")
        s.text(cx, y + 36, "contexto" if not last else "actor externo", 14, PINK, anchor="middle", italic=True)
        s.text(cx, y + 130, uc, 14, "#fff", 700, "middle", MONO)
        for k, l in enumerate(wrap(d, 210, 15)):
            s.text(cx, y + 156 + k * 19, l, 15, GRAY, anchor="middle")
        if not last:
            s.arrow(cx + 104, cx + 158, y)
    s.panel(60, 640, 1800, 360, "Comunicación entre contextos (puerto de salida → adaptador → contexto destino)")
    rows = [("Despacho", "MotorPromocionesPort", "PromocionesAdapter", "Promociones"),
            ("Despacho", "InventarioPort", "InventarioAdapter", "Inventario y Compras"),
            ("Cobros", "CajaPort", "CajaAdapter", "Caja"),
            ("Facturación", "AsignarPromocionPort", "PromocionesAdapter", "Promociones"),
            ("Devoluciones", "InventarioPort · CajaPort", "InventarioAdapter · CajaAdapter", "Inventario · Caja")]
    xs = [90, 420, 860, 1340]
    for x, h in zip(xs, ["Contexto origen", "Puerto de salida", "Adaptador", "Contexto destino"]):
        s.text(x, 712, h.upper(), 13, GRAY, 700, ls=1.5)
    for r, row in enumerate(rows):
        yy = 752 + r * 46
        s.add(f'<line x1="90" y1="{yy+16}" x2="1830" y2="{yy+16}" stroke="{LINE}"/>')
        for c, (x, t) in enumerate(zip(xs, row)):
            s.text(x, yy, t, 17, "#fff", 700 if c in (0, 3) else 400, font=MONO if c in (1, 2) else SANS)
        for x in (370, 810, 1290):
            s.text(x, yy, "→", 20, RED, 700, "middle")
    return s


# ==================================================================== ESTRUCTURA LARAVEL
def estructura():
    s = Slide("Especificaciones de construcción", "Estructura del proyecto en Laravel",
              "PHP 8 + Laravel (MVC) + PostgreSQL organizados en puertos y adaptadores", 13)
    cols = [
        ("Adaptadores primarios", "Entrada · capa web MVC", [
            "routes/web.php", "app/Infrastructure/Http/", "  Controllers/", "    VentaController.php", "    CobroController.php",
            "    FacturaController.php", "    DevolucionController.php", "    CajaController.php", "    PromocionQrController.php",
            "    ReporteController.php", "  Middleware/VerificarRol.php", "resources/views/  (Blade)", "  menu · despacho · cobro", "  factura · caja · reportes"]),
        ("Núcleo: aplicación + dominio", "Sin dependencias de Laravel", [
            "app/Domain/", "  Venta/  Venta.php", "         LineaVenta.php", "  Caja/   Promocion/   Factura/", "  Devolucion/  Inventario/ ...",
            "app/Application/", "  Venta/", "    Ports/In/", "      AgregarProductoUseCase.php", "    Ports/Out/",
            "      VentaRepository.php", "      InventarioPort.php", "    Services/", "      RegistrarVentaService.php"]),
        ("Adaptadores secundarios", "Salida · tecnología concreta", [
            "app/Infrastructure/", "  Persistence/Eloquent/", "    Models/  (Venta, Producto…)", "    EloquentVentaRepository.php",
            "  External/", "    CoreConsumoHttpAdapter.php", "  Qr/SimpleQrCodeAdapter.php", "  Pdf/DomPdfAdapter.php",
            "  Security/BcryptPasswordHasher.php", "database/migrations/", "  (PostgreSQL)", "config/database.php", "  DB_CONNECTION=pgsql"]),
    ]
    for i, (h, sub, lines) in enumerate(cols):
        x = 60 + i * 610
        s.add(f'<rect x="{x}" y="215" width="580" height="500" rx="6" fill="{CARD}" stroke="{RED if i == 1 else LINE}" stroke-width="1.5"/>')
        s.add(icon(["web", "cube", "db"][i], x + 40, 255))
        s.text(x + 74, 252, h.upper(), 16, "#fff", 700, ls=1.2)
        s.text(x + 74, 274, sub, 14, GRAY, italic=True)
        for k, l in enumerate(lines):
            s.add(f'<text x="{x+30}" y="{320+k*27}" font-size="16" fill="{RED if (l.strip().endswith("/") and " " not in l.strip()) else LIGHT}" xml:space="preserve" font-family="{MONO}">{esc(l)}</text>')
    s.panel(60, 745, 1180, 255, "Ensamblaje con inyección de dependencias")
    code = ["// app/Providers/HexagonalServiceProvider.php",
            "public function register(): void {",
            "    $this->app->bind(VentaRepository::class, EloquentVentaRepository::class);",
            "    $this->app->bind(InventarioPort::class,  InventarioAdapter::class);",
            "    $this->app->bind(GeneradorQrPort::class, SimpleQrCodeAdapter::class);",
            "}"]
    for k, l in enumerate(code):
        s.add(f'<text x="84" y="{808+k*28}" font-size="16" fill="{"#7fb77e" if k == 0 else LIGHT}" xml:space="preserve" font-family="{MONO}">{esc(l)}</text>')
    s.panel(1270, 745, 590, 255, "Regla de dependencia", red=True)
    yy = 810
    for t in ["Domain no importa nada de Laravel.", "Application solo conoce Domain y sus puertos.",
              "Infrastructure implementa los puertos y es la única capa que usa Eloquent, Blade, HTTP o DomPDF."]:
        for l in wrap(t, 520, 18, 0.48):
            s.text(1292, yy, l, 18, "#fff")
            yy += 25
        yy += 8
    return s


if __name__ == "__main__":
    out = sys.argv[1]
    os.makedirs(out, exist_ok=True)
    items = [("HEX-00_Vista_General", general())]
    for i, m in enumerate(MODULES):
        slug = ["Seguridad", "Catalogos", "Inventario_Compras", "Caja", "Despacho", "Cobros", "Facturacion_QR",
                "Devoluciones", "Promociones_QR", "Reportes"][i]
        items.append((f"HEX-{m['num']:02d}_{slug}", module(i + 2, **m)))
    items.append(("HEX-11_Flujo_de_Venta", flujo()))
    items.append(("HEX-12_Estructura_Laravel", estructura()))
    for name, sl in items:
        svg = sl.svg()
        open(os.path.join(out, name + ".svg"), "w", encoding="utf-8").write(svg)
        open(os.path.join(out, name + ".html"), "w", encoding="utf-8").write(
            '<!doctype html><html><head><meta charset="utf-8"><style>html,body{margin:0;overflow:hidden;background:#121212}</style></head><body>' + svg + '</body></html>')
        print(name)
