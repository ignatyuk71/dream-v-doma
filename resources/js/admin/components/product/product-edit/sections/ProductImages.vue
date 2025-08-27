<template>
  <div class="card mb-4 p-3">
    <h6 class="fw-bold mb-3">Зображення товару</h6>
    <div class="mb-3">
      <input type="file" class="form-control" multiple @change="handleFilesChange">
      <small class="text-muted">Файлів: {{ images.length }}</small>
    </div>

    <div ref="containerRef" class="d-flex gap-2 flex-wrap">
      <div v-for="(img, index) in sortedImages" :key="img.id" class="image-item">
        <img :src="viewUrl(img)" alt="" @error="onImgError" />
        <button type="button" class="remove-btn" @click="removeImage(index)">&times;</button>
        <span class="badge-main">
          {{ index === 0 ? 'Головне' : '#' + (index + 1) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import Sortable from 'sortablejs'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  /** опціонально: підкинь id продукту явно, інакше візьму з URL */
  productId: { type: [Number, String], default: null },
  /** на випадок іншої структури */
  imagesFolder: { type: String, default: 'products' },
})

const emit = defineEmits(['update:modelValue'])
const images = ref([...props.modelValue])

watch(() => props.modelValue, (val) => {
  if (val !== images.value) images.value = [...val]
})

const sortedImages = computed(() =>
  [...images.value].sort((a, b) => (a.position ?? 0) - (b.position ?? 0))
)

const productIdFromUrl = computed(() => {
  if (props.productId) return Number(props.productId)
  const m = window.location.pathname.match(/\/products\/(\d+)(?:\/|$)/)
  return m ? Number(m[1]) : null
})

function stripPublic(p) {
  return String(p).replace(/^public\//, '')
}
function joinPath(...parts) {
  return '/' + parts
    .map(p => String(p || '').replace(/^\/+|\/+$/g, ''))
    .filter(Boolean)
    .join('/')
}

function viewUrl(img) {
  let u =
    img?.url ||
    img?.full_url ||
    img?.path ||
    img?.storage_path ||
    img?.image ||
    img?.filepath ||
    img?.filename ||
    img?.name

  if (!u) return ''

  u = String(u)

  // blob/data/http(s)
  if (u.startsWith('blob:') || u.startsWith('data:') || /^https?:\/\//i.test(u)) return u
  // абсолютний від кореня
  if (u.startsWith('/')) return u

  // вже зі storage
  if (u.startsWith('storage/')) return joinPath(u)
  const storageIdx = u.indexOf('/storage/')
  if (storageIdx !== -1) return u.slice(storageIdx)

  // прибрати public/
  u = stripPublic(u)

  // якщо у — з підкаталогом (наприклад 'products/79/img.png')
  if (u.includes('/')) return joinPath('storage', u)

  // якщо лише ім'я файлу — будуємо шлях: /storage/products/{id}/{filename}
  const pid = productIdFromUrl.value
  if (pid) return joinPath('storage', props.imagesFolder, pid, u)

  // фолбек (без id): спробуємо /storage/{filename}
  console.warn('[images] filename without productId — додай prop :product-id або збережи шлях із каталогом', u)
  return joinPath('storage', u)
}

function onImgError(e) {
  // Плейсхолдер — заміни під свій
  e.target.src = '/images/placeholder.png'
}

function updatePositionsAndMain() {
  images.value.forEach((img, idx) => {
    img.position = idx
    img.is_main = (idx === 0)
  })
  emit('update:modelValue', [...images.value])
}

function handleFilesChange(event) {
  const files = Array.from(event.target.files)
  files.forEach(file => {
    images.value.push({
      id: Date.now() + Math.random(),
      file,
      url: URL.createObjectURL(file), // preview
      position: images.value.length,
      is_main: false
    })
  })
  updatePositionsAndMain()
  event.target.value = ''
}

function removeImage(sortedIdx) {
  const target = sortedImages.value[sortedIdx]
  const realIdx = images.value.findIndex(i => i.id === target.id)
  if (realIdx !== -1) {
    const removed = images.value[realIdx]
    if (removed?.url && String(removed.url).startsWith('blob:')) {
      try { URL.revokeObjectURL(removed.url) } catch {}
    }
    images.value.splice(realIdx, 1)
    updatePositionsAndMain()
  }
}

const containerRef = ref(null)
onMounted(() => {
  Sortable.create(containerRef.value, {
    animation: 150,
    onEnd: (evt) => {
      const list = [...sortedImages.value]
      const [moved] = list.splice(evt.oldIndex, 1)
      list.splice(evt.newIndex, 0, moved)
      images.value = list
      updatePositionsAndMain()
    }
  })
})
</script>

<style scoped>
.image-item {
  width: 200px; height: 200px; position: relative; border-radius: 10px;
  overflow: hidden; border: 1.5px solid #eee; background: #fafbfc;
  margin-bottom: 10px; display: flex; align-items: center; justify-content: center;
}
.image-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
.remove-btn {
  position: absolute; top: 5px; right: 5px; background: #fff; color: #dc3545;
  border: 1px solid #eee; border-radius: 50%; width: 26px; height: 26px;
  font-size: 20px; font-weight: bold; cursor: pointer; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.07); z-index: 2; padding: 0; line-height: 1; transition: background 0.2s;
}
.remove-btn:hover { background: #ffeaea; }
.badge-main {
  position: absolute; bottom: 7px; left: 7px; background: #0d6efd; color: #fff;
  font-size: 12px; padding: 2px 8px; border-radius: 5px; font-weight: 500;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
</style>
