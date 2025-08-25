<template>
    <div class="card mb-4 p-4">
      <h6 class="fw-bold mb-3">Опис категорії</h6>
      <!-- Таби для мов -->
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
          <button class="nav-link" :class="{ active: lang === 'uk' }" @click="switchLang('uk')" type="button">Українська</button>
        </li>
        <li class="nav-item">
          <button class="nav-link" :class="{ active: lang === 'ru' }" @click="switchLang('ru')" type="button">Російська</button>
        </li>
      </ul>
  
      <!-- Відображення блоків -->
      <div v-if="blocks[lang] && blocks[lang].length">
        <div v-for="(block, idx) in blocks[lang]" :key="block._key" class="desc-block mb-4 position-relative">
          <div class="desc-action-bar">
            <template v-if="editIdx !== idx">
              <button class="btn btn-outline-secondary btn-sm me-2" @click="editBlock(idx)">
                <i class="bi bi-pencil"></i> Редагувати
              </button>
              <button class="btn btn-outline-danger btn-sm" @click="deleteBlock(idx)">
                <i class="bi bi-trash"></i>
              </button>
            </template>
          </div>
  
          <!-- Інлайн-форма редагування -->
          <template v-if="editIdx === idx">
            <form @submit.prevent="saveEditBlock(editIdx)">
              <template v-if="editForm.type === 'text'">
                <div class="mb-3">
                  <label class="form-label">Заголовок</label>
                  <input class="form-control" v-model="editForm.title" />
                </div>
                <div class="mb-3">
                  <label class="form-label">Текст</label>
                  <textarea class="form-control" rows="3" v-model="editForm.text"></textarea>
                </div>
              </template>
  
              <template v-else-if="editForm.type === 'image_right'">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Заголовок</label>
                    <input class="form-control" v-model="editForm.title" />
                    <label class="form-label mt-3">Текст</label>
                    <textarea class="form-control" rows="3" v-model="editForm.text"></textarea>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Картинка справа</label>
                    <input
                      type="file"
                      class="form-control"
                      accept="image/*"
                      @change="onImageChange($event, 'image_right')"
                    />
                    <div v-if="editForm.imageUrl" class="mt-2">
                      <img :src="editForm.imageUrl" style="width: 100%; max-width: 300px" />
                    </div>
                  </div>
                </div>
              </template>
  
              <template v-else-if="editForm.type === 'image_left'">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Картинка зліва</label>
                    <input
                      type="file"
                      class="form-control"
                      accept="image/*"
                      @change="onImageChange($event, 'image_left')"
                    />
                    <div v-if="editForm.imageUrl" class="mt-2">
                      <img :src="editForm.imageUrl" style="width: 100%; max-width: 300px" />
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Заголовок</label>
                    <input class="form-control" v-model="editForm.title" />
                    <label class="form-label mt-3">Текст</label>
                    <textarea class="form-control" rows="3" v-model="editForm.text"></textarea>
                  </div>
                </div>
              </template>
  
              <template v-else-if="editForm.type === 'two_images'">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Картинка 1</label>
                    <input
                      type="file"
                      class="form-control"
                      accept="image/*"
                      @change="onImageChange($event, 'two_images', 1)"
                    />
                    <div v-if="editForm.imageUrl1" class="mt-2">
                      <img :src="editForm.imageUrl1" style="width: 100%; max-width: 300px" />
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Картинка 2</label>
                    <input
                      type="file"
                      class="form-control"
                      accept="image/*"
                      @change="onImageChange($event, 'two_images', 2)"
                    />
                    <div v-if="editForm.imageUrl2" class="mt-2">
                      <img :src="editForm.imageUrl2" style="width: 100%; max-width: 300px" />
                    </div>
                  </div>
                </div>
              </template>
  
              <button class="btn btn-success me-2" type="submit">Оновити</button>
              <button class="btn btn-outline-secondary" @click.prevent="cancelEdit">Відміна</button>
            </form>
          </template>
  
          <!-- Відображення блока, якщо не редагується -->
          <template v-else>
            <!-- Текстовий блок -->
            <div v-if="block.type === 'text'" class="mb-4">
              <div class="fw-bold mb-2" style="font-size:1.15rem;">{{ block.title }}</div>
              <div style="font-size:1.05rem;">{{ block.text }}</div>
            </div>
  
            <!-- Картинка справа -->
            <div v-else-if="block.type === 'image_right'" class="row align-items-center mb-4">
              <div class="col-md-6 mb-3 mb-md-0">
                <div class="fw-bold mb-2" style="font-size:1.15rem;">{{ block.title }}</div>
                <div style="font-size:1.05rem;">{{ block.text }}</div>
              </div>
              <div class="col-md-6 d-flex justify-content-center">
                <img :src="block.imageUrl" alt="" class="img-fluid rounded " style="max-height:520px;object-fit:contain;">
              </div>
            </div>
  
            <!-- Картинка зліва -->
            <div v-else-if="block.type === 'image_left'" class="row align-items-center mb-4">
              <div class="col-md-6 d-flex justify-content-center mb-3 mb-md-0">
                <img :src="block.imageUrl" alt="" class="img-fluid rounded " style="max-height:520px;object-fit:contain;">
              </div>
              <div class="col-md-6">
                <div class="fw-bold mb-2" style="font-size:1.15rem;">{{ block.title }}</div>
                <div style="font-size:1.05rem;">{{ block.text }}</div>
              </div>
            </div>
  
            <!-- Дві картинки -->
            <div v-else-if="block.type === 'two_images'" class="row align-items-center mb-4">
              <div class="col-md-6 d-flex justify-content-center mb-3 mb-md-0">
                <img :src="block.imageUrl1" alt="" class="img-fluid rounded " style="max-height:520px;object-fit:contain;">
              </div>
              <div class="col-md-6 d-flex justify-content-center">
                <img :src="block.imageUrl2" alt="" class="img-fluid rounded " style="max-height:520px;object-fit:contain;">
              </div>
            </div>
          </template>
  
        </div>
      </div>
      <div v-else class="text-muted">
        Опис для цієї мови ще не доданий.
      </div>
  
      <!-- Додавання нового блоку -->
      <div class="mt-4">
        <button
          v-if="!addMode"
          class="btn btn-primary"
          @click="startAddBlock"
          type="button"
        >
          + Додати блок
        </button>
        <form v-if="addMode" @submit.prevent="saveNewBlock" class="card p-3 mt-2">
          <div class="row g-3 align-items-stretch">
            <!-- Тип блоку -->
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
  
            <!-- Для Тексту -->
            <template v-if="newBlock.type === 'text'">
              <div class="col-12">
                <input class="form-control mb-2" v-model="newBlock.title" placeholder="Заголовок" />
              </div>
              <div class="col-12">
                <textarea
                  class="form-control"
                  v-model="newBlock.text"
                  placeholder="Текст"
                  rows="10"
                  style="min-height:150px; border-radius:18px; font-size:1.15rem; padding:18px;"
                ></textarea>
              </div>
            </template>
  
            <!-- Картинка справа -->
            <template v-else-if="newBlock.type === 'image_right'">
              <div class="col-md-6 col-12">
                <input class="form-control mb-2" v-model="newBlock.title" placeholder="Заголовок" />
                <textarea
                  class="form-control"
                  v-model="newBlock.text"
                  placeholder="Текст"
                  rows="10"
                  style="min-height:130px; border-radius:18px; font-size:1.15rem; padding:18px;"
                ></textarea>
              </div>
              <div class="col-md-6 col-12 d-flex flex-column align-items-center">
                <input type="file" class="form-control mb-2" accept="image/*" @change="onAddImage($event, 'single')" />
                <div v-if="newBlock.imageUrl" class="mt-2 w-100 d-flex justify-content-center">
                  <img :src="newBlock.imageUrl" style="max-width:250px; border-radius:10px;">
                </div>
              </div>
            </template>
  
            <!-- Картинка зліва -->
            <template v-else-if="newBlock.type === 'image_left'">
              <div class="col-md-6 col-12 d-flex flex-column align-items-center">
                <input type="file" class="form-control mb-2" accept="image/*" @change="onAddImage($event, 'single')" />
                <div v-if="newBlock.imageUrl" class="mt-2 w-100 d-flex justify-content-center">
                  <img :src="newBlock.imageUrl" style="max-width:250px; border-radius:10px;">
                </div>
              </div>
              <div class="col-md-6 col-12">
                <input class="form-control mb-2" v-model="newBlock.title" placeholder="Заголовок" />
                <textarea
                  class="form-control"
                  v-model="newBlock.text"
                  placeholder="Текст"
                  rows="10"
                  style="min-height:130px; border-radius:18px; font-size:1.15rem; padding:18px;"
                ></textarea>
              </div>
            </template>
  
            <!-- Дві картинки -->
            <template v-else-if="newBlock.type === 'two_images'">
              <div class="col-md-6 col-12 d-flex flex-column align-items-center">
                <input type="file" class="form-control mb-2" accept="image/*" @change="onAddImage($event, 1)" />
                <div v-if="newBlock.imageUrl1" class="mt-2 w-100 d-flex justify-content-center">
                  <img :src="newBlock.imageUrl1" style="max-width:130px; border-radius:10px;">
                </div>
              </div>
              <div class="col-md-6 col-12 d-flex flex-column align-items-center">
                <input type="file" class="form-control mb-2" accept="image/*" @change="onAddImage($event, 2)" />
                <div v-if="newBlock.imageUrl2" class="mt-2 w-100 d-flex justify-content-center">
                  <img :src="newBlock.imageUrl2" style="max-width:130px; border-radius:10px;">
                </div>
              </div>
            </template>
  
            <!-- Кнопки -->
            <div class="col-12 d-flex gap-2 mt-3">
              <button class="btn btn-success" type="submit">Зберегти</button>
              <button class="btn btn-outline-secondary" @click.prevent="cancelAddBlock">Відміна</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios'
  export default {
    name: 'CategoryDescription',
    props: {
      modelValue: {
        type: Object,
        default: () => ({ uk: [], ru: [] })
      },
      categoryId: {
        type: [String, Number],
        required: true
      }
    },
    data() {
      return {
        lang: 'uk',
        blocks: { uk: [], ru: [] },
        editIdx: null,
        editForm: { title: '', text: '', imageUrl: '', imageUrl1: '', imageUrl2: '', type: '' },
        addMode: false,
        newBlock: { type: '', title: '', text: '', imageUrl: '', imageUrl1: '', imageUrl2: '' },
        uploading: false
      }
    },
    watch: {
      modelValue: {
        immediate: true,
        deep: true,
        handler(newVal) {
          this.blocks = {
            uk: Array.isArray(newVal.uk) ? JSON.parse(JSON.stringify(newVal.uk)) : [],
            ru: Array.isArray(newVal.ru) ? JSON.parse(JSON.stringify(newVal.ru)) : []
          }
        }
      }
    },
    methods: {
      switchLang(newLang) {
        this.lang = newLang
        this.cancelEdit()
        this.cancelAddBlock()
      },
      editBlock(idx) {
        this.editIdx = idx
        this.editForm = { ...this.blocks[this.lang][idx] }
      },
      cancelEdit() {
        this.editIdx = null
        this.editForm = { title: '', text: '', imageUrl: '', imageUrl1: '', imageUrl2: '', type: '' }
      },
      async saveEditBlock(idx) {
        if (this.editIdx !== null) {
          this.blocks[this.lang][this.editIdx] = {
            ...this.editForm,
            _key: this.blocks[this.lang][this.editIdx]._key,
            type: this.blocks[this.lang][this.editIdx].type
          }
          this.cancelEdit()
          this.$emit('update:modelValue', this.blocks)
        }
      },
      deleteBlock(idx) {
        this.blocks[this.lang].splice(idx, 1)
        this.$emit('update:modelValue', this.blocks)
        if (this.editIdx === idx) this.cancelEdit()
      },
      async uploadImage(file) {
        const formData = new FormData()
        formData.append('image', file)
        formData.append('category_id', this.categoryId)
        this.uploading = true
        try {
          const resp = await axios.post('/api/upload-image-category', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
          this.uploading = false
          return resp.data.url
        } catch (e) {
          this.uploading = false
          alert('Помилка завантаження зображення!')
          return ''
        }
      },
      async onImageChange(e, type, idx = null) {
        const file = e.target.files[0]
        if (!file) return
  
        const url = await this.uploadImage(file)
        if (!url) return
  
        if (type === 'image_right' || type === 'image_left') {
          this.editForm.imageUrl = url
        } else if (type === 'two_images' && idx === 1) {
          this.editForm.imageUrl1 = url
        } else if (type === 'two_images' && idx === 2) {
          this.editForm.imageUrl2 = url
        }
      },
      startAddBlock() {
        this.addMode = true
        this.newBlock = { type: '', title: '', text: '', imageUrl: '', imageUrl1: '', imageUrl2: '' }
      },
      cancelAddBlock() {
        this.addMode = false
        this.newBlock = { type: '', title: '', text: '', imageUrl: '', imageUrl1: '', imageUrl2: '' }
      },
      async saveNewBlock() {
        if (!this.newBlock.type) return
        const key = Date.now() + Math.floor(Math.random() * 1000)
        const block = { ...this.newBlock, _key: key }
        this.blocks[this.lang].push(block)
        this.addMode = false
        this.$emit('update:modelValue', this.blocks)
        this.cancelAddBlock()
      },
      async onAddImage(e, pos) {
        const file = e.target.files[0]
        if (!file) return
  
        const url = await this.uploadImage(file)
        if (!url) return
  
        if (pos === 'single') {
          this.newBlock.imageUrl = url
        } else if (pos === 1) {
          this.newBlock.imageUrl1 = url
        } else if (pos === 2) {
          this.newBlock.imageUrl2 = url
        }
      }
    }
  }
  </script>
  
  
  <style scoped>
  .desc-block {
    position: relative;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 2px 16px rgba(80,90,125,.10);
    padding: 54px 36px 32px 36px; /* додано місце під action bar */
    border: 1.5px solid #e7eaf1;
    margin-bottom: 24px;
  }
  
  .desc-action-bar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 44px;
    background: #f3f4f8;
    border-top-left-radius: 18px;
    border-top-right-radius: 18px;
    border-bottom: 1.5px solid #e1e4ea;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 20px 0 0;
    z-index: 10;
    gap: 10px;
    box-sizing: border-box;
  }
  
  .btn-sm {
    padding: 5px 15px 5px 13px;
    font-size: 0.96em;
  }
  
  .desc-title {
    font-size: 1.17em;
    color: #263238;
    font-weight: 600;
  }
  .desc-content {
    font-size: 1.13em;
    color: #191919;
    line-height: 1.44;
  }
  .desc-img-row {
    display: flex;
    align-items: center;
    gap: 34px;
    flex-wrap: wrap;
  }
  .desc-img-col {
    flex: 1 1 0;
    min-width: 240px;
  }
  .desc-img-col-img {
    max-width: 340px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .desc-img {
    display: block;
    width: 320px;
    max-width: 100%;
    height: auto;
    object-fit: cover;
    border-radius: 15px;
    border: 1.5px solid #e7eaf1;
    background: #fafbfc;
    box-shadow: 0 2px 10px rgba(80,90,125,.09);
  }
  .desc-two-images {
    display: flex;
    gap: 24px;
    align-items: center;
    justify-content: center;
  }
  @media (max-width: 900px) {
    .desc-img-row, .desc-two-images {
      flex-direction: column;
      gap: 16px;
    }
    .desc-block {
      padding: 50px 10px 18px 10px;
    }
    .desc-action-bar {
      padding-right: 8px;
    }
  }
  
  
  </style>