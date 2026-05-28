# Guía Completa de Grabación de Video — Demostración Pet Spa 🐾

Esta guía está diseñada paso a paso como un guion de producción para ayudarte a grabar un video profesional y fluido. En ella se detalla exactamente **qué hacer en pantalla**, **qué credenciales usar**, **qué explicar por voz** y **qué punto de la autoevaluación se está demostrando**.

---

## 📋 Datos de Acceso para la Demostración

Ten estas credenciales listas para alternar de pestañas de navegador o iniciar sesión rápidamente:

| Rol | Correo Electrónico | Contraseña | ¿Qué demuestra? |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@petspa.com` | `password` | Dashboard principal de control de personal y auditoría. |
| **Recepcionista** | `recepcion@petspa.com` | `password` | Calendario de cobros, registro de pagos con persistencia y panel de inventario. |
| **Estilista (Groomer)** | `groomer1@petspa.com` | `password` | Workspace de estilistas, checklist obligatorio, descuento automático de stock. |
| **Cliente** | `cliente@petspa.com` | `password` | Gestión de múltiples mascotas, Tienda Boutique, carrito y pedido de WhatsApp. |

---

## 🎬 Guion Escena por Escena

### Escena 1: Introducción y Resumen General de Conformidad
* **Qué hacer en pantalla:** Muestra la aplicación corriendo en tu navegador (página de login o el documento [evaluacion_proyecto.md](file:///d:/Desktop/proyecto-web/pet-spa/evaluacion_proyecto.md)).
* **Qué hacer físicamente:** Habla a micrófono con entusiasmo.
* **Qué decir (Guion de voz):**
  > *"¡Hola a todos! En este video voy a demostrar el funcionamiento completo del sistema Pet Spa, detallando cómo cumple al 100% con los requerimientos técnicos y de negocio exigidos en la Guía de Autoevaluación. Veremos desde validaciones duras en la API de agenda, gestión avanzada de mascotas y cobros físicos, hasta un sistema completo de inventario y una Tienda Boutique integrada con WhatsApp y descuentos automáticos de stock firmados por los estilistas."*

---

### Escena 2: Panel del Cliente — Multi-Mascotas con Campos Avanzados
* **Qué hacer en pantalla:**
  1. Inicia sesión como Cliente con el correo `cliente@petspa.com` y contraseña `password`.
  2. Haz clic en el botón **"Mis Mascotas"** (se cargará la vista premium con tarjetas).
  3. Muestra una mascota existente (destacando las insignias de tamaño).
  4. Haz clic en **"Registrar Mascota"** para abrir el formulario `PetFormModal.vue`.
  5. Llena los datos de una mascota ficticia (Ej: *"Toby"*, Perro, Edad: *2 años*).
  6. **CLAVE:** En la sección visual de tamaño, haz clic dinámicamente sobre la tarjeta **"Mediano (11-25 kg)"** o **"Grande (26-45 kg)"** (demostrando el selector visual).
  7. Agrega restricciones médicas (Ej: *"Alergia cutánea, no usar perfumes fuertes"*).
  8. Guarda el registro y muestra la nueva tarjeta. Haz clic en "Ver detalles" para que se aprecie el **banner rojo de alerta médica** y los badges de tamaño.
* **Punto de la Autoevaluación que cumple:**
  - **Mascotas (Punto 2 y Checklist):** *¿Permite el perfil de cliente gestionar más de una mascota?* (Sí, relación 1:N completamente funcional con campos extendidos como tamaño y alerta visual de restricciones de salud).

---

### Escena 3: Tienda Virtual Boutique y Carrito con WhatsApp
* **Qué hacer en pantalla:**
  1. Regresa al panel del cliente y haz clic en la nueva tarjeta destacada **"Tienda Boutique"** (ruta `/tienda`).
  2. Muestra los filtros por categoría y la barra de búsqueda en vivo (escribe *"shampoo"* para ver el filtrado instantáneo).
  3. Señala las etiquetas dinámicas de stock (*"Disponibles: 19"*, *"Agotado"* o *"⚠️ Pocas unidades"*).
  4. Haz clic en **"Añadir"** en varios productos (Ej: *2x Shampoo de Avena* y *1x Perfume Brisa Tropical*).
  5. Muestra cómo el panel lateral del Carrito se actualiza en tiempo real con las cantidades, sumas de subtotales y el gran total estimado.
  6. Haz clic en **"Hacer Pedido por WhatsApp"**.
  7. **CLAVE:** Se abrirá una nueva pestaña de WhatsApp. Muestra el texto pre-formateado que contiene emojis, datos del cliente y el desglose exacto de los productos del carrito con sus respectivos precios y total.
* **Punto de la Autoevaluación que cumple:**
  - **Tienda y pagos (Punto 4 y Checklist):** *Generación dinámica de pedido para WhatsApp. El mensaje debe coincidir con el carrito.* (Funcionalidad boutique con carrito reactivo y enlace de checkout directo).

---

### Escena 4: Inventario Inicial y Alertas de Stock (Cajero / Administrador)
* **Qué hacer en pantalla:**
  1. Cierra sesión como cliente e inicia sesión como Recepcionista (`recepcion@petspa.com` / `password`).
  2. En el navbar superior, haz clic en **"Inventario"** (ruta `/admin/inventario`).
  3. Muestra el panel glassmórfico oscuro con los indicadores KPI (*"Total Productos"*, *"Productos en Alerta (Bajo Stock)"*).
  4. Muestra la lista de productos y destaca los indicadores de alerta en rojo/naranja.
  5. Señala los stocks iniciales de **"Shampoo Canino Avena Premium"** (Ej: *20 unidades*) y **"Acondicionador Brillo Sedoso"** (Ej: *15 unidades*).
* **Punto de la Autoevaluación que cumple:**
  - **Gestión de insumos y Alertas (Puntos 6, 7 y 8):** *¿Se generan alertas por bajo stock o consumo elevado?* (Completamente cubierto con el dashboard analítico de inventario).

---

### Escena 5: Workspace del Estilista — Cierre con Checklist y Descuento Automático
* **Qué hacer en pantalla:**
  1. Abre otra pestaña en modo incógnito (o cierra sesión) e inicia sesión como Estilista (`groomer1@petspa.com` / `password`).
  2. Entra a **"Groomer Workspace"** (ruta `/groomer/workspace`). Verás la agenda del día asignada al groomer.
  3. Haz clic en **"Iniciar Servicio"** en una cita en estado programado. Muestra cómo la cita cambia a *"EN_PROCESO"*.
  4. Llena los campos de la ficha técnica (Temperamento, condición general).
  5. **CLAVE - DEMOSTRACIÓN DE BLOQUEO:** Desmarca todas las opciones del checklist y muestra que el botón **"Finalizar Servicio / Cerrar Ficha"** se encuentra **deshabilitado**.
  6. Marca al menos un ítem (Ej: *"Lavado"* o *"Secado"*). El botón se habilitará.
  7. Haz clic en **"Finalizar Servicio"** y confirma en el modal.
  8. Muestra el mensaje informativo emergente: *"¡Tu mascota Toby está lista para recoger!"*.
  9. **Explicación por voz:** Explica al evaluador que en este preciso instante, el backend de Laravel ha enviado de forma real y asíncrona un correo electrónico estilizado en Markdown (`PickupReadyMail`) al email del cliente avisándole del retiro de su mascota.
* **Punto de la Autoevaluación que cumple:**
  - **Grooming (Punto 3 y Checklist):** *Checklist obligatorio antes del cierre. ¿El botón de cierre se bloquea si falta?*
  - **Notificaciones (Punto 9 y Checklist):** *Aviso automático de recojo al cliente por correo real.*

---

### Escena 6: Auditoría de Insumos y Log Firmado por el Groomer
* **Qué hacer en pantalla:**
  1. Regresa a la pestaña donde tenías iniciada la sesión de Recepción/Administrador y recarga el panel de **Inventario**.
  2. Muestra que el stock global de **"Shampoo Canino Avena Premium"** ha disminuido automáticamente de 20 a 19, y el **"Acondicionador Brillo Sedoso"** de 15 a 14.
  3. Haz clic en el botón de **"Ver Historial / Movimientos"** de alguno de los insumos consumidos.
  4. Muestra en el log de movimientos el nuevo registro de tipo `SALIDA` de 1 unidad con el motivo: *"Consumo automático en servicio de grooming de Toby"*.
  5. **MUESTRA EL CAMPO DE FIRMA:** Señala que el movimiento está vinculado al nombre del groomer que realizó el servicio y al ID único de la Cita de la mascota.
* **Punto de la Autoevaluación que cumple:**
  - **Gestión de insumos e Inventario integrado (Puntos 6 y 7):** *¿Existe un log de salida vinculado al groomer? ¿Disminuye el stock global después del servicio?* (Cumplido al 100% con descuento automático e historial transaccional).

---

### Escena 7: Recepción — Cobro de Citas con Persistencia Física en Pagos
* **Qué hacer en pantalla:**
  1. Sigue en tu sesión de Recepcionista y dirígete al **Calendario de Recepción** (ruta `/reception/calendar`).
  2. Busca la cita que el groomer acaba de finalizar (estará en estado *"FINALIZADO"*, lista para cobro).
  3. Haz clic en **"Registrar Pago"** para abrir el modal de facturación.
  4. Selecciona el método de pago: **"TRANSFERENCIA"** (demuestra que el sistema valida e implementa QR, Efectivo y Transferencia).
  5. Registra un descuento opcional (Ej: *$5.00*), escribe el número de referencia bancaria (*"TX-987654"*), añade notas y haz clic en **"Confirmar Pago"**.
  6. Muestra que la cita cambia a estado *"PAGADO"* y el recibo se puede previsualizar en pantalla.
  7. **Explicación por voz:** Explica al evaluador que en lugar de guardarse únicamente en un log de actividades básico, todos estos detalles de facturación se han registrado físicamente en la nueva tabla `pagos` en PostgreSQL (asociando ID de pago, cita, cajero, método, montos brutos/netos y referencias bancarias).
* **Punto de la Autoevaluación que cumple:**
  - **Pagos QR/Efectivo (Puntos 10, 20 y Checklist):** *Registro de pagos persistido en una tabla estructurada en base de datos PostgreSQL.*

---

### Escena 8: Cierre y Arquitectura de Código (Opcional - Toque Técnico)
* **Qué hacer en pantalla:** Abre tu editor de código (VS Code o similar).
* **Qué mostrar brevemente:**
  - Abre el archivo `GroomingController.php` y resalta las líneas donde se realiza el `try/catch` para mandar el email `PickupReadyMail` y el descuento automático de insumos con `MovimientoInventario::create`.
  - Abre el archivo `CitaController.php` en `registrarPago` y destaca la creación del registro en el modelo `Pago::create`.
* **Qué decir (Guion de voz):**
  - > *"Como podemos observar en el código, todas las reglas de negocio están blindadas en el backend con transacciones atómicas de base de datos. Las notificaciones son asíncronas y seguras, y el frontend reactivo implementado en Vue 3 y Pinia se comunica de forma óptima bajo los más altos estándares de desarrollo web. Con esto queda completamente demostrada la entrega."*

---

## 💡 Consejos Técnicos para la Grabación

1. **Usa dos navegadores diferentes:** Ten iniciada la sesión de Recepción/Admin en Google Chrome y la de Estilista/Groomer en Firefox (o una ventana de incógnito). Esto te evitará tener que cerrar e iniciar sesión a cada rato en el video, logrando transiciones fluidas.
2. **Haz zoom en el navegador:** Incrementa el zoom a un 110% o 120% en tu navegador para que los textos, botones, badges y modales se vean perfectos a resoluciones de video como 1080p.
3. **Resalta las validaciones de error:** Explicar los controles de error (como desmarcar el checklist para mostrar el botón bloqueado) le demuestra al evaluador que el sistema es robusto y que las validaciones son reales y no simuladas.
