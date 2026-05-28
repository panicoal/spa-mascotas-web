import api from './api'

// export const getGroomerAgenda = (fecha) => {
//   return api.get('/groomer/agenda', { params: { fecha } })
// }

export const getGroomerAgenda = (params = {}) => {
  return api.get('/groomer/agenda', {
    params
  })
}

export const iniciarFicha = (citaId) => {
  return api.post(`/groomer/fichas/${citaId}/iniciar`)
}

export const cerrarFicha = (citaId, data) => {
  return api.post(`/groomer/fichas/${citaId}/cerrar`, data)
}
