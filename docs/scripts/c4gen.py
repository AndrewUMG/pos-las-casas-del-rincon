"""Genera los diagramas C4 (SVG) del Sistema POS - Las Casas del Rincón Guatemalteco."""
import html, os

FONT = "'Segoe UI', 'Open Sans', Arial, sans-serif"
MONO = "Consolas, 'Courier New', monospace"
FILL = dict(person="#08427b", system="#1168bd", container="#438dd5",
            component="#85bbf0", external="#999999")
STROKE = dict(person="#073b6f", system="#0e5aa7", container="#3c7fc0",
              component="#78a8d8", external="#8a8a8a")
INK = dict(person="#fff", system="#fff", container="#fff",
           component="#0b2d4f", external="#fff")
GRAY = "#707070"


def esc(t):
    return html.escape(t, quote=True)


def wrap(text, maxw, fs, bold=False):
    cw = fs * (0.58 if bold else 0.52)
    per = max(4, int(maxw / cw))
    out, cur = [], ""
    for w in text.split():
        if cur and len(cur) + 1 + len(w) > per:
            out.append(cur)
            cur = w
        else:
            cur = (cur + " " + w).strip()
    if cur:
        out.append(cur)
    return out


class Diagram:
    def __init__(self, w, h):
        self.w, self.h = w, h
        self.back, self.shapes, self.lines, self.labels = [], [], [], []

    # ---------- texto centrado en bloque ----------
    def text_block(self, cx, top, h, maxw, title, tag, desc, ink, tfs=26, dfs=17, gfs=14):
        rows = []
        for l in wrap(title, maxw, tfs, True):
            rows.append((l, tfs, 700, tfs * 1.18))
        if tag:
            for l in wrap(tag, maxw, gfs):
                rows.append((l, gfs, 400, gfs * 1.35))
        if desc:
            rows.append(("", 0, 0, dfs * 0.7))
            for l in wrap(desc, maxw, dfs):
                rows.append((l, dfs, 400, dfs * 1.3))
        total = sum(r[3] for r in rows)
        y = top + (h - total) / 2
        for txt, fs, wt, lh in rows:
            if txt:
                self.shapes.append(
                    f'<text x="{cx}" y="{y + lh * 0.78:.1f}" font-size="{fs}" font-weight="{wt}" '
                    f'fill="{ink}" text-anchor="middle" font-family="{FONT}">{esc(txt)}</text>')
            y += lh

    # ---------- elementos ----------
    def box(self, x, y, w, h, title, tag, desc, kind="container", shape="rect", tfs=26, dfs=17, gfs=14):
        f, s, ink = FILL[kind], STROKE[kind], INK[kind]
        top, hh = y, h
        if shape == "rect":
            self.shapes.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="{f}" stroke="{s}" stroke-width="2"/>')
        elif shape == "browser":
            self.shapes.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="10" fill="{f}" stroke="{s}" stroke-width="2"/>')
            self.shapes.append(f'<rect x="{x+8}" y="{y+34}" width="{w-16}" height="{h-42}" rx="6" fill="#4a95dd"/>')
            for i in range(3):
                self.shapes.append(f'<circle cx="{x+22+i*24}" cy="{y+17}" r="7" fill="#6aa9e6"/>')
            self.shapes.append(f'<rect x="{x+92}" y="{y+9}" width="{w-104}" height="16" rx="8" fill="#6aa9e6"/>')
            top, hh = y + 30, h - 34
        elif shape == "mobile":
            self.shapes.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="26" fill="{s}" stroke="{s}" stroke-width="2"/>')
            self.shapes.append(f'<rect x="{x+34}" y="{y+10}" width="{w-68}" height="{h-20}" rx="6" fill="{f}"/>')
            self.shapes.append(f'<circle cx="{x+17}" cy="{y+h/2}" r="6" fill="#6aa9e6"/>')
            self.shapes.append(f'<rect x="{x+w-21}" y="{y+h/2-28}" width="5" height="56" rx="2" fill="#6aa9e6"/>')
            x, w = x + 30, w - 60
        elif shape == "cylinder":
            ry = 26
            self.shapes.append(
                f'<path d="M{x},{y+ry} A{w/2},{ry} 0 0 1 {x+w},{y+ry} L{x+w},{y+h-ry} '
                f'A{w/2},{ry} 0 0 1 {x},{y+h-ry} Z" fill="{f}" stroke="{s}" stroke-width="2"/>')
            self.shapes.append(f'<path d="M{x},{y+ry} A{w/2},{ry} 0 0 0 {x+w},{y+ry}" fill="none" stroke="{s}" stroke-width="2"/>')
            top, hh = y + ry * 1.6, h - ry * 2
        self.text_block(x + w / 2, top, hh, w - 36, title, tag, desc, ink, tfs, dfs, gfs)
        return (x, y, w, h)

    def person(self, cx, y, title, desc, w=360, bh=215, kind="person"):
        f, s = FILL[kind], STROKE[kind]
        self.shapes.append(f'<circle cx="{cx}" cy="{y+70}" r="70" fill="{f}" stroke="{s}" stroke-width="2"/>')
        self.shapes.append(f'<rect x="{cx-w/2}" y="{y+115}" width="{w}" height="{bh}" rx="62" fill="{f}" stroke="{s}" stroke-width="2"/>')
        self.text_block(cx, y + 115, bh, w - 70, title, "[Persona]", desc, "#fff", 26, 16)

    def boundary(self, x, y, w, h, name, typ, color="#444", inner=False):
        dash = "10,8" if not inner else "6,6"
        self.back.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="{"#f6f9fd" if inner else "none"}" '
                         f'stroke="{color}" stroke-width="2" stroke-dasharray="{dash}"/>')
        self.back.append(f'<text x="{x+14}" y="{y+h-30}" font-size="18" font-weight="700" fill="{color}" font-family="{FONT}">{esc(name)}</text>')
        self.back.append(f'<text x="{x+14}" y="{y+h-11}" font-size="14" fill="{color}" font-family="{FONT}">{esc(typ)}</text>')

    def arrow(self, pts, label=None, tech=None, lpos=None, lw=240, lfs=18, dashed=True,
              head="arrow", tail=None, color=GRAY):
        d = "M" + " L".join(f"{x},{y}" for x, y in pts)
        da = ' stroke-dasharray="12,9"' if dashed else ""
        me = {"arrow": ' marker-end="url(#arr)"', "tri": ' marker-end="url(#tri)"', None: ""}[head]
        ms = {"diamond": ' marker-start="url(#dia)"', None: ""}[tail]
        self.lines.append(f'<path d="{d}" fill="none" stroke="{color}" stroke-width="2.2"{da}{me}{ms}/>')
        if label:
            if lpos is None:
                best = max(range(len(pts) - 1), key=lambda i: abs(pts[i+1][0]-pts[i][0]) + abs(pts[i+1][1]-pts[i][1]))
                (x1, y1), (x2, y2) = pts[best], pts[best + 1]
                lpos = ((x1 + x2) / 2, (y1 + y2) / 2)
            self.label(lpos[0], lpos[1], label, tech, lw, lfs)

    def label(self, cx, cy, label, tech=None, lw=240, lfs=18):
        rows = [(l, lfs, 700, "#5f5f5f") for l in wrap(label, lw, lfs, True)]
        if tech:
            rows.append((tech, lfs - 4, 400, "#6f6f6f"))
        lh = [r[1] * 1.25 for r in rows]
        tot = sum(lh)
        width = max(len(r[0]) * r[1] * (0.58 if r[2] == 700 else 0.52) for r in rows) + 14
        y = cy - tot / 2
        self.labels.append(f'<rect x="{cx-width/2:.1f}" y="{y-3:.1f}" width="{width:.1f}" height="{tot+6:.1f}" fill="#fff" opacity="0.95"/>')
        for (t, fs, wt, col), h in zip(rows, lh):
            self.labels.append(f'<text x="{cx}" y="{y + h*0.8:.1f}" font-size="{fs}" font-weight="{wt}" fill="{col}" '
                               f'text-anchor="middle" font-family="{FONT}">{esc(t)}</text>')
            y += h

    def title(self, y, title, subtitle):
        self.labels.append(f'<text x="40" y="{y}" font-size="34" font-weight="700" fill="#111" font-family="{FONT}">{esc(title)}</text>')
        for i, s in enumerate(subtitle):
            self.labels.append(f'<text x="40" y="{y+34+i*24}" font-size="18" fill="#555" font-family="{FONT}">{esc(s)}</text>')

    def legend(self, x, y, items):
        self.labels.append(f'<text x="{x}" y="{y}" font-size="16" font-weight="700" fill="#333" font-family="{FONT}">Leyenda</text>')
        for i, (kind, name) in enumerate(items):
            yy = y + 14 + i * 30
            self.labels.append(f'<rect x="{x}" y="{yy}" width="34" height="22" rx="4" fill="{FILL[kind]}"/>')
            self.labels.append(f'<text x="{x+44}" y="{yy+17}" font-size="15" fill="#333" font-family="{FONT}">{esc(name)}</text>')

    def raw(self, s, layer="shapes"):
        getattr(self, layer).append(s)

    def svg(self):
        defs = f"""<defs>
<marker id="arr" viewBox="0 0 12 12" refX="11" refY="6" markerWidth="12" markerHeight="12" markerUnits="userSpaceOnUse" orient="auto"><path d="M0,0 L12,6 L0,12 Z" fill="{GRAY}"/></marker>
<marker id="tri" viewBox="0 0 20 20" refX="19" refY="10" markerWidth="20" markerHeight="20" markerUnits="userSpaceOnUse" orient="auto"><path d="M1,1 L19,10 L1,19 Z" fill="#fff" stroke="{GRAY}" stroke-width="2"/></marker>
<marker id="dia" viewBox="0 0 24 14" refX="1" refY="7" markerWidth="24" markerHeight="14" markerUnits="userSpaceOnUse" orient="auto"><path d="M1,7 L12,1 L23,7 L12,13 Z" fill="{GRAY}"/></marker>
</defs>"""
        body = "\n".join(self.back + self.shapes + self.lines + self.labels)
        return (f'<svg xmlns="http://www.w3.org/2000/svg" width="{self.w}" height="{self.h}" viewBox="0 0 {self.w} {self.h}">'
                f'{defs}<rect width="100%" height="100%" fill="#fff"/>{body}</svg>')


SYS = "Sistema POS e Inventario"
SUB_SYS = "Las Casas del Rincón Guatemalteco"
CORE = "Core de Empresas de Consumo Masivo"


# =====================================================================
# NIVEL 1 - CONTEXTO
# =====================================================================
def contexto():
    d = Diagram(2000, 1430)
    d.person(330, 40, "Cajero", "Abre y cierra la caja, despacha productos, cobra, factura y procesa devoluciones.")
    d.person(1000, 40, "Administrador", "Gestiona catálogos, inventario, compras, promociones, usuarios y reportes.")
    d.person(1670, 40, "Cliente", "Residente de colonias residenciales que compra productos y recibe su factura con código QR.")

    d.box(720, 600, 560, 250, SYS, "[Sistema de Software]",
          "Controla inventario, ventas, cobros, facturación con QR, devoluciones, promociones, caja y reportes del negocio.",
          "system", tfs=30, dfs=18)
    d.box(800, 1060, 400, 210, CORE, "[Sistema de Software Existente]",
          "Core existente que provee productos, familias, proveedores y precios.", "external")

    d.arrow([(380, 370), (800, 600)], "Registra ventas, cobros, facturas, devoluciones y caja en", "[HTTPS]")
    d.arrow([(1000, 370), (1000, 600)], "Administra catálogos, inventario y consulta reportes en", "[HTTPS]")
    d.arrow([(1580, 370), (1200, 600)], "Escanea con su celular el QR de la factura y consulta su promoción en", "[HTTPS]")
    d.arrow([(1000, 850), (1000, 1060)], "Consulta productos, proveedores y precios del", "[HTTPS/JSON]")

    d.legend(40, 520, [("person", "Persona"), ("system", "Sistema en alcance"), ("external", "Sistema existente")])
    d.title(1340, f"Diagrama de Contexto del Sistema – {SUB_SYS}",
            ["Nivel 1 del modelo C4: el sistema POS e Inventario, sus usuarios y el Core existente con el que se integra."])
    return d


# =====================================================================
# NIVEL 2 - CONTENEDORES
# =====================================================================
def contenedores():
    d = Diagram(2360, 1660)
    d.person(360, 30, "Cajero", "Abre y cierra caja, despacha, cobra, factura y procesa devoluciones.")
    d.person(900, 30, "Administrador", "Gestiona catálogos, inventario, compras, promociones y reportes.")
    d.person(1590, 30, "Cliente", "Compra productos y recibe su factura con código QR.")

    d.boundary(40, 470, 1840, 930, SYS, "[Sistema de Software]")
    d.box(420, 520, 420, 250, "Interfaz Web del POS", "[Contenedor: Blade + Bootstrap + JavaScript]",
          "Menú interactivo con todos los módulos; se ejecuta en el navegador del cajero y del administrador.",
          shape="browser", tfs=24, dfs=16)
    d.box(1400, 520, 380, 250, "Vista de Promoción QR", "[Contenedor: Blade + Bootstrap – vista móvil]",
          "Página que abre el celular al leer el QR de la factura e indica la promoción obtenida.",
          shape="mobile", tfs=24, dfs=16)
    d.box(700, 1000, 440, 260, "Aplicación Laravel", "[Contenedor: PHP 8 + Laravel – MVC]",
          "Rutas, controladores y vistas Blade; lógica de negocio con DDD y Arquitectura Hexagonal.",
          tfs=26, dfs=17)
    d.box(100, 1000, 380, 260, "Base de Datos", "[Contenedor: PostgreSQL – esquema relacional]",
          "Usuarios, roles, catálogos, inventario, ventas, cajas, facturas, devoluciones, promociones y puntos.",
          shape="cylinder", dfs=16)
    d.box(1960, 1030, 360, 200, CORE, "[Sistema Existente]",
          "Productos, proveedores y precios.", "external", tfs=24)

    d.arrow([(420, 360), (560, 520)], "Registra ventas, cobros, facturas y devoluciones con", "[HTTPS]", lw=220)
    d.arrow([(880, 360), (760, 520)], "Administra catálogos, inventario y reportes con", "[HTTPS]", lw=220)
    d.arrow([(1580, 360), (1590, 520)], "Escanea el QR de la factura con su celular y abre", "[HTTPS]", lw=230)
    d.arrow([(560, 770), (760, 1000)], "Envía formularios y peticiones AJAX a", "[HTTPS/JSON]", lw=200)
    d.arrow([(1040, 1000), (840, 700)], "Entrega las vistas Blade (HTML, CSS, JS) a", "[HTTPS]", lpos=(960, 820), lw=200)
    d.arrow([(1450, 770), (1140, 1060)], "Consulta la promoción del código QR en", "[HTTPS]", lw=200)
    d.arrow([(700, 1130), (480, 1130)], "Lee y escribe en", "[SQL – Eloquent]", lw=170)
    d.arrow([(1140, 1130), (1960, 1130)], "Consulta productos, proveedores y precios del", "[HTTPS/JSON]", lpos=(1560, 1130), lw=260)

    d.legend(1960, 1290, [("person", "Persona"), ("container", "Contenedor"), ("external", "Sistema existente")])
    d.title(1570, f"Diagrama de Contenedores – {SUB_SYS}",
            ["Nivel 2 del modelo C4: interfaces web, aplicación Laravel (MVC) y base de datos PostgreSQL que componen el Sistema POS e Inventario."])
    return d


# =====================================================================
# NIVEL 3 - COMPONENTES (Aplicación Laravel)
# =====================================================================
def componentes():
    d = Diagram(2580, 2120)
    d.box(420, 40, 380, 220, "Interfaz Web del POS", "[Contenedor: Blade + Bootstrap + JS]",
          "Menú interactivo del POS.", shape="browser", tfs=22, dfs=16)
    d.box(1460, 40, 380, 220, "Vista de Promoción QR", "[Contenedor: Blade – vista móvil]",
          "Muestra la promoción del QR.", shape="mobile", tfs=22, dfs=16)

    d.boundary(40, 330, 2040, 1270, "Aplicación Laravel", "[Contenedor: PHP 8 + Laravel – MVC + DDD + Arquitectura Hexagonal]")
    d.boundary(60, 640, 1670, 640, "Módulos de dominio – Bounded Contexts (DDD)",
               "[Cada módulo: Controlador (C) + Servicios de aplicación + Entidades de dominio (M) + Puertos]", color="#2c5f93", inner=True)

    C = dict(tfs=20, dfs=15, gfs=13)
    d.box(560, 370, 460, 200, "Rutas y Middleware de Acceso", "[Componente: Laravel Router + Middleware auth/rol]",
          "Recibe las peticiones, verifica la sesión del usuario y los permisos de su rol y las envía al controlador del módulo.",
          "component", **C)
    d.box(1240, 370, 460, 200, "Vistas Blade y Menú Interactivo", "[Componente: Blade Templates (V de MVC)]",
          "Plantillas de cada pantalla: menú, despacho, cobro, factura con QR, caja, reportes y promoción.",
          "component", **C)

    cols = [90, 430, 770, 1110, 1450]
    rowA, rowB, W, H = 690, 1000, 250, 210
    tag = "[Componente: Módulo Laravel]"
    A = [("Seguridad y Accesos", "Login contra la base de datos, usuarios, roles y permisos del menú."),
         ("Reportes", "Corte de caja, devoluciones, clientes y compras de productos para existencias."),
         ("Inventario y Compras", "Compras a proveedores, existencias por producto y kardex de movimientos."),
         ("Caja", "Apertura con monto inicial, movimientos y cierre de caja (corte)."),
         ("Catálogos", "Clientes, proveedores, productos por familia y precios.")]
    B = [("Promociones y Descuentos", "Producto gratis, vale de regalo, 2.º a mitad de precio, mayoreo y puntos; valida el QR."),
         ("Despacho (Punto de Venta)", "Lectura por línea de producto, subtotal por línea y total de la compra."),
         ("Devoluciones", "Devolución de productos por daño o vencimiento."),
         ("Cobros", "Pago en efectivo, tarjeta de crédito o débito; cálculo de cambio."),
         ("Facturación", "Factura con los datos del cliente, detalle de productos y código QR de promoción.")]
    for x, (t, ds) in zip(cols, A):
        d.box(x, rowA, W, H, t, tag, ds, "component", **C)
    for x, (t, ds) in zip(cols, B):
        d.box(x, rowB, W, H, t, tag, ds, "component", **C)

    d.box(90, 1340, 1610, 160, "Repositorios – Adaptador de Persistencia", "[Componente: Eloquent ORM]",
          "Implementan los puertos de salida (VentaRepository, ProductoRepository, CajaRepository, FacturaRepository, …) sobre PostgreSQL.",
          "component", **C)
    d.box(1800, 690, 240, 210, "Adaptador Core Consumo Masivo", "[Componente: Laravel HTTP Client]",
          "Obtiene productos, proveedores y precios del Core.", "component", **C)
    d.box(1800, 1000, 240, 210, "Generador de QR y Factura", "[Componente: simple-qrcode + DomPDF]",
          "Crea el código QR con la promoción y la factura imprimible.", "component", **C)
    d.box(2200, 710, 320, 170, CORE, "[Sistema Existente]", None, "external", tfs=22)
    d.arrow([(2040, 795), (2200, 795)], "Usa", "[HTTPS/JSON]", lw=120, lfs=16)

    d.box(630, 1700, 390, 220, "Base de Datos", "[Contenedor: PostgreSQL]",
          "Esquema relacional del negocio.", shape="cylinder", tfs=24, dfs=16)

    s = dict(lfs=16)
    d.arrow([(660, 260), (760, 370)], "Envía peticiones HTTP/AJAX a", "[HTTPS]", lw=200, **s)
    d.arrow([(1480, 260), (1000, 370)], "Consulta la promoción del QR en", "[HTTPS]", lpos=(1200, 300), lw=220, **s)
    d.arrow([(560, 470), (215, 470), (215, 690)], "Autentica y valida permisos con", lpos=(390, 470), lw=280, **s)
    d.arrow([(790, 570), (790, 640)], "Enruta peticiones a", lpos=(940, 605), lw=200, **s)
    d.arrow([(1470, 640), (1470, 570)], "Renderizan respuestas con", lpos=(1600, 605), lw=220, **s)
    d.arrow([(430, 1105), (340, 1105)], "Aplica descuentos", lw=80, lfs=14)
    d.arrow([(640, 1000), (820, 900)], "Descuenta existencias", lpos=(725, 950), lw=90, lfs=14)
    d.arrow([(935, 1000), (935, 900)], "Ajusta existencias", lpos=(935, 950), lw=90, lfs=14)
    d.arrow([(1235, 1000), (1235, 900)], "Registra ingreso en", lpos=(1235, 950), lw=90, lfs=14)
    d.arrow([(1700, 795), (1800, 795)], "Consulta con", lw=80, lfs=14)
    d.arrow([(1700, 1105), (1800, 1105)], "Genera QR y PDF con", lw=80, lfs=14)
    d.arrow([(895, 1280), (895, 1340)], "Persisten agregados mediante puertos de repositorio", lpos=(1220, 1310), lw=520, **s)
    d.arrow([(825, 1500), (825, 1700)], "Lee y escribe en", "[SQL – Eloquent]", lpos=(825, 1650), lw=200, **s)

    d.legend(1200, 1730, [("container", "Contenedor"), ("component", "Componente"), ("external", "Sistema existente")])
    d.title(2010, f"Diagrama de Componentes – Aplicación Laravel – {SUB_SYS}",
            ["Nivel 3 del modelo C4: módulos (bounded contexts) y adaptadores de la aplicación Laravel, organizados con MVC, DDD y Arquitectura Hexagonal."])
    return d


# =====================================================================
# NIVEL 4 - CÓDIGO (componente Despacho)
# =====================================================================
LAYER = {"infra": "#438dd5", "app": "#1168bd", "dom": "#08427b"}


def klass(d, x, y, w, stereo, name, attrs, methods, layer, italic=False):
    hc = LAYER[layer]
    lh, fs = 24, 16
    hh = 62
    ah = (len(attrs) * lh + 14) if attrs else 10
    mh = (len(methods) * lh + 14) if methods else 10
    h = hh + ah + mh
    d.raw(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="6" fill="#fff" stroke="{hc}" stroke-width="2.5"/>')
    d.raw(f'<path d="M{x},{y+6} a6,6 0 0 1 6,-6 h{w-12} a6,6 0 0 1 6,6 v{hh-6} h-{w} Z" fill="{hc}"/>')
    d.raw(f'<text x="{x+w/2}" y="{y+22}" font-size="14" fill="#dbe9f8" text-anchor="middle" font-family="{FONT}">«{esc(stereo)}»</text>')
    st = ' font-style="italic"' if italic else ""
    d.raw(f'<text x="{x+w/2}" y="{y+48}" font-size="21" font-weight="700" fill="#fff" text-anchor="middle"{st} font-family="{FONT}">{esc(name)}</text>')
    yy = y + hh
    d.raw(f'<line x1="{x}" y1="{yy+ah}" x2="{x+w}" y2="{yy+ah}" stroke="{hc}" stroke-width="1.5"/>')
    for i, a in enumerate(attrs):
        d.raw(f'<text x="{x+14}" y="{yy+26+i*lh}" font-size="{fs}" fill="#222" font-family="{MONO}">{esc(a)}</text>')
    yy += ah
    for i, m in enumerate(methods):
        d.raw(f'<text x="{x+14}" y="{yy+26+i*lh}" font-size="{fs}" fill="#222" font-family="{MONO}">{esc(m)}</text>')
    return (x, y, w, h)


def codigo():
    d = Diagram(2480, 1680)
    bands = [(40, 560, "Infraestructura (Adaptadores)", "#eaf2fb"),
             (620, 1160, "Aplicación (Servicios y Puertos)", "#e4eef9"),
             (1200, 2440, "Dominio (Modelo DDD)", "#e1e8f2")]
    for x1, x2, name, bg in bands:
        d.raw(f'<rect x="{x1}" y="40" width="{x2-x1}" height="1000" rx="10" fill="{bg}"/>', "back")
        d.raw(f'<text x="{x1+18}" y="74" font-size="20" font-weight="700" fill="#2c4a6b" font-family="{FONT}">{esc(name)}</text>', "back")

    klass(d, 90, 130, 420, "controlador Laravel (C de MVC)", "VentaController", [],
          ["+ agregarLinea(Request)", "+ finalizar(int $id)", "+ show(int $id): View"], "infra")
    srv = klass(d, 670, 130, 440, "servicio de aplicación", "RegistrarVentaService",
                ["- ventas: VentaRepository", "- promociones: MotorPromociones"],
                ["+ agregarProducto($ventaId, $codigo, $cant)", "+ finalizar($ventaId): Venta"], "app")
    klass(d, 670, 700, 440, "interfaz · puerto de salida", "VentaRepository", [],
          ["+ guardar(Venta $venta): void", "+ buscarPorId(int $id): Venta"], "app", italic=True)
    klass(d, 90, 700, 420, "adaptador de salida · Eloquent", "EloquentVentaRepository",
          [], ["+ guardar(Venta $venta): void", "+ buscarPorId(int $id): Venta"], "infra")

    venta = klass(d, 1250, 130, 420, "raíz de agregado", "Venta",
                  ["- id: int", "- fecha: DateTime", "- cajaId: int", "- lineas: LineaVenta[]"],
                  ["+ agregarLinea(Producto, int)", "+ calcularTotal(): float"], "dom")
    klass(d, 1250, 640, 420, "entidad", "LineaVenta",
          ["- producto: Producto", "- cantidad: int", "- precioUnitario: float", "- descuento: float"],
          ["+ subtotal(): float"], "dom")
    klass(d, 1860, 130, 400, "entidad", "Producto",
          ["- codigo: string", "- nombre: string", "- familia: string", "- precio: float", "- existencia: int"], [], "dom")
    promo = klass(d, 1860, 600, 400, "interfaz", "Promocion", [], ["+ aplicar(LineaVenta): float"], "dom", italic=True)
    klass(d, 1760, 860, 300, "regla", "SegundoMitadPrecio", [], ["+ aplicar(LineaVenta)"], "dom")
    klass(d, 2100, 860, 300, "regla", "PrecioMayoreo", [], ["+ aplicar(LineaVenta)"], "dom")

    s = dict(lfs=16, lw=150)
    d.arrow([(510, 190), (670, 190)], "usa", **s)
    d.arrow([(1110, 190), (1250, 190)], "crea / modifica", **s)
    d.arrow([(890, srv[1] + srv[3]), (890, 700)], "usa (puerto)", **s)
    d.arrow([(510, 790), (670, 790)], "implementa", head="tri", **s)
    vb = venta[1] + venta[3]
    d.arrow([(1460, vb), (1460, 640)], "contiene 1..*", tail="diamond", dashed=False, head=None, lpos=(1460, (vb + 640) / 2), **s)
    d.arrow([(1670, 690), (1760, 690), (1760, 300), (1860, 300)], "referencia", lpos=(1760, 470), **s)
    d.arrow([(1670, 760), (1860, 660)], "aplica 0..1", lpos=(1765, 712), **s)
    pb = promo[1] + promo[3]
    d.arrow([(1910, 860), (1960, pb)], "implementa", head="tri", lpos=(1890, (860 + pb) / 2 + 4), lw=120, lfs=14)
    d.arrow([(2250, 860), (2160, pb)], "implementa", head="tri", lpos=(2260, (860 + pb) / 2 + 4), lw=120, lfs=14)

    code = [("// app/Domain/Venta/Venta.php  (fragmento ilustrativo)", "#7fb77e"),
            ("class Venta", "#e6edf3"),
            ("{", "#e6edf3"),
            ("    private array $lineas = [];", "#e6edf3"),
            ("", ""),
            ("    public function agregarLinea(Producto $producto, int $cantidad): void", "#e6edf3"),
            ("    {", "#e6edf3"),
            ("        if ($producto->existencia < $cantidad) throw new StockInsuficiente();", "#e6edf3"),
            ("        $this->lineas[] = new LineaVenta($producto, $cantidad);", "#e6edf3"),
            ("    }", "#e6edf3"),
            ("", ""),
            ("    public function calcularTotal(): float", "#e6edf3"),
            ("    {", "#e6edf3"),
            ("        return array_sum(array_map(fn($l) => $l->subtotal(), $this->lineas));", "#e6edf3"),
            ("    }", "#e6edf3"),
            ("}", "#e6edf3")]
    cx, cy, cw = 40, 1080, 1120
    chh = 50 + len(code) * 24
    d.raw(f'<rect x="{cx}" y="{cy}" width="{cw}" height="{chh}" rx="10" fill="#1f2a37"/>', "labels")
    for i, (t, col) in enumerate(code):
        if t:
            d.raw(f'<text x="{cx+24}" y="{cy+38+i*24}" font-size="16" fill="{col}" xml:space="preserve" font-family="{MONO}">{esc(t)}</text>', "labels")

    notes = ["Relaciones:  ──▷ usa / dependencia    - - -▷ (triángulo) implementa    ◆── composición",
             "Flujo: el controlador recibe la lectura del producto → el servicio carga la Venta,",
             "aplica la promoción, recalcula subtotales/total y la guarda a través del puerto VentaRepository."]
    for i, n in enumerate(notes):
        d.raw(f'<text x="1220" y="{1120+i*30}" font-size="17" fill="#444" font-family="{FONT}">{esc(n)}</text>', "labels")

    d.title(1580, "Diagrama de Código – Componente Despacho (Punto de Venta)",
            ["Nivel 4 del modelo C4: clases principales del módulo de despacho en Laravel con Arquitectura Hexagonal (representación simplificada)."])
    return d


if __name__ == "__main__":
    out = os.path.join(os.path.dirname(os.path.abspath(__file__)), "C4_Las_Casas_del_Rincon")
    os.makedirs(out, exist_ok=True)
    for name, fn in [("Nivel1_Contexto", contexto), ("Nivel2_Contenedores", contenedores),
                     ("Nivel3_Componentes", componentes), ("Nivel4_Codigo", codigo)]:
        d = fn()
        with open(os.path.join(out, name + ".svg"), "w", encoding="utf-8") as f:
            f.write(d.svg())
        with open(os.path.join(out, name + ".html"), "w", encoding="utf-8") as f:
            f.write(f'<!doctype html><html><head><meta charset="utf-8"><style>html,body{{margin:0;padding:0;background:#fff;overflow:hidden}}</style></head><body>{d.svg()}</body></html>')
        print(name, d.w, d.h)
