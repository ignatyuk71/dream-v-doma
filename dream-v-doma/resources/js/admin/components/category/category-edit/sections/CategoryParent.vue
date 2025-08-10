<template>
    <div class="card mb-4 p-4">
      <h6 class="fw-bold mb-3">Ієрархія та статус</h6>
      <div class="mb-3">
        <label class="form-label">Батьківська категорія</label>
        <select class="form-select" v-model="parent_id">
          <option :value="null">— Коренева категорія —</option>
          <option
            v-for="cat in categories.filter(c => !currentId || c.id !== currentId)"
            :key="cat.id"
            :value="cat.id"
          >
            {{ getCategoryName(cat) }}
          </option>
        </select>
      </div>
      <div>
        <label class="form-label">Статус</label>
        <select class="form-select" v-model="status">
          <option :value="1">Опубліковано</option>
          <option :value="0">Неактивний</option>
        </select>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'CategoryParent',
    props: {
      form: { type: Object, required: true },
      categories: { type: Array, required: true },
      currentId: { type: [Number, String], default: null }
    },
    computed: {
      parent_id: {
        get() {
          return this.form.parent_id ?? null;
        },
        set(value) {
          this.$emit('update:form', { ...this.form, parent_id: value });
        }
      },
      status: {
        get() {
          // Враховуємо, що статус може бути 0 чи 1, тому явна перевірка
          return typeof this.form.status !== 'undefined' ? this.form.status : 1;
        },
        set(value) {
          this.$emit('update:form', { ...this.form, status: value });
        }
      }
    },
    methods: {
      getCategoryName(cat) {
        if (cat.translations?.length) {
          const tr = cat.translations.find(t => t.locale === 'uk');
          return tr?.name || cat.slug || `#${cat.id}`;
        }
        return cat.name || cat.slug || `#${cat.id}`;
      }
    }
  };
  </script>
  