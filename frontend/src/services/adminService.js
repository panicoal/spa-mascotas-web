import api from './api'

export const createStaffRequest = (data) => {
  return api.post('/admin/users/create-staff', data)
}

// Empleados - CRUD
export const getEmployees = () => {
  return api.get('/admin/employees')
}

export const createEmployee = (data) => {
  return api.post('/admin/employees', data)
}

export const updateEmployee = (id, data) => {
  return api.put(`/admin/employees/${id}`, data)
}

export const deleteEmployee = (id) => {
  return api.delete(`/admin/employees/${id}`)
}

export const restoreEmployee = (id) => {
  return api.patch(`/admin/employees/${id}/restore`)
}

// Audit Logs
export const getAuditLogs = (params = {}) => {
  return api.get('/admin/audit-logs', { params })
}

export const getAuditLog = (id) => {
  return api.get(`/admin/audit-logs/${id}`)
}