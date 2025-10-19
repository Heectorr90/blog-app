// Helper para obtener URL completa de imágenes
export const getImageUrl = (imagePath) => {
  if (!imagePath) return null

  // Si ya es una URL completa, retornarla
  if (imagePath.startsWith('http')) {
    return imagePath
  }

  // Construir URL completa
  const baseURL = 'http://localhost:8000'
  return `${baseURL}/storage/${imagePath}`
}

// Imagen placeholder por defecto
export const getPlaceholderImage = (width = 400, height = 300, text = 'Sin Imagen') => {
  // Usar colores del tema personalizado
  const bgColor = '112e42' // --second-bg-color
  const textColor = '00abf0' // --main-color
  return `https://via.placeholder.com/${width}x${height}/${bgColor}/${textColor}?text=${encodeURIComponent(text)}`
}

// Formatear fecha
export const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Formatear fecha corta
export const formatDateShort = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Extraer texto plano de HTML
export const stripHtml = (html) => {
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
