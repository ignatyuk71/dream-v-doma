<template>
  <div class="card mb-4 p-4">
    <h5 class="fw-bold mb-3">Кольори товару</h5>

    <!-- Форма додавання кольору (зверху) -->
    <div class="color-form shadow-sm rounded-3 p-3 mb-4 bg-light">
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <input
            v-model="form.color"
            type="text"
            class="form-control mb-3"
            placeholder="Назва кольору"
          />
        </div>

        <div class="col-12">
          <Multiselect
            v-model="form.product"
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
            <!-- СПИСОК: мініатюра + назва + SKU -->
            <template #option="{ option }">
              <div class="d-flex align-items-center gap-3 w-100 py-1">
                <img
                  v-if="getImage(option)"
                  :src="fullImageUrl(getImage(option))"
                  alt=""
                  style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px;"
                />
                <div class="d-flex flex-column flex-grow-1 min-w-0">
                  <span class="fw-semibold text-truncate">{{ getTitle(option) }}</span>
                  <small v-if="getSku(option)" class="text-muted">SKU: {{ getSku(option) }}</small>
                </div>
              </div>
            </template>

            <!-- ВИБРАНЕ: мініатюра + назва (і SKU в дужках) -->
            <template #singleLabel="{ option }">
              <div class="d-flex align-items-center gap-2 min-w-0">
                <img
                  v-if="getImage(option)"
                  :src="fullImageUrl(getImage(option))"
                  alt=""
                  style="width: 38px; height: 38px; object-fit: cover; border-radius: 7px;"
                />
                <span class="text-truncate">
                  {{ getTitle(option) }}<template v-if="getSku(option)"> (SKU: {{ getSku(option) }})</template>
                </span>
              </div>
            </template>
          </Multiselect>
        </div>

        <div class="col-12 col-md-6">
          <input type="file" class="form-control mb-3" @change="onImageChange" />
          <img v-if="form.image" :src="form.image" alt="" class="color-img-preview mb-2" />
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button
          v-if="editIndex === null"
          class="btn btn-primary flex-fill d-flex align-items-center justify-content-center gap-2"
          @click="addColor"
        >
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
          Додати колір
        </button>
        <button
          v-else
          class="btn btn-success flex-fill d-flex align-items-center justify-content-center gap-2"
          @click="saveEdit"
        >
          <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
          Зберегти зміни
        </button>
      </div>
    </div>

    <!-- Список кольорів (знизу) -->
    <div class="color-list">
      <draggable
        v-model="colors"
        item-key="_id"
        class="d-flex flex-column gap-3 mb-0"
        :animation="200"
        handle=".drag-handle"
      >
        <template #item="{ element, index }">
          <div class="color-card shadow rounded-4 px-4 py-3 bg-white d-flex align-items-center position-relative gap-3" style="min-height:100px;">
            <span class="drag-handle me-2" title="Перемістити" style="cursor: grab; flex-shrink:0;">
              <svg width="22" height="22" fill="none" stroke="#b4b4b4" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="7" cy="7" r="1.5" />
                <circle cx="7" cy="12" r="1.5" />
                <circle cx="7" cy="17" r="1.5" />
                <circle cx="12" cy="7" r="1.5" />
                <circle cx="12" cy="12" r="1.5" />
                <circle cx="12" cy="17" r="1.5" />
                <circle cx="17" cy="7" r="1.5" />
                <circle cx="17" cy="12" r="1.5" />
                <circle cx="17" cy="17" r="1.5" />
              </svg>
            </span>
            <img v-if="element.image" :src="element.image" style="width: 64px; height: 64px; border-radius: 12px; border: 2px solid #f2f4f8; background: #fff; object-fit:cover; flex-shrink:0;" />
            <div class="flex-grow-1 d-flex flex-column justify-content-center">
              <div class="fw-bold fs-5 lh-sm mb-1">{{ element.color }}</div>
              <div class="text-secondary fw-semibold">{{ element.product ? productLabel(element.product) : '' }}</div>
            </div>
            <div class="color-actions d-flex flex-column align-items-center justify-content-center gap-2 position-absolute end-0 top-0 mt-2 me-2">
              <button class="btn-action btn-edit" @click="startEdit(index)" title="Редагувати">
                <svg width="24" height="24" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232a2.828 2.828 0 1 1 4 4L7.5 21H3v-4.5л12.232-12.268z"/></svg>
              </button>
              <button class="btn-action btn-remove" @click="removeColor(index)" title="Видалити">
                <svg width="24" height="24" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
              </button>
            </div>
          </div>
        </template>
      </draggable>
      <div v-if="!colors.length" class="text-muted mt-4 ms-2">
        Ще не додано жодного кольору
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import draggable from 'vuedraggable'
import 'vue-multiselect/dist/vue-multiselect.css'

// v-model з parent (ProductForm)
const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  productList: {
    type: Array,
    default: () => []
  }
})
const emit = defineEmits(['update:modelValue'])

// Список продуктів з бекенду
const products = ref([])

onMounted(async () => {
  try {
    const res = await axios.get('/admin/products/list')
    products.value = res.data
  } catch (e) {
    console.error('Не вдалося завантажити продукти:', e)
    products.value = []
  }
})

// ---------- ХЕЛПЕРИ ДЛЯ ПОЛІВ ВІД БЕКЕНДА ----------
function getTitle(p) {
  return p?.name || p?.title || p?.product_name || (p?.translations?.[0]?.title ?? p?.translations?.[0]?.name) || ''
}
function getSku(p) {
  return p?.sku ?? p?.article ?? p?.code ?? ''
}
function getImage(p) {
  return (
    p?.image ||
    p?.image_url ||
    p?.image_path ||
    p?.thumbnail ||
    p?.preview ||
    (Array.isArray(p?.images) ? (p.images[0]?.url || p.images[0]?.path) : '')
  )
}
function fullImageUrl(path) {
  if (!path) return ''
  if (/^data:/i.test(path)) return path
  if (path.startsWith('http') || path.startsWith('/storage/') || path.startsWith('/')) return path
  if (path.startsWith('//')) return `${window.location.protocol}${path}`
  return '/storage/' + path.replace(/^\/+/, '')   // <— ВАЖЛИВО: /^\/+/
}

// Пошук по назві або артикулу (sku)
function customFilter(option, search) {
  if (!search) return true
  const name = (getTitle(option) || '').toLowerCase()
  const sku = (getSku(option) || '').toLowerCase()
  const q = search.toLowerCase()
  return name.includes(q) || sku.includes(q)
}

// Відображення: назва + sku (якщо є)
function productLabel(option) {
  if (!option) return ''
  const t = getTitle(option)
  const s = getSku(option)
  return s ? `${t} (SKU: ${s})` : t
}

const form = reactive({
  color: '',
  product: null,
  image: null,
})
const colors = ref([...props.modelValue])
let colorId = 0
const editIndex = ref(null)

// Синхронізація з parent
watch(colors, (val) => {
  emit('update:modelValue', val)
}, { deep: true })

watch(() => props.modelValue, (val) => {
  if (val !== colors.value) {
    colors.value = [...val]
  }
})

// Далі логіка роботи з кольорами
function addColor() {
  if (!form.color || !form.product || !form.image) {
    alert('Заповніть всі поля та виберіть зображення!')
    return
  }
  colors.value.push({
    _id: ++colorId,
    color: form.color,
    linked_product_id: form.product.id, // <--- тільки ID!
    product: form.product,              // для відображення (UI)
    image: form.image
  })
  resetForm()
}
function startEdit(idx) {
  editIndex.value = idx
  const c = colors.value[idx]
  form.color = c.color
  form.product = c.product
  form.image = c.image
}
function saveEdit() {
  if (editIndex.value === null) return
  colors.value[editIndex.value] = {
    ...colors.value[editIndex.value],
    color: form.color,
    linked_product_id: form.product.id,
    product: form.product,
    image: form.image
  }
  editIndex.value = null
  resetForm()
}
function removeColor(idx) {
  colors.value.splice(idx, 1)
  if (editIndex.value === idx) {
    editIndex.value = null
    resetForm()
  }
}
function onImageChange(event) {
  const file = event.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = e => { form.image = e.target.result }
  reader.readAsDataURL(file)
}
function resetForm() {
  form.color = ''
  form.product = null
  form.image = ''
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
.btn-action {
  border: none;
  background: #f3f3f3;
  border-radius: 50%;
  width: 32px; height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .2s, color .2s;
  margin-left: 4px;
  margin-right: 2px;
}
.btn-edit:hover { background: #e0ebff; }
.btn-remove:hover { background: #ffeaea; color: #dc2626; }
.color-card { min-height: 52px; }
:deep(.multiselect) { min-width: 100%; }

.color-list { min-height: 150px; }
.color-card {
  transition: box-shadow .18s;
  box-shadow: 0 1.5px 16px #e3e9fa;
  margin-bottom: 10px;
  min-height: 100px;
}
.btn-action {
  border: none;
  background: #f4f6fb;
  border-radius: 50%;
  width: 40px; height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .18s, color .18s;
  margin-bottom: 4px;
  margin-right: 0;
  outline: none;
}
.btn-edit:hover { background: #e0ebff; }
.btn-remove:hover { background: #ffeaea; }

/* Multiselect на всю ширину */
:deep(.multiselect-fullwidth .multiselect) {
  width: 100% !important;
  min-width: 100% !important;
  max-width: 100% !important;
}

/* Ширина випадайки */
:deep(.multiselect__content-wrapper) {
  min-width: 420px !important;
  max-width: 100% !important;
}

/* Підсвітка як на макеті + без “Press enter …” */
:deep(.multiselect__option--highlight) {
  background: #34b57a !important;
  color: #fff !important;
}
:deep(.multiselect__option--highlight:after) {
  content: '' !important;
}
:deep(.multiselect__option--selected) {
  background: #eaf5ff !important;
  color: inherit !important;
}
</style>
