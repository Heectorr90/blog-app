<template>
  <v-container>
    <v-row>
      <v-col cols="12" class="d-flex justify-space-between align-center">
        <h1 class="text-h4">Gestionar Artículos</h1>
        <v-btn color="primary" prepend-icon="mdi-plus" @click="$router.push('/articles/create')">
          Nuevo Artículo
        </v-btn>
      </v-col>
    </v-row>

    <!-- Filtros y búsqueda -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-text-field
          v-model="search"
          label="Buscar artículos..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          @input="searchArticles"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="filterStatus"
          :items="statusOptions"
          label="Estado"
          variant="outlined"
          density="compact"
          clearable
          @update:modelValue="filterArticles"
        ></v-select>
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          v-model="filterCategory"
          :items="categories"
          item-title="name"
          item-value="id"
          label="Categoría"
          variant="outlined"
          density="compact"
          clearable
          @update:modelValue="filterArticles"
        ></v-select>
      </v-col>
    </v-row>

    <!-- Tabla de artículos -->
    <v-row>
      <v-col cols="12">
        <v-card>
          <v-data-table
            :headers="headers"
            :items="articles"
            :loading="loading"
            :items-per-page="10"
            class="elevation-1"
          >
            <!-- Imagen -->
            <template #[`item.image`]="{ item }">
              <v-avatar size="50" rounded class="my-2">
                <v-img v-if="item.image" :src="getImageUrl(item.image)" cover></v-img>
                <v-icon v-else>mdi-image-off</v-icon>
              </v-avatar>
            </template>

            <!-- Título -->
            <template #[`item.title`]="{ item }">
              <div class="text-truncate" style="max-width: 300px">
                {{ item.title }}
              </div>
            </template>

            <!-- Categoría -->
            <template #[`item.category`]="{ item }">
              <v-chip v-if="item.category" size="small" color="primary" variant="tonal">
                {{ item.category.name }}
              </v-chip>
              <span v-else class="text-grey">Sin categoría</span>
            </template>

            <!-- Estado -->
            <template #[`item.status`]="{ item }">
              <v-chip :color="item.status === 'published' ? 'success' : 'warning'" size="small">
                {{ item.status === 'published' ? 'Publicado' : 'Borrador' }}
              </v-chip>
            </template>

            <!-- Fecha -->
            <template #[`item.created_at`]="{ item }">
              {{ formatDate(item.created_at) }}
            </template>

            <!-- Acciones -->
            <template #[`item.actions`]="{ item }">
              <v-btn
                icon="mdi-eye"
                size="small"
                variant="text"
                @click="viewArticle(item.id)"
              ></v-btn>
              <v-btn
                icon="mdi-pencil"
                size="small"
                variant="text"
                color="primary"
                @click="editArticle(item.id)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                size="small"
                variant="text"
                color="error"
                @click="confirmDelete(item)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <!-- Dialog de confirmación -->
    <v-dialog v-model="deleteDialog" max-width="500">
      <v-card>
        <v-card-title class="text-h5"> ¿Eliminar artículo? </v-card-title>
        <v-card-text>
          ¿Estás seguro de que deseas eliminar el artículo "{{ articleToDelete?.title }}"? Esta
          acción no se puede deshacer.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn variant="text" @click="deleteDialog = false"> Cancelar </v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteArticle">
            Eliminar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar de notificaciones -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarMessage }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios'
import { getImageUrl, formatDateShort } from '@/plugins/helpers'

const router = useRouter()

const articles = ref([])
const categories = ref([])
const loading = ref(false)
const deleting = ref(false)
const search = ref('')
const filterStatus = ref(null)
const filterCategory = ref(null)
const deleteDialog = ref(false)
const articleToDelete = ref(null)

const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const statusOptions = [
  { title: 'Publicado', value: 'published' },
  { title: 'Borrador', value: 'draft' },
]

const headers = [
  { title: 'Imagen', key: 'image', sortable: false },
  { title: 'Título', key: 'title' },
  { title: 'Categoría', key: 'category' },
  { title: 'Estado', key: 'status' },
  { title: 'Fecha', key: 'created_at' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'center' },
]

onMounted(() => {
  loadArticles()
  loadCategories()
})

const loadArticles = async () => {
  loading.value = true
  try {
    const params = {}

    if (search.value) params.search = search.value
    if (filterStatus.value) params.status = filterStatus.value
    if (filterCategory.value) params.category_id = filterCategory.value

    const response = await api.get('/articles', { params })
    articles.value = response.data.data || response.data
  } catch (error) {
    console.error('Error al cargar artículos:', error)
    showSnackbar('Error al cargar artículos', 'error')
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Error al cargar categorías:', error)
  }
}

const searchArticles = () => {
  loadArticles()
}

const filterArticles = () => {
  loadArticles()
}

const viewArticle = (id) => {
  router.push(`/blog/${id}`)
}

const editArticle = (id) => {
  router.push(`/articles/${id}/edit`)
}

const confirmDelete = (article) => {
  articleToDelete.value = article
  deleteDialog.value = true
}

const deleteArticle = async () => {
  deleting.value = true
  try {
    await api.delete(`/articles/${articleToDelete.value.id}`)
    showSnackbar('Artículo eliminado exitosamente', 'success')
    deleteDialog.value = false
    loadArticles()
  } catch (error) {
    console.error('Error al eliminar artículo:', error)
    showSnackbar('Error al eliminar artículo', 'error')
  } finally {
    deleting.value = false
  }
}

const formatDate = formatDateShort

const showSnackbar = (message, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  snackbar.value = true
}
</script>
