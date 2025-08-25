<template>
  <div class="card mb-4 p-4">
    <h6 class="fw-bold mb-3">Опис товару</h6>
    <!-- Таби для мов -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <button class="nav-link"
          :class="{ active: lang === 'uk' }"
          @click="lang = 'uk'"
          type="button"
        >Українська</button>
      </li>
      <li class="nav-item">
        <button class="nav-link"
          :class="{ active: lang === 'ru' }"
          @click="lang = 'ru'"
          type="button"
        >Російська</button>
      </li>
    </ul>
    <!-- Таби для типів блоку -->
    <div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
      <button
        v-for="tab in getTabs"
        :key="tab.value"
        @click="activeTab = tab.value"
        class="btn d-flex align-items-center"
        :class="activeTab === tab.value ? 'btn-primary' : 'btn-outline-primary'"
        type="button"
      >
        {{ tab.label }}
        <span class="badge ms-2" :class="lang === 'uk' ? 'bg-primary' : 'bg-danger'">
          {{ lang.toUpperCase() }}
        </span>
      </button>
    </div>
    

      <!-- Текст -->
      <form @submit.prevent="addBlock" v-if="activeTab === 'text'">
        <div class="mb-3">
          <label class="form-label">{{ getLabel('title') }}</label>
          <input type="text" class="form-control" v-model="form.title">
        </div>
        <div class="mb-3">
          <label class="form-label">{{ getLabel('text') }}</label>
          <textarea class="form-control" rows="4" v-model="form.text"></textarea>
        </div>
        <button type="submit" class="btn btn-success">{{ getLabel('add') }}</button>
      </form>

      <!-- Картинка справа -->
      <form @submit.prevent="addBlock" v-if="activeTab === 'image_right'">
        <div class="row">
          <div class="col-md-7 mb-3">
            <label class="form-label">{{ getLabel('title') }}</label>
            <input type="text" class="form-control" v-model="form.title">
            <label class="form-label mt-3">{{ getLabel('text') }}</label>
            <textarea class="form-control" rows="4" v-model="form.text"></textarea>
          </div>
          <div class="col-md-5 mb-3 d-flex flex-column align-items-center">
            <label class="form-label">{{ getLabel('image_right') }}</label>
            <input type="file" class="form-control" accept="image/*" @change="onImageChange($event, 'image_right')">
            <div v-if="form.imageUrl" class="mt-3 w-100 d-flex justify-content-center">
              <img :src="form.imageUrl" alt="preview" style="width: 400px; object-fit: cover; border-radius:12px; border:1.5px solid #eee;">
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-success mt-2 w-100">{{ getLabel('add') }}</button>
      </form>

      <!-- Картинка зліва -->
      <form @submit.prevent="addBlock" v-if="activeTab === 'image_left'">
        <div class="row">
          <div class="col-md-5 mb-3 d-flex flex-column align-items-center">
            <label class="form-label">{{ getLabel('image_left') }}</label>
            <input type="file" class="form-control" accept="image/*" @change="onImageChange($event, 'image_left')">
            <div v-if="form.imageUrl" class="mt-3 w-100 d-flex justify-content-center">
              <img :src="form.imageUrl" alt="preview" style="width: 400px; object-fit: cover; border-radius:12px; border:1.5px solid #eee;">
            </div>
          </div>
          <div class="col-md-7 mb-3">
            <label class="form-label">{{ getLabel('title') }}</label>
            <input type="text" class="form-control" v-model="form.title">
            <label class="form-label mt-3">{{ getLabel('text') }}</label>
            <textarea class="form-control" rows="4" v-model="form.text"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-success mt-2 w-100">{{ getLabel('add') }}</button>
      </form>

      <!-- Дві картинки -->
      <form @submit.prevent="addBlock" v-if="activeTab === 'two_images'">
        <div class="row">
          <div class="col-md-6 mb-3 d-flex flex-column align-items-center">
            <label class="form-label">{{ getLabel('image1') }}</label>
            <input type="file" class="form-control" accept="image/*" @change="onImageChange($event, 'two_images', 1)">
            <div v-if="form.imageUrl1" class="mt-3 w-100 d-flex justify-content-center">
              <img :src="form.imageUrl1" alt="preview" style="width: 400px; object-fit: cover; border-radius:12px; border:1.5px solid #eee;">
            </div>
          </div>
          <div class="col-md-6 mb-3 d-flex flex-column align-items-center">
            <label class="form-label">{{ getLabel('image2') }}</label>
            <input type="file" class="form-control" accept="image/*" @change="onImageChange($event, 'two_images', 2)">
            <div v-if="form.imageUrl2" class="mt-3 w-100 d-flex justify-content-center">
              <img :src="form.imageUrl2" alt="preview" style="width: 400px; object-fit: cover; border-radius:12px; border:1.5px solid #eee;">
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-success mt-2 w-100">{{ getLabel('add') }}</button>
      </form>

      <!-- ДОДАНІ БЛОКИ -->
      <transition-group name="fade" tag="div" class="mt-4" ref="blockList">
        <div
          v-for="(block, idx) in descriptionBlocks[lang]"
          :key="block._key"
          class="card p-3 mb-2 description-preview-block"
          :ref="idx === descriptionBlocks[lang].length - 1 ? 'lastBlock' : null"
        >
    <!-- Текстовий блок -->
    <template v-if="block.type === 'text'">
      <div class="desc-text-only">
        <div class="desc-title">{{ block.title }}</div>
        <div class="desc-content">{{ block.text }}</div>
      </div>
    </template>
    <!-- Картинка справа -->
    <template v-if="block.type === 'image_right'">
      <div class="desc-two-cols">
        <div class="desc-cols-text text-start">
          <div class="desc-title">{{ block.title }}</div>
          <div class="desc-content">{{ block.text }}</div>
        </div>
        <div class="desc-cols-img-wrap">
          <img v-if="block.imageUrl" :src="block.imageUrl" alt="" class="desc-cols-img" />
        </div>
      </div>
    </template>
    <!-- Картинка зліва -->
    <template v-if="block.type === 'image_left'">
      <div class="desc-two-cols">
        <div class="desc-cols-img-wrap">
          <img v-if="block.imageUrl" :src="block.imageUrl" alt="" class="desc-cols-img" />
        </div>
        <div class="desc-cols-text text-start">
          <div class="desc-title">{{ block.title }}</div>
          <div class="desc-content">{{ block.text }}</div>
        </div>
      </div>
    </template>
    <!-- Дві картинки -->
    <template v-if="block.type === 'two_images'">
      <div class="desc-two-images">
        <div class="desc-cols-img-wrap">
          <img v-if="block.imageUrl1" :src="block.imageUrl1" alt="" class="desc-cols-img" />
        </div>
        <div class="desc-cols-img-wrap">
          <img v-if="block.imageUrl2" :src="block.imageUrl2" alt="" class="desc-cols-img" />
        </div>
      </div>
    </template>
    <!-- Кнопки редагування/видалення -->
    <div class="mt-3 text-end">
      <button class="btn btn-outline-secondary btn-sm me-1" @click="editBlock(idx)">
        Редагувати
      </button>
      <button class="btn btn-outline-danger btn-sm" @click="deleteBlock(idx)">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  </div>
</transition-group>

  </div>
</template>




<script>
export default {
  name: 'ProductDescription',
  props: {
    modelValue: {
      type: Object,
      default: () => ({ uk: [], ru: [] })
    }
  },
  emits: ['update:modelValue'],
  data() {
    return {
      lang: 'uk',
      activeTab: 'text',
      editIdx: null, // індекс блоку, який редагується
      form: {
        title: '',
        text: '',
        imageUrl: '',
        imageUrl1: '',
        imageUrl2: ''
      },
      tabs: {
        uk: [
          { value: 'text', label: 'Текст' },
          { value: 'image_right', label: 'Картинка справа' },
          { value: 'image_left', label: 'Картинка зліва' },
          { value: 'two_images', label: 'Дві картинки' },
        ],
        ru: [
          { value: 'text', label: 'Текст' },
          { value: 'image_right', label: 'Картинка справа' },
          { value: 'image_left', label: 'Картинка слева' },
          { value: 'two_images', label: 'Две картинки' },
        ]
      }
    };
  },
  computed: {
    getTabs() {
      return this.lang === 'uk' ? this.tabs.uk : this.tabs.ru;
    },
    descriptionBlocks: {
      get() {
        return this.modelValue;
      },
      set(val) {
        this.$emit('update:modelValue', val);
      }
    }
  },
  watch: {
    activeTab() {
      this.resetForm();
      this.editIdx = null;
    },
    lang() {
      this.resetForm();
      this.editIdx = null;
    }
  },
  methods: {
    onImageChange(e, block, pos = 1) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (evt) => {
        if (block === 'two_images') {
          if (pos === 1) this.form.imageUrl1 = evt.target.result;
          else this.form.imageUrl2 = evt.target.result;
        } else {
          this.form.imageUrl = evt.target.result;
        }
      };
      reader.readAsDataURL(file);
    },

    addBlock() {
      const newBlock = {
        type: this.activeTab,
        _key: this.editIdx !== null 
          ? this.descriptionBlocks[this.lang][this.editIdx]._key 
          : Date.now() + Math.random()
      };

      if (this.activeTab === 'text') {
        newBlock.title = this.form.title;
        newBlock.text = this.form.text;
      } else if (this.activeTab === 'image_right' || this.activeTab === 'image_left') {
        newBlock.title = this.form.title;
        newBlock.text = this.form.text;
        newBlock.imageUrl = this.form.imageUrl;
      } else if (this.activeTab === 'two_images') {
        newBlock.imageUrl1 = this.form.imageUrl1;
        newBlock.imageUrl2 = this.form.imageUrl2;
      }

      const updated = { ...this.descriptionBlocks };
      const arr = [...(updated[this.lang] || [])];

      if (this.editIdx !== null) {
        // Якщо редагування
        arr[this.editIdx] = newBlock;
      } else {
        // Додавання нового
        arr.push(newBlock);
      }

      updated[this.lang] = arr;
      this.descriptionBlocks = updated;
      this.resetForm();
      this.editIdx = null;

      this.$nextTick(() => {
    const lastBlock = this.$refs.lastBlock;
    if (lastBlock && lastBlock.scrollIntoView) {
      lastBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
    },

    resetForm() {
      this.form = {
        title: '',
        text: '',
        imageUrl: '',
        imageUrl1: '',
        imageUrl2: ''
      };
    },

    getLabel(key) {
      const labels = {
        uk: {
          title: 'Заголовок',
          text: 'Текст',
          image_right: 'Картинка справа',
          image_left: 'Картинка зліва',
          image1: 'Перша картинка',
          image2: 'Друга картинка',
          add: this.editIdx !== null ? 'Оновити' : 'Додати',
        },
        ru: {
          title: 'Заголовок',
          text: 'Текст',
          image_right: 'Картинка справа',
          image_left: 'Картинка слева',
          image1: 'Первая картинка',
          image2: 'Вторая картинка',
          add: this.editIdx !== null ? 'Обновить' : 'Добавить',
        }
      };
      return labels[this.lang][key];
    },

    getBlockTypeLabel(type) {
      const map = {
        text: { uk: 'Текст', ru: 'Текст' },
        image_right: { uk: 'Картинка справа', ru: 'Картинка справа' },
        image_left: { uk: 'Картинка зліва', ru: 'Картинка слева' },
        two_images: { uk: 'Дві картинки', ru: 'Две картинки' },
      };
      return map[type][this.lang];
    },

    deleteBlock(idx) {
      const updated = { ...this.descriptionBlocks };
      updated[this.lang] = [...(updated[this.lang] || [])];
      updated[this.lang].splice(idx, 1);
      this.descriptionBlocks = updated;
      this.resetForm();
      this.editIdx = null;
    },

    editBlock(idx) {
      const block = this.descriptionBlocks[this.lang][idx];
      this.activeTab = block.type;
      this.editIdx = idx;
      // Заповнюємо форму даними блоку
      this.form = {
        title: block.title || '',
        text: block.text || '',
        imageUrl: block.imageUrl || '',
        imageUrl1: block.imageUrl1 || '',
        imageUrl2: block.imageUrl2 || ''
      };
      // Прокрутка до верху форми (для зручності)
      this.$nextTick(() => {
        const el = this.$el.querySelector('form');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
    }
  }
};
</script>


<style scoped>
.badge {
  font-size: 0.72em;
  font-weight: 600;
  padding: 0.35em 0.6em;
  border-radius: 8px;
  letter-spacing: 1px;
}
.bg-primary {
  background: #2563eb;
  color: #fff;
}
.bg-danger {
  background: #e43f30;
  color: #fff;
}
.fade-enter-active,
.fade-leave-active {
  transition: all .4s cubic-bezier(.55,0,.1,1);
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(24px);
}

.variant-preview-card {
  background: #fafbfc;
  border-radius: 18px;
  box-shadow: 0 2px 14px rgba(56, 59, 66, 0.08);
  color: #757575;
  font-size: 1.14rem;
  padding-bottom: 12px;
}

.description-preview-block {
  max-width: 1200px;
  margin: 32px auto 0 auto;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 2px 14px rgba(56, 59, 66, 0.09);
  padding: 36px 40px;
  border: 1.5px solid #e7eaf1;
  position: relative;
  transition: border-color 0.25s;
}

.description-preview-block.active-block {
  border-color: #3475d1;
  box-shadow: 0 2px 24px rgba(52,117,209,0.06);
}

.description-preview-block .fw-bold,
.desc-title {
  font-size: 1.2em;
  color: #263238;
  font-weight: 600;
  margin-bottom: 12px;
}

.desc-content {
  font-size: 1.13rem;
  color: #191919;
  line-height: 1.45;
  font-weight: 400;
}

.desc-text-only {
  max-width: 740px;
  margin: 0 auto;
  text-align: left;
}

.desc-two-cols {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 44px;
}

.desc-cols-text {
  flex: 1 1 0;
  min-width: 300px;
  max-width: 540px;
}

.desc-cols-img-wrap {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 320px;
  max-width: 400px;
}

.desc-cols-img {
  display: block;
  width: 350px;
  max-width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 16px;
  background: #f8fafb;
  box-shadow: 0 2px 10px rgba(80,90,125,.09);
  border: 1.5px solid #e7eaf1;
}

.desc-two-images {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 38px;
}

.form-label {
  font-weight: 600;
  color: #4561a6;
}

.btn-success {
  font-weight: 600;
  letter-spacing: 1px;
}

.btn-outline-secondary.btn-sm, .btn-outline-danger.btn-sm {
  padding: 2px 12px 2px 10px;
  border-radius: 6px;
  font-size: 0.92em;
  margin-left: 2px;
}

@media (max-width: 900px) {
  .desc-two-cols, .desc-two-images {
    flex-direction: column;
    gap: 22px;
  }
  .desc-cols-img-wrap {
    min-width: 0;
    max-width: 100%;
  }
  .desc-cols-img {
    width: 100%;
    max-width: 320px;
  }
  .description-preview-block {
    padding: 22px 8px;
  }
}

</style>

