import api from './api'

export const getPetsRequest = () => {
    return api.get('/pets')
}

export const getPetRequest = (id) => {
    return api.get(`/pets/${id}`)
}

export const createPetRequest = (data) => {
    return api.post('/pets', data)
}

export const updatePetRequest = (id, data) => {
    return api.put(`/pets/${id}`, data)
}

export const deletePetRequest = (id) => {
    return api.delete(`/pets/${id}`)
}