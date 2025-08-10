<template>
  <div class="card mb-4 p-3">
    <h6 class="fw-bold mb-3">Зображення товару</h6>
    <div class="mb-3">
      <input type="file" class="form-control" multiple @change="handleFilesChange">
      <small class="text-muted">Файлів: {{ images.length }}</small>
    </div>
    <div ref="containerRef" class="d-flex gap-2 flex-wrap">
      <div v-for="(img, index) in images" :key="img.id" class="image-item">
        <img :src="img.url" alt="" />
        <button
          type="button"
          class="remove-btn"
          @click="removeImage(index)"
        >&times;</button>
        <span class="badge-main">
          {{ index === 0 ? 'Головне' : '#' + (index + 1) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import Sortable from 'sortablejs'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})
const emit = defineEmits(['update:modelValue'])

const images = ref([...props.modelValue])

watch(
  () => props.modelValue,
  (val) => {
    if (val !== images.value) {
      images.value = [...val]
    }
  }
)

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
      file, // Файл для FormData
      url: URL.createObjectURL(file), // Preview
      position: images.value.length,
      is_main: false
    })
  })
  updatePositionsAndMain()
  event.target.value = ''
}

function removeImage(idx) {
  images.value.splice(idx, 1)
  updatePositionsAndMain()
}

const containerRef = ref(null)
onMounted(() => {
  Sortable.create(containerRef.value, {
    animation: 150,
    onEnd: (evt) => {
      const moved = images.value.splice(evt.oldIndex, 1)[0]
      images.value.splice(evt.newIndex, 0, moved)
      updatePositionsAndMain()
    }
  })
})
</script>

<style scoped>
.image-item {
  width: 200px;
  height: 200px;
  position: relative;
  border-radius: 10px;
  overflow: hidden;
  border: 1.5px solid #eee;
  background: #fafbfc;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.image-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}
.remove-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  background: #fff;
  color: #dc3545;
  border: 1px solid #eee;
  border-radius: 50%;
  width: 26px;
  height: 26px;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.07);
  z-index: 2;
  padding: 0;
  line-height: 1;
  transition: background 0.2s;
}
.remove-btn:hover {
  background: #ffeaea;
}
.badge-main {
  position: absolute;
  bottom: 7px;
  left: 7px;
  background: #0d6efd;
  color: #fff;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 5px;
  font-weight: 500;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
</style>
