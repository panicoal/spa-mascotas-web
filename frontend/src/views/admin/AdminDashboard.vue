<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Pet Spa - Administrador</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">Bienvenido, {{ user?.nombre_completo }}</span>
            <button
              @click="handleLogout"
              class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm font-medium"
            >
              Cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- Alertas -->
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ errorMessage }}
        </div>

        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Gestión de Personal</h2>

          <!-- Formulario para crear empleado -->
          <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Crear Nuevo Empleado</h3>
            <form @submit.prevent="handleCreateStaff" class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label for="nombre_completo" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                <input
                  id="nombre_completo"
                  v-model="newStaff.nombre_completo"
                  type="text"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input
                  id="email"
                  v-model="newStaff.email"
                  type="email"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input
                  id="telefono"
                  v-model="newStaff.telefono"
                  type="tel"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <label for="rol" class="block text-sm font-medium text-gray-700">Rol</label>
                <select
                  id="rol"
                  v-model="newStaff.rol"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Seleccionar rol</option>
                  <option value="RECEPCION">Recepcionista</option>
                  <option value="GROOMER">Groomer</option>
                  <option value="ADMIN">Administrador</option>
                </select>
              </div>
              <div>
                <label for="turno" class="block text-sm font-medium text-gray-700">Turno</label>
                <select
                  id="turno"
                  v-model="newStaff.turno"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Sin turno asignado</option>
                  <option value="MAÑANA">Mañana</option>
                  <option value="TARDE">Tarde</option>
                  <option value="NOCHE">Noche</option>
                </select>
              </div>
              <div class="md:col-span-4">
                <button
                  type="submit"
                  :disabled="loadingCreate"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  <span v-if="loadingCreate" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                  Crear Empleado
                </button>
              </div>
            </form>
          </div>

          <!-- Tabla de empleados -->
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Empleados Actuales</h3>
              <div class="flex space-x-2">
                <button
                  @click="showDeleted = false"
                  :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    !showDeleted ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                  ]"
                >
                  Activos
                </button>
                <button
                  @click="showDeleted = true"
                  :class="[
                    'px-3 py-2 rounded-md text-sm font-medium',
                    showDeleted ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                  ]"
                >
                  Eliminados
                </button>
              </div>
            </div>

            <!-- Loading state -->
            <div v-if="loadingEmployees" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Cargando empleados...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="filteredEmployees.length === 0" class="text-center py-8">
              <p class="text-gray-600">
                {{ showDeleted ? 'No hay empleados eliminados' : 'No hay empleados registrados' }}
              </p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Nombre
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Teléfono
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Rol
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Turno
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="employee in filteredEmployees" :key="employee.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ employee.nombre_completo }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ employee.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ employee.telefono || 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ getRoleName(employee) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ employee.turno || 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                          employee.deleted_at
                            ? 'bg-red-100 text-red-800'
                            : 'bg-green-100 text-green-800'
                        ]"
                      >
                        {{ employee.deleted_at ? 'Eliminado' : 'Activo' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button
                        v-if="!employee.deleted_at"
                        @click="openEditModal(employee)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        Editar
                      </button>
                      <button
                        v-if="!employee.deleted_at"
                        @click="confirmDelete(employee)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Eliminar
                      </button>
                      <button
                        v-if="employee.deleted_at"
                        @click="handleRestore(employee)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Restaurar
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Trazabilidad / Audit Logs -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-4">Trazabilidad del Sistema</h2>

          <!-- Filtros -->
          <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros de Búsqueda</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label for="filter_user" class="block text-sm font-medium text-gray-700">Usuario</label>
                <select
                  id="filter_user"
                  v-model="auditFilters.usuario_id"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Todos los usuarios</option>
                  <option v-for="user in allUsers" :key="user.id" :value="user.id">
                    {{ user.nombre_completo }} ({{ user.email }})
                  </option>
                </select>
              </div>
              <div>
                <label for="filter_action" class="block text-sm font-medium text-gray-700">Acción</label>
                <select
                  id="filter_action"
                  v-model="auditFilters.accion"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="">Todas las acciones</option>
                  <option value="LOGIN">Login</option>
                  <option value="LOGOUT">Logout</option>
                  <option value="REGISTRO">Registro</option>
                  <option value="REGISTRO_GOOGLE">Registro Google</option>
                  <option value="LOGIN_GOOGLE">Login Google</option>
                  <option value="CREAR_EMPLEADO">Crear Empleado</option>
                  <option value="ACTUALIZAR_EMPLEADO">Actualizar Empleado</option>
                  <option value="ELIMINAR_EMPLEADO">Eliminar Empleado</option>
                  <option value="RESTAURAR_EMPLEADO">Restaurar Empleado</option>
                </select>
              </div>
              <div>
                <label for="filter_date_from" class="block text-sm font-medium text-gray-700">Fecha Desde</label>
                <input
                  id="filter_date_from"
                  v-model="auditFilters.fecha_desde"
                  type="date"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <label for="filter_date_to" class="block text-sm font-medium text-gray-700">Fecha Hasta</label>
                <input
                  id="filter_date_to"
                  v-model="auditFilters.fecha_hasta"
                  type="date"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>
            </div>
            <div class="mt-4 flex space-x-2">
              <button
                @click="loadAuditLogs"
                :disabled="loadingAudit"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                <span v-if="loadingAudit" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                Buscar
              </button>
              <button
                @click="clearAuditFilters"
                class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
              >
                Limpiar Filtros
              </button>
            </div>
          </div>

          <!-- Tabla de logs de auditoría -->
          <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Registro de Actividades</h3>
              <div class="text-sm text-gray-600">
                Total: {{ auditLogs.total || 0 }} registros
              </div>
            </div>

            <!-- Loading state -->
            <div v-if="loadingAudit" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
              <p class="mt-2 text-gray-600">Cargando registros...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="!auditLogs.data || auditLogs.data.length === 0" class="text-center py-8">
              <p class="text-gray-600">No se encontraron registros de auditoría</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Usuario
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acción
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Fecha/Hora
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      IP/Navegador
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Detalles
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="log in auditLogs.data" :key="log.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      <div>
                        <div class="font-medium">{{ log.nombre_completo || 'Usuario desconocido' }}</div>
                        <div class="text-gray-500 text-xs">{{ log.email }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                          getActionBadgeClass(log.accion)
                        ]"
                      >
                        {{ getActionLabel(log.accion) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ formatDate(log.created_at) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                      <div>
                        <div class="font-medium">{{ log.ip_address }}</div>
                        <div class="text-xs text-gray-500 truncate max-w-xs" :title="log.user_agent">
                          {{ truncateUserAgent(log.user_agent) }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                      <div v-if="log.tabla_afectada" class="space-y-1">
                        <div><strong>Tabla:</strong> {{ log.tabla_afectada }}</div>
                        <div v-if="log.registro_id"><strong>ID:</strong> {{ log.registro_id }}</div>
                        <button
                          v-if="log.datos_antes || log.datos_despues"
                          @click="showLogDetails(log)"
                          class="text-indigo-600 hover:text-indigo-900 text-xs underline"
                        >
                          Ver cambios
                        </button>
                      </div>
                      <div v-else class="text-gray-400 italic">
                        Sin detalles adicionales
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination -->
              <div v-if="auditLogs.last_page > 1" class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Mostrando {{ auditLogs.from }} a {{ auditLogs.to }} de {{ auditLogs.total }} resultados
                </div>
                <div class="flex space-x-2">
                  <button
                    @click="changeAuditPage(auditLogs.current_page - 1)"
                    :disabled="auditLogs.current_page <= 1"
                    class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Anterior
                  </button>
                  <span class="px-3 py-1 text-sm text-gray-700">
                    Página {{ auditLogs.current_page }} de {{ auditLogs.last_page }}
                  </span>
                  <button
                    @click="changeAuditPage(auditLogs.current_page + 1)"
                    :disabled="auditLogs.current_page >= auditLogs.last_page"
                    class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                  >
                    Siguiente
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal de Edición -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Empleado</h3>
        <form @submit.prevent="handleUpdate" class="space-y-4">
          <div>
            <label for="edit_nombre_completo" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
            <input
              id="edit_nombre_completo"
              v-model="editingEmployee.nombre_completo"
              type="text"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
              id="edit_email"
              v-model="editingEmployee.email"
              type="email"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="edit_telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
            <input
              id="edit_telefono"
              v-model="editingEmployee.telefono"
              type="tel"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="edit_rol" class="block text-sm font-medium text-gray-700">Rol</label>
            <select
              id="edit_rol"
              v-model="editingEmployee.rol"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="RECEPCION">Recepcionista</option>
              <option value="GROOMER">Groomer</option>
              <option value="ADMIN">Administrador</option>
            </select>
          </div>
          <div>
            <label for="edit_turno" class="block text-sm font-medium text-gray-700">Turno</label>
            <select
              id="edit_turno"
              v-model="editingEmployee.turno"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Sin turno asignado</option>
              <option value="MAÑANA">Mañana</option>
              <option value="TARDE">Tarde</option>
              <option value="NOCHE">Noche</option>
            </select>
          </div>
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="showEditModal = false"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="loadingUpdate"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50"
            >
              <span v-if="loadingUpdate" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></span>
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div
      v-if="showDeleteConfirm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar a <strong>{{ employeeToDelete?.nombre_completo }}</strong>? 
          Esta acción puede ser revertida más tarde.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteConfirm = false"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="handleDelete"
            :disabled="loadingDelete"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50"
          >
            <span v-if="loadingDelete" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></span>
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Detalles del Log -->
    <div
      v-if="showLogDetailsModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Detalles del Registro de Auditoría</h3>
          <button
            @click="showLogDetailsModal = false"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <div v-if="selectedLog" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Usuario</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedLog.nombre_completo || 'Usuario desconocido' }}</p>
              <p class="text-xs text-gray-500">{{ selectedLog.email }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Acción</label>
              <p class="mt-1 text-sm text-gray-900">{{ getActionLabel(selectedLog.accion) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Fecha y Hora</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedLog.created_at) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Dirección IP</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedLog.ip_address }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">User Agent (Navegador)</label>
            <p class="mt-1 text-sm text-gray-900 break-all">{{ selectedLog.user_agent }}</p>
          </div>

          <div v-if="selectedLog.tabla_afectada" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Tabla Afectada</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedLog.tabla_afectada }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">ID del Registro</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedLog.registro_id || 'N/A' }}</p>
            </div>
          </div>

          <!-- Datos antes -->
          <div v-if="selectedLog.datos_antes" class="border-t pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Datos Anteriores</label>
            <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">{{ formatJson(selectedLog.datos_antes) }}</pre>
          </div>

          <!-- Datos después -->
          <div v-if="selectedLog.datos_despues" class="border-t pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Datos Posteriores</label>
            <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">{{ formatJson(selectedLog.datos_despues) }}</pre>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <button
            @click="showLogDetailsModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import {
  getEmployees,
  createEmployee,
  updateEmployee,
  deleteEmployee,
  restoreEmployee,
  getAuditLogs,
  getAuditLog
} from '@/services/adminService'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const employees = ref([])
const loadingEmployees = ref(false)
const loadingCreate = ref(false)
const loadingUpdate = ref(false)
const loadingDelete = ref(false)

const successMessage = ref('')
const errorMessage = ref('')

const newStaff = ref({
  nombre_completo: '',
  email: '',
  telefono: '',
  ci: '',
  rol: '',
  turno: ''
})

const showEditModal = ref(false)
const editingEmployee = ref({})
const originalEmployee = ref({})

const showDeleteConfirm = ref(false)
const employeeToDelete = ref(null)

const showDeleted = ref(false)

const filteredEmployees = computed(() => {
  if (showDeleted.value) {
    return employees.value.filter(emp => emp.deleted_at)
  } else {
    return employees.value.filter(emp => !emp.deleted_at)
  }
})

// Audit Logs
const auditLogs = ref({})
const loadingAudit = ref(false)
const auditFilters = ref({
  usuario_id: '',
  accion: '',
  fecha_desde: '',
  fecha_hasta: ''
})
const allUsers = ref([])
const showLogDetailsModal = ref(false)
const selectedLog = ref(null)

const getRoleName = (employee) => {
  const roles = employee.role_names || []
  return roles.length > 0 ? roles[0] : 'Sin rol'
}

const fetchEmployees = async () => {
  loadingEmployees.value = true
  errorMessage.value = ''
  try {
    const response = await getEmployees()
    employees.value = response.data.employees
  } catch (err) {
    errorMessage.value = 'Error al cargar los empleados'
    console.error(err)
  } finally {
    loadingEmployees.value = false
  }
}

const handleCreateStaff = async () => {
  loadingCreate.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await createEmployee(newStaff.value)
    successMessage.value = 'Empleado creado exitosamente'
    newStaff.value = { nombre_completo: '', email: '', telefono: '', ci: '', rol: '', turno: '' }
    await fetchEmployees()
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error al crear empleado'
  } finally {
    loadingCreate.value = false
  }
}

const openEditModal = (employee) => {
  originalEmployee.value = employee
  editingEmployee.value = { ...employee }
  showEditModal.value = true
}

const handleUpdate = async () => {
  loadingUpdate.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const updateData = {
      nombre_completo: editingEmployee.value.nombre_completo,
      email: editingEmployee.value.email,
      telefono: editingEmployee.value.telefono,
      ci: editingEmployee.value.ci,
      rol: editingEmployee.value.role_names?.[0] || editingEmployee.value.rol,
      turno: editingEmployee.value.turno
    }
    
    await updateEmployee(editingEmployee.value.id, updateData)
    successMessage.value = 'Empleado actualizado exitosamente'
    showEditModal.value = false
    await fetchEmployees()
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error al actualizar empleado'
  } finally {
    loadingUpdate.value = false
  }
}

const confirmDelete = (employee) => {
  employeeToDelete.value = employee
  showDeleteConfirm.value = true
}

const handleDelete = async () => {
  loadingDelete.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await deleteEmployee(employeeToDelete.value.id)
    successMessage.value = 'Empleado eliminado exitosamente'
    showDeleteConfirm.value = false
    employeeToDelete.value = null
    await fetchEmployees()
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error al eliminar empleado'
  } finally {
    loadingDelete.value = false
  }
}

const handleRestore = async (employee) => {
  loadingDelete.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await restoreEmployee(employee.id)
    successMessage.value = 'Empleado restaurado exitosamente'
    await fetchEmployees()
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Error al restaurar empleado'
  } finally {
    loadingDelete.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

// Audit Logs Functions
const loadAuditLogs = async (page = 1) => {
  loadingAudit.value = true
  errorMessage.value = ''
  try {
    const params = {
      page,
      ...auditFilters.value
    }
    const response = await getAuditLogs(params)
    auditLogs.value = response.data.logs
  } catch (err) {
    errorMessage.value = 'Error al cargar los logs de auditoría'
    console.error(err)
  } finally {
    loadingAudit.value = false
  }
}

const clearAuditFilters = () => {
  auditFilters.value = {
    usuario_id: '',
    accion: '',
    fecha_desde: '',
    fecha_hasta: ''
  }
  loadAuditLogs()
}

const changeAuditPage = (page) => {
  loadAuditLogs(page)
}

const showLogDetails = (log) => {
  selectedLog.value = log
  showLogDetailsModal.value = true
}

const getActionLabel = (action) => {
  const labels = {
    'LOGIN': 'Inicio de Sesión',
    'LOGOUT': 'Cierre de Sesión',
    'REGISTRO': 'Registro de Usuario',
    'REGISTRO_GOOGLE': 'Registro con Google',
    'LOGIN_GOOGLE': 'Login con Google',
    'CREAR_EMPLEADO': 'Crear Empleado',
    'ACTUALIZAR_EMPLEADO': 'Actualizar Empleado',
    'ELIMINAR_EMPLEADO': 'Eliminar Empleado',
    'RESTAURAR_EMPLEADO': 'Restaurar Empleado'
  }
  return labels[action] || action
}

const getActionBadgeClass = (action) => {
  const classes = {
    'LOGIN': 'bg-green-100 text-green-800',
    'LOGOUT': 'bg-gray-100 text-gray-800',
    'REGISTRO': 'bg-blue-100 text-blue-800',
    'REGISTRO_GOOGLE': 'bg-blue-100 text-blue-800',
    'LOGIN_GOOGLE': 'bg-green-100 text-green-800',
    'CREAR_EMPLEADO': 'bg-indigo-100 text-indigo-800',
    'ACTUALIZAR_EMPLEADO': 'bg-yellow-100 text-yellow-800',
    'ELIMINAR_EMPLEADO': 'bg-red-100 text-red-800',
    'RESTAURAR_EMPLEADO': 'bg-green-100 text-green-800'
  }
  return classes[action] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleString('es-ES', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const truncateUserAgent = (userAgent) => {
  if (!userAgent) return 'N/A'
  return userAgent.length > 50 ? userAgent.substring(0, 50) + '...' : userAgent
}

const formatJson = (jsonString) => {
  try {
    const parsed = JSON.parse(jsonString)
    return JSON.stringify(parsed, null, 2)
  } catch {
    return jsonString
  }
}

const loadAllUsers = async () => {
  try {
    const response = await getEmployees()
    allUsers.value = response.data.employees || []
  } catch (err) {
    console.error('Error al cargar usuarios:', err)
  }
}

onMounted(() => {
  fetchEmployees()
  loadAllUsers()
  loadAuditLogs()
})
</script>