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
              <v-btn color="primary" block @click="openMedia = true"> Seleccionar imagen </v-btn>

              <v-img
                v-if="article.media"
                :src="article.media.url"
                class="mt-4"
                max-height="200"
                cover
              ></v-img>

              <v-btn
                v-if="article.media_id"
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
          <MediaLibrary v-model="openMedia" @select="setImage" />
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
import MediaLibrary from '@/components/MediaLibrary.vue'

const route = useRoute()
const router = useRouter()

const form = ref(null)
const valid = ref(false)
const saving = ref(false)
const categories = ref([])

const article = ref({
  title: '',
  excerpt: '',
  content: '',
  status: 'draft',
  category_id: null,
  media_id: null,
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
      media_id: response.data.media_id,
      media: response.data.media || null,
    }
  } catch (error) {
    console.error('Error al cargar artículo:', error)
    showSnackbar('Error al cargar artículo', 'error')
  }
}

const saveArticle = async () => {
  const { valid: isValid } = await form.value.validate()

  if (!isValid || !article.value.content) {
    showSnackbar('Por favor completa todos los campos requeridos', 'warning')
    return
  }

  saving.value = true

  try {
    const endpoint = isEditMode.value ? `/articles/${route.params.id}` : '/articles'

    const method = isEditMode.value ? 'put' : 'post'

    await api[method](endpoint, article.value)

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

const openMedia = ref(false)

const setImage = (img) => {
  article.value.media_id = img.id
  article.value.media = img
}

const removeImage = () => {
  article.value.media = null
  article.value.media_id = null
}
</script>

<style>
.ql-editor {
  min-height: 400px;
}
</style>
