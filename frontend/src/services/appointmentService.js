import api from './api'

export const getDisponibilidad = (fecha, servicioId, mascotaId) => {
  return api.get('/agenda/disponibilidad', {
    params: { fecha, servicio_id: servicioId, mascota_id: mascotaId }
  })
}

export const getCitas = (params = {}) => {
  return api.get('/citas', { params })
}

export const createCita = (data) => {
  return api.post('/citas', data)
}

export const confirmarCita = (id) => {
  return api.put(`/citas/${id}/confirmar`)
}

export const reprogramarCita = (id, data) => {
  return api.put(`/citas/${id}/reprogramar`, data)
}

export const cancelarCita = (id, motivoCancelacion) => {
  return api.post(`/citas/${id}/cancelar`, { motivo_cancelacion: motivoCancelacion })
}

export const pagarCita = (id, data) => {
  return api.post(`/citas/${id}/pagar`, data) // data: { metodo_pago, monto }
}

export const storeBloqueo = (data) => {
  return api.post('/admin/bloqueos', data)
}
