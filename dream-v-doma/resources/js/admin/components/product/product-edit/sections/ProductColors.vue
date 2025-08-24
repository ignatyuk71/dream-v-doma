<template>
  <div class="card mb-4 p-4">
    <h5 class="fw-bold mb-3">Кольори товару</h5>
    <div class="color-list d-flex flex-column gap-3">
      <div
        v-for="(color, idx) in localColors"
        :key="color.id || idx"
        class="position-relative bg-white rounded-4 border p-4 mb-3 shadow-sm"
      >
        <!-- Action bar -->
        <div class="desc-action-bar">
          <div class="position-absolute top-0 end-0 m-3 d-flex gap-2">
            <button
              v-if="editIdx !== idx"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
              style="border-radius:8px"
              @click="startEdit(idx)"
            >
              <svg width="18" height="18" fill="none" stroke="#222" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232a2.828 2.828 0 1 1 4 4L7.5 21H3v-4.5l12.232-12.268z"/></svg>
              <span class="d-none d-md-inline">Редагувати</span>
            </button>
            <button
              v-if="editIdx !== idx"
              class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1"
              style="border-radius:8px"
              @click="removeColor(idx)"
            >
              <i class="bi bi-trash"></i>
            </button>
            <button
              v-if="editIdx === idx"
              class="btn btn-success btn-sm d-flex align-items-center gap-1"
              style="border-radius:8px"
              @click="saveEdit(idx)"
            >
              <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" /></svg>
              <span class="d-none d-md-inline">Зберегти</span>
            </button>
            <button
              v-if="editIdx === idx"
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
              style="border-radius:8px"
              @click="cancelEdit"
            >
              <span>Скасувати</span>
            </button>
          </div>
        </div>

        <!-- Edit Form -->
        <div v-if="editIdx === idx" class="row g-3 align-items-center">
          <div class="col-12 col-md-6">
            <input
              v-model="editForm.name"
              type="text"
              class="form-control mb-3"
              placeholder="Назва кольору"
            />
          </div>
          <div class="col-12">
            <Multiselect
              v-model="editForm.product"
              :options="products"
              :searchable="true"
              :custom-label="productLabel"
              :filter="customFilter"
              :close-on-select="true"
              :clear-on-select="true"
              :preserve-search="true"
              placeholder="Виберіть продукт"
              label="name"
              track-by="id"
              class="mb-3 multiselect-fullwidth"
            >
              <template #option="{ option }">
                <div class="d-flex align-items-center gap-3">
                  <img
                    v-if="option.image"
                    :src="fullImageUrl(option.image)"
                    alt="color image"
                    class="me-4"
                    style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #f2f4f8; background: #fff;"
                  />
                  <span class="fw-bold fs-5">{{ productLabel(option) }}</span>
                </div>
              </template>
              <template #singleLabel="{ option }">
                <div class="d-flex align-items-center gap-2">
                  <img
                    v-if="option.image"
                    :src="fullImageUrl(option.image)"
                    alt=""
                    style="width: 38px; height: 38px; object-fit: cover; border-radius: 7px;"
                  />
                  <span>{{ productLabel(option) }}</span>
                </div>
              </template>
            </Multiselect>
          </div>
          <div class="col-12 col-md-6">
            <input type="file" class="form-control mb-3" @change="onEditImageChange" />
            <img v-if="editForm.image" :src="fullImageUrl(editForm.image)" alt="" class="color-img-preview mb-2" />
          </div>
        </div>

        <!-- Display -->
        <div v-else class="d-flex align-items-center">
          <img
            v-if="color.icon_path"
            :src="fullImageListUrl(color.icon_path)"
            alt="color image"
            class="me-4"
            style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #f2f4f8; background: #fff;"
          />
          <div>
            <div class="fw-bold fs-5 lh-sm mb-1">{{ color.name }}</div>
            <div v-if="linkedProducts[color.linked_product_id]" class="fw-semibold">
              <a
                :href="productUrl(linkedProducts[color.linked_product_id].slug)"
                target="_blank"
                class="text-primary fw-bold"
              >
                {{ linkedProducts[color.linked_product_id].name }}
              </a>
              <span class="ms-2 text-muted">SKU: {{ linkedProducts[color.linked_product_id].sku }}</span>
            </div>
            <div class="text-secondary fw-semibold" v-else-if="color.linked_product_id">
              ID товару: {{ color.linked_product_id }} (товар не знайдено)
            </div>
          </div>
        </div>
      </div>

      <!-- Форма додавання нового кольору -->
      <div v-if="editIdx === 'add'" class="position-relative bg-light rounded-4 border p-4 mb-3 shadow-sm">
        <div class="row g-3 align-items-center">
          <div class="col-12 col-md-6">
            <input
              v-model="editForm.name"
              type="text"
              class="form-control mb-3"
              placeholder="Назва кольору"
            />
          </div>
          <div class="col-12">
            <Multiselect
              v-model="editForm.product"
              :options="products"
              :searchable="true"
              :custom-label="productLabel"
              :filter="customFilter"
              :close-on-select="true"
              :clear-on-select="true"
              :preserve-search="true"
              placeholder="Виберіть продукт"
              label="name"
              track-by="id"
              class="mb-3 multiselect-fullwidth"
            >
              <template #option="{ option }">
                <div class="d-flex align-items-center gap-3">
                  <img
                    v-if="option.image"
                    :src="fullImageUrl(option.image)"
                    alt=""
                    style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px;"
                  />
                  <span class="fw-bold fs-5">{{ productLabel(option) }}</span>
                </div>
              </template>
              <template #singleLabel="{ option }">
                <div class="d-flex align-items-center gap-2">
                  <img
                    v-if="option.image"
                    :src="fullImageUrl(option.image)"
                    alt=""
                    style="width: 38px; height: 38px; object-fit: cover; border-radius: 7px;"
                  />
                  <span>{{ productLabel(option) }}</span>
                </div>
              </template>
            </Multiselect>
          </div>
          <div class="col-12 col-md-6">
            <input type="file" class="form-control mb-3" @change="onEditImageChange" />
            <img v-if="editForm.image" :src="fullImageUrl(editForm.image)" alt="" class="color-img-preview mb-2" />
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="button" class="btn btn-success flex-fill" @click="saveAdd">
              Додати
            </button>
            <button type="button" class="btn btn-outline-secondary flex-fill" @click="cancelEdit">
              Скасувати
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Кнопка ДОДАТИ КОЛІР -->
    <div class="mt-3">
      <button class="btn btn-primary w-100" @click="showAddForm">
        + Додати колір
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  productId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const localColors = ref([...props.modelValue])
watch(() => props.modelValue, v => { localColors.value = [...v] })

// --- Linked products API ---
const linkedProducts = ref({})
function fullImageUrl(path) {
  if (!path) return ''
  if (path.startsWith('http') || path.startsWith('/storage/') || path.startsWith('/')) {
    return path
  }
  return '/storage/' + path.replace(/^\/+/, '')
}

function fullImageListUrl(path) {
  if (!path) return ''
  // Якщо шлях не починається з http або /, додаємо провідний /
  if (path.startsWith('http') || path.startsWith('/')) {
    return path
  }
  return '/' + path.replace(/^\/+/, '')
}

function productUrl(slug) {
  if (!slug) return '#'
  return `/uk/product/${slug}`
}

async function fetchLinkedProducts() {
  const ids = [...new Set(localColors.value.map(c => c.linked_product_id).filter(Boolean))]
  if (!ids.length) {
    linkedProducts.value = {}
    return
  }
  try {
    const { data } = await axios.post('/api/products/get-linked-info', { ids })
    linkedProducts.value = data || {}
  } catch {
    linkedProducts.value = {}
  }
}

onMounted(fetchLinkedProducts)
watch(() => localColors.value.map(c => c.linked_product_id).join(','), fetchLinkedProducts)

// --- Multiselect Products ---
const products = ref([])
onMounted(async () => {
  try {
    const res = await axios.get('/admin/products/list')
    products.value = Array.isArray(res.data) ? res.data : (res.data.data ?? [])
  } catch {
    products.value = []
  }
})

function productLabel(option) {
  if (!option) return ''
  return option.sku ? `${option.name} (SKU: ${option.sku})` : option.name
}


function customFilter(option, search) {
  if (!search) return true
  const name = (option.name || '').toLowerCase()
  const sku = (option.sku || '').toLowerCase()
  const q = search.toLowerCase()
  return name.includes(q) || sku.includes(q)
}

// --- Inline редагування/додавання ---
const editIdx = ref(null)
const editForm = ref({ name: '', linked_product_id: '', icon_path: '', product: null, image: '' })

function startEdit(idx) {
  editIdx.value = idx
  const item = localColors.value[idx]
  const selectedProduct = products.value.find(p => p.id === item.linked_product_id) || null
  editForm.value = {
    name: item.name || '',
    linked_product_id: item.linked_product_id || '',
    icon_path: item.icon_path || '',
    image: item.icon_path || '',
    product: selectedProduct,
  }
}

function showAddForm() {
  editIdx.value = 'add'
  editForm.value = { name: '', linked_product_id: '', icon_path: '', image: '', product: null }
}

function saveEdit(idx) {
  if (editIdx.value === null) return
  if (!editForm.value.name || !editForm.value.product) {
    alert('Будь ласка, заповніть усі поля перед збереженням кольору.')
    return
  }
  localColors.value[idx] = {
    ...localColors.value[idx],
    name: editForm.value.name,
    linked_product_id: editForm.value.product.id,
    icon_path: editForm.value.image || '',
  }
  emit('update:modelValue', [...localColors.value])
  cancelEdit()
}

function saveAdd() {
  if (!editForm.value.name || !editForm.value.product) {
    alert('Будь ласка, заповніть усі поля перед додаванням кольору.')
    return
  }
  localColors.value.push({
    name: editForm.value.name,
    linked_product_id: editForm.value.product.id,
    icon_path: editForm.value.image || '',
    product_id: props.productId
  })
  emit('update:modelValue', [...localColors.value])
  cancelEdit()
}

function cancelEdit() {
  editIdx.value = null
  editForm.value = { name: '', linked_product_id: '', icon_path: '', product: null, image: '' }
}

function removeColor(idx) {
  localColors.value.splice(idx, 1)
  emit('update:modelValue', [...localColors.value])
}

// --- Upload image and save only url ---
async function uploadImage(file) {
  if (!file) return ''
  if (!props.productId) {
    alert('ID продукту не знайдено. Збережіть товар перед додаванням фото!')
    return ''
  }
  const formData = new FormData()
  formData.append('image', file)
  formData.append('product_id', props.productId)
  try {
    const resp = await axios.post('/api/upload-image-color', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    console.log('uploadImage response:', resp.data)
    return resp.data.url || ''
  } catch (e) {
    console.error('uploadImage error:', e)
    alert('Помилка завантаження зображення!')
    return ''
  }
}

async function onEditImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  const url = await uploadImage(file)
  if (url) editForm.value.image = url
}
</script>

<style scoped>
.color-img-preview {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e3e3e3;
  margin-right: 8px;
  background: #fff;
}
.color-list { min-height: 80px; }
.color-card {
  transition: box-shadow .18s;
  box-shadow: 0 1.5px 16px #e3e9fa;
  margin-bottom: 10px;
}
</style>