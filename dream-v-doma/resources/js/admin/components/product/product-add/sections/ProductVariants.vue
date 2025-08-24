<template>
  <div class="card mb-4 p-4">
    <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
      Варіанти товару
      <span v-if="variants.length" class="badge bg-secondary">{{ variants.length }}</span>
    </h5>

    <div class="row g-3 align-items-start">
      <!-- Зліва: Форма -->
      <div class="col-md-5">
        <div class="variant-form shadow-sm rounded-3 p-3 mb-3 bg-light">
          <div class="mb-2">
            <label class="form-label mb-1">Розмір</label>
            <input
              v-model.trim="form.size"
              @keydown.enter.prevent="submitForm"
              type="text"
              class="form-control"
              placeholder="36-37"
              list="sizeOptions"
            />
            <datalist id="sizeOptions">
              <option v-for="s in presetSizes" :key="s" :value="s" />
            </datalist>
          </div>

          <div class="mb-2">
            <label class="form-label mb-1">Колір</label>
            <input
              v-model.trim="form.color"
              @keydown.enter.prevent="submitForm"
              type="text"
              class="form-control"
              placeholder="Колір (наприклад, Чорний)"
              list="colorOptions"
            />
            <datalist id="colorOptions">
              <option v-for="c in presetColors" :key="c" :value="c" />
            </datalist>
          </div>

          <div class="mb-2">
            <label class="form-label mb-1">Ціна</label>
            <div class="input-group">
              <input v-model.number="form.price_override" @keydown.enter.prevent="submitForm" type="number" min="0" step="1" class="form-control" placeholder="Ціна" />
              <span class="input-group-text">{{ currency }}</span>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label mb-1">Стара ціна (необов’язково)</label>
            <div class="input-group">
              <input v-model.number="form.old_price" @keydown.enter.prevent="submitForm" type="number" min="0" step="1" class="form-control" placeholder="Стара ціна" />
              <span class="input-group-text">{{ currency }}</span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label mb-1">Кількість</label>
            <input v-model.number="form.quantity" @keydown.enter.prevent="submitForm" type="number" min="0" step="1" class="form-control" placeholder="Кількість" />
          </div>

          <div class="small text-danger mb-2" v-if="error">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ error }}
          </div>

          <div class="d-grid gap-2">
            <button
              v-if="editIndex === null"
              class="btn btn-primary d-flex align-items-center justify-content-center gap-2"
              :disabled="!isValid"
              @click="submitForm"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
              Додати варіант
            </button>

            <div v-else class="d-grid gap-2">
              <button
                class="btn btn-success d-flex align-items-center justify-content-center gap-2"
                :disabled="!isValid"
                @click="saveEdit"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                Зберегти зміни
              </button>
              <button class="btn btn-outline-secondary" @click="cancelEdit">Скасувати</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Справа: Список -->
      <div class="col-md-7">
        <div class="variant-list shadow-sm rounded-3 p-3 mb-3 bg-light">
          <transition-group name="fade" tag="div" class="d-flex flex-column gap-3 mb-0">
            <div
              v-for="(v, i) in variants"
              :key="v._id"
              class="variant-card shadow-sm rounded-4 px-3 py-3 bg-white d-flex align-items-center position-relative"
            >
              <div class="w-100">
                <div class="row gy-2 gx-3 align-items-center">
                  <div class="col-6 col-lg-4">
                    <div class="label">Розмір</div>
                    <div class="value d-flex align-items-center gap-2">
                      <h6 class="mb-0">{{ v.size }}</h6>
                    </div>
                  </div>

                  <div class="col-6 col-lg-4">
                    <div class="label">Ціна</div>
                    <div class="value price">
                      <h6 class="mb-0">{{ fmt(v.price_override) }}</h6>
                      <small v-if="v.old_price" class="text-muted ms-2 text-decoration-line-through">{{ fmt(v.old_price) }}</small>
                    </div>
                  </div>

                  <div class="col-6 col-lg-4">
                    <div class="label">Кількість</div>
                    <div class="value d-flex align-items-center gap-2">
                      <h6 class="mb-0">{{ v.quantity }} шт</h6>
                      <span v-if="v.quantity === 0" class="badge bg-danger">Немає</span>
                      <span v-else-if="v.quantity > 0 && v.quantity < 5" class="badge bg-warning text-dark">Мало</span>
                    </div>
                  </div>

                  <div class="col-6 col-lg-4">
                    <div class="label">Колір</div>
                    <div class="value"><h6 class="mb-0">{{ v.color || '—' }}</h6></div>
                  </div>
                </div>
              </div>

              <div class="variant-actions position-absolute top-0 end-0 mt-2 me-2 d-flex gap-1">
                <button class="btn-action btn-edit" title="Редагувати" @click="startEdit(i)">
                  <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232a2.828 2.828 0 1 1 4 4L7.5 21H3v-4.5l12.232-12.268z"/></svg>
                </button>
                <button class="btn-action btn-remove" title="Видалити" @click="confirmRemove(i)">
                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M15 9l-6 6M9 9l6 6"/></svg>
                </button>
              </div>
            </div>
          </transition-group>

          <div v-if="!variants.length" class="text-muted mt-4 ms-2">Ще не додано жодного варіанта</div>
        </div>

        <div class="d-flex justify-content-end small text-muted px-1">
          Загалом: <span class="ms-1 fw-semibold">{{ totalQty }}</span> шт
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  currency: { type: String, default: 'грн' },
  sizePresets: { type: Array, default: () => ['35-36','36-37','37-38','38-39','40-41'] },
  colorPresets: { type: Array, default: () => ['Чорний','Білий','Бежевий','Сірий','Червоний','Синій'] }
})
const emits = defineEmits(['update:modelValue'])

const form = reactive({
  size: '',
  color: '',
  price_override: null,
  old_price: null,
  quantity: null,
})

const variants = ref(props.modelValue.map((v, i) => ({ ...v, _id: i + 1 })))
let variantId = variants.value.length

const editIndex = ref(null)
const error = ref('')

const presetSizes = computed(() => props.sizePresets)
const presetColors = computed(() => props.colorPresets)

watch(
  variants,
  (v) => {
    emits('update:modelValue', v.map(({ _id, ...rest }) => rest))
  },
  { deep: true }
)

const isValid = computed(() => {
  return Boolean(form.size && form.price_override !== null && form.price_override >= 0 && form.quantity !== null && form.quantity >= 0)
})

const totalQty = computed(() => variants.value.reduce((sum, v) => sum + (Number(v.quantity) || 0), 0))

function fmt(val){
  const n = Number(val)
  if (Number.isNaN(n)) return val
  return new Intl.NumberFormat('uk-UA').format(n) + ' ' + props.currency
}

function resetForm(){
  form.size = ''
  form.color = ''
  form.price_override = null
  form.old_price = null
  form.quantity = null
  error.value = ''
}

function duplicateExists(size, color, skipIndex = null){
  const needle = (size || '').toLowerCase() + '|' + (color || '').toLowerCase()
  return variants.value.some((v, idx) => ((v.size || '').toLowerCase() + '|' + (v.color || '').toLowerCase()) === needle && idx !== skipIndex)
}

function submitForm(){
  error.value = ''
  if (!isValid.value){
    error.value = 'Заповніть обовʼязкові поля (розмір, ціна, кількість).'
    return
  }
  if (duplicateExists(form.size, form.color)){
    error.value = 'Такий варіант уже існує (комбінація розмір + колір).'
    return
  }
  variants.value.push({
    _id: ++variantId,
    size: form.size,
    color: form.color,
    price_override: Number(form.price_override),
    old_price: form.old_price !== null && form.old_price !== '' ? Number(form.old_price) : null,
    quantity: Number(form.quantity)
  })
  resetForm()
}

function startEdit(idx){
  editIndex.value = idx
  const v = variants.value[idx]
  form.size = v.size
  form.color = v.color
  form.price_override = v.price_override
  form.old_price = v.old_price
  form.quantity = v.quantity
  error.value = ''
}

function saveEdit(){
  if (editIndex.value === null) return
  if (!isValid.value){
    error.value = 'Заповніть обовʼязкові поля.'
    return
  }
  if (duplicateExists(form.size, form.color, editIndex.value)){
    error.value = 'Комбінація розмір + колір уже існує.'
    return
  }
  variants.value[editIndex.value] = {
    ...variants.value[editIndex.value],
    size: form.size,
    color: form.color,
    price_override: Number(form.price_override),
    old_price: form.old_price !== null && form.old_price !== '' ? Number(form.old_price) : null,
    quantity: Number(form.quantity)
  }
  editIndex.value = null
  resetForm()
}

function cancelEdit(){
  editIndex.value = null
  resetForm()
}

function confirmRemove(idx){
  if (confirm('Видалити цей варіант?')){
    removeVariant(idx)
  }
}

function removeVariant(idx){
  variants.value.splice(idx, 1)
  if (editIndex.value === idx){
    editIndex.value = null
    resetForm()
  }
}
</script>

<style scoped>
.label{font-size:.8rem;color:#6c757d}
.value{display:flex;align-items:center;gap:.25rem}
.btn-action{border:none;background:#f3f3f3;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;transition:background .2s,color .2s;z-index:2;margin-left:4px;margin-right:2px}
.btn-edit:hover{background:#e0ebff}
.btn-remove:hover{background:#ffeaea;color:#dc2626}
.fade-enter-active,.fade-leave-active{transition:opacity .15s ease}
.fade-enter-from,.fade-leave-to{opacity:0}
</style>
