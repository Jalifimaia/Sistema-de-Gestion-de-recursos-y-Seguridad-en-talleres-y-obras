$(document).ready(function() {
  // Definir placeholders personalizados por tipo
  const placeholders = {
    'casco': 'Seleccione el casco',
    'guantes': 'Seleccione los guantes',
    'lentes': 'Seleccione los lentes',
    'botas': 'Seleccione las botas',
    'chaleco': 'Seleccione el chaleco',
    'arnes': 'Seleccione el arnés'
  };

  // Inicializar todos los selects de EPP con Select2
  $('.select2-epp').each(function() {
    let tipo = $(this).data('tipo');
    let placeholderTexto = placeholders[tipo] || 'Buscar serie...';
    
    $(this).select2({
      placeholder: placeholderTexto,
      allowClear: true,
      width: '100%',
      ajax: {
        url: '/epp/buscar',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            tipo: tipo
          };
        },
        processResults: function (data) {
          return {
            results: data.map(function(item) {
              let texto = item.nro_serie;

              if (item.color && item.talle) {
                texto += ` (${item.recurso || 'EPP'} - ${item.color}, Talle ${item.talle})`;
              } else if (item.color) {
                texto += ` (${item.recurso || 'EPP'} - ${item.color})`;
              } else if (item.talle) {
                texto += ` (${item.recurso || 'EPP'}, Talle ${item.talle})`;
              } else if (item.recurso) {
                texto += ` (${item.recurso})`;
              }

              return { id: item.id, text: texto };
            })
          };
        }
      }
    });
  });

  // Validación al enviar el formulario
  const tipos = ['casco','guantes','lentes','botas','chaleco','arnes'];
  const form = document.querySelector('form');
  const submitBtn = document.getElementById('submitBtn');

  form.addEventListener('submit', function(e) {
    let incompletos = [];

    tipos.forEach(tipo => {
      const select = document.getElementById(tipo);
      const alertaAsignado = document.getElementById('alert-' + tipo);
      const alertaVacio = document.getElementById('alert-' + tipo + '-vacio');

      const valor = select?.value?.trim();
      const estaAsignado = select?.disabled;

      if (!estaAsignado && !valor) {
        incompletos.push(tipo);
        if (alertaVacio) alertaVacio.classList.remove('d-none');
        select.classList.add('is-invalid');
      } else {
        if (alertaVacio) alertaVacio.classList.add('d-none');
        select.classList.remove('is-invalid');
      }

      if (estaAsignado) {
        if (alertaAsignado) alertaAsignado.classList.remove('d-none');
      } else {
        if (alertaAsignado) alertaAsignado.classList.add('d-none');
      }
    });

    if (incompletos.length > 0) {
      e.preventDefault();
      submitBtn.disabled = false;
      return;
    }
  });
});