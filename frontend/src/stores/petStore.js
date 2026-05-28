import { defineStore } from 'pinia'

import {

    getPetsRequest,

    getPetRequest,

    createPetRequest,

    updatePetRequest,

    deletePetRequest

} from '@/services/petService'

export const usePetStore = defineStore('pets', {

    state: () => ({

        pets: [],

        selectedPet: null,

        loading: false
    }),

    actions: {

        async fetchPets() {

            this.loading = true

            try {

                const response =
                    await getPetsRequest()

                this.pets =
                    response.data.pets

            } catch (error) {

                console.error(error)

            } finally {

                this.loading = false
            }
        },

        async fetchPet(id) {

            this.loading = true

            try {

                const response =
                    await getPetRequest(id)

                this.selectedPet =
                    response.data.pet

            } catch (error) {

                console.error(error)

            } finally {

                this.loading = false
            }
        },

        async createPet(data) {

            const response =
                await createPetRequest(data)

            this.pets.unshift(
                response.data.pet
            )

            return response
        },

        async updatePet(id, data) {

            const response =
                await updatePetRequest(
                    id,
                    data
                )

            const index =
                this.pets.findIndex(
                    pet => pet.id === id
                )

            if (index !== -1) {

                this.pets[index] =
                    response.data.pet
            }

            return response
        },

        async deletePet(id) {

            await deletePetRequest(id)

            this.pets =
                this.pets.filter(
                    pet => pet.id !== id
                )
        }
    }
})