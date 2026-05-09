/* =========================================================
   Dashboard Gerencial — Exportar PDF
   Depende de: jsPDF 2.x + jspdf-autotable
   ========================================================= */
(function () {
    'use strict';

    /* ── Helpers ─────────────────────────────────────────── */
    function clp(v) {
        return '$' + Number(v || 0).toLocaleString('es-CL');
    }
    function fmt(v, dec) {
        if (v === null || v === undefined) return '—';
        return Number(v).toFixed(dec !== undefined ? dec : 0)
            .replace('.', ',')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function canvasImg(id) {
        var c = document.getElementById(id);
        if (!c) return null;
        try { return c.toDataURL('image/png'); } catch (e) { return null; }
    }
    function domText(id) {
        var el = document.getElementById(id);
        return el ? (el.textContent || '').trim() : '—';
    }

    /* ── Colores ─────────────────────────────────────────── */
    var C = {
        BLUE:       [31,  95,  139],
        ORANGE:     [230, 126,  34],
        TEAL:       [15,  124, 144],
        PURPLE:     [90,   63, 160],
        GREEN:      [25,  135,  84],
        RED:        [183,  58,  58],
        YELLOW:     [217, 138,   0],
        GRAY:       [95,  115, 129],
        LGRAY:      [240, 243, 245],
        WHITE:      [255, 255, 255]
    };

    /* ── Init: esperar a que el DOM esté listo ───────────── */
    document.addEventListener('DOMContentLoaded', function () {

        /* ── Botón descargar PDF ─── */
        var btn = document.getElementById('btnExportarPdfGerencial');
        if (btn) {
            btn.addEventListener('click', function () {
                if (typeof window.jspdf === 'undefined') {
                    alert('La librería PDF aún está cargando. Intenta en unos segundos.');
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando PDF...';
                setTimeout(function () {
                    try   { generarPDF(); }
                    catch (e) { console.error('[PDF]', e); alert('Error al generar el PDF. Ver consola.'); }
                    finally {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-file-pdf-o" style="color:#c0392b;margin-right:5px;"></i> Exportar PDF';
                    }
                }, 80);
            });
        }

        /* ── Botón enviar por correo ─── */
        var modalEmail = document.getElementById('modalEnviarPdfEmail');
        var btnEnviar  = document.getElementById('btnConfirmarEnvioEmailPdf');
        var inputEmail = document.getElementById('inputEmailPdf');
        var errorDiv   = document.getElementById('emailPdfError');
        var resultDiv  = document.getElementById('emailPdfResultado');

        if (modalEmail) {
            $(modalEmail).on('show.bs.modal', function () {
                inputEmail.value = '';
                errorDiv.style.display  = 'none';
                errorDiv.textContent    = '';
                resultDiv.style.display = 'none';
                resultDiv.textContent   = '';
                if (btnEnviar) {
                    btnEnviar.disabled    = false;
                    btnEnviar.innerHTML   = '<i class="fa fa-paper-plane-o"></i> Enviar PDF';
                }
            });
        }

        if (btnEnviar) {
            btnEnviar.addEventListener('click', function () {
                var email  = (inputEmail ? inputEmail.value : '').trim();
                var reEmail = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
                errorDiv.style.display  = 'none';
                errorDiv.textContent    = '';
                resultDiv.style.display = 'none';

                if (!email) {
                    errorDiv.textContent    = 'El correo electrónico es obligatorio.';
                    errorDiv.style.display  = 'block';
                    if (inputEmail) inputEmail.focus();
                    return;
                }
                if (!reEmail.test(email)) {
                    errorDiv.textContent   = 'Ingresa un correo válido (ej: nombre@dominio.com).';
                    errorDiv.style.display = 'block';
                    if (inputEmail) inputEmail.focus();
                    return;
                }
                if (typeof window.jspdf === 'undefined') {
                    errorDiv.textContent   = 'La librería PDF aún está cargando. Intenta de nuevo.';
                    errorDiv.style.display = 'block';
                    return;
                }

                btnEnviar.disabled  = true;
                btnEnviar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generando y enviando...';

                setTimeout(function () {
                    var base64;
                    try {
                        base64 = generarPDFBase64();
                    } catch (e) {
                        console.error('[PDF Email] Error generando PDF:', e);
                        errorDiv.textContent   = 'Error al generar el PDF. Ver consola.';
                        errorDiv.style.display = 'block';
                        btnEnviar.disabled     = false;
                        btnEnviar.innerHTML    = '<i class="fa fa-paper-plane-o"></i> Enviar PDF';
                        return;
                    }

                    var csrfMeta  = document.querySelector('meta[name="csrf-token"]');
                    var csrfToken = csrfMeta
                        ? csrfMeta.getAttribute('content')
                        : ($('input[name="_token"]').val() || '');
                    $.ajax({
                        url:         '/dashboard/enviar-pdf-gerencial',
                        type:        'POST',
                        contentType: 'application/json',
                        data:        JSON.stringify({ email: email, pdf_base64: base64 }),
                        headers:     { 'X-CSRF-TOKEN': csrfToken },
                        success: function (res) {
                            resultDiv.style.cssText = 'display:block;background:#eafaf1;border:1px solid #a9ddb8;color:#1e6b3a;padding:10px 14px;border-radius:6px;font-size:13px;margin-top:8px;';
                            resultDiv.innerHTML     = '<i class="fa fa-check-circle"></i> ' + (res.message || 'PDF enviado correctamente.');
                            btnEnviar.disabled  = false;
                            btnEnviar.innerHTML = '<i class="fa fa-paper-plane-o"></i> Enviar PDF';
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Error al enviar. Verifica la configuración de correo.';
                            resultDiv.style.cssText = 'display:block;background:#fdf0f0;border:1px solid #f5c6be;color:#7b241c;padding:10px 14px;border-radius:6px;font-size:13px;margin-top:8px;';
                            resultDiv.innerHTML     = '<i class="fa fa-times-circle"></i> ' + msg;
                            btnEnviar.disabled  = false;
                            btnEnviar.innerHTML = '<i class="fa fa-paper-plane-o"></i> Enviar PDF';
                        }
                    });
                }, 80);
            });
        }
    });

    /* ══════════════════════════════════════════════════════
       GENERADOR PRINCIPAL
    ══════════════════════════════════════════════════════ */
    function _buildDoc() {
        /* Devuelve { doc, nombreArchivo } */
        var jsPDF = window.jspdf.jsPDF;
        var doc   = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        var d     = window.homePdfData || {};
        var PW    = 210;   // A4 width mm
        var M     = 14;    // margen izq/der
        var CW    = PW - M * 2; // ancho contenido
        var y     = M;

        /* ── Primitivas de layout ──────────────────────── */
        function checkPage(need) {
            if (y + need > 282) { doc.addPage(); y = M; headerMini(); }
        }
        function addPage() { doc.addPage(); y = M; headerMini(); }

        function headerMini() {
            doc.setFillColor(...C.BLUE);
            doc.rect(0, 0, PW, 9, 'F');
            doc.setFontSize(7); doc.setFont('helvetica', 'bold');
            doc.setTextColor(...C.WHITE);
            var title = (d.empresa && (d.empresa.fantasia || d.empresa.nombre) || 'Dashboard Gerencial').toUpperCase();
            doc.text(title + '  —  Dashboard Gerencial', M, 6);
            doc.text(new Date().toLocaleDateString('es-CL'), PW - M, 6, { align: 'right' });
            doc.setTextColor(0, 0, 0);
            y = 14;
        }

        function sectionBand(label, badge, color) {
            checkPage(14);
            doc.setFillColor(...(color || C.ORANGE));
            doc.roundedRect(M, y, CW, 10, 2, 2, 'F');
            doc.setFontSize(9); doc.setFont('helvetica', 'bold');
            doc.setTextColor(...C.WHITE);
            doc.text(badge + '  ·  ' + label, M + 4, y + 7);
            doc.setTextColor(0, 0, 0);
            y += 14;
        }

        function subTitle(txt) {
            checkPage(8);
            doc.setFontSize(8.5); doc.setFont('helvetica', 'bold');
            doc.setTextColor(...C.GRAY);
            doc.text(txt, M, y);
            y += 5;
            doc.setTextColor(0, 0, 0);
        }

        function kpiRow(items) {
            /* items: [{label, value, note, color}] */
            checkPage(28);
            var n  = items.length;
            var bw = (CW - (n - 1) * 3) / n;
            items.forEach(function (item, i) {
                var bx = M + i * (bw + 3);
                var by = y;
                doc.setFillColor(...C.LGRAY);
                doc.roundedRect(bx, by, bw, 26, 2, 2, 'F');
                /* label */
                doc.setFontSize(7.5); doc.setFont('helvetica', 'normal');
                doc.setTextColor(...C.GRAY);
                doc.text((item.label || '').toUpperCase(), bx + 4, by + 6);
                /* value */
                var valStr = String(item.value !== undefined ? item.value : '—');
                doc.setFontSize(13); doc.setFont('helvetica', 'bold');
                doc.setTextColor(...(item.color || C.BLUE));
                doc.text(valStr, bx + 4, by + 15);
                /* note */
                if (item.note) {
                    doc.setFontSize(7); doc.setFont('helvetica', 'normal');
                    doc.setTextColor(...C.GRAY);
                    var noteLines = doc.splitTextToSize(item.note, bw - 8);
                    doc.text(noteLines[0] || '', bx + 4, by + 21);
                }
                doc.setTextColor(0, 0, 0);
            });
            y += 30;
        }

        function chartImg(canvasId, label, w, h, xOverride) {
            checkPage(h + 8);
            var img = canvasImg(canvasId);
            if (label) {
                doc.setFontSize(8); doc.setFont('helvetica', 'bold');
                doc.setTextColor(...C.GRAY);
                doc.text(label, xOverride !== undefined ? xOverride : M, y);
                y += 5;
            }
            if (img) {
                doc.addImage(img, 'PNG', xOverride !== undefined ? xOverride : M, y, w, h);
            } else {
                doc.setFillColor(...C.LGRAY);
                doc.rect(xOverride !== undefined ? xOverride : M, y, w, h, 'F');
                doc.setFontSize(8); doc.setTextColor(...C.GRAY);
                doc.text('Gráfico no disponible', (xOverride !== undefined ? xOverride : M) + w / 2, y + h / 2, { align: 'center' });
            }
            y += h + 6;
            doc.setTextColor(0, 0, 0);
        }

        /* ══════════════════════════════════════════════════
           PÁG 1 — PORTADA + ESTADO + SECCIÓN DIARIA
        ══════════════════════════════════════════════════ */

        /* Header principal */
        doc.setFillColor(...C.BLUE);
        doc.rect(0, 0, PW, 26, 'F');
        var nombre = ((d.empresa && (d.empresa.fantasia || d.empresa.nombre)) || 'Empresa').toUpperCase();
        doc.setFontSize(15); doc.setFont('helvetica', 'bold'); doc.setTextColor(...C.WHITE);
        doc.text(nombre, M, 12);
        doc.setFontSize(9); doc.setFont('helvetica', 'normal');
        doc.text('Dashboard Gerencial  ·  ' + (d.empresa && d.empresa.tipoNegocio || ''), M, 20);
        var fechaLarga = new Date().toLocaleDateString('es-CL', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        var fechaLargaCap = fechaLarga.charAt(0).toUpperCase() + fechaLarga.slice(1);
        doc.text(fechaLargaCap, PW - M, 20, { align: 'right' });
        doc.setTextColor(0, 0, 0);
        y = 32;

        /* Estado general */
        var st = d.status || {};
        var stColor = st.level === 'danger'  ? C.RED   :
                      st.level === 'warning' ? C.YELLOW : C.GREEN;
        doc.setFillColor(...stColor);
        doc.roundedRect(M, y, CW, 14, 2, 2, 'F');
        doc.setFontSize(9.5); doc.setFont('helvetica', 'bold'); doc.setTextColor(...C.WHITE);
        doc.text('Estado general: ' + (st.title || ''), M + 4, y + 6);
        doc.setFontSize(8.5); doc.setFont('helvetica', 'normal');
        doc.text((st.message || '') + '   ·   Índice: ' + (st.score || 0) + '/100', M + 4, y + 12);
        doc.setTextColor(0, 0, 0);
        y += 18;

        /* ── Sección DIARIA ── */
        sectionBand('Vista del día', 'DIARIO', C.ORANGE);

        var s = d.summary || {};
        var promedio7 = s.promedio7Dias || 0;
        var cumpl = promedio7 > 0 ? Math.round((s.ventasHoy / promedio7) * 100) : null;
        var cumplColor = cumpl === null ? C.GRAY : (cumpl >= 100 ? C.GREEN : (cumpl >= 70 ? C.YELLOW : C.RED));

        kpiRow([
            { label: 'Ventas hoy',       value: clp(s.ventasHoy),        note: s.ticketsHoy + ' transacciones' },
            { label: 'Ticket promedio',  value: clp(s.ticketPromedioHoy), note: 'promedio del día' },
            { label: 'Cajas abiertas',   value: String(s.cajasAbiertas),  note: 'en operación activa' },
            { label: 'Cumplimiento hoy', value: cumpl !== null ? cumpl + '%' : 'N/D', note: 'vs promedio 7 días', color: cumplColor }
        ]);

        chartImg('homeHourlyChart', 'Ventas por hora del día', CW, 48);

        /* ══════════════════════════════════════════════════
           PÁG 2 — SECCIÓN SEMANAL
        ══════════════════════════════════════════════════ */
        addPage();

        sectionBand('Últimos 7 días', 'SEMANAL', C.TEAL);

        kpiRow([
            { label: 'Promedio 7 días',  value: clp(s.promedio7Dias),    note: 'promedio diario' },
            { label: 'Alertas de stock', value: String(s.alertasStock),  note: 'bajo el mínimo' },
            { label: 'Sin stock',        value: String(s.stockCritico),  note: 'productos agotados' },
            { label: 'Cumplimiento hoy', value: cumpl !== null ? cumpl + '%' : 'N/D', note: 'vs promedio', color: cumplColor }
        ]);

        /* Dos gráficos lado a lado */
        var halfW = (CW - 4) / 2;
        checkPage(60);

        var img1 = canvasImg('homeSalesTrendChart');
        var img2 = canvasImg('homeDayOfWeekChart');

        doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(...C.GRAY);
        doc.text('Tendencia últimos 7 días', M, y);
        doc.text('Promedio por día de semana', M + halfW + 4, y);
        y += 5;

        if (img1) { doc.addImage(img1, 'PNG', M, y, halfW, 52); }
        else       { doc.setFillColor(...C.LGRAY); doc.rect(M, y, halfW, 52, 'F'); }
        if (img2) { doc.addImage(img2, 'PNG', M + halfW + 4, y, halfW, 52); }
        else       { doc.setFillColor(...C.LGRAY); doc.rect(M + halfW + 4, y, halfW, 52, 'F'); }
        y += 58;
        doc.setTextColor(0, 0, 0);

        /* ══════════════════════════════════════════════════
           SECCIÓN MENSUAL (KPIs + Gráficos)
        ══════════════════════════════════════════════════ */
        checkPage(16);
        sectionBand('Este mes', 'MENSUAL', C.PURPLE);

        var deltaStr  = d.deltaMes !== null ? (d.deltaMes >= 0 ? '+' : '') + d.deltaMes + '%' : 'N/D';
        var deltaColor= d.deltaMes === null ? C.GRAY : (d.deltaMes >= 0 ? C.GREEN : C.RED);
        var margenStr = d.margenBruto !== null ? d.margenBruto + '%' : 'N/D';
        var margenColor = d.margenBruto === null ? C.GRAY
                        : (d.margenBruto >= 40 ? C.GREEN : (d.margenBruto >= 20 ? C.YELLOW : C.RED));

        kpiRow([
            { label: 'Ventas del mes',   value: clp(s.ventasMes),       note: 'acumulado este mes' },
            { label: 'vs mes anterior',  value: deltaStr,               note: d.deltaMes !== null ? 'Ant: ' + clp(d.ventasMesAnterior) : '', color: deltaColor },
            { label: 'Margen bruto',     value: margenStr,              note: 'con costo registrado', color: margenColor },
            { label: 'Sobrestock',       value: String((d.sobrestock || []).length), note: 'prods. inmovilizados' }
        ]);

        /* Categorías + Formas de pago lado a lado */
        checkPage(60);
        var catImg = canvasImg('homeCategoryChart');

        doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(...C.GRAY);
        doc.text('Ventas por categoría del mes', M, y);
        y += 5;
        if (catImg) { doc.addImage(catImg, 'PNG', M, y, halfW, 52); }
        else         { doc.setFillColor(...C.LGRAY); doc.rect(M, y, halfW, 52, 'F'); }

        /* Formas de pago (texto) */
        doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(...C.GRAY);
        doc.text('Formas de pago del mes', M + halfW + 4, y - 5);
        var fpY = y + 1;
        var pbs = d.paymentBreakdown || [];
        pbs.forEach(function (fp) {
            if (fpY > y + 52) return;
            doc.setFontSize(8); doc.setFont('helvetica', 'normal'); doc.setTextColor(0, 0, 0);
            doc.text(fp.label || '', M + halfW + 4, fpY);
            var pctText = clp(fp.amount) + '  (' + Number(fp.percentage).toFixed(1) + '%)';
            doc.text(pctText, PW - M, fpY, { align: 'right' });
            /* barra mini */
            var barW = halfW - 2;
            var barX = M + halfW + 4;
            doc.setFillColor(...C.LGRAY);
            doc.rect(barX, fpY + 1, barW, 2.5, 'F');
            doc.setFillColor(...C.BLUE);
            doc.rect(barX, fpY + 1, Math.max(1, barW * Math.min(100, fp.percentage) / 100), 2.5, 'F');
            fpY += 10;
        });
        if (pbs.length === 0) {
            doc.setFontSize(8); doc.setTextColor(...C.GRAY);
            doc.text('Sin datos', M + halfW + 4, y + 8);
        }

        y += 58;
        doc.setTextColor(0, 0, 0);

        /* ══════════════════════════════════════════════════
           PÁG 3 — TABLAS MENSUALES
        ══════════════════════════════════════════════════ */
        addPage();
        sectionBand('Tablas mensuales', 'MENSUAL', C.PURPLE);

        /* Top productos */
        var topProds = d.topProducts || [];
        if (topProds.length > 0) {
            subTitle('Productos más vendidos del mes (Top ' + topProds.length + ')');
            doc.autoTable({
                startY: y,
                margin: { left: M, right: M },
                head: [['#', 'Producto', 'Unidades', 'Venta total']],
                body: topProds.map(function (p, i) {
                    return [i + 1, p.nombre, fmt(p.cantidad, 2) + ' u.', clp(p.monto)];
                }),
                styles: { fontSize: 8.5, cellPadding: 3 },
                headStyles: { fillColor: C.PURPLE, textColor: C.WHITE, fontStyle: 'bold' },
                alternateRowStyles: { fillColor: [248, 245, 255] },
                columnStyles: { 0: { halign: 'center', cellWidth: 8 }, 2: { halign: 'right' }, 3: { halign: 'right' } }
            });
            y = doc.lastAutoTable.finalY + 10;
        }

        /* Rotación de inventario */
        var rotacion = d.rotacionInventario || [];
        if (rotacion.length > 0) {
            checkPage(30);
            subTitle('Rotación de inventario — últimos 30 días');
            doc.autoTable({
                startY: y,
                margin: { left: M, right: M },
                head: [['Producto', 'Categoría', 'Stock', 'Vendido 30d', 'Días stock']],
                body: rotacion.map(function (r) {
                    return [r.nombre, r.categoria, fmt(r.stock, 2), fmt(r.vendido30, 2), r.diasStock >= 999 ? '+999' : r.diasStock];
                }),
                styles: { fontSize: 8, cellPadding: 3 },
                headStyles: { fillColor: C.TEAL, textColor: C.WHITE, fontStyle: 'bold' },
                alternateRowStyles: { fillColor: [245, 252, 253] },
                columnStyles: { 2: { halign: 'right' }, 3: { halign: 'right' }, 4: { halign: 'center' } },
                didParseCell: function (data) {
                    if (data.section === 'body' && data.column.index === 4) {
                        var dias = parseInt(data.cell.raw, 10);
                        if (dias <= 7)        data.cell.styles.textColor = C.RED;
                        else if (dias <= 14)  data.cell.styles.textColor = C.YELLOW;
                        else                  data.cell.styles.textColor = C.GREEN;
                        data.cell.styles.fontStyle = 'bold';
                    }
                }
            });
            y = doc.lastAutoTable.finalY + 10;
        }

        /* Sobrestock */
        var sobre = d.sobrestock || [];
        if (sobre.length > 0) {
            checkPage(30);
            subTitle('Sobrestock detectado (' + sobre.length + ' productos)');
            doc.autoTable({
                startY: y,
                margin: { left: M, right: M },
                head: [['Producto', 'Categoría', 'Stock actual', 'Stock mínimo', 'Exceso']],
                body: sobre.map(function (s2) {
                    return [s2.nombre, s2.categoria, fmt(s2.stock, 2), fmt(s2.stockMinimo, 2), '+' + fmt(s2.exceso, 2)];
                }),
                styles: { fontSize: 8, cellPadding: 3 },
                headStyles: { fillColor: C.RED, textColor: C.WHITE, fontStyle: 'bold' },
                alternateRowStyles: { fillColor: [252, 246, 246] },
                columnStyles: {
                    2: { halign: 'right' }, 3: { halign: 'right' },
                    4: { halign: 'right', textColor: C.RED, fontStyle: 'bold' }
                }
            });
            y = doc.lastAutoTable.finalY + 10;
        }

        /* ══════════════════════════════════════════════════
           PÁG 4 — SEMESTRAL / INSIGHTS / CONTROL INTERNO
        ══════════════════════════════════════════════════ */
        addPage();
        sectionBand('Tendencias históricas', 'SEMESTRAL / ANUAL', C.BLUE);

        /* Chart 6 meses */
        checkPage(65);
        var sixImg = canvasImg('home6MonthsChart');
        doc.setFontSize(8); doc.setFont('helvetica', 'bold'); doc.setTextColor(...C.GRAY);
        doc.text('Evolución 6 meses: ventas vs compras estimadas', M, y);
        y += 5;
        if (sixImg) { doc.addImage(sixImg, 'PNG', M, y, CW, 58); }
        else         { doc.setFillColor(...C.LGRAY); doc.rect(M, y, CW, 58, 'F'); }
        y += 64;
        doc.setTextColor(0, 0, 0);

        /* Insights */
        var insights = d.insights || [];
        if (insights.length > 0) {
            subTitle('Lectura gerencial');
            insights.forEach(function (ins) {
                var lines   = doc.splitTextToSize('• ' + ins, CW - 8);
                var lineH   = lines.length * 5 + 6;
                checkPage(lineH + 3);
                doc.setFillColor(238, 244, 251);
                doc.roundedRect(M, y, CW, lineH, 1.5, 1.5, 'F');
                doc.setFontSize(8.5); doc.setFont('helvetica', 'normal'); doc.setTextColor(0, 0, 0);
                doc.text(lines, M + 4, y + 5);
                y += lineH + 3;
            });
            y += 4;
        }

        /* Control Interno (KPIs dinámicos del DOM) */
        checkPage(20);
        sectionBand('Anulaciones y Mermas', 'CONTROL INTERNO', [192, 57, 43]);

        var periodoLabel = domText('ci-periodo-label');
        if (periodoLabel && periodoLabel !== '—') {
            doc.setFontSize(8); doc.setFont('helvetica', 'italic'); doc.setTextColor(...C.GRAY);
            doc.text('Período: ' + periodoLabel, M, y - 5);
            doc.setTextColor(0, 0, 0);
        }

        kpiRow([
            { label: 'Anulaciones hoy',     value: domText('ci-kpi-anu-hoy'),       note: 'ítems anulados hoy' },
            { label: 'Anulaciones período', value: domText('ci-kpi-anu-total'),      note: 'total período' },
            { label: 'Monto anulado',       value: domText('ci-kpi-anu-monto'),      note: 'valor anulaciones', color: C.RED },
            { label: 'Costo mermas',        value: domText('ci-kpi-merma-costo'),    note: 'mermas registradas', color: [142, 68, 173] }
        ]);

        checkPage(16);
        var retTotal = domText('ci-kpi-retiros-total');
        var retCant  = domText('ci-kpi-retiros-cant');
        var alertas  = domText('ci-kpi-alertas-total');

        doc.setFillColor(...C.LGRAY);
        doc.roundedRect(M, y, CW, 12, 2, 2, 'F');
        doc.setFontSize(8.5); doc.setFont('helvetica', 'normal'); doc.setTextColor(0, 0, 0);
        doc.text('Retiros de caja — Total: ' + retTotal + '  ·  Movimientos: ' + retCant, M + 4, y + 5);
        doc.text('Cierres con diferencia alta: ' + alertas + ' alertas', M + 4, y + 10);
        y += 16;

        /* ── Pie de página en todas las páginas ─────────── */
        var totalPages = doc.internal.getNumberOfPages();
        for (var i = 1; i <= totalPages; i++) {
            doc.setPage(i);
            doc.setFontSize(7); doc.setFont('helvetica', 'normal'); doc.setTextColor(...C.GRAY);
            doc.setDrawColor(...C.LGRAY);
            doc.line(M, 290, PW - M, 290);
            doc.text('Generado el ' + new Date().toLocaleString('es-CL') + '  ·  Sistema PVenta', M, 294);
            doc.text('Página ' + i + ' de ' + totalPages, PW - M, 294, { align: 'right' });
        }

        /* Guardar */
        var nombreArchivo = 'dashboard-gerencial-' + new Date().toISOString().slice(0, 10) + '.pdf';
        return { doc: doc, nombreArchivo: nombreArchivo };
    }

    function generarPDF() {
        var result = _buildDoc();
        result.doc.save(result.nombreArchivo);
    }

    function generarPDFBase64() {
        var result  = _buildDoc();
        var dataUri = result.doc.output('datauristring');
        // "data:application/pdf;base64,<base64data>"
        return dataUri.split(',')[1];
    }

}());
