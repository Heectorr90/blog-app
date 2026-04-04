<template>
  <v-img
    :src="imageSrc"
    :height="height"
    :max-height="maxHeight"
    :cover="cover"
    @error="handleError"
  >
    <slot />
  </v-img>
</template>

<script setup>
import { ref, computed } from 'vue'
import { getImageUrl, getPlaceholderImage } from '@/plugins/helpers'

const props = defineProps({
  image: String,
  placeholder: String,
  height: [String, Number],
  maxHeight: [String, Number],
  cover: {
    type: Boolean,
    default: true,
  },
})

const imageError = ref(false)

const imageSrc = computed(() => {
  if (imageError.value) {
    return getPlaceholderImage()
  }
  const url = getImageUrl(props.image)

  return url || getPlaceholderImage()
})

const handleError = () => {
  imageError.value = true
}
</script>
