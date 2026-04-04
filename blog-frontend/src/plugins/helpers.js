// ===============================
// IMÁGENES
// ===============================

// Obtener URL completa de imagen
export const getImageUrl = (imagePath, fallbackText = 'Sin Imagen') => {
  if (!imagePath) {
    return getPlaceholderImage(400, 300, fallbackText)
  }

  // Caso 1: URL completa (Cloudinary, externa, etc)
  if (typeof imagePath === 'string' && imagePath.startsWith('http')) {
    return imagePath
  }

  // Caso 2: path relativo (legacy Laravel storage)
  const baseURL = import.meta.env.VITE_API_URL_HELPERS

  if (!baseURL) {
    console.warn('⚠️ VITE_API_URL_HELPERS no está definido')
    return getPlaceholderImage(400, 300, fallbackText)
  }

  return `${baseURL}/storage/${imagePath}`
}

// Imagen placeholder por defecto
export const getPlaceholderImage = (width = 400, height = 300, text = 'Sin Imagen') => {
  const bgColor = '112e42'
  const textColor = '00abf0'

  return `https://via.placeholder.com/${width}x${height}/${bgColor}/${textColor}?text=${encodeURIComponent(
    text,
  )}`
}

// Manejo de error en <v-img> o <img>
export const handleImageError = (event, text = 'Error') => {
  event.target.src = getPlaceholderImage(400, 300, text)
}

// ===============================
// FECHAS
// ===============================

export const formatDate = (date) => {
  if (!date) return ''

  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

export const formatDateShort = (date) => {
  if (!date) return ''

  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// ===============================
// TEXTO
// ===============================

// Extraer texto plano de HTML
export const stripHtml = (html) => {
  if (!html) return ''

  const tmp = document.createElement('div')
  tmp.innerHTML = html
  return tmp.textContent || tmp.innerText || ''
}

// Truncar texto
export const truncate = (text, length = 100) => {
  if (!text) return ''
  if (text.length <= length) return text

  return text.substring(0, length) + '...'
}
