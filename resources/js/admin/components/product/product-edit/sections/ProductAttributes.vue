<template>
  <div class="card mb-4 p-4">
    <h5 class="fw-bold mb-3">Характеристики товару</h5>
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <button class="nav-link" :class="{active: lang === 'uk'}" @click="lang = 'uk'">Українська</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" :class="{active: lang === 'ru'}" @click="lang = 'ru'">Російська</button>
      </li>
    </ul>
    <div class="row g-3 align-items-start">
      <!-- Форма додавання -->
      <div class="col-md-5">
        <div class="attr-form shadow-sm rounded-3 p-3 mb-3 bg-light">
          <input v-model="form.name" type="text" class="form-control mb-2" :placeholder="lang === 'uk' ? 'Характеристика' : 'Характеристика (рус)'" />
          <input v-model="form.value" type="text" class="form-control mb-3" :placeholder="lang === 'uk' ? 'Значення' : 'Значение'" />
          <button
            v-if="editIndex === null"
            class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
            @click="addAttr"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
            Додати характеристику
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
      <!-- Список характеристик (sortable) -->
      <div class="col-md-7">
        <div class="attr-list shadow-sm rounded-3 p-3 mb-3 bg-light">
          <draggable
            v-model="attributes[lang]"
            item-key="_id"
            handle=".drag-handle"
            :animation="180"
            class="d-flex flex-column gap-2 mb-0"
          >
            <template #item="{ element, index }">
              <div
                class="attr-card shadow-sm rounded-4 px-3 py-2 bg-white d-flex align-items-center position-relative"
              >
                <span class="drag-handle me-3" title="Перетягнути" style="cursor:grab;">
                  <svg width="18" height="18" fill="#b0b0b0" viewBox="0 0 24 24"><circle cx="7" cy="7" r="2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="7" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                </span>
                <div class="w-100 d-flex flex-row flex-wrap align-items-center">
                  <div class="attr-label fw-bold">{{ element.name }}</div>
                  <div class="attr-sep mx-2">:</div>
                  <div class="attr-value flex-fill">{{ element.value }}</div>
                </div>
                <div class="attr-actions position-absolute top-0 end-0 mt-2 me-2 d-flex gap-1">
                  <button class="btn-action btn-edit" @click="startEdit(index)">
                    <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232a2.828 2.828 0 1 1 4 4L7.5 21H3v-4.5l12.232-12.268z"/></svg>
                  </button>
                  <button class="btn-action btn-remove" @click="removeAttr(index)">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"
                      viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path d="M15 9l-6 6M9 9l6 6"/></svg>
                  </button>
                </div>
              </div>
            </template>
          </draggable>
          <div v-if="!attributes[lang].length" class="text-muted mt-4 ms-2">Ще не додано жодної характеристики</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, defineProps, defineEmits } from 'vue'
import draggable from 'vuedraggable'

// v-model support
const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ uk: [], ru: [] })
  }
})
const emits = defineEmits(['update:modelValue'])

const lang = ref('uk')

// 1. Стан для обох мов
const attributes = reactive({
  uk: [],
  ru: [],
})

const form = reactive({ name: '', value: '' })
const editIndex = ref(null)
let attrId = 0

// 2. При ініціалізації та зміні пропса — синхронізуємо
watch(
  () => props.modelValue,
  (val) => {
    // старий масив — залишаємо все для поточної мови
    if (Array.isArray(val)) {
      attributes.uk = val.map((v, i) => ({ ...v, _id: v._id || Date.now() + i }))
      attributes.ru = []
    } else if (val && typeof val === 'object') {
      attributes.uk = Array.isArray(val.uk) ? val.uk.map((v, i) => ({ ...v, _id: v._id || Date.now() + i })) : []
      attributes.ru = Array.isArray(val.ru) ? val.ru.map((v, i) => ({ ...v, _id: v._id || Date.now() + i })) : []
    }
    // Задаємо атрибутний лічильник (ID)
    attrId = Math.max(
      ...attributes.uk.map(a => a._id || 0),
      ...attributes.ru.map(a => a._id || 0),
      0
    )
  },
  { deep: true, immediate: true }
)

function emitAttrs() {
  emits('update:modelValue', {
    uk: attributes.uk.map(({ _id, ...rest }) => rest),
    ru: attributes.ru.map(({ _id, ...rest }) => rest),
  })
}

function addAttr() {
  if (!form.name || !form.value) {
    alert('Заповніть обидва поля!')
    return
  }
  attributes[lang.value].push({
    _id: ++attrId,
    name: form.name,
    value: form.value
  })
  emitAttrs()
  resetForm()
}
function startEdit(idx) {
  editIndex.value = idx
  const a = attributes[lang.value][idx]
  form.name = a.name
  form.value = a.value
}
function saveEdit() {
  if (editIndex.value === null) return
  attributes[lang.value][editIndex.value] = {
    ...attributes[lang.value][editIndex.value],
    name: form.name,
    value: form.value
  }
  emitAttrs()
  editIndex.value = null
  resetForm()
}
function removeAttr(idx) {
  attributes[lang.value].splice(idx, 1)
  emitAttrs()
  if (editIndex.value === idx) {
    editIndex.value = null
    resetForm()
  }
}
function resetForm() {
  form.name = ''
  form.value = ''
}
</script>



<style scoped>
/* ... стилі залишаються ті самі ... */
.attr-form, .attr-list {
  min-width: 220px;
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
  z-index: 2;
  margin-left: 4px;
  margin-right: 2px;
}
.btn-edit:hover { background: #e0ebff; }
.btn-remove:hover { background: #ffeaea; color: #dc2626; }
.attr-label {
  min-width: 100px;
  font-weight: 600;
  font-size: 1.06rem;
}
.attr-sep {
  color: #b1b1b1;
}
.attr-value {
  font-size: 1.08rem;
}
.attr-card {
  min-height: 44px;
}
.drag-handle {
  user-select: none;
  opacity: .7;
  transition: opacity .18s;
}
.drag-handle:active {
  opacity: 1;
}
</style>
