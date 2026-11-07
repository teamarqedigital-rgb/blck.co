// Pedido nuevo: calcula el total en pantalla (el servidor lo recalcula con precios de la BD).
document.addEventListener('DOMContentLoaded', () => {
  const filas = document.querySelectorAll('[data-precio]');
  const totalEl = document.getElementById('totalPedido');
  const itemsEl = document.getElementById('totalItems');
  const fmt = new Intl.NumberFormat('es-CO');

  function recalcular() {
    let total = 0, items = 0;
    filas.forEach((fila) => {
      const input = fila.querySelector('input[type=number]');
      const max = parseInt(input.max, 10);
      let cant = parseInt(input.value, 10) || 0;
      if (cant < 0) cant = 0;
      if (cant > max) cant = max;           // regla: no pasar del stock
      input.value = cant || '';
      total += cant * parseFloat(fila.dataset.precio);
      items += cant;
      fila.querySelector('.subtotal').textContent = cant ? '$' + fmt.format(cant * fila.dataset.precio) : '';
    });
    totalEl.textContent = '$' + fmt.format(total);
    itemsEl.textContent = items;
    document.getElementById('btnGuardar').disabled = items === 0;
  }
  filas.forEach((f) => f.querySelector('input').addEventListener('input', recalcular));
  recalcular();
});
