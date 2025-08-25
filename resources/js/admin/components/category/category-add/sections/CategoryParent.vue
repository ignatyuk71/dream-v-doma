<template>
  <div class="card mb-4 p-4">
    <h6 class="fw-bold mb-3">Ієрархія та статус</h6>
    <div class="mb-3">
      <label class="form-label">Батьківська категорія</label>
      <select class="form-select" v-model="form.parent_id">
        <option :value="null">— Коренева категорія —</option>
        <option
          v-for="cat in categories"
          :key="cat.id"
          :value="cat.id"
        >
          {{ getCategoryName(cat) }}
        </option>
      </select>
    </div>
    <div class="d-flex align-items-center gap-3 mt-3">
      <label class="form-label mb-0">Статус</label>
      <div class="status-switch">
        <label class="switch mb-0">
          <input type="checkbox" v-model="form.status" :true-value="1" :false-value="0" />
          <span class="slider"></span>
        </label>
        <span class="status-label"
          :class="{
            'published': form.status == 1,
            'unpublished': form.status == 0
          }"
        >
          {{ form.status == 1 ? 'Опубліковано' : 'Неактивний' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CategoryParent',
  props: {
    form: { type: Object, required: true },
    categories: { type: Array, required: true },
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

<style scoped>
.status-switch {
  display: flex;
  align-items: center;
  gap: 15px;
  background: #e6faf0;
  border-radius: 14px;
  padding: 6px 18px;
}

.switch {
  position: relative;
  display: inline-block;
  width: 47px;
  height: 28px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  background-color: #27cb69;
  border-radius: 34px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  transition: .3s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: #fff;
  transition: .3s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #27cb69;
}

input:not(:checked) + .slider {
  background-color: #e4e5e7;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

input:not(:checked) + .slider:before {
  transform: translateX(0);
}

.status-label {
  font-size: 1.5rem;
  font-weight: 500;
  padding: 0 8px;
  border-radius: 10px;
  transition: 0.2s;
  background: #e6faf0;
  color: #27cb69;
  line-height: 1;
}

.status-label.unpublished {
  color: #fc3c3c;
  background: #fff3f3;
}
</style>
