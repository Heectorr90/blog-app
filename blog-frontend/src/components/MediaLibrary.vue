<template>
  <v-dialog v-model="dialog">
    <v-card>
      <v-card-title class="text-h6"> Media Library </v-card-title>

      <v-card-text>
        <!-- Subir imagen -->
        <v-file-input
          label="Subir imagen"
          accept="image/*"
          prepend-icon="mdi-upload"
          variant="outlined"
          density="compact"
          @change="uploadImage"
        ></v-file-input>

        <!-- Loading -->
        <v-progress-linear v-if="loading" indeterminate class="my-2"></v-progress-linear>

        <!-- Galería -->
        <v-container>
          <v-row>
            <v-col v-for="img in images" :key="img.id" cols="6" sm="4" md="3">
              <v-card class="media-item" @click="selectImage(img)">
                <v-img :src="getImageUrl(img.url)" height="120" cover></v-img>
              </v-card>
            </v-col>
          </v-row>

          <v-row v-if="images.length === 0 && !loading">
            <v-col cols="12">
              <v-alert type="info" variant="tonal"> No hay imágenes aún </v-alert>
            </v-col>
          </v-row>
        </v-container>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn variant="text" @click="close"> Cerrar </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import api from '@/plugins/axios'
import { getImageUrl } from '@/plugins/helpers'

const props = defineProps({
  modelValue: Boolean,
})

const emit = defineEmits(['update:modelValue', 'select'])

const images = ref([])
const loading = ref(false)

// CONFIG CLOUDINARY
const CLOUD_NAME = import.meta.env.VITE_CLOUDINARY_CLOUD_NAME
const UPLOAD_PRESET = import.meta.env.VITE_CLOUDINARY_UPLOAD_PRESET

// cargar imágenes cuando se abre
watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      loadImages()
    }
  },
)

// obtener imágenes guardadas
const loadImages = async () => {
  loading.value = true
  try {
    const res = await api.get('/media')
    images.value = res.data
  } catch (error) {
    console.error('Error cargando media:', error)
  } finally {
    loading.value = false
  }
}

// subir a Cloudinary
const uploadImage = async (fileInput) => {
  console.log('Archivo seleccionado:', fileInput) // Debug: Verificar el archivo seleccionado
  const file = fileInput.target.files[0]
  if (!file) return

  loading.value = true

  try {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('upload_preset', UPLOAD_PRESET)

    const res = await fetch(`https://api.cloudinary.com/v1_1/${CLOUD_NAME}/image/upload`, {
      method: 'POST',
      body: formData,
    })

    const data = await res.json()

    // guardar en backend
    const mediaRes = await api.post('/media', {
      url: data.secure_url,
      public_id: data.public_id,
      name: file.name,
    })

    images.value.unshift(mediaRes.data)
  } catch (error) {
    console.error('Error subiendo imagen:', error)
  } finally {
    loading.value = false
  }
}

// seleccionar imagen
const selectImage = (img) => {
  emit('select', img)
  close()
}

// cerrar modal
const close = () => {
  emit('update:modelValue', false)
}

const dialog = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})
</script>

<style scoped>
.media-item {
  cursor: pointer;
  transition:
    transform 0.2s,
    box-shadow 0.2s;
}

.media-item:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
</style>
