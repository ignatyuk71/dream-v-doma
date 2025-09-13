<template>
  <div class="card p-4 mb-4  attrs-simple">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <h5 class="fw-bold mb-0">Характеристики товару</h5>
      <ul class="nav nav-tabs small">
        <li class="nav-item">
          <button class="nav-link" :class="{active: lang==='uk'}" @click="switchLang('uk')">
            Українська <span class="badge bg-secondary-subtle text-secondary ms-1">{{ attributes.uk.length }}</span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" :class="{active: lang==='ru'}" @click="switchLang('ru')">
            Російська <span class="badge bg-secondary-subtle text-secondary ms-1">{{ attributes.ru.length }}</span>
          </button>
        </li>
      </ul>
    </div>

    <div class="row g-4">
      <!-- LEFT: форма -->
      <div class="col-lg-5">
        <div class="panel p-3 rounded-4 shadow-sm">
          <label class="form-label fw-semibold">{{ lang==='uk' ? 'Характеристика' : 'Характеристика (рус)' }}</label>
          <input
            v-model.trim="form.name"
            type="text"
            class="form-control form-accent mb-3"
            :placeholder="lang==='uk' ? 'Напр.: Матеріал' : 'Напр.: Материал'"
            :class="{'is-invalid': errors.name}"
            @input="errors.name=false"
          />
          <div class="invalid-feedback">Вкажіть назву.</div>

          <label class="form-label fw-semibold">{{ lang==='uk' ? 'Значення' : 'Значение' }}</label>
          <input
            v-model.trim="form.value"
            type="text"
            class="form-control mb-3"
            :placeholder="lang==='uk' ? 'Напр.: Шкіра' : 'Напр.: Кожа'"
            :class="{'is-invalid': errors.value}"
            @input="errors.value=false"
          />
          <div class="invalid-feedback">Вкажіть значення.</div>

          <div class="d-grid gap-2">
            <button
              type="button"
              class="btn btn-accent w-100 d-flex align-items-center justify-content-center gap-2"
              :disabled="!canSubmit"
              @click="editIndex===null ? addAttr() : saveEdit()"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path v-if="editIndex===null" d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/>
                <path v-else d="M5 13l4 4L19 7" stroke="white" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span v-if="editIndex===null">{{ lang==='uk' ? 'Додати' : 'Добавить' }}</span>
              <span v-else>{{ lang==='uk' ? 'Зберегти' : 'Сохранить' }}</span>
            </button>

            <button type="button" class="btn btn-light" @click="cancelEdit" :disabled="editIndex===null">
              {{ lang==='uk' ? 'Скасувати' : 'Отменить' }}
            </button>
          </div>
        </div>
      </div>

      <!-- RIGHT: список -->
      <div class="col-lg-7">
        <div class="attr-list shadow-sm rounded-4 p-2 bg-light">
          <draggable
            v-model="attributes[lang]"
            item-key="_id"
            handle=".drag-handle"
            :animation="150"
            class="d-flex flex-column gap-2"
            @end="emitAttrs"
          >
            <template #item="{ element, index }">
              <div class="attr-card rounded-4 bg-white shadow-sm">
                <div class="d-flex align-items-center gap-2">
                  <span class="drag-handle" title="Перетягнути">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="#b0b0b0">
                      <circle cx="7" cy="7" r="2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="7" r="2"/><circle cx="17" cy="17" r="2"/>
                    </svg>
                  </span>

                  <div class="flex-grow-1 d-flex flex-wrap align-items-center gap-1">
                    <span class="k">{{ element.name }}</span><span class="sep">:</span><span class="v">{{ element.value }}</span>
                  </div>

                  <div class="d-flex align-items-center gap-1 ms-auto">
                    <button class="btn-icon edit" @click="startEdit(index)" title="Редагувати">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15.2 5.2a2.8 2.8 0 0 1 4 4L7.5 21H3v-4.5L15.2 5.2z" stroke="#3864ff" stroke-width="2"/></svg>
                    </button>
                    <button class="btn-icon remove" @click="removeAttr(index)" title="Видалити">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                  </div>
                </div>
              </div>
            </template>
          </draggable>

          <div v-if="!attributes[lang].length" class="text-muted text-center py-4">
            {{ lang==='uk' ? 'Ще не додано жодної характеристики' : 'Пока что нет характеристик' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, defineProps, defineEmits, computed } from 'vue'
import draggable from 'vuedraggable'

const props = defineProps({
  modelValue: { type: Object, default: () => ({ uk: [], ru: [] }) }
})
const emit = defineEmits(['update:modelValue'])

const lang = ref('uk')
function switchLang(l){ cancelEdit(); lang.value = l }

const attributes = reactive({ uk: [], ru: [] })
const form = reactive({ name: '', value: '' })
const errors = reactive({ name: false, value: false })
const editIndex = ref(null)
let seq = 0

// sync in
watch(() => props.modelValue, (val) => {
  const toList = (arr) => Array.isArray(arr) ? arr.map((a, i) => ({ ...a, _id: a._id || `${Date.now()}-${i}-${Math.random().toString(36).slice(2)}` })) : []
  if (val && typeof val === 'object') {
    attributes.uk = toList(val.uk)
    attributes.ru = toList(val.ru)
  } else {
    attributes.uk = toList(val || [])
    attributes.ru = []
  }
  seq = Date.now()
}, { deep:true, immediate:true })

// sync out
function emitAttrs(){
  emit('update:modelValue', {
    uk: attributes.uk.map(({ _id, ...rest }) => rest),
    ru: attributes.ru.map(({ _id, ...rest }) => rest)
  })
}

const canSubmit = computed(() => !!form.name && !!form.value)

function addAttr(){
  if (!validate()) return
  attributes[lang.value].push({ _id: `${++seq}-${Math.random().toString(36).slice(2)}`, name: form.name, value: form.value })
  emitAttrs()
  resetForm()
}

function startEdit(i){
  editIndex.value = i
  const a = attributes[lang.value][i]
  form.name = a.name
  form.value = a.value
}

function saveEdit(){
  if (editIndex.value === null || !validate()) return
  const i = editIndex.value
  attributes[lang.value][i] = { ...attributes[lang.value][i], name: form.name, value: form.value }
  emitAttrs()
  cancelEdit()
}

function removeAttr(i){
  if (!confirm(lang.value==='uk' ? 'Видалити характеристику?' : 'Удалить характеристику?')) return
  attributes[lang.value].splice(i, 1)
  emitAttrs()
  if (editIndex.value === i) cancelEdit()
}

function cancelEdit(){ editIndex.value = null; resetForm(); errors.name=false; errors.value=false }
function resetForm(){ form.name=''; form.value='' }

function validate(){
  errors.name = !form.name
  errors.value = !form.value
  // перевірка дубля (назва+значення)
  const n = (form.name||'').trim().toLowerCase()
  const v = (form.value||'').trim().toLowerCase()
  const dup = attributes[lang.value].some((a, idx) =>
    (editIndex.value === null || idx !== editIndex.value) &&
    (a.name||'').trim().toLowerCase() === n &&
    (a.value||'').trim().toLowerCase() === v
  )
  if (dup) { alert(lang.value==='uk' ? 'Така пара вже існує.' : 'Такая пара уже существует.'); return false }
  return !(errors.name || errors.value)
}
</script>

<style scoped>
.attrs-simple{ border:1px solid #eef1f5; border-radius:1rem; }
.panel{ background:#f4f5f8; border:1px solid #eceef3; }

/* фіолетова кнопка Додати/Добавить */
.btn-accent{
  background:#7b70f2; /* насичений фіолетовий */
  color:#fff;
  border:none;
}
.btn-accent:hover{ filter:brightness(.96); }
.btn-accent:disabled{ opacity:.6; }

/* акцент фокуса */
.form-accent:focus{
  border-color:#7b70f2 !important;
  box-shadow:0 0 0 .2rem rgba(123,112,242,.25) !important;
}

/* список */
.attr-list{ border:1px solid #eef1f5; }
.attr-card{
  padding:.55rem .7rem;
  border:1px solid #e7e8ee;
}
.drag-handle{ cursor:grab; user-select:none; opacity:.75; display:inline-flex; }
.k{ font-weight:600; color:#2b2f3c; }
.sep{ color:#b1b1b1; }
.v{ color:#374151; }

/* кнопки дій */
.btn-icon{
  width:32px; height:32px; border-radius:50%;
  background:#f3f4f7; color:#111; border:none;
  display:inline-flex; align-items:center; justify-content:center;
  transition: background .15s ease, transform .05s;
}
.btn-icon.edit:hover{ background:#e7e9fd; }
.btn-icon.remove:hover{ background:#ffeaea; color:#dc2626; }
.btn-icon:active{ transform: translateY(1px); }

/* трохи щільніші вкладки */
.small .nav-link{ padding:.4rem .75rem; }
</style>
