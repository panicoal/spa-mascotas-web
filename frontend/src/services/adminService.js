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

// Reportes / KPIs
export const getDashboardKpis = () => {
  return api.get('/admin/dashboard-kpis')
}

export const getCierreCaja = () => {
  return api.get('/recepcion/cierre-caja')
}

export const closeCaja = () => {
  return api.post('/recepcion/cierre-caja')
}

// Audit Logs
export const getAuditLogs = (params = {}) => {
  return api.get('/admin/audit-logs', { params })
}

export const getAuditLog = (id) => {
  return api.get(`/admin/audit-logs/${id}`)
}

// Reportes en PDF
export const downloadReporteMensualPdf = (mes, anio) => {
  return api.get(`/admin/reporte/mensual-pdf?mes=${mes}&anio=${anio}`, {
    responseType: 'blob'
  })
}

export const downloadCierreCajaPdf = () => {
  return api.get('/recepcion/cierre-caja-pdf', {
    responseType: 'blob'
  })
}

export const downloadReporteInventarioPdf = (bajoStockOnly = false) => {
  const params = bajoStockOnly ? '?bajo_stock_only=true' : ''
  return api.get(`/inventario/reporte-pdf${params}`, {
    responseType: 'blob'
  })
}