<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5">
        <v-card class="elevation-12">
          <v-toolbar color="primary" dark flat>
            <v-toolbar-title>Crear Cuenta</v-toolbar-title>
          </v-toolbar>

          <v-card-text>
            <v-form ref="form" v-model="valid" @submit.prevent="handleRegister">
              <v-text-field
                v-model="name"
                :rules="nameRules"
                label="Nombre completo"
                prepend-icon="mdi-account"
                required
              ></v-text-field>

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

              <v-text-field
                v-model="passwordConfirmation"
                :rules="passwordConfirmationRules"
                label="Confirmar contraseña"
                prepend-icon="mdi-lock-check"
                :type="showPasswordConfirm ? 'text' : 'password'"
                :append-icon="showPasswordConfirm ? 'mdi-eye' : 'mdi-eye-off'"
                @click:append="showPasswordConfirm = !showPasswordConfirm"
                required
              ></v-text-field>

              <v-alert v-if="authStore.error" type="error" dense class="mb-4">
                <div v-if="typeof authStore.error === 'object'">
                  <div v-for="(errors, field) in authStore.error" :key="field">
                    <div v-for="error in errors" :key="error">{{ error }}</div>
                  </div>
                </div>
                <div v-else>{{ authStore.error }}</div>
              </v-alert>
            </v-form>
          </v-card-text>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
              color="primary"
              :loading="authStore.loading"
              :disabled="!valid"
              @click="handleRegister"
              block
            >
              Registrarse
            </v-btn>
          </v-card-actions>

          <v-card-text class="text-center">
            <v-divider class="mb-4"></v-divider>
            ¿Ya tienes cuenta?
            <router-link to="/login">Inicia sesión aquí</router-link>
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
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)
const showPasswordConfirm = ref(false)

const nameRules = [
  (v) => !!v || 'El nombre es requerido',
  (v) => (v && v.length >= 3) || 'El nombre debe tener al menos 3 caracteres',
]

const emailRules = [
  (v) => !!v || 'El email es requerido',
  (v) => /.+@.+\..+/.test(v) || 'Email debe ser válido',
]

const passwordRules = [
  (v) => !!v || 'La contraseña es requerida',
  (v) => (v && v.length >= 6) || 'La contraseña debe tener al menos 6 caracteres',
]

const passwordConfirmationRules = [
  (v) => !!v || 'Confirmar contraseña es requerido',
  (v) => v === password.value || 'Las contraseñas no coinciden',
]

const handleRegister = async () => {
  const { valid } = await form.value.validate()

  if (valid) {
    try {
      await authStore.register({
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
      })
    } catch (error) {
      console.error('Error al registrarse:', error)
    }
  }
}
</script>
