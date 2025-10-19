<template>
  <v-app>
    <v-app-bar color="primary" prominent>
      <v-app-bar-nav-icon
        v-if="authStore.isAuthenticated"
        @click="drawer = !drawer"
      ></v-app-bar-nav-icon>

      <v-toolbar-title>
        <router-link to="/" style="color: white; text-decoration: none"> 📝 Mi Blog </router-link>
      </v-toolbar-title>

      <v-spacer></v-spacer>

      <div v-if="!authStore.isAuthenticated">
        <v-btn to="/login" variant="text">Iniciar Sesión</v-btn>
        <v-btn to="/register" variant="text">Registrarse</v-btn>
      </div>

      <div v-else>
        <v-chip class="mr-2" prepend-icon="mdi-account">
          {{ authStore.userName }}
        </v-chip>
        <v-btn icon="mdi-logout" @click="logout"></v-btn>
      </div>
    </v-app-bar>

    <v-navigation-drawer v-model="drawer" temporary v-if="authStore.isAuthenticated">
      <v-list>
        <v-list-item
          prepend-icon="mdi-view-dashboard"
          to="/dashboard"
          title="Dashboard"
        ></v-list-item>
        <v-list-item prepend-icon="mdi-home" to="/blog" title="Ver Blog"></v-list-item>

        <v-divider v-if="authStore.isAdmin"></v-divider>

        <template v-if="authStore.isAdmin">
          <v-list-subheader>ADMINISTRACIÓN</v-list-subheader>
          <v-list-item
            prepend-icon="mdi-file-document"
            to="/articles"
            title="Artículos"
          ></v-list-item>
          <v-list-item prepend-icon="mdi-tag" to="/categories" title="Categorías"></v-list-item>
        </template>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container>
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const drawer = ref(false)

const logout = () => {
  authStore.logout()
}
</script>
