<template>
  <div class="card mb-4 p-4">
    <label class="form-label">Категорія</label>
    <Multiselect
      v-model="localSelected"
      :options="options"
      :multiple="true"
      placeholder="Виберіть категорії"
      label="name"
      track-by="id"
      class="mb-2"
      :class="{'is-invalid': hasError}"
    />
    <div v-if="hasError" class="invalid-feedback" style="display:block">
      {{ errorText }}
    </div>
    <div v-if="localSelected.length" class="mt-2">
      <span v-for="cat in localSelected" :key="cat.id" class="badge bg-primary me-1">
        {{ cat.name }}
      </span>
    </div>
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import axios from 'axios'

export default {
  name: 'ProductCategory',
  components: { Multiselect },
  props: {
    modelValue: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) }
  },
  data() {
    return {
      options: [],
      localSelected: []
    }
  },
  computed: {
    hasError() {
      return !!this.errors?.categories
    },
    errorText() {
      return this.errors?.categories || ''
    }
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(val) {
        if (JSON.stringify(val) !== JSON.stringify(this.localSelected)) {
          this.localSelected = Array.isArray(val) ? [...val] : []
        }
      }
    },
    localSelected(val) {
      if (JSON.stringify(val) !== JSON.stringify(this.modelValue)) {
        this.$emit('update:modelValue', val)
      }
    }
  },
  mounted() {
    this.fetchCategories()
  },
  methods: {
    async fetchCategories() {
      try {
        const res = await axios.get(`/api/category-select/uk`)
        this.options = res.data
      } catch (e) {
        console.error('Не вдалося отримати категорії', e)
      }
    }
  }
}
</script>

<style scoped>
.badge.bg-primary {
  font-size: 1em;
  padding: 6px 16px;
  border-radius: 8px;
  background: #2563eb;
  color: #fff;
}
:deep(.is-invalid .multiselect__tags),
:deep(.multiselect.is-invalid .multiselect__tags) {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.2rem rgba(220,53,69,.1);
}
</style>
