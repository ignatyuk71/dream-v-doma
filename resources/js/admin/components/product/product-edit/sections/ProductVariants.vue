<template>
  <div class="card p-4 variant-manager">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <h5 class="fw-bold mb-0">Варіанти товару</h5>
        <span class="badge bg-secondary-subtle text-secondary fw-semibold">{{ variants.length }}</span>
      </div>
      <div class="small text-muted">Загалом: <strong>{{ totalQty }}</strong> шт</div>
    </div>

    <div class="row g-4">
      <!-- LEFT: форма -->
      <div class="col-lg-5">
        <form
          class="panel p-3 rounded-4 shadow-sm"
          :class="{ 'panel-expanded': editIndex !== null }"
          @submit.prevent="submit"
          novalidate
        >
          <label class="form-label fw-semibold">Розмір</label>
          <input v-model.trim="form.size" list="sizesList" type="text"
                 class="form-control form-accent mb-3"
                 placeholder="36-37" :class="{'is-invalid': errors.size}" @input="clear('size')" />
          <datalist id="sizesList"><option v-for="s in sizeOptions" :key="s" :value="s" /></datalist>
          <div class="invalid-feedback">Вкажіть розмір.</div>

          <label class="form-label fw-semibold">Колір</label>
          <input v-model.trim="form.color" list="colorsList" type="text"
                 class="form-control mb-3" :disabled="!form.size" placeholder="Чорний або #000000" />
          <datalist id="colorsList"><option v-for="c in colorOptions" :key="c" :value="c" /></datalist>

          <label class="form-label fw-semibold">Ціна</label>
          <div class="input-group mb-3">
            <input v-model.number="form.price_override" type="number" min="0" step="1"
                   class="form-control" :disabled="!form.size"
                   :class="{'is-invalid': errors.price_override}" placeholder="Ціна"
                   @input="clear('price_override')" />
            <span class="input-group-text">{{ currencySymbol }}</span>
            <div class="invalid-feedback">Вкажіть коректну ціну (≥ 0).</div>
          </div>

          <label class="form-label fw-semibold">Стара ціна (необовʼязково)</label>
          <div class="input-group mb-2">
            <input v-model.number="form.old_price" type="number" min="0" step="1"
                   class="form-control" :disabled="!form.size" placeholder="Стара ціна" />
            <span class="input-group-text">{{ currencySymbol }}</span>
          </div>
          <div v-if="discountHint" class="form-text mb-3">
            Знижка: {{ discountPercent(form.price_override, form.old_price) }}%
          </div>

          <label class="form-label fw-semibold">Кількість</label>
          <div class="input-group mb-3">
            <button class="btn btn-outline-secondary" type="button" @click="stepQty(-1)"
                    :disabled="!form.size || (form.quantity ?? 0) <= 0">−</button>
            <input v-model.number="form.quantity" type="number" min="0" step="1"
                   class="form-control text-center" :disabled="!form.size"
                   :class="{'is-invalid': errors.quantity}" placeholder="0" @input="clear('quantity')" />
            <button class="btn btn-outline-secondary" type="button" @click="stepQty(1)" :disabled="!form.size">+</button>
            <span class="input-group-text">шт</span>
            <div class="invalid-feedback">Кількість не може бути відʼємною.</div>
          </div>

          <!-- CTA -->
          <div class="d-grid gap-2 mt-2">
            <template v-if="editIndex !== null">
              <button type="submit" class="btn btn-giant btn-approve w-100 d-flex align-items-center justify-content-center gap-2">
                <svg class="icon" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                Зберегти зміни
              </button>
              <button type="button" class="btn btn-giant btn-ghost w-100" @click="cancelEdit">Скасувати</button>
            </template>
            <button v-else type="submit" class="btn btn-accent w-100 d-flex align-items-center justify-content-center gap-2" :disabled="!canSubmit">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
              Додати варіант
            </button>
          </div>
        </form>
      </div>

      <!-- RIGHT: картки (компактні) -->
      <div class="col-lg-7">
        <transition-group name="rows" tag="div" class="d-flex flex-column gap-2">
          <div
            v-for="(v,i) in variants"
            :key="v._id"
            class="variant-card expanded position-relative rounded-4 shadow-sm"
            :class="[{ editing: editIndex===i }, compact && 'compact']"
          >
            <!-- actions -->
            <div class="actions">
              <button class="act-btn" @click="startEdit(i)" title="Редагувати">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15.2 5.2a2.8 2.8 0 0 1 4 4L7.5 21H3v-4.5L15.2 5.2z" stroke="#3864ff" stroke-width="2"/></svg>
              </button>
              <button class="act-btn" @click="removeItem(i)" title="Видалити">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2"/></svg>
              </button>
            </div>

            <div class="vgrid">
              <div class="cell">
                <div class="v-label">Розмір</div>
                <div class="v-value size">{{ v.size }}</div>
              </div>

              <div class="cell">
                <div class="v-label">Ціна</div>
                <div class="v-value price d-flex align-items-baseline gap-2 flex-wrap">
                  <span class="cur">{{ money(v.price_override) }}</span>
                  <span v-if="v.old_price" class="old text-muted text-decoration-line-through">{{ money(v.old_price) }}</span>
                  <span v-if="v.old_price && v.old_price > v.price_override" class="disc badge bg-success-subtle text-success">
                    −{{ discountPercent(v.price_override, v.old_price) }}%
                  </span>
                </div>
              </div>

              <div class="cell">
                <div class="v-label">Кількість</div>
                <div class="v-value qty">{{ v.quantity }} шт</div>
              </div>

              <div class="cell color-row">
                <div class="v-label">Колір</div>
                <div class="v-value d-flex align-items-center gap-2">
                  <span>{{ v.color || '—' }}</span>
                  <span v-if="isHex(v.color)" class="swatch" :style="{ backgroundColor: v.color }" :title="v.color"></span>
                </div>
              </div>
            </div>
          </div>
        </transition-group>

        <div v-if="!variants.length" class="text-muted text-center py-4">Ще не додано жодного варіанта</div>
        <div class="text-end text-muted small mt-1">Загалом: <strong>{{ totalQty }}</strong> шт</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, computed, defineProps, defineEmits } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  sizePresets: { type: Array, default: () => ['35-36','36-37','37-38','38-39','40-41'] },
  colorSuggestions: { type: Array, default: () => ['Чорний','Сірий','#000000','#888888','#ffffff'] },
  currency: { type: String, default: 'UAH' },
  compact: { type: Boolean, default: true } // ← увімкнено «щільний» режим
})
const emit = defineEmits(['update:modelValue'])

const variants = ref([])
const editIndex = ref(null)

const form = reactive({ size:'', color:'', price_override:null, old_price:null, quantity:null })
const errors = reactive({ size:false, price_override:false, quantity:false })

watch(() => props.modelValue, (val) => {
  if (Array.isArray(val)) {
    variants.value = val.map((v,i) => ({ ...v, _id: v.id || `${Date.now()}-${i}-${Math.random().toString(36).slice(2)}` }))
  }
}, { deep:true, immediate:true })

function emitOut(){ emit('update:modelValue', variants.value.map(({ _id, ...rest }) => rest)) }

const sizeOptions = computed(() => props.sizePresets)
const colorOptions = computed(() => props.colorSuggestions)
const currencySymbol = computed(() => ({ UAH:'грн', PLN:'zł', USD:'$', EUR:'€' }[props.currency] ?? props.currency))

const canSubmit = computed(() =>
  !!form.size && form.price_override !== null && Number(form.price_override) >= 0 &&
  form.quantity !== null && Number(form.quantity) >= 0
)
const totalQty = computed(() => variants.value.reduce((s,v)=>s+Number(v.quantity||0),0))

function clear(k){ errors[k]=false }

function validate(){
  errors.size = !form.size
  errors.price_override = form.price_override === null || Number(form.price_override) < 0
  errors.quantity = form.quantity === null || Number(form.quantity) < 0
  const normColor = (form.color||'').trim().toLowerCase()
  const dup = variants.value.findIndex((v,idx) =>
    (editIndex.value===null || idx!==editIndex.value) &&
    v.size===form.size &&
    (v.color||'').trim().toLowerCase()===normColor
  )
  if (dup !== -1) { errors.size = true; alert('Такий варіант (розмір + колір) уже існує.'); return false }
  return !(errors.size || errors.price_override || errors.quantity)
}

function submit(){
  if (!validate()) return
  const payload = {
    size: form.size,
    color: form.color || '',
    price_override: Number(form.price_override ?? 0),
    old_price: form.old_price !== null && form.old_price !== '' ? Number(form.old_price) : null,
    quantity: Number(form.quantity ?? 0)
  }
  if (editIndex.value === null) variants.value.push({ _id: Date.now() + (Math.random()*1000|0), ...payload })
  else { variants.value[editIndex.value] = { ...variants.value[editIndex.value], ...payload }; editIndex.value = null }
  emitOut(); reset()
}

function startEdit(i){
  editIndex.value = i
  const v = variants.value[i]
  form.size = v.size; form.color = v.color || ''
  form.price_override = v.price_override ?? 0
  form.old_price = v.old_price ?? null
  form.quantity = v.quantity ?? 0
}
function cancelEdit(){ editIndex.value = null; reset() }

function removeItem(i){
  if (!confirm('Видалити варіант?')) return
  variants.value.splice(i,1); emitOut()
  if (editIndex.value === i) editIndex.value = null
}

function reset(){ form.size=''; form.color=''; form.price_override=null; form.old_price=null; form.quantity=null; Object.keys(errors).forEach(k=>errors[k]=false) }
function stepQty(d){ if(!form.size) return; const next = Number(form.quantity||0)+d; form.quantity = next<0?0:next; clear('quantity') }

// helpers
function isHex(s){ return typeof s==='string' && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(s.trim()) }
function money(n){
  const num = Number(n ?? 0)
  if (props.currency === 'UAH') return `${num.toLocaleString('uk-UA')} ${currencySymbol.value}`
  if (props.currency === 'PLN') return `${num.toLocaleString('pl-PL')} ${currencySymbol.value}`
  return new Intl.NumberFormat(undefined,{style:'currency',currency:props.currency,maximumFractionDigits:0}).format(num)
}
function discountPercent(price, oldPrice){ const p=Number(price), o=Number(oldPrice); if(!o || o<=p) return 0; return Math.round((1-p/o)*100) }
const discountHint = computed(()=>{ const p=Number(form.price_override??0), o=Number(form.old_price??0); return !!o && o>p && p>=0 })
</script>

<style scoped>
.variant-manager{ border:1px solid #eef1f5; border-radius:1rem; }
.panel{ background:#f4f5f8; border:1px solid #eceef3; }
.panel-expanded{ background:#eef0f5; border-color:#e0e3ea; }

/* accent */
.form-accent:focus{ border-color:#7b70f2 !important; box-shadow:0 0 0 .2rem rgba(123,112,242,.25) !important; }
.btn-accent{ background:#a79bf0; color:#fff; border:none; }
.btn-accent:hover{ filter:brightness(.96); }
.btn-accent:disabled{ opacity:.6; }

/* великі CTA */
.btn-approve{ background:#22c55e; color:#fff; border:none; box-shadow:inset 0 -2px 0 rgba(0,0,0,.06); }
.btn-approve:hover{ filter:brightness(.98); }
.btn-ghost{ background:#edeff3; color:#6b7280; border:1px solid #cfd3dc; }
.btn-ghost:hover{ background:#e6e9f0; }
.icon{ width:20px; height:20px; }

/* cards */
.variant-card{ background:#fff; border:1px solid #e7e8ee; }
.variant-card.editing{ box-shadow:0 0 0 3px rgba(123,112,242,.15); }

/* actions */
.actions{ position:absolute; top:.45rem; right:.45rem; display:flex; gap:.4rem; }
.act-btn{
  width:32px; height:32px; border-radius:50%;
  background:#f3f4f7; color:#111; border:none;
  display:inline-flex; align-items:center; justify-content:center;
  transition:background .15s ease, transform .05s;
}
.act-btn:hover{ background:#e9ebf3; }
.act-btn:active{ transform:translateY(1px); }

/* GRID — компактні відступи */
.vgrid{
  display:grid;
  grid-template-columns:1fr 1fr 1fr;
  gap:.6rem 1.2rem;      /* було більше — зменшив */
  padding: .75rem 3.75rem .6rem .9rem; /* тонші внутрішні поля і місце під кнопки */
}
.variant-card.compact{ padding:0 !important; } /* прибираємо p-3 від класів bootstrap */

/* типографіка компактна */
.cell .v-label{ color:#7a7f8f; font-size:.8rem; margin-bottom:.15rem; line-height:1.1; }
.v-value.size{ font-size:1.2rem; font-weight:700; color:#2a2f3a; line-height:1.15; }
.v-value.price .cur{ font-size:1.05rem; font-weight:700; color:#2a2f3a; line-height:1.1; }
.v-value.qty{ font-size:1rem; font-weight:700; color:#2a2f3a; line-height:1.1; }
.color-row{ grid-column:1 / -1; }
.swatch{ width:12px; height:12px; border-radius:50%; border:1px solid #e5e7eb; }
.disc{ font-size:.7rem; padding:.15rem .35rem; }

/* анімація */
.rows-enter-active,.rows-leave-active{ transition:all .12s ease; }
.rows-enter-from,.rows-leave-to{ opacity:0; transform:translateY(-3px); }

/* адаптив */
@media (max-width:768px){ .vgrid{ grid-template-columns:1fr 1fr; padding:.6rem 2.6rem .5rem .7rem; } }
@media (max-width:576px){ .vgrid{ grid-template-columns:1fr; padding:.55rem 2.4rem .5rem .7rem; } }

.rounded-4{ border-radius:1rem !important; }
</style>
