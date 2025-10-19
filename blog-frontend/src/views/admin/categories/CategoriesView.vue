<template>
  <v-container>
    <v-row>
      <v-col cols="12" class="d-flex justify-space-between align-center">
        <h1 class="text-h4">Gestionar Categorías</h1>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreateDialog">
          Nueva Categoría
        </v-btn>
      </v-col>
    </v-row>

    <!-- Tabla de categorías -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-data-table
            :headers="headers"
            :items="categories"
            :loading="loading"
            :items-per-page="10"
            class="elevation-1"
          >
            <!-- Nombre -->
            <template #[`item.name`]="{ item }">
              <div class="d-flex align-center">
                <v-icon color="primary" class="mr-2">mdi-tag</v-icon>
                <strong>{{ item.name }}</strong>
              </div>
            </template>

            <!-- Slug -->
            <template #[`item.slug`]="{ item }">
              <code class="text-grey">{{ item.slug }}</code>
            </template>

            <!-- Artículos -->
            <template #[`item.articles_count`]="{ item }">
              <v-chip size="small" color="info" variant="tonal">
                {{ item.articles_count || 0 }} artículos
              </v-chip>
            </template>

            <!-- Acciones -->
            <template #[`item.actions`]="{ item }">
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                color="primary"
                @click="openEditDialog(item)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="small"
                variant="text"
                color="error"
                :disabled="item.articles_count > 0"
                @click="confirmDelete(item)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <!-- Dialog de crear/editar -->
    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title class="text-h5">
          {{ isEditMode ? 'Editar Categoría' : 'Nueva Categoría' }}
        </v-card-title>

        <v-card-text>
          <v-form ref="form" v-model="valid">
            <v-text-field
              v-model="categoryForm.name"
              :rules="[rules.required]"
              label="Nombre *"
              variant="outlined"
              counter="255"
              class="mb-4"
            ></v-text-field>

            <v-textarea
              v-model="categoryForm.description"
              label="Descripción"
              variant="outlined"
              rows="3"
              counter="500"
            ></v-textarea>
          </v-form>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="dialog = false"> Cancelar </v-btn>
          <v-btn
            color="primary"
            variant="flat"
            :loading="saving"
            :disabled="!valid"
            @click="saveCategory"
          >
            {{ isEditMode ? 'Actualizar' : 'Crear' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog de confirmación de eliminación -->
    <v-dialog v-model="deleteDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5"> ¿Eliminar categoría? </v-card-title>
        <v-card-text>
          ¿Estás seguro de que deseas eliminar la categoría "{{ categoryToDelete?.name }}"? Esta
          acción no se puede deshacer.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false"> Cancelar </v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteCategory">
            Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarMessage }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/plugins/axios'

const categories = ref([])
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const dialog = ref(false)
const deleteDialog = ref(false)
const form = ref(null)
const valid = ref(false)
const isEditMode = ref(false)
const categoryToDelete = ref(null)

const categoryForm = ref({
  id: null,
  name: '',
  description: '',
})

const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const headers = [
  { title: 'Nombre', key: 'name' },
  { title: 'Slug', key: 'slug' },
  { title: 'Descripción', key: 'description' },
  { title: 'Artículos', key: 'articles_count', align: 'center' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'center' },
]

const rules = {
  required: (v) => !!v || 'Este campo es requerido',
}

onMounted(() => {
  loadCategories()
})

const loadCategories = async () => {
  loading.value = true
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Error al cargar categorías:', error)
    showSnackbar('Error al cargar categorías', 'error')
  } finally {
    loading.value = false
  }
}

const openCreateDialog = () => {
  isEditMode.value = false
  categoryForm.value = {
    id: null,
    name: '',
    description: '',
  }
  dialog.value = true
}

const openEditDialog = (category) => {
  isEditMode.value = true
  categoryForm.value = {
    id: category.id,
    name: category.name,
    description: category.description || '',
  }
  dialog.value = true
}

const saveCategory = async () => {
  const { valid: isValid } = await form.value.validate()

  if (!isValid) return

  saving.value = true

  try {
    const data = {
      name: categoryForm.value.name,
      description: categoryForm.value.description,
    }

    if (isEditMode.value) {
      await api.put(`/categories/${categoryForm.value.id}`, data)
      showSnackbar('Categoría actualizada exitosamente', 'success')
    } else {
      await api.post('/categories', data)
      showSnackbar('Categoría creada exitosamente', 'success')
    }

    dialog.value = false
    loadCategories()
  } catch (error) {
    console.error('Error al guardar categoría:', error)
    showSnackbar(error.response?.data?.message || 'Error al guardar categoría', 'error')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (category) => {
  categoryToDelete.value = category
  deleteDialog.value = true
}

const deleteCategory = async () => {
  deleting.value = true
  try {
    await api.delete(`/categories/${categoryToDelete.value.id}`)
    showSnackbar('Categoría eliminada exitosamente', 'success')
    deleteDialog.value = false
    loadCategories()
  } catch (error) {
    console.error('Error al eliminar categoría:', error)
    showSnackbar('Error al eliminar categoría', 'error')
  } finally {
    deleting.value = false
  }
}

const showSnackbar = (message, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  snackbar.value = true
}
</script>
