import api from './api'

export const loginRequest = (data) => {
  return api.post('/auth/login', data)
}

export const registerRequest = (data) => {
  return api.post('/auth/register', data)
}

export const getMe = () => {
  return api.get('/auth/me')
}

export const logoutRequest = () => {
  return api.post('/auth/logout')
}

export const verify2FARequest = (data) => {
  return api.post('/2fa/verify', data)
}

export const generate2FARequest = () => {
  return api.post('/2fa/generate')
}

export const enable2FARequest = (data) => {
  return api.post('/2fa/enable', data)
}

export const verifyEmailRequest = (params) => {
  return api.get('/email/verify', { params })
}

export const disable2FARequest = () => {
  return api.post('/2fa/disable')
}