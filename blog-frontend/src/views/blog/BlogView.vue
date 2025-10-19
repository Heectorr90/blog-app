<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h3 mb-4">📝 Blog</h1>
      </v-col>
    </v-row>

    <!-- Barra de búsqueda y filtros -->
    <v-row>
      <v-col cols="12" md="8">
        <v-text-field
          v-model="search"
          label="Buscar artículos..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          clearable
          @input="searchArticles"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="4">
        <v-select
          v-model="selectedCategory"
          :items="categories"
          item-title="name"
          item-value="id"
          label="Filtrar por categoría"
          variant="outlined"
          clearable
          @update:modelValue="filterByCategory"
        ></v-select>
      </v-col>
    </v-row>

    <!-- Lista de artículos -->
    <v-row v-if="loading">
      <v-col v-for="n in 3" :key="n" cols="12" md="4">
        <v-skeleton-loader type="card"></v-skeleton-loader>
      </v-col>
    </v-row>

    <v-row v-else-if="articles.length > 0">
      <v-col v-for="article in articles" :key="article.id" cols="12" md="4">
        <v-card class="h-100" hover @click="goToArticle(article.id)">
          <ArticleImage :image="article.image" :placeholder="article.title" height="200">
            <v-chip v-if="article.category" class="ma-2" color="primary" size="small">
              {{ article.category.name }}
            </v-chip>
          </ArticleImage>

          <v-card-title class="text-h6">
            {{ article.title }}
          </v-card-title>

          <v-card-subtitle v-if="article.user">
            Por {{ article.user.name }} • {{ formatDate(article.created_at) }}
          </v-card-subtitle>

          <v-card-text>
            <p class="text-truncate-3">
              {{ article.excerpt || stripHtml(article.content) }}
            </p>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" variant="text">
              Leer más
              <v-icon end>mdi-arrow-right</v-icon>
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-alert type="info" variant="tonal"> No se encontraron artículos </v-alert>
      </v-col>
    </v-row>

    <!-- Paginación -->
    <v-row v-if="totalPages > 1" class="mt-4">
      <v-col cols="12" class="d-flex justify-center">
        <v-pagination
          v-model="currentPage"
          :length="totalPages"
          @update:modelValue="loadArticles"
        ></v-pagination>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/plugins/axios'
import ArticleImage from '@/components/ArticleImage.vue'
import { formatDate, stripHtml } from '@/plugins/helpers'

const router = useRouter()

const articles = ref([])
const categories = ref([])
const loading = ref(false)
const search = ref('')
const selectedCategory = ref(null)
const currentPage = ref(1)
const totalPages = ref(1)

onMounted(() => {
  loadArticles()
  loadCategories()
})

const loadArticles = async () => {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      status: 'published',
    }

    if (search.value) {
      params.search = search.value
    }

    if (selectedCategory.value) {
      params.category_id = selectedCategory.value
    }

    const response = await api.get('/articles', { params })
    articles.value = response.data.data
    totalPages.value = response.data.last_page
  } catch (error) {
    console.error('Error al cargar artículos:', error)
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
  currentPage.value = 1
  loadArticles()
}

const filterByCategory = () => {
  currentPage.value = 1
  loadArticles()
}

const goToArticle = (id) => {
  router.push(`/blog/${id}`)
}
</script>

<style scoped>
.text-truncate-3 {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.h-100 {
  height: 100%;
}
</style>
