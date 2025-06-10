<template>
    <div class="container-xl py-5">
      <h2 class="mb-4 fw-bold d-flex align-items-center gap-2">
        <i class="bi bi-bag-plus"></i> Створення продукту
      </h2>
  
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link" :class="{ active: activeTab === 'main' }" @click="activeTab = 'main'">Основне</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" :class="{ active: activeTab === 'translations' }" @click="activeTab = 'translations'">Переклади</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" :class="{ active: activeTab === 'description' }" @click="activeTab = 'description'">Опис</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" :class="{ active: activeTab === 'variants' }" @click="activeTab = 'variants'">Варіації</button>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <form @submit.prevent="submitForm">
            <div v-show="activeTab === 'main'">
              <div class="card mb-4 shadow-sm p-4">
                <h5 class="mb-3 text-primary fw-semibold">Основна інформація</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Назва товару 🇺🇦</label>
                        <input v-model="getTranslation('ua').name" type="text" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Назва товару 🇷🇺</label>
                        <input v-model="getTranslation('ru').name" type="text" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (URL) 🇺🇦</label>
                        <input v-model="getTranslation('ua').slug" type="text" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slug (URL) 🇷🇺</label>
                        <input v-model="getTranslation('ru').slug" type="text" class="form-control" />
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">SKU (артикул)</label>
                        <input v-model="form.sku" type="text" class="form-control" required />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ціна</label>
                        <input v-model="form.price" type="number" class="form-control" required />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Статус</label>
                        <select v-model="form.status" class="form-select">
                        <option :value="1">Активний</option>
                        <option :value="0">Неактивний</option>
                        </select>
                    </div>
                    </div>


              </div>
  
              <div class="card mb-4 shadow-sm p-4">
                <h5 class="mb-3 text-primary fw-semibold">Категорії</h5>
                <multiselect
                  v-model="form.category_ids"
                  :options="categories"
                  :multiple="true"
                  :close-on-select="false"
                  :clear-on-select="false"
                  :preserve-search="true"
                  placeholder="Оберіть категорії"
                  label="name"
                  track-by="id"
                  class="form-control"
                />
              </div>
  
              <div class="card mb-4 shadow-sm p-4">
                <h5 class="mb-3 text-primary fw-semibold">Зображення товару</h5>
                <input type="file" multiple class="form-control mb-3" @change="handleImageUpload" />
                <div class="row g-3">
                  <draggable v-model="form.images" item-key="preview" class="row g-3">
                    <template #item="{ element, index }">
                      <div class="col-md-6">
                        <div class="card h-100 shadow-sm border rounded">
                          <div class="d-flex align-items-center p-3" style="gap: 1rem">
                            <img :src="element.preview" style="width: 100px; height: 100px; object-fit: cover" class="rounded" />
                            <div class="flex-grow-1">
                              <input v-model="element.title" class="form-control mb-2" placeholder="Title зображення" />
                              <input v-model="element.alt" class="form-control" placeholder="Alt зображення" />
                            </div>
                            <div class="text-end d-flex flex-column justify-content-between" style="min-width: 60px;">
                              <span v-if="index === 0" class="badge bg-success mb-2">Головне</span>
                              <button type="button" class="btn btn-sm btn-outline-danger" @click="removeImage(index)">✖</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </draggable>
                </div>
              </div>
            </div>
  
            <div v-show="activeTab === 'translations'">
              <div class="card mb-4 shadow-sm p-4">
                <h5 class="mb-3 text-primary fw-semibold">Переклади</h5>
                <div v-for="(t, index) in translations" :key="t.locale" class="mb-4 border rounded p-3 bg-light">
                  <h6 class="mb-3">🌐 {{ t.locale.toUpperCase() }}</h6>
                  <div class="mb-2">
                    <label class="form-label">Назва</label>
                    <input v-model="t.name" class="form-control" />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">SEO-заголовок</label>
                    <input v-model="t.meta_title" class="form-control" />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">SEO-опис</label>
                    <input v-model="t.meta_description" class="form-control" />
                  </div>
                </div>
              </div>
            </div>
  
            <div v-show="activeTab === 'description'">
              <div class="card mb-4 shadow-sm p-4">
                <h5 class="mb-3 text-primary fw-semibold">Опис товару</h5>
                <div class="row g-4">
                  <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">🇺🇦 Українською</label>
                    <ckeditor :editor="editor" v-model="getTranslation('ua').description" :config="editorConfig"></ckeditor>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-bold mb-2">🇷🇺 Російською</label>
                    <ckeditor :editor="editor" v-model="getTranslation('ru').description" :config="editorConfig"></ckeditor>
                  </div>
                </div>
              </div>
            </div>
  
            <div v-show="activeTab === 'variants'">
              <div class="card mb-4 shadow-sm p-4">
                <h5 class="mb-3 text-primary fw-semibold">Варіації товару</h5>
                <div v-for="(variant, index) in variants" :key="index" class="border rounded p-3 mb-3 bg-light">
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label class="form-label">Розмір</label>
                      <select v-model="variant.size" class="form-select">
                        <option disabled value="">Оберіть розмір</option>
                        <option v-for="size in sizes" :key="size" :value="size">{{ size }}</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Колір</label>
                      <select v-model="variant.color" class="form-select">
                        <option disabled value="">Оберіть колір</option>
                        <option v-for="color in colors" :key="color" :value="color">{{ color }}</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Ціна</label>
                      <input v-model="variant.price" class="form-control" type="number">
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Кількість</label>
                      <input v-model="variant.quantity" class="form-control" type="number">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                      <button class="btn custom-variant-btn-delete" type="button" @click="removeVariant(index)">  🗑️  Удалити  </button>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn custom-variant-btn" @click="addVariant">
                ➕ Додати варіацію
                </button>
              </div>
            </div>
  
            <div class="text-end mt-4">
                <button class="btn btn-success px-4 py-2" type="submit" :disabled="loading || isFormInvalid">
                    <i class="bi bi-save me-1"></i>
                    {{ loading ? 'Збереження...' : 'Зберегти продукт' }}
                </button>
                
            </div>
  
          </form>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  .ck-editor__editable_inline {
    min-height: 300px;
  }

  .custom-variant-btn-delete {
  background-color: #fbabab;
  color: #ffffff;
  border: 1px solid #f48a8a;
  padding: 8px 16px;
  font-weight: 500;
  border-radius: 6px;
  transition: 0.2s;
}
.custom-variant-btn-delete:hover {
  background-color: #fe6b6b;
}
  .custom-variant-btn {
  background-color: #eef6ff;
  color: #0d6efd;
  border: 1px solid #85ade9;
  padding: 8px 16px;
  font-weight: 500;
  border-radius: 6px;
  transition: 0.2s;
}
.custom-variant-btn:hover {
  background-color: #d0e7ff;
}
  </style>
  
  <script>
  import Multiselect from 'vue-multiselect'
  import 'vue-multiselect/dist/vue-multiselect.min.css'
  import draggable from 'vuedraggable'
  import { CKEditor } from '@ckeditor/ckeditor5-vue'
  import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
  import axios from 'axios'
  
  export default {
      components: {
          Multiselect,
          draggable,
          CKEditor
      },
      props: {
          categories: {
              type: Array,
              default: () => [],
          }
      },
      data() {
          return {
              activeTab: 'main',
              editor: ClassicEditor,
              editorConfig: {
                  toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote']
              },
              form: {
                  meta_description: '',
                  sku: '',
                  price: '',
                  status: 1,
                  category_ids: [],
                  images: [],
                  description: ''
              },
              translations: [
                  { locale: 'ua', name: '', meta_title: '', meta_description: '', slug: '', description: '' },
                  { locale: 'ru', name: '', meta_title: '', meta_description: '', slug: '', description: '' },
              ],
              variants: [
                  { size: '', color: '', price: '', quantity: '' }
              ],
              sizes: ['36/37', '38/39', '40/41', '42/43'],
              colors: ['Чорний', 'Білий', 'Сірий', 'Бежевий', 'Синій', 'Рожевий'],
              loading: false,
              success: false,
          }
      },
      computed: {
          isFormInvalid() {
              return (
                  !this.form.sku ||
                  !this.form.price ||
                  !this.translations.find(t => t.locale === 'ua')?.name
              );
          }
      },
      methods: {
          getTranslation(locale) {
              return this.translations.find(t => t.locale === locale) || {}
          },
          handleImageUpload(event) {
              const files = event.target.files
              for (let file of files) {
                  const reader = new FileReader()
                  reader.onload = (e) => {
                      this.form.images.push({
                          file: file,
                          preview: e.target.result,
                          title: '',
                          alt: ''
                      })
                  }
                  reader.readAsDataURL(file)
              }
          },
          removeImage(index) {
              this.form.images.splice(index, 1)
          },
          addVariant() {
              this.variants.push({
                  size: '',
                  color: '',
                  price: '',
                  quantity: ''
              });
          },
          removeVariant(index) {
              this.variants.splice(index, 1);
          },
          async submitForm() {
              this.loading = true
              this.success = false
  
              try {
                  const formData = new FormData()
                  formData.append('sku', this.form.sku)
                  formData.append('price', this.form.price)
                  formData.append('status', this.form.status)
  
                  this.form.category_ids.forEach(cat => {
                      formData.append('category_ids[]', cat.id)
                  })
  
                  this.form.images.forEach((img, index) => {
                    formData.append('images[]', img.file)
                    formData.append('positions[]', index)
                    formData.append('titles[]', img.title)
                    formData.append('alts[]', img.alt)
                    formData.append('is_main[]', index === 0 ? 1 : 0) // ✅ головне — перше
                })
  
                  this.translations.forEach((t, index) => {
                      for (const key in t) {
                          formData.append(`translations[${index}][${key}]`, t[key])
                      }
                  })
  
                  this.variants.forEach((v, index) => {
                      for (const key in v) {
                          formData.append(`variants[${index}][${key}]`, v[key])
                      }
                  })
  
                  const res = await axios.post('/admin/products', formData)
                  localStorage.setItem('toastMessage', JSON.stringify({
                  message: '✅ Товар успішно опубліковано!',
                  type: 'success' // або 'error', 'warning', 'info'
                  }))
                  window.location.href = '/admin/products'
                  this.success = true
  
              } catch (err) {
                localStorage.setItem('toastMessage', JSON.stringify({
                  message: '❌ Сталася помилка під час додавання продукту.',
                  type: 'error' // або 'error', 'warning', 'info'
                  }))
                  alert('🚫 Сталася помилка при збереженні продукту.')
              } finally {
                  this.loading = false
              }
          }
      }
  }
  </script>
  