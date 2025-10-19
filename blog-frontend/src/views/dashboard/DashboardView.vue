<template>
  <v-container>
    <v-row>
      <v-col cols="12">
        <h1 class="text-h3 mb-2">Dashboard</h1>
        <p class="text-subtitle-1 mb-6">Bienvenido, {{ authStore.userName }}</p>
      </v-col>
    </v-row>

    <!-- Estadísticas -->
    <v-row v-if="authStore.isAdmin">
      <v-col cols="12" sm="6" md="3">
        <v-card color="primary" dark>
          <v-card-text>
            <div class="text-h4">{{ stats.totalArticles }}</div>
            <div class="text-subtitle-1">Total Artículos</div>
          </v-card-text>
          <v-card-actions>
            <v-icon>mdi-file-document</v-icon>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card color="success" dark>
          <v-card-text>
            <div class="text-h4">{{ stats.publishedArticles }}</div>
            <div class="text-subtitle-1">Publicados</div>
          </v-card-text>
          <v-card-actions>
            <v-icon>mdi-check-circle</v-icon>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card color="warning" dark>
          <v-card-text>
            <div class="text-h4">{{ stats.draftArticles }}</div>
            <div class="text-subtitle-1">Borradores</div>
          </v-card-text>
          <v-card-actions>
            <v-icon>mdi-pencil</v-icon>
          </v-card-actions>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card color="info" dark>
          <v-card-text>
            <div class="text-h4">{{ stats.totalCategories }}</div>
            <div class="text-subtitle-1">Categorías</div>
          </v-card-text>
          <v-card-actions>
            <v-icon>mdi-tag</v-icon>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Acciones rápidas -->
    <v-row class="mt-4">
      <v-col cols="12">
        <h2 class="text-h5 mb-4">Acciones Rápidas</h2>
      </v-col>

      <v-col cols="12" sm="6" md="4" v-if="authStore.isAdmin">
        <v-card hover @click="$router.push('/articles/create')">
          <v-card-text class="text-center pa-6">
            <v-icon size="64" color="primary">mdi-plus-circle</v-icon>
            <div class="text-h6 mt-2">Crear Artículo</div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" v-if="authStore.isAdmin">
        <v-card hover @click="$router.push('/articles')">
          <v-card-text class="text-center pa-6">
            <v-icon size="64" color="primary">mdi-file-document-multiple</v-icon>
            <div class="text-h6 mt-2">Gestionar Artículos</div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4" v-if="authStore.isAdmin">
        <v-card hover @click="$router.push('/categories')">
          <v-card-text class="text-center pa-6">
            <v-icon size="64" color="primary">mdi-tag-multiple</v-icon>
            <div class="text-h6 mt-2">Gestionar Categorías</div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="4">
        <v-card hover @click="$router.push('/blog')">
          <v-card-text class="text-center pa-6">
            <v-icon size="64" color="primary">mdi-book-open-page-variant</v-icon>
            <div class="text-h6 mt-2">Ver Blog Público</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Artículos recientes (solo admin) -->
    <v-row class="mt-4" v-if="authStore.isAdmin">
      <v-col cols="12">
        <h2 class="text-h5 mb-4">Artículos Recientes</h2>
      </v-col>

      <v-col cols="12">
        <v-card>
          <v-list v-if="recentArticles.length > 0">
            <v-list-item
              v-for="article in recentArticles"
              :key="article.id"
              @click="$router.push(`/articles/${article.id}/edit`)"
            >
              <template v-slot:prepend>
                <v-avatar color="primary">
                  <v-icon>mdi-file-document</v-icon>
                </v-avatar>
              </template>

              <v-list-item-title>{{ article.title }}</v-list-item-title>
              <v-list-item-subtitle>
                {{ article.category?.name }} • {{ formatDate(article.created_at) }}
              </v-list-item-subtitle>

              <template v-slot:append>
                <v-chip
                  :color="article.status === 'published' ? 'success' : 'warning'"
                  size="small"
                >
                  {{ article.status === 'published' ? 'Publicado' : 'Borrador' }}
                </v-chip>
              </template>
            </v-list-item>
          </v-list>

          <v-card-text v-else> No hay artículos recientes </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/plugins/axios'
import { formatDateShort } from '@/plugins/helpers'

const authStore = useAuthStore()

const stats = ref({
  totalArticles: 0,
  publishedArticles: 0,
  draftArticles: 0,
  totalCategories: 0,
})

const recentArticles = ref([])

onMounted(() => {
  if (authStore.isAdmin) {
    loadStats()
    loadRecentArticles()
  }
})

const loadStats = async () => {
  try {
    const [articlesRes, categoriesRes] = await Promise.all([
      api.get('/articles'),
      api.get('/categories'),
    ])

    const allArticles = articlesRes.data.data
    stats.value.totalArticles = allArticles.length
    stats.value.publishedArticles = allArticles.filter((a) => a.status === 'published').length
    stats.value.draftArticles = allArticles.filter((a) => a.status === 'draft').length
    stats.value.totalCategories = categoriesRes.data.length
  } catch (error) {
    console.error('Error al cargar estadísticas:', error)
  }
}

const loadRecentArticles = async () => {
  try {
    const response = await api.get('/articles', {
      params: { per_page: 5 },
    })
    recentArticles.value = response.data.data
  } catch (error) {
    console.error('Error al cargar artículos recientes:', error)
  }
}

const formatDate = formatDateShort
</script>
