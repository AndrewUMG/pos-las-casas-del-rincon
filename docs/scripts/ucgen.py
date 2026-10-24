"""Diagramas de casos de uso - Sistema POS e Inventario, Las Casas del Rincón Guatemalteco."""
import html, math, os, sys

FONT = "'Segoe UI', Arial, sans-serif"
INK = "#1d2b3a"
BLUE = "#1168bd"


def esc(t):
    return html.escape(t, quote=True)


def wrap(text, maxw, fs):
    per = max(4, int(maxw / (fs * 0.53)))
    out, cur = [], ""
    for w in text.split():
        if cur and len(cur) + 1 + len(w) > per:
            out.append(cur)
            cur = w
        else:
            cur = (cur + " " + w).strip()
    return out + ([cur] if cur else [])


class UC:
    def __init__(self, w, h, title, code, subtitle, box):
        self.w, self.h = w, h
        self.title, self.code, self.subtitle = title, code, subtitle
        self.box = box  # (x, y, w, h, nombre del sistema)
        self.actors, self.cases = {}, {}
        self.back, self.lines, self.front = [], [], []

    def actor(self, id, name, x, y, stereo=None):
        self.actors[id] = (x, y)
        c = "#08427b" if not stereo else "#6b6b6b"
        s = f'stroke="{c}" stroke-width="4" stroke-linecap="round" fill="none"'
        self.front += [
            f'<circle cx="{x}" cy="{y-62}" r="22" fill="#fff" {s}/>',
            f'<line x1="{x}" y1="{y-40}" x2="{x}" y2="{y+18}" {s}/>',
            f'<line x1="{x-40}" y1="{y-18}" x2="{x+40}" y2="{y-18}" {s}/>',
            f'<line x1="{x}" y1="{y+18}" x2="{x-30}" y2="{y+70}" {s}/>',
            f'<line x1="{x}" y1="{y+18}" x2="{x+30}" y2="{y+70}" {s}/>',
        ]
        yy = y + 100
        if stereo:
            self.front.append(f'<text x="{x}" y="{yy}" font-size="15" fill="#555" text-anchor="middle" font-family="{FONT}">«{esc(stereo)}»</text>')
            yy += 24
        for i, l in enumerate(wrap(name, 200, 21)):
            self.front.append(f'<text x="{x}" y="{yy+i*25}" font-size="21" font-weight="600" fill="{INK}" text-anchor="middle" font-family="{FONT}">{esc(l)}</text>')

    def case(self, id, text, cx, cy, w=330, h=84, sec=False):
        self.cases[id] = (cx, cy, w / 2, h / 2)
        fill, st = ("#f4f8fd", "#6a9bd0") if sec else ("#dbe9f8", BLUE)
        self.front.append(f'<ellipse cx="{cx}" cy="{cy}" rx="{w/2}" ry="{h/2}" fill="{fill}" stroke="{st}" stroke-width="2.5"/>')
        fs = 18 if not sec else 17
        lines = wrap(text, w * 0.74, fs)
        y0 = cy - (len(lines) - 1) * fs * 0.62 + fs * 0.35
        for i, l in enumerate(lines):
            self.front.append(f'<text x="{cx}" y="{y0 + i*fs*1.24:.1f}" font-size="{fs}" fill="{INK}" text-anchor="middle" font-family="{FONT}">{esc(l)}</text>')

    def _ept(self, cid, tx, ty):
        cx, cy, a, b = self.cases[cid]
        dx, dy = tx - cx, ty - cy
        t = 1 / math.sqrt((dx / a) ** 2 + (dy / b) ** 2)
        return cx + dx * t, cy + dy * t

    def assoc(self, aid, cid):
        ax, ay = self.actors[aid]
        cx, cy, _, _ = self.cases[cid]
        sx = ax + 50 if cx > ax else ax - 50
        ex, ey = self._ept(cid, sx, ay - 10)
        self.lines.append(f'<line x1="{sx}" y1="{ay-10}" x2="{ex:.1f}" y2="{ey:.1f}" stroke="#2b2b2b" stroke-width="2"/>')

    def rel(self, src, dst, kind, lpos=0.5):
        (sx, sy, _, _), (dx, dy, _, _) = self.cases[src], self.cases[dst]
        p1 = self._ept(src, dx, dy)
        p2 = self._ept(dst, sx, sy)
        if kind == "gen":
            self.lines.append(f'<line x1="{p1[0]:.1f}" y1="{p1[1]:.1f}" x2="{p2[0]:.1f}" y2="{p2[1]:.1f}" stroke="#2b2b2b" stroke-width="2" marker-end="url(#gen)"/>')
            return
        self.lines.append(f'<line x1="{p1[0]:.1f}" y1="{p1[1]:.1f}" x2="{p2[0]:.1f}" y2="{p2[1]:.1f}" stroke="#555" stroke-width="2" stroke-dasharray="9,7" marker-end="url(#open)"/>')
        mx, my = p1[0] + (p2[0] - p1[0]) * lpos, p1[1] + (p2[1] - p1[1]) * lpos
        lab = f"«{kind}»"
        wdt = len(lab) * 8.2 + 10
        self.lines.append(f'<rect x="{mx-wdt/2:.1f}" y="{my-12:.1f}" width="{wdt:.1f}" height="22" rx="4" fill="#fff"/>')
        self.lines.append(f'<text x="{mx:.1f}" y="{my+4:.1f}" font-size="15" font-style="italic" fill="#555" text-anchor="middle" font-family="{FONT}">{esc(lab)}</text>')

    def svg(self):
        x, y, w, h, name = self.box
        self.back.append(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" rx="14" fill="#fbfdff" stroke="{BLUE}" stroke-width="3"/>')
        self.back.append(f'<path d="M{x},{y+14} a14,14 0 0 1 14,-14 h{w-28} a14,14 0 0 1 14,14 v40 h-{w} Z" fill="{BLUE}"/>')
        self.back.append(f'<text x="{x+22}" y="{y+36}" font-size="21" font-weight="700" fill="#fff" font-family="{FONT}">{esc(name)}</text>')
        # leyenda
        lx, ly = self.w - 520, self.h - 92
        leg = [
            f'<line x1="{lx}" y1="{ly}" x2="{lx+60}" y2="{ly}" stroke="#2b2b2b" stroke-width="2"/>',
            f'<text x="{lx+72}" y="{ly+5}" font-size="15" fill="#444" font-family="{FONT}">Asociación actor – caso de uso</text>',
            f'<line x1="{lx}" y1="{ly+28}" x2="{lx+60}" y2="{ly+28}" stroke="#555" stroke-width="2" stroke-dasharray="9,7" marker-end="url(#open)"/>',
            f'<text x="{lx+72}" y="{ly+33}" font-size="15" fill="#444" font-family="{FONT}">«include» obligatorio  /  «extend» opcional</text>',
            f'<line x1="{lx}" y1="{ly+56}" x2="{lx+60}" y2="{ly+56}" stroke="#2b2b2b" stroke-width="2" marker-end="url(#gen)"/>',
            f'<text x="{lx+72}" y="{ly+61}" font-size="15" fill="#444" font-family="{FONT}">Generalización (tipo de)</text>',
        ]
        ty = self.h - 80
        tit = [f'<text x="40" y="{ty}" font-size="30" font-weight="700" fill="#111" font-family="{FONT}">{esc(self.code)} · {esc(self.title)}</text>',
               f'<text x="40" y="{ty+32}" font-size="17" fill="#555" font-family="{FONT}">{esc(self.subtitle)}</text>']
        defs = """<defs>
<marker id="open" viewBox="0 0 14 14" refX="13" refY="7" markerWidth="14" markerHeight="14" markerUnits="userSpaceOnUse" orient="auto"><path d="M1,1 L13,7 L1,13" fill="none" stroke="#555" stroke-width="2"/></marker>
<marker id="gen" viewBox="0 0 22 22" refX="21" refY="11" markerWidth="22" markerHeight="22" markerUnits="userSpaceOnUse" orient="auto"><path d="M1,1 L21,11 L1,21 Z" fill="#fff" stroke="#2b2b2b" stroke-width="2"/></marker>
</defs>"""
        body = "\n".join(self.back + self.lines + self.front + leg + tit)
        return (f'<svg xmlns="http://www.w3.org/2000/svg" width="{self.w}" height="{self.h}" viewBox="0 0 {self.w} {self.h}">'
                f'{defs}<rect width="100%" height="100%" fill="#fff"/>{body}</svg>')


SYS = "Sistema POS e Inventario – Las Casas del Rincón Guatemalteco"


# ---------------------------------------------------------------- CU-00 General
def general():
    d = UC(1900, 1560, "Diagrama General de Casos de Uso", "CU-00",
           "Visión global de las funciones del sistema y los actores que participan en cada una.",
           (360, 40, 1200, 1300, SYS))
    d.actor("caj", "Cajero", 170, 440)
    d.actor("adm", "Administrador", 170, 1080)
    d.actor("cli", "Cliente", 1730, 430)
    d.actor("core", "Core de Consumo Masivo", 1730, 990, "sistema existente")
    c1 = 780
    for i, (k, t) in enumerate([("abrir", "Abrir caja"), ("desp", "Despachar productos"), ("cobrar", "Cobrar venta"),
                                ("fact", "Generar factura con código QR"), ("dev", "Procesar devolución"),
                                ("cerrar", "Cerrar caja (corte de caja)")]):
        d.case(k, t, c1, 160 + i * 105)
    d.case("login", "Iniciar sesión", 560, 805)
    for i, (k, t) in enumerate([("cat", "Gestionar catálogos"), ("inv", "Gestionar inventario y compras"),
                                ("promo", "Gestionar promociones"), ("rep", "Generar reportes")]):
        d.case(k, t, c1, 935 + i * 105)
    d.case("qr", "Generar código QR", 1240, 300, sec=True)
    d.case("consulta", "Consultar promoción con código QR", 1240, 470)
    d.case("valida", "Validar código QR", 1240, 640, sec=True)
    d.case("sync", "Sincronizar productos y precios con el Core", 1240, 990, sec=True)
    for k in ["abrir", "desp", "cobrar", "fact", "dev", "cerrar", "login"]:
        d.assoc("caj", k)
    for k in ["login", "cat", "inv", "promo", "rep"]:
        d.assoc("adm", k)
    d.assoc("cli", "consulta")
    d.assoc("core", "sync")
    d.rel("fact", "qr", "include")
    d.rel("consulta", "valida", "include")
    d.rel("sync", "cat", "extend")
    return d


# ---------------------------------------------------------------- CU-01 Acceso y catálogos
def acceso():
    d = UC(1800, 1420, "Acceso de Usuarios y Catálogos", "CU-01",
           "Interfaz de acceso validada contra la base de datos y módulos de catálogos: clientes, proveedores, productos y precios.",
           (360, 40, 1080, 1160, SYS))
    d.actor("caj", "Cajero", 170, 300)
    d.actor("adm", "Administrador", 170, 860)
    d.actor("core", "Core de Consumo Masivo", 1630, 1090, "sistema existente")
    c1, c2 = 760, 1210
    rows = [("login", "Iniciar sesión", 570, 250), ("logout", "Cerrar sesión", 790, 385), ("usr", "Gestionar usuarios y roles", c1, 560),
            ("cli", "Gestionar clientes", c1, 680), ("prov", "Gestionar proveedores", c1, 800), ("prod", "Gestionar productos", c1, 960),
            ("prec", "Gestionar precios", c1, 1100)]
    for k, t, x, y in rows:
        d.case(k, t, x, y)
    d.case("cred", "Validar credenciales en la base de datos", c2, 250, sec=True)
    d.case("perm", "Asignar permisos del menú por rol", c2, 560, sec=True)
    d.case("fam", "Clasificar producto por familia", c2, 840, sec=True)
    d.case("coreq", "Consultar productos del Core", c2, 1090, sec=True)
    for k in ["login", "logout"]:
        d.assoc("caj", k)
    for k, _, _, _ in rows:
        d.assoc("adm", k)
    d.assoc("core", "coreq")
    d.rel("login", "cred", "include")
    d.rel("usr", "perm", "include")
    d.rel("prod", "fam", "include")
    d.rel("coreq", "prod", "extend")
    return d


# ---------------------------------------------------------------- CU-02 Ventas y caja
def ventas():
    d = UC(1800, 1500, "Punto de Venta: Caja, Despacho, Cobro y Facturación", "CU-02",
           "Apertura de caja, despacho por línea de producto, cobro (efectivo, crédito o débito), factura con QR y corte de caja.",
           (360, 40, 1060, 1240, SYS))
    d.actor("caj", "Cajero", 170, 770)
    d.actor("cli", "Cliente", 1620, 960)
    c1, c2 = 640, 1130
    d.case("abrir", "Abrir caja", c1, 150)
    d.case("desp", "Despachar productos", c1 + 70, 390)
    d.case("cobrar", "Cobrar venta", c1, 760)
    d.case("fact", "Generar factura", c1 + 70, 1010)
    d.case("cerrar", "Cerrar caja (corte de caja)", c1, 1190)
    sec = [("monto", "Registrar monto inicial de caja", 150), ("leer", "Leer producto por línea", 265),
           ("sub", "Calcular subtotal por línea", 365), ("total", "Calcular total de la compra", 465),
           ("promo", "Aplicar promoción o descuento", 570), ("efe", "Pago en efectivo", 680),
           ("cre", "Pago con tarjeta de crédito", 770), ("deb", "Pago con tarjeta de débito", 860),
           ("datos", "Ingresar datos del cliente (NIT y nombre)", 960), ("qr", "Generar código QR de promoción", 1065),
           ("corte", "Registrar monto final y calcular ventas e ingresos", 1190)]
    for k, t, y in sec:
        d.case(k, t, c2, y, w=350, h=80, sec=True)
    for k in ["abrir", "desp", "cobrar", "fact", "cerrar"]:
        d.assoc("caj", k)
    d.assoc("cli", "datos")
    d.rel("abrir", "monto", "include")
    for k in ["leer", "sub", "total"]:
        d.rel("desp", k, "include", 0.55)
    d.rel("promo", "desp", "extend", 0.45)
    for k in ["efe", "cre", "deb"]:
        d.rel(k, "cobrar", "gen")
    d.rel("fact", "datos", "include", 0.55)
    d.rel("fact", "qr", "include", 0.55)
    d.rel("cerrar", "corte", "include")
    return d


# ---------------------------------------------------------------- CU-03 Devoluciones e inventario
def inventario():
    d = UC(1800, 1250, "Devoluciones, Compras e Inventario", "CU-03",
           "Devolución de productos por daño o vencimiento y control del inventario que ingresa (compras) y sale (ventas).",
           (360, 40, 1080, 990, SYS))
    d.actor("caj", "Cajero", 170, 300)
    d.actor("adm", "Administrador", 170, 790)
    c1, c2 = 680, 1170
    d.case("dev", "Procesar devolución", c1, 250)
    d.case("exist", "Consultar existencias", c1, 450)
    d.case("compra", "Registrar compra a proveedor", c1, 680)
    d.case("kardex", "Consultar kardex de movimientos", c1, 900)
    sec = [("fac", "Buscar factura de la venta", 140), ("sel", "Seleccionar productos a devolver", 240),
           ("mot", "Indicar motivo: daño o vencimiento", 340), ("act", "Actualizar existencias", 480),
           ("prov", "Seleccionar proveedor", 620), ("prod", "Ingresar productos, cantidades y costos", 730)]
    for k, t, y in sec:
        d.case(k, t, c2, y, w=350, h=80, sec=True)
    d.assoc("caj", "dev")
    d.assoc("caj", "exist")
    for k in ["exist", "compra", "kardex"]:
        d.assoc("adm", k)
    for k in ["fac", "sel", "mot", "act"]:
        d.rel("dev", k, "include", 0.55)
    for k in ["act", "prov", "prod"]:
        d.rel("compra", k, "include", 0.55)
    return d


# ---------------------------------------------------------------- CU-04 Promociones y QR
def promociones():
    d = UC(1800, 1400, "Promociones y Lectura del Código QR", "CU-04",
           "Configuración de promociones, lectura del QR de la factura desde el celular y canje de descuentos o puntos.",
           (360, 40, 1080, 1140, SYS))
    d.actor("adm", "Administrador", 170, 330)
    d.actor("caj", "Cajero", 170, 930)
    d.actor("cli", "Cliente", 1630, 740)
    c1, c2 = 680, 1170
    d.case("conf", "Configurar promoción", c1, 330)
    tipos = [("gratis", "Producto gratuito", 130), ("vale", "Vale de regalo", 230),
             ("mitad", "Segundo producto a mitad de precio", 330), ("mayor", "Compra por mayoreo", 430),
             ("puntos", "Puntos por producto", 530)]
    for k, t, y in tipos:
        d.case(k, t, c2, y, w=350, h=78, sec=True)
        d.rel(k, "conf", "gen")
    d.case("leerqr", "Leer el QR de la factura con el celular", c2, 740)
    d.case("valida", "Validar código QR", c1, 740, sec=True)
    d.case("canje", "Canjear promoción en la compra", c1, 900)
    d.case("ptos", "Canjear puntos acumulados", c1, 1040)
    d.case("ver", "Ver tipo de promoción obtenida", c2, 900, w=350, h=80, sec=True)
    d.case("saldo", "Consultar saldo de puntos del cliente", c2, 1040, w=350, h=80, sec=True)
    d.assoc("adm", "conf")
    d.assoc("cli", "leerqr")
    d.assoc("caj", "canje")
    d.assoc("caj", "ptos")
    d.rel("leerqr", "valida", "include")
    d.rel("leerqr", "ver", "include")
    d.rel("canje", "valida", "include")
    d.rel("ptos", "saldo", "include")
    return d


# ---------------------------------------------------------------- CU-05 Reportes
def reportes():
    d = UC(1800, 1120, "Reportes", "CU-05",
           "Cierre del día (corte de caja), devoluciones, clientes y compras de productos para existencias.",
           (360, 40, 1080, 860, SYS))
    d.actor("caj", "Cajero", 170, 210)
    d.actor("adm", "Administrador", 170, 480)
    d.case("gen", "Generar reporte", 640, 470)
    reps = [("corte", "Reporte de cierre del día (corte de caja)", 170), ("dev", "Reporte de devoluciones", 330),
            ("cli", "Reporte de clientes", 490), ("comp", "Reporte de compras de productos para existencias", 650)]
    for k, t, y in reps:
        d.case(k, t, 1190, y, w=380, h=96)
        d.rel(k, "gen", "gen")
    d.case("fechas", "Filtrar por rango de fechas", 560, 760, sec=True)
    d.case("pdf", "Imprimir o exportar a PDF", 1000, 800, sec=True)
    d.assoc("adm", "gen")
    d.assoc("caj", "corte")
    d.rel("gen", "fechas", "include")
    d.rel("pdf", "gen", "extend")
    return d


if __name__ == "__main__":
    out = sys.argv[1]
    os.makedirs(out, exist_ok=True)
    for name, fn in [("CU-00_General", general), ("CU-01_Acceso_y_Catalogos", acceso),
                     ("CU-02_Punto_de_Venta", ventas), ("CU-03_Devoluciones_e_Inventario", inventario),
                     ("CU-04_Promociones_y_QR", promociones), ("CU-05_Reportes", reportes)]:
        d = fn()
        s = d.svg()
        open(os.path.join(out, name + ".svg"), "w", encoding="utf-8").write(s)
        open(os.path.join(out, name + ".html"), "w", encoding="utf-8").write(
            '<!doctype html><html><head><meta charset="utf-8"><style>html,body{margin:0;overflow:hidden;background:#fff}</style></head><body>' + s + '</body></html>')
        print(name, d.w, d.h)
