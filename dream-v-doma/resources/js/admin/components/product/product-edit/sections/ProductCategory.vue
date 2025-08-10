<template>
  <div class="card mb-4 p-4">
    <label class="form-label">Категорія</label>
    <Multiselect
      v-model="localSelected"
      :options="categories"
      :multiple="true"
      label="name"
      track-by="id"
      placeholder="Виберіть категорії"
      :class="{ 'is-invalid': hasError }"
    />
    <div v-if="hasError" class="invalid-feedback" style="display:block">
      {{ errorText }}
    </div>
    <div v-if="localSelected.length" class="mt-2">
      <span
        v-for="cat in localSelected"
        :key="cat.id"
        class="badge bg-primary me-1"
      >
        {{ cat.name }}
      </span>
    </div>
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

export default {
  name: 'ProductCategory',
  components: { Multiselect },
  props: {
    modelValue: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) }
  },
  data() {
    return {
      localSelected: []
    }
  },
  computed: {
    hasError() {
      return !!this.errors.categories
    },
    errorText() {
      return this.errors.categories
    }
  },
  watch: {
    modelValue: {
      immediate: true,
      handler(newVal) {
        const currentIds = this.localSelected.map(cat => cat.id).sort().join(',')
        const newIds = [...new Set(newVal)].sort().join(',')
        if (currentIds !== newIds) {
          this.syncLocalSelected()
        }
      }
    },
    localSelected: {
      deep: true,
      handler(val) {
        const uniqueIds = [...new Set(val.map(cat => cat.id))]
        const modelIds = this.modelValue.slice().sort().join(',')
        const newIds = uniqueIds.slice().sort().join(',')
        if (modelIds !== newIds) {
          this.$emit('update:modelValue', uniqueIds)
        }
      }
    },
    categories: {
      immediate: true,
      handler() {
        this.syncLocalSelected()
      }
    }
  },
  methods: {
    syncLocalSelected() {
      if (Array.isArray(this.categories) && this.categories.length) {
        const uniqueModelValue = [...new Set(this.modelValue)]
        this.localSelected = this.categories.filter(cat =>
          uniqueModelValue.includes(cat.id)
        )
      } else {
        this.localSelected = []
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
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.1);
}
</style>
