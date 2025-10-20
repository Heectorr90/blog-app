<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="4">
        <v-card class="elevation-12">
          <v-toolbar color="primary" dark flat>
            <v-toolbar-title>Iniciar Sesión</v-toolbar-title>
          </v-toolbar>

          <v-card-text>
            <v-alert type="info" variant="tonal" class="mb-6 text-center" density="compact">
              <span class="font-weight-bold">Acceso de Administrador (DEMO)</span>
              <v-divider class="my-1"></v-divider>
              <p class="mt-1 mb-0 text-caption">Email: {{ DEMO_EMAIL }}</p>
              <p class="mb-0 text-caption">Contraseña: {{ DEMO_PASSWORD }}</p>
            </v-alert>

            <v-form ref="form" v-model="valid" @submit.prevent="handleLogin">
              <v-text-field
                v-model="email"
                :rules="emailRules"
                label="Email"
                prepend-icon="mdi-email"
                type="email"
                required
              ></v-text-field>

              <v-text-field
                v-model="password"
                :rules="passwordRules"
                label="Contraseña"
                prepend-icon="mdi-lock"
                :type="showPassword ? 'text' : 'password'"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="showPassword = !showPassword"
                required
              ></v-text-field>

              <v-alert v-if="authStore.error" type="error" dense class="mb-4">
                {{ authStore.error }}
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              :loading="authStore.loading"
              :disabled="!valid"
              @click="handleLogin"
              block
            >
              Iniciar Sesión
            </v-btn>
          </v-card-actions>

          <v-card-text class="text-center">
            <v-divider class="mb-4"></v-divider>
            ¿No tienes cuenta?
            <router-link to="/register">Regístrate aquí</router-link>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const form = ref(null)
const valid = ref(false)
const email = ref('')
const password = ref('')
const showPassword = ref(false)

// --- Credenciales Demo (Constantes para mostrar y usar) ---
const DEMO_EMAIL = 'test@example.com'
const DEMO_PASSWORD = '12345678'

const emailRules = [
  (v) => !!v || 'El email es requerido',
  (v) => /.+@.+\..+/.test(v) || 'Email debe ser válido',
]

const passwordRules = [
  (v) => !!v || 'La contraseña es requerida',
  (v) => (v && v.length >= 6) || 'La contraseña debe tener al menos 6 caracteres',
]

const handleLogin = async () => {
  const { valid } = await form.value.validate()

  if (valid) {
    try {
      await authStore.login({
        email: email.value,
        password: password.value,
      })
    } catch (error) {
      console.error('Error al iniciar sesión:', error)
    }
  }
}
</script>
