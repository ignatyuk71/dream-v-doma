<template>
  <div class="card p-4 mb-4 desc-simple">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
      <div class="d-flex align-items-center gap-3">
        <h5 class="fw-bold mb-0">Опис товару</h5>
        <ul class="nav nav-tabs small">
          <li class="nav-item">
            <button class="nav-link" :class="{active: lang==='uk'}" @click="switchLang('uk')">
              Українська <span class="badge bg-secondary-subtle text-secondary ms-1">{{ blocks.uk.length }}</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" :class="{active: lang==='ru'}" @click="switchLang('ru')">
              Російська <span class="badge bg-secondary-subtle text-secondary ms-1">{{ blocks.ru.length }}</span>
            </button>
          </li>
        </ul>
      </div>

      <button v-if="!addMode" class="btn btn-accent d-flex align-items-center gap-2" @click="startAdd">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 5v14m-7-7h14" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
        Додати блок
      </button>
    </div>

    <!-- Add form -->
    <form v-if="addMode" class="card p-3 rounded-4 shadow-sm mb-4" @submit.prevent="saveNew">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label">Тип блоку</label>
          <select class="form-select" v-model="newBlock.type" required>
            <option value="" disabled>Оберіть тип</option>
            <option value="text">Текст</option>
            <option value="image_right">Картинка справа</option>
            <option value="image_left">Картинка зліва</option>
            <option value="two_images">Дві картинки</option>
          </select>
        </div>

        <template v-if="newBlock.type==='text'">
          <div class="col-12">
            <input class="form-control mb-2" v-model.trim="newBlock.title" placeholder="Заголовок" />
            <textarea class="form-control" rows="6" v-model.trim="newBlock.text" placeholder="Текст"></textarea>
          </div>
        </template>

        <template v-else-if="newBlock.type==='image_right'">
          <div class="col-md-6">
            <input class="form-control mb-2" v-model.trim="newBlock.title" placeholder="Заголовок" />
            <textarea class="form-control" rows="6" v-model.trim="newBlock.text" placeholder="Текст"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Зображення</label>
            <input type="file" class="form-control" accept="image/*" @change="onAddImage($event,'single')" />
            <div v-if="newBlock.imageUrl" class="mt-2 preview"><img :src="newBlock.imageUrl" class="img-fluid rounded"/></div>
          </div>
        </template>

        <template v-else-if="newBlock.type==='image_left'">
          <div class="col-md-6">
            <label class="form-label">Зображення</label>
            <input type="file" class="form-control" accept="image/*" @change="onAddImage($event,'single')" />
            <div v-if="newBlock.imageUrl" class="mt-2 preview"><img :src="newBlock.imageUrl" class="img-fluid rounded"/></div>
          </div>
          <div class="col-md-6">
            <input class="form-control mb-2" v-model.trim="newBlock.title" placeholder="Заголовок" />
            <textarea class="form-control" rows="6" v-model.trim="newBlock.text" placeholder="Текст"></textarea>
          </div>
        </template>

        <template v-else-if="newBlock.type==='two_images'">
          <div class="col-md-6">
            <label class="form-label">Картинка 1</label>
            <input type="file" class="form-control" accept="image/*" @change="onAddImage($event,1)" />
            <div v-if="newBlock.imageUrl1" class="mt-2 preview small"><img :src="newBlock.imageUrl1" class="img-fluid rounded"/></div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Картинка 2</label>
            <input type="file" class="form-control" accept="image/*" @change="onAddImage($event,2)" />
            <div v-if="newBlock.imageUrl2" class="mt-2 preview small"><img :src="newBlock.imageUrl2" class="img-fluid rounded"/></div>
          </div>
        </template>

        <div class="col-12 d-flex gap-2">
          <button class="btn btn-success" type="submit" :disabled="uploading">Зберегти</button>
          <button class="btn btn-outline-secondary" type="button" @click="cancelAdd" :disabled="uploading">Скасувати</button>
        </div>
      </div>
    </form>

    <!-- List -->
    <div v-if="blocks[lang]?.length">
      <draggable
        v-model="blocks[lang]"
        item-key="_key"
        handle=".drag"
        :animation="160"
        class="d-flex flex-column gap-3"
        @end="emitOut"
      >
        <template #item="{ element, index }">
          <div class="desc-card rounded-4 shadow-sm">
            <!-- Header (in flow, no absolute) -->
            <div class="card-head">
              <span class="drag" title="Перетягнути">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#b0b0b0">
                  <circle cx="7" cy="7" r="2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="7" r="2"/><circle cx="17" cy="17" r="2"/>
                </svg>
              </span>
              <span class="badge type-badge">{{ typeLabel(element.type) }}</span>
              <div class="ms-auto d-flex align-items-center gap-1">
                <button class="icon-btn" @click="duplicate(index)" title="Клонувати">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M8 8h10v10H8z"/><path d="M6 16H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="#0ea5e9" stroke-width="2"/></svg>
                </button>
                <button class="icon-btn" @click="startEdit(index)" title="Редагувати">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15.2 5.2a2.8 2.8 0 0 1 4 4L7.5 21H3v-4.5L15.2 5.2z" stroke="#3864ff" stroke-width="2"/></svg>
                </button>
                <button class="icon-btn" @click="removeBlock(index)" title="Видалити">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M15 9l-6 6M9 9l6 6" stroke="currentColor" stroke-width="2"/></svg>
                </button>
              </div>
            </div>

            <!-- Read -->
            <div v-if="editIdx!==index" class="card-body">
              <template v-if="element.type==='text'">
                <div v-if="element.title" class="desc-title">{{ element.title }}</div>
                <div v-if="element.text"  class="desc-text">{{ element.text }}</div>
              </template>

              <template v-else-if="element.type==='image_right'">
                <div class="row align-items-center">
                  <div class="col-md-6">
                    <div v-if="element.title" class="desc-title">{{ element.title }}</div>
                    <div v-if="element.text"  class="desc-text">{{ element.text }}</div>
                  </div>
                  <div class="col-md-6 text-center"><img :src="element.imageUrl" class="img-fluid rounded"/></div>
                </div>
              </template>

              <template v-else-if="element.type==='image_left'">
                <div class="row align-items-center">
                  <div class="col-md-6 text-center mb-3 mb-md-0"><img :src="element.imageUrl" class="img-fluid rounded"/></div>
                  <div class="col-md-6">
                    <div v-if="element.title" class="desc-title">{{ element.title }}</div>
                    <div v-if="element.text"  class="desc-text">{{ element.text }}</div>
                  </div>
                </div>
              </template>

              <template v-else-if="element.type==='two_images'">
                <div class="row align-items-center">
                  <div class="col-md-6 text-center mb-3 mb-md-0"><img :src="element.imageUrl1" class="img-fluid rounded"/></div>
                  <div class="col-md-6 text-center"><img :src="element.imageUrl2" class="img-fluid rounded"/></div>
                </div>
              </template>
            </div>

            <!-- Edit -->
            <form v-else class="card-body" @submit.prevent="saveEdit(index)">
              <div class="small text-muted mb-2"><strong>Тип:</strong> {{ typeLabel(editForm.type) }}</div>

              <template v-if="editForm.type==='text'">
                <input class="form-control mb-2" v-model.trim="editForm.title" placeholder="Заголовок"/>
                <textarea class="form-control" rows="6" v-model.trim="editForm.text" placeholder="Текст"></textarea>
              </template>

              <template v-else-if="editForm.type==='image_right'">
                <div class="row g-3">
                  <div class="col-md-6">
                    <input class="form-control mb-2" v-model.trim="editForm.title" placeholder="Заголовок"/>
                    <textarea class="form-control" rows="6" v-model.trim="editForm.text" placeholder="Текст"></textarea>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Зображення</label>
                    <input type="file" class="form-control" accept="image/*" @change="onEditImage($event,'single')"/>
                    <div v-if="editForm.imageUrl" class="mt-2 preview"><img :src="editForm.imageUrl" class="img-fluid rounded"/></div>
                  </div>
                </div>
              </template>

              <template v-else-if="editForm.type==='image_left'">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Зображення</label>
                    <input type="file" class="form-control" accept="image/*" @change="onEditImage($event,'single')"/>
                    <div v-if="editForm.imageUrl" class="mt-2 preview"><img :src="editForm.imageUrl" class="img-fluid rounded"/></div>
                  </div>
                  <div class="col-md-6">
                    <input class="form-control mb-2" v-model.trim="editForm.title" placeholder="Заголовок"/>
                    <textarea class="form-control" rows="6" v-model.trim="editForm.text" placeholder="Текст"></textarea>
                  </div>
                </div>
              </template>

              <template v-else-if="editForm.type==='two_images'">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Картинка 1</label>
                    <input type="file" class="form-control" accept="image/*" @change="onEditImage($event,1)"/>
                    <div v-if="editForm.imageUrl1" class="mt-2 preview small"><img :src="editForm.imageUrl1" class="img-fluid rounded"/></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Картинка 2</label>
                    <input type="file" class="form-control" accept="image/*" @change="onEditImage($event,2)"/>
                    <div v-if="editForm.imageUrl2" class="mt-2 preview small"><img :src="editForm.imageUrl2" class="img-fluid rounded"/></div>
                  </div>
                </div>
              </template>

              <div class="d-grid gap-2 mt-3">
                <button class="btn btn-giant btn-approve" type="submit" :disabled="uploading">✓ Зберегти зміни</button>
                <button class="btn btn-giant btn-ghost" type="button" @click="cancelEdit" :disabled="uploading">Скасувати</button>
              </div>
            </form>
          </div>
        </template>
      </draggable>
    </div>

    <div v-else class="text-muted">Опис для цієї мови ще не доданий.</div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import draggable from 'vuedraggable'
import axios from 'axios'

const props = defineProps({
  modelValue: { type: Object, default: () => ({ uk: [], ru: [] }) },
  productId: { type: [String, Number], required: true },
  uploadUrl: { type: String, default: '/api/upload-image' }
})
const emit = defineEmits(['update:modelValue'])

const lang = ref('uk')
const blocks = reactive({ uk: [], ru: [] })
const addMode = ref(false)
const uploading = ref(false)

const newBlock = reactive({ type:'', title:'', text:'', imageUrl:'', imageUrl1:'', imageUrl2:'' })
const editIdx = ref(null)
const editForm = reactive({ type:'', title:'', text:'', imageUrl:'', imageUrl1:'', imageUrl2:'' })

// sync in
watch(() => props.modelValue, (v) => {
  const clone = a => (Array.isArray(a) ? JSON.parse(JSON.stringify(a)) : [])
  blocks.uk = clone(v?.uk || [])
  blocks.ru = clone(v?.ru || [])
}, { deep:true, immediate:true })

// sync out
function emitOut(){ emit('update:modelValue', { uk: blocks.uk, ru: blocks.ru }) }

function switchLang(l){ cancelEdit(); cancelAdd(); lang.value = l }

function startAdd(){ addMode.value = true; Object.assign(newBlock,{ type:'', title:'', text:'', imageUrl:'', imageUrl1:'', imageUrl2:'' }) }
function cancelAdd(){ addMode.value = false; Object.assign(newBlock,{ type:'', title:'', text:'', imageUrl:'', imageUrl1:'', imageUrl2:'' }) }
function saveNew(){
  if (!newBlock.type) return
  blocks[lang.value].push({ ...newBlock, _key: `${Date.now()}-${Math.random().toString(36).slice(2)}` })
  addMode.value = false; emitOut()
}

function startEdit(i){ editIdx.value = i; Object.assign(editForm, JSON.parse(JSON.stringify(blocks[lang.value][i]))) }
function cancelEdit(){ editIdx.value = null; Object.assign(editForm,{ type:'', title:'', text:'', imageUrl:'', imageUrl1:'', imageUrl2:'' }) }
function saveEdit(i){
  if (editIdx.value === null) return
  const keepType = blocks[lang.value][i].type
  blocks[lang.value][i] = { ...editForm, type: keepType, _key: blocks[lang.value][i]._key }
  emitOut(); cancelEdit()
}
function removeBlock(i){
  if (!confirm('Видалити блок?')) return
  blocks[lang.value].splice(i,1); emitOut()
  if (editIdx.value === i) cancelEdit()
}
function duplicate(i){
  const b = blocks[lang.value][i]
  blocks[lang.value].splice(i+1,0,{ ...b, _key: `${Date.now()}-${Math.random().toString(36).slice(2)}` })
  emitOut()
}

async function uploadImage(file){
  const fd = new FormData()
  fd.append('image', file)
  fd.append('product_id', String(props.productId))
  uploading.value = true
  try{
    const res = await axios.post(props.uploadUrl, fd, { headers:{'Content-Type':'multipart/form-data'} })
    return res.data.url
  }catch(e){
    alert('Помилка завантаження зображення')
    return ''
  }finally{
    uploading.value = false
  }
}

async function onAddImage(e, pos){
  const file = e.target.files?.[0]; if(!file) return
  const url = await uploadImage(file); if(!url) return
  if (pos==='single') newBlock.imageUrl = url
  else if (pos===1) newBlock.imageUrl1 = url
  else if (pos===2) newBlock.imageUrl2 = url
}
async function onEditImage(e, pos){
  const file = e.target.files?.[0]; if(!file) return
  const url = await uploadImage(file); if(!url) return
  if (pos==='single') editForm.imageUrl = url
  else if (pos===1) editForm.imageUrl1 = url
  else if (pos===2) editForm.imageUrl2 = url
}

function typeLabel(t){
  return ({
    text: 'Текст',
    image_right: 'Картинка справа',
    image_left: 'Картинка зліва',
    two_images: 'Дві картинки'
  })[t] || t
}
</script>

<style scoped>
.desc-simple{ border:1px solid #eef1f5; border-radius:1rem; }

/* purple CTA */
.btn-accent{ background:#7b70f2; color:#fff; border:none; }
.btn-accent:hover{ filter:brightness(.96); }

/* cards */
.desc-card{ border:1px solid #e7e8ee; background:#fff; }
.card-head{
  display:flex; align-items:center; gap:.6rem;
  padding:.55rem .75rem; background:#f6f7fb;
  border-bottom:1px solid #eceff5; border-top-left-radius:.75rem; border-top-right-radius:.75rem;
}
.card-body{ padding:.9rem; }
.drag{ cursor:grab; user-select:none; opacity:.75; display:inline-flex; }
.icon-btn{
  width:32px; height:32px; border-radius:50%; background:#f3f4f7; border:none;
  display:inline-flex; align-items:center; justify-content:center;
  transition: background .15s ease, transform .05s;
}
.icon-btn:hover{ background:#e9ebf3; }
.icon-btn:active{ transform: translateY(1px); }
.type-badge{ background:#eef2ff; color:#4f46e5; }

.desc-title{ font-weight:700; font-size:1.15rem; color:#2a2f3a; margin-bottom:.25rem; }
.desc-text{ font-size:1.05rem; color:#374151; line-height:1.45; }
.preview{ display:flex; justify-content:center; }
.preview img{ max-height:260px; object-fit:contain; }
.preview.small img{ max-height:180px; }

/* large edit CTAs */
.btn-giant{ height:52px; font-weight:700; border-radius:14px; }
.btn-approve{ background:#22c55e; color:#fff; border:none; }
.btn-ghost{ background:#edeff3; color:#6b7280; border:1px solid #cfd3dc; }

/* compact tabs */
.small .nav-link{ padding:.4rem .75rem; }
</style>
