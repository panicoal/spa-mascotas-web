import api from './api'

export const getPublicServices = () => {
  return api.get('/servicios')
}

export const getServices = () => {
  return api.get('/admin/services')
}

export const getService = (id) => {
  return api.get(`/admin/services/${id}`)
}

export const createService = (data) => {
  return api.post('/admin/services', data)
}

export const updateService = (id, data) => {
  return api.put(`/admin/services/${id}`, data)
}

export const deleteService = (id) => {
  return api.delete(`/admin/services/${id}`)
}

export const restoreService = (id) => {
  return api.patch(`/admin/services/${id}/restore`)
}