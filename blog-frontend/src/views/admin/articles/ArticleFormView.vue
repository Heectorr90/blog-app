<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
          @click="$router.push('/articles')"
          class="mb-4"
        >
          Volver a artículos
        </v-btn>

        <h1 class="text-h4 mb-6">
          {{ isEditMode ? 'Editar Artículo' : 'Crear Artículo' }}
        </h1>
      </v-col>
    </v-row>

    <v-form ref="form" v-model="valid" @submit.prevent="saveArticle">
      <v-row>
        <!-- Columna principal -->
        <v-col cols="12" md="8">
          <v-card>
            <v-card-text>
              <!-- Título -->
              <v-text-field
                v-model="article.title"
                :rules="[rules.required]"
                label="Título del artículo *"
                variant="outlined"
                counter="255"
                class="mb-4"
              ></v-text-field>

              <!-- Extracto -->
              <v-textarea
                v-model="article.excerpt"
                label="Extracto (resumen corto)"
                variant="outlined"
                rows="3"
                counter="500"
                class="mb-4"
              ></v-textarea>

              <!-- Editor de contenido -->
              <div class="mb-4">
                <label class="text-subtitle-2 mb-2 d-block"> Contenido del artículo * </label>
                <QuillEditor
                  v-model:content="article.content"
                  content-type="html"
                  theme="snow"
                  :toolbar="editorToolbar"
                  style="min-height: 400px"
                />
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Columna lateral -->
        <v-col cols="12" md="4">
          <!-- Publicación -->
          <v-card class="mb-4">
            <v-card-title class="text-h6">Publicación</v-card-title>
            <v-card-text>
              <v-select
                v-model="article.status"
                :items="statusOptions"
                label="Estado *"
                variant="outlined"
                density="compact"
              ></v-select>

              <v-select
                v-model="article.category_id"
                :items="categories"
                :rules="[rules.required]"
                item-title="name"
                item-value="id"
                label="Categoría *"
                variant="outlined"
                density="compact"
              ></v-select>
            </v-card-text>
          </v-card>

          <!-- Imagen destacada -->
          <v-card class="mb-4">
            <v-card-title class="text-h6">Imagen Destacada</v-card-title>
            <v-card-text>
              <v-file-input
                v-model="imageFile"
                label="Seleccionar imagen"
                prepend-icon="mdi-camera"
                variant="outlined"
                accept="image/*"
                density="compact"
                @change="previewImage"
              ></v-file-input>

              <v-img
                v-if="imagePreview"
                :src="imagePreview"
                class="mt-4"
                max-height="200"
                cover
              ></v-img>

              <v-btn
                v-if="imagePreview && !imageFile"
                color="error"
                size="small"
                variant="text"
                class="mt-2"
                @click="removeImage"
              >
                Eliminar imagen
              </v-btn>
            </v-card-text>
          </v-card>

          <!-- Acciones -->
          <v-card>
            <v-card-text>
              <v-btn
                type="submit"
                color="primary"
                block
                :loading="saving"
                :disabled="!valid"
                class="mb-2"
              >
                {{ isEditMode ? 'Actualizar' : 'Publicar' }}
              </v-btn>

              <v-btn variant="outlined" block @click="$router.push('/articles')"> Cancelar </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-form>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
      {{ snackbarMessage }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import api from '@/plugins/axios'
import { getImageUrl } from '@/plugins/helpers'

const route = useRoute()
const router = useRouter()

const form = ref(null)
const valid = ref(false)
const saving = ref(false)
const categories = ref([])
const imageFile = ref(null)
const imagePreview = ref(null)
const imageToDelete = ref(false)

const article = ref({
  title: '',
  excerpt: '',
  content: '',
  status: 'draft',
  category_id: null,
  image: null,
})

const snackbar = ref(false)
const snackbarMessage = ref('')
const snackbarColor = ref('success')

const isEditMode = computed(() => !!route.params.id)

const statusOptions = [
  { title: 'Borrador', value: 'draft' },
  { title: 'Publicado', value: 'published' },
]

const editorToolbar = [
  ['bold', 'italic', 'underline', 'strike'],
  ['blockquote', 'code-block'],
  [{ header: 1 }, { header: 2 }],
  [{ list: 'ordered' }, { list: 'bullet' }],
  [{ indent: '-1' }, { indent: '+1' }],
  [{ size: ['small', false, 'large', 'huge'] }],
  [{ header: [1, 2, 3, 4, 5, 6, false] }],
  [{ color: [] }, { background: [] }],
  [{ align: [] }],
  ['link', 'image'],
  ['clean'],
]

const rules = {
  required: (v) => !!v || 'Este campo es requerido',
}

onMounted(() => {
  loadCategories()
  if (isEditMode.value) {
    loadArticle()
  }
})

const loadCategories = async () => {
  try {
    const response = await api.get('/categories')
    categories.value = response.data
  } catch (error) {
    console.error('Error al cargar categorías:', error)
    showSnackbar('Error al cargar categorías', 'error')
  }
}

const loadArticle = async () => {
  try {
    const response = await api.get(`/articles/${route.params.id}`)
    article.value = {
      title: response.data.title,
      excerpt: response.data.excerpt || '',
      content: response.data.content,
      status: response.data.status,
      category_id: response.data.category_id,
      image: response.data.image,
    }

    if (response.data.image) {
      imagePreview.value = getImageUrl(response.data.image)
    }
  } catch (error) {
    console.error('Error al cargar artículo:', error)
    showSnackbar('Error al cargar artículo', 'error')
  }
}

const previewImage = () => {
  if (imageFile.value && imageFile.value.length > 0) {
    const file = imageFile.value[0]
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = () => {
  imagePreview.value = null
  article.value.image = null
  imageToDelete.value = true
}

const saveArticle = async () => {
  const { valid: isValid } = await form.value.validate()

  if (!isValid || !article.value.content) {
    showSnackbar('Por favor completa todos los campos requeridos', 'warning')
    return
  }

  try {
    const formData = new FormData()
    formData.append('title', article.value.title)
    formData.append('excerpt', article.value.excerpt || '')
    formData.append('content', article.value.content)
    formData.append('status', article.value.status)
    formData.append('category_id', article.value.category_id)

    // Log de depuración: Confirmamos el objeto.
    console.log('--- Depuración de Imagen (Estructura Corregida) ---')
    console.log('imageFile.value:', imageFile.value)

    if (imageFile.value && imageFile.value instanceof File) {
      formData.append('image', imageFile.value)
    } else if (imageToDelete.value) {
      formData.append('remove_image', '1')
    }

    if (isEditMode.value) {
      formData.append('_method', 'PUT')
    }

    const config = {
      headers: {
        'Content-Type': null,
        Accept: 'application/json',
      },
    }

    const endpoint = isEditMode.value ? `/articles/${route.params.id}` : '/articles'

    // 3. Ejecutar la petición con la nueva configuración
    await api.post(endpoint, formData, config)

    showSnackbar(
      isEditMode.value ? 'Artículo actualizado exitosamente' : 'Artículo creado exitosamente',
      'success',
    )

    setTimeout(() => {
      router.push('/articles')
    }, 1500)
  } catch (error) {
    console.error('Error al guardar artículo:', error)
    showSnackbar(error.response?.data?.message || 'Error al guardar artículo', 'error')
  } finally {
    saving.value = false
  }
}

const showSnackbar = (message, color = 'success') => {
  snackbarMessage.value = message
  snackbarColor.value = color
  snackbar.value = true
}
</script>

<style>
.ql-editor {
  min-height: 400px;
}
</style>
