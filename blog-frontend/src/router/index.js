import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/blog',
    },
    // Rutas de autenticación
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    // Rutas del blog público
    {
      path: '/blog',
      name: 'blog',
      component: () => import('@/views/blog/BlogView.vue'),
    },
    {
      path: '/blog/:id',
      name: 'article',
      component: () => import('@/views/blog/ArticleView.vue'),
    },
    // Dashboard
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/dashboard/DashboardView.vue'),
      meta: { requiresAuth: true },
    },
    // Gestión de artículos (Admin)
    {
      path: '/articles',
      name: 'articles',
      component: () => import('@/views/admin/articles/ArticlesManageView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/articles/create',
      name: 'article-create',
      component: () => import('@/views/admin/articles/ArticleFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/articles/:id/edit',
      name: 'article-edit',
      component: () => import('@/views/admin/articles/ArticleFormView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    // Gestión de categorías (Admin)
    {
      path: '/categories',
      name: 'categories',
      component: () => import('@/views/admin/categories/CategoriesView.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
})

// Guard de navegación
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
