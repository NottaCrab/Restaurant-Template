function toggleMenu() {
    const links = document.getElementById('navbarLinks');
    links.classList.toggle('active');
}

const inputFecha = document.getElementById('input-fecha');
if (inputFecha) {
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.setAttribute('min', hoy);
}

const formReserva = document.getElementById('form-reserva');

if (formReserva) {
    formReserva.addEventListener('submit', async (e) => {
        e.preventDefault();

        const mensaje = document.getElementById('reserva-mensaje');
        const boton = formReserva.querySelector('button');

        mensaje.textContent = '';
        mensaje.className = 'reserva-mensaje';
        boton.disabled = true;
        boton.textContent = 'Reservando...';

        try {
            const datos = new FormData(formReserva);
            const respuesta = await fetch('reservas/api/reservar.php', {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();

            mensaje.textContent = resultado.mensaje;
            mensaje.classList.add(resultado.ok ? 'ok' : 'error');

            if (resultado.ok) {
                formReserva.reset();
            }
        } catch (error) {
            mensaje.textContent = 'No se pudo conectar con el servidor. Inténtalo más tarde.';
            mensaje.classList.add('error');
        } finally {
            boton.disabled = false;
            boton.textContent = 'Reservar';
        }
    });
}
