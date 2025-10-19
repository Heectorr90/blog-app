<template>
  <v-container>
    <v-row v-if="loading">
      <v-col cols="12">
        <v-skeleton-loader type="article"></v-skeleton-loader>
      </v-col>
    </v-row>

    <v-row v-else-if="article">
      <v-col cols="12">
        <v-btn
          variant="text"
          prepend-icon="mdi-arrow-left"
          @click="$router.push('/blog')"
          class="mb-4"
        >
          Volver al blog
        </v-btn>

        <v-card>
          <ArticleImage
            v-if="article.image"
            :image="article.image"
            :placeholder="article.title"
            height="400"
          />

          <v-card-title class="text-h3 mt-4">
            {{ article.title }}
          </v-card-title>

          <v-card-subtitle class="mt-2">
            <v-chip v-if="article.category" color="primary" size="small" class="mr-2">
              {{ article.category.name }}
            </v-chip>
            Por {{ article.user?.name || 'Anónimo' }} •
            {{ formatDate(article.created_at) }}
          </v-card-subtitle>

          <v-divider class="my-4"></v-divider>

          <v-card-text>
            <div v-if="article.excerpt" class="text-h6 mb-4 text-grey-darken-1">
              {{ article.excerpt }}
            </div>

            <div class="article-content" v-html="article.content"></div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <v-row v-else>
      <v-col cols="12">
        <v-alert type="error"> Artículo no encontrado </v-alert>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/plugins/axios'
import ArticleImage from '@/components/ArticleImage.vue'
import { formatDate } from '@/plugins/helpers'

const route = useRoute()

const article = ref(null)
const loading = ref(false)

onMounted(() => {
  loadArticle()
})

const loadArticle = async () => {
  loading.value = true
  try {
    const response = await api.get(`/articles/${route.params.id}`)
    article.value = response.data
  } catch (error) {
    console.error('Error al cargar artículo:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.article-content {
  font-size: 1.1rem;
  line-height: 1.8;
}

.article-content >>> p {
  margin-bottom: 1rem;
}

.article-content >>> h1,
.article-content >>> h2,
.article-content >>> h3 {
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.article-content >>> img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
}

.article-content >>> code {
  background-color: #f5f5f5;
  padding: 2px 6px;
  border-radius: 4px;
}

.article-content >>> pre {
  background-color: #f5f5f5;
  padding: 1rem;
  border-radius: 8px;
  overflow-x: auto;
}
</style>
