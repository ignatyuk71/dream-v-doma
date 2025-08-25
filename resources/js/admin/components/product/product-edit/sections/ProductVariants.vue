<template>
  <div class="card mb-4 p-4">
    <h5 class="fw-bold mb-3">Варіанти товару</h5>
    <div class="row g-3 align-items-start">
      <!-- Зліва: Форма -->
      <div class="col-md-5">
        <div class="variant-form shadow-sm rounded-3 p-3 mb-3 bg-light">
          <input v-model="form.size" type="text" class="form-control mb-2" placeholder="Розмір (36-37)" />
          <input v-model="form.color" type="text" class="form-control mb-2" placeholder="Колір" />
          <input v-model="form.price_override" type="number" class="form-control mb-2" placeholder="Ціна" />
          <input v-model="form.old_price" type="number" class="form-control mb-2" placeholder="Стара ціна" />
          <input v-model="form.quantity" type="number" class="form-control mb-3" placeholder="Кількість" />
          <button
            v-if="editIndex === null"
            class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
            @click="addVariant"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            Додати варіант
          </button>
          <button
            v-else
            class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2"
            @click="saveEdit"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            Зберегти зміни
          </button>
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
                <div class="row gy-2 gx-3">
                  <div class="col-6 col-lg-4">
                    <div class="label">Розмір</div>
                    <div class="value"><h5>{{ v.size }}</h5></div>
                  </div>
                  <div class="col-6 col-lg-4">
                    <div class="label">Ціна</div>
                    <div class="value price"><h5>{{ v.price_override }} грн</h5></div>
                  </div>
                  <div class="col-6 col-lg-4">
                    <div class="label">Стара ціна</div>
                    <div class="value old-price" v-if="v.old_price"><h5>{{ v.old_price }} грн</h5></div>
                    <div class="value text-muted" v-else>—</div>
                  </div>
                  <div class="col-6 col-lg-4">
                    <div class="label">Кількість</div>
                    <div class="value"><h5>{{ v.quantity }} шт</h5></div>
                  </div>
                  <div class="col-6 col-lg-4">
                    <div class="label">Колір</div>
                    <div class="value"><h5>{{ v.color }}</h5></div>
                  </div>
                </div>
              </div>
              <div class="variant-actions position-absolute top-0 end-0 mt-2 me-2 d-flex gap-1">
                <button class="btn-action btn-edit" @click="startEdit(i)">
                  <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232a2.828 2.828 0 1 1 4 4L7.5 21H3v-4.5l12.232-12.268z"/></svg>
                </button>
                <button class="btn-action btn-remove" @click="removeVariant(i)">
                  <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M15 9l-6 6M9 9l6 6"/></svg>
                </button>
              </div>
            </div>
          </transition-group>
          <div v-if="!variants.length" class="text-muted mt-4 ms-2">Ще не додано жодного варіанта</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, defineProps, defineEmits, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})
const emits = defineEmits(['update:modelValue'])

const form = reactive({
  size: '',
  color: '',
  price_override: '',
  old_price: '',
  quantity: '',
})

const variants = ref([])

watch(
  () => props.modelValue,
  (val) => {
    if (Array.isArray(val)) {
      variants.value = val.map((v, i) => ({
        ...v,
        _id: v.id || Date.now() + i
      }))
    }
  },
  { deep: true, immediate: true }
)

const editIndex = ref(null)

function emitVariants() {
  emits('update:modelValue', variants.value.map(({ _id, ...rest }) => rest))
}

function addVariant() {
  if (!form.size || !form.price_override || !form.quantity) {
    alert('Заповніть обовʼязкові поля!')
    return
  }
  variants.value.push({
    _id: Date.now() + (Math.random() * 1000 | 0),
    size: form.size,
    color: form.color,
    price_override: Number(form.price_override),
    old_price: form.old_price ? Number(form.old_price) : null,
    quantity: Number(form.quantity)
  })
  emitVariants()
  resetForm()
}

function startEdit(idx) {
  editIndex.value = idx
  const v = variants.value[idx]
  form.size = v.size
  form.color = v.color
  form.price_override = v.price_override
  form.old_price = v.old_price
  form.quantity = v.quantity
}
function saveEdit() {
  if (editIndex.value === null) return
  variants.value[editIndex.value] = {
    ...variants.value[editIndex.value],
    size: form.size,
    color: form.color,
    price_override: Number(form.price_override),
    old_price: form.old_price ? Number(form.old_price) : null,
    quantity: Number(form.quantity)
  }
  emitVariants()
  editIndex.value = null
  resetForm()
}
function removeVariant(idx) {
  variants.value.splice(idx, 1)
  emitVariants()
  if (editIndex.value === idx) {
    editIndex.value = null
    resetForm()
  }
}
function resetForm() {
  form.size = ''
  form.color = ''
  form.price_override = ''
  form.old_price = ''
  form.quantity = ''
}
</script>

<style scoped>
.btn-action {
  border: none;
  background: #f3f3f3;
  border-radius: 50%;
  width: 36px; height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background .2s, color .2s;
  z-index: 2;
  margin-left: 4px;
  margin-right: 2px;
}
.btn-edit:hover {
  background: #e0ebff;
}
.btn-remove:hover {
  background: #ffeaea;
  color: #dc2626;
}
</style>
