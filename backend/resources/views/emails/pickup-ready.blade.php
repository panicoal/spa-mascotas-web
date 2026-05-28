@component('mail::message')
# 🐾 {{ $mascotaNombre }} está lista para recoger

Hola **{{ $clienteNombre }}**,

¡Buenas noticias! El servicio **{{ $servicio }}** de tu mascota **{{ $mascotaNombre }}** ha finalizado exitosamente.

---

**Detalles del servicio:**
- 🐾 **Mascota:** {{ $mascotaNombre }}
- ✂️ **Servicio:** {{ $servicio }}
- 👤 **Atendida por:** {{ $groomerNombre }}
- 📅 **Fecha:** {{ $fecha }}
- ⏰ **Hora de finalización:** {{ $horaFin }}

---

Puedes venir a recoger a tu mascota en cualquier momento durante nuestro horario de atención.

@component('mail::button', ['url' => config('app.frontend_url', '#'), 'color' => 'success'])
Ver mis citas
@endcomponent

Si tienes alguna pregunta, no dudes en contactarnos al **{{ $telefono }}**.

¡Gracias por confiar en **{{ $espaSpa }}**! 🐶🐱

<br>

*{{ $espaSpa }} — Cuidamos a tus mascotas con amor.*

@endcomponent
