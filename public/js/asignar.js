$(document).ready(function() {
  $('.select2-epp').each(function() {
    let tipo = $(this).data('tipo');
    $(this).select2({
      placeholder: 'Buscar serie...',
      allowClear: true,
      width: '100%',
      ajax: {
        url: '/series-epp',
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
              return {
                id: item.id,
                text: item.nro_serie + ' (' + item.recurso + ', vence ' + item.vencimiento + ')'
              };
            })
          };
        }
      }
    });
  });
});
