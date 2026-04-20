// ============================================================================
// Dashboard Preventas Pendientes - Funciones JavaScript
// ============================================================================

// ============================================================================
// FUNCIONES PARA DASHBOARD GERENCIAL
// ============================================================================

function cargarPreventasPendientesGerencial() {
  $('#preventasPendientesContainer').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando preventas pendientes...</div>');
  
  $.ajax({
    url: '/dashboard/preventas-pendientes',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.preventas && response.preventas.length > 0) {
        renderPreventasPendientesGerencial(response.preventas, response.monto_total);
      } else {
        $('#preventasPendientesContainer').html('<div class="home-empty">No hay preventas pendientes en este momento.</div>');
      }
    },
    error: function() {
      $('#preventasPendientesContainer').html('<div class="alert alert-danger">Error al cargar las preventas pendientes.</div>');
    }
  });
}

function renderPreventasPendientesGerencial(preventas, montoTotal) {
  let html = '<div class="row mb-3">';
  html += '<div class="col-md-4"><div class="alert alert-info"><strong>Total Preventas:</strong> ' + preventas.length + '</div></div>';
  html += '<div class="col-md-4"><div class="alert alert-warning"><strong>Monto Total:</strong> $' + new Intl.NumberFormat('es-CL').format(montoTotal) + '</div></div>';
  html += '</div>';

  // Procesar datos para gráfico de vendedores
  let vendedoresCount = {};
  preventas.forEach(function(preventa) {
    if (vendedoresCount[preventa.vendedor]) {
      vendedoresCount[preventa.vendedor]++;
    } else {
      vendedoresCount[preventa.vendedor] = 1;
    }
  });

  // Agregar gráfico de barras horizontales
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12">';
  html += '<div class="panel panel-default">';
  html += '<div class="panel-heading"><h5 class="panel-title"><i class="fa fa-bar-chart"></i> Preventas por Vendedor</h5></div>';
  html += '<div class="panel-body" style="height: 200px; padding: 15px;">';
  html += '<canvas id="chartVendedoresPreventas" width="400" height="150"></canvas>';
  html += '</div>';
  html += '</div>';
  html += '</div>';
  html += '</div>';

  html += '<div class="table-responsive">';
  html += '<table class="table table-striped table-hover">';
  html += '<thead class="thead-dark">';
  html += '<tr>';
  html += '<th class="text-center">N° Preventa</th>';
  html += '<th class="text-center">Total Items</th>';
  html += '<th class="text-center">Monto Total</th>';
  html += '<th class="text-center">Vendedor</th>';
  html += '<th class="text-center">Fecha</th>';
  html += '</tr>';
  html += '</thead>';
  html += '<tbody>';
  
  preventas.forEach(function(preventa) {
    html += '<tr>';
    html += '<td class="text-center"><strong>' + preventa.numero_preventa + '</strong></td>';
    html += '<td class="text-center">' + preventa.total_items + '</td>';
    html += '<td class="text-center"><strong style="color: #e74c3c;">$' + new Intl.NumberFormat('es-CL').format(preventa.total) + '</strong></td>';
    html += '<td class="text-center">' + preventa.vendedor + '</td>';
    html += '<td class="text-center"><small>' + preventa.fecha_preventa + '</small></td>';
    html += '</tr>';
  });
  
  html += '</tbody>';
  html += '</table>';
  html += '</div>';

  $('#preventasPendientesContainer').html(html);

  // Renderizar gráfico después de insertar el HTML - capturar vendedoresCount en closure
  var vendedoresData = vendedoresCount;
  setTimeout(function() {
    renderGraficoVendedoresGerencial(vendedoresData);
  }, 100);
}

function renderGraficoVendedoresGerencial(vendedoresCount) {
  if (typeof Chart === 'undefined') return;

  const ctx = document.getElementById('chartVendedoresPreventas');
  if (!ctx) return;

  // Preparar datos para el gráfico
  const vendedores = Object.keys(vendedoresCount);
  const cantidades = Object.values(vendedoresCount);

  // Colores para las barras
  const colores = [
    '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
    '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#16a085'
  ];

  // Destruir gráfico anterior si existe
  if (window.chartVendedoresInstance) {
    window.chartVendedoresInstance.destroy();
  }

  window.chartVendedoresInstance = new Chart(ctx, {
    type: 'horizontalBar',
    data: {
      labels: vendedores,
      datasets: [{
        label: 'Preventas Pendientes',
        data: cantidades,
        backgroundColor: colores.slice(0, vendedores.length),
        borderColor: colores.slice(0, vendedores.length),
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [{
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }]
      },
      legend: {
        display: false
      }
    }
  });
}

// ============================================================================
// FUNCIONES PARA DASHBOARD ADMINISTRADOR
// ============================================================================

function cargarPreventasPendientesAdmin() {
  console.log('Cargando preventas pendientes desde administrador...');
  $('#preventasPendientesContainer').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando preventas pendientes...</div>');
  
  $.ajax({
    url: '/dashboard/preventas-pendientes',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      console.log('Respuesta recibida (administrador):', response);
      if (response.success && response.preventas && response.preventas.length > 0) {
        renderPreventasPendientesAdmin(response.preventas, response.monto_total);
      } else {
        console.log('No hay preventas, mostrando mensaje vacío');
        $('#preventasPendientesContainer').html('<div class="home-empty">No hay preventas pendientes en este momento.</div>');
      }
    },
    error: function(xhr, status, error) {
      console.error('Error AJAX (administrador):', {
        status: status,
        error: error,
        responseText: xhr.responseText,
        statusText: xhr.statusText
      });
      $('#preventasPendientesContainer').html('<div class="alert alert-danger">Error al cargar las preventas pendientes: ' + error + '</div>');
    }
  });
}

function renderPreventasPendientesAdmin(preventas, montoTotal) {
  console.log('Renderizando preventas (administrador):', preventas.length, 'preventas');
  let html = '<div class="row mb-3">';
  html += '<div class="col-md-4"><div class="alert alert-info"><strong>Total Preventas:</strong> ' + preventas.length + '</div></div>';
  html += '<div class="col-md-4"><div class="alert alert-warning"><strong>Monto Total:</strong> $' + new Intl.NumberFormat('es-CL').format(montoTotal) + '</div></div>';
  html += '</div>';

  // Procesar datos para gráfico de vendedores
  let vendedoresCount = {};
  preventas.forEach(function(preventa) {
    if (vendedoresCount[preventa.vendedor]) {
      vendedoresCount[preventa.vendedor]++;
    } else {
      vendedoresCount[preventa.vendedor] = 1;
    }
  });
  console.log('Datos de vendedores:', vendedoresCount);

  // Agregar gráfico de barras horizontales
  html += '<div class="row mb-4">';
  html += '<div class="col-md-12">';
  html += '<div class="panel panel-default">';
  html += '<div class="panel-heading"><h5 class="panel-title"><i class="fa fa-bar-chart"></i> Preventas por Vendedor</h5></div>';
  html += '<div class="panel-body" style="height: 200px; padding: 15px;">';
  html += '<canvas id="chartVendedoresPreventas" width="400" height="150"></canvas>';
  html += '</div>';
  html += '</div>';
  html += '</div>';
  html += '</div>';

  html += '<div class="table-responsive">';
  html += '<table class="table table-striped table-hover">';
  html += '<thead class="thead-dark">';
  html += '<tr>';
  html += '<th class="text-center">N° Preventa</th>';
  html += '<th class="text-center">Total Items</th>';
  html += '<th class="text-center">Monto Total</th>';
  html += '<th class="text-center">Vendedor</th>';
  html += '<th class="text-center">Fecha</th>';
  html += '</tr>';
  html += '</thead>';
  html += '<tbody>';
  
  preventas.forEach(function(preventa) {
    html += '<tr>';
    html += '<td class="text-center"><strong>' + preventa.numero_preventa + '</strong></td>';
    html += '<td class="text-center">' + preventa.total_items + '</td>';
    html += '<td class="text-center"><strong style="color: #e74c3c;">$' + new Intl.NumberFormat('es-CL').format(preventa.total) + '</strong></td>';
    html += '<td class="text-center">' + preventa.vendedor + '</td>';
    html += '<td class="text-center"><small>' + preventa.fecha_preventa + '</small></td>';
    html += '</tr>';
  });
  
  html += '</tbody>';
  html += '</table>';
  html += '</div>';

  $('#preventasPendientesContainer').html(html);

  // Renderizar gráfico después de insertar el HTML - capturar vendedoresCount en closure admin
  var vendedoresDataAdmin = vendedoresCount;
  setTimeout(function() {
    renderGraficoVendedoresAdmin(vendedoresDataAdmin);
  }, 100);
}

function renderGraficoVendedoresAdmin(vendedoresCount) {
  console.log('Iniciando renderización del gráfico...');
  console.log('Chart.js disponible:', typeof Chart !== 'undefined');
  
  if (typeof Chart === 'undefined') {
    console.error('Chart.js no está disponible');
    return;
  }

  const ctx = document.getElementById('chartVendedoresPreventas');
  console.log('Canvas encontrado:', ctx !== null);
  
  if (!ctx) {
    console.error('No se encontró el canvas chartVendedoresPreventas');
    return;
  }

  // Preparar datos para el gráfico
  const vendedores = Object.keys(vendedoresCount);
  const cantidades = Object.values(vendedoresCount);
  
  console.log('Datos para gráfico:', {
    vendedores: vendedores,
    cantidades: cantidades
  });

  // Colores para las barras
  const colores = [
    '#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6',
    '#1abc9c', '#34495e', '#e67e22', '#95a5a6', '#16a085'
  ];

  // Destruir gráfico anterior si existe
  if (window.chartVendedoresAdminInstance) {
    console.log('Destruyendo gráfico anterior');
    window.chartVendedoresAdminInstance.destroy();
  }

  try {
    window.chartVendedoresAdminInstance = new Chart(ctx, {
      type: 'horizontalBar',
      data: {
        labels: vendedores,
        datasets: [{
          label: 'Preventas Pendientes',
          data: cantidades,
          backgroundColor: colores.slice(0, vendedores.length),
          borderColor: colores.slice(0, vendedores.length),
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          xAxes: [{
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }]
        },
        legend: {
          display: false
        }
      }
    });
    console.log('Gráfico creado exitosamente');
  } catch (e) {
    console.error('Error al crear el gráfico:', e);
  }
}

// ============================================================================
// INICIALIZACIÓN DE EVENTOS
// ============================================================================

$(document).ready(function() {
  // Event listener para modal preventas pendientes
  $('#modalPreventasPendientes').on('show.bs.modal', function () {
    console.log('Modal abierto, cargando preventas...');
    
    // Determinar qué función usar según el tipo de dashboard
    if (typeof window.adminTrendLabels !== 'undefined') {
      // Dashboard administrador
      cargarPreventasPendientesAdmin();
    } else if (typeof window.homeTrendLabels !== 'undefined') {
      // Dashboard gerencial  
      cargarPreventasPendientesGerencial();
    }
  });
});