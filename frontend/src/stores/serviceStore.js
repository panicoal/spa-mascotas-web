import { defineStore } from 'pinia'

import {

  getServices,

  createService,

  updateService,

  deleteService,

  restoreService

} from '@/services/serviceService'

export const useServiceStore = defineStore(
  'service',
  {

    state: () => ({

      services: [],

      loading: false,

      selectedService: null
    }),

    actions: {

      async fetchServices() {

        this.loading = true

        try {

          const response =
            await getServices()

          this.services =
            response.data.services

        } catch (error) {

          console.error(error)

        } finally {

          this.loading = false
        }
      },

      async createService(data) {

        await createService(data)

        await this.fetchServices()
      },

      async updateService(id, data) {

        await updateService(id, data)

        await this.fetchServices()
      },

      async deleteService(id) {

        await deleteService(id)

        await this.fetchServices()
      },

      async restoreService(id) {

        await restoreService(id)

        await this.fetchServices()
      }
    }
  }
)