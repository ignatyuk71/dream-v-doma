<template>
  <div class="card mb-4 p-4">
    <div class="mb-3">
      <h6>Розмірна сітка</h6>
      <select
        v-model="internalSelectedId"
        class="form-control"
        :class="{'is-invalid': errors?.size_guide_id}"
      >
        <option value="">Оберіть розмірну сітку</option>
        <option v-for="guide in sizeGuides" :value="guide.id" :key="guide.id">
          {{ guide.name }}
        </option>
      </select>
      <div v-if="errors?.size_guide_id" class="invalid-feedback" style="display:block">
        {{ errors.size_guide_id }}
      </div>
    </div>

    <div class="mt-3" v-if="selectedGuide">
      <label class="form-label">Розмірна сітка: {{ selectedGuide.name }}</label>
      <div v-if="ukTableRows && ukTableRows.length">
        <table class="table table-sm mt-2">
          <thead>
            <tr>
              <th v-for="col in ukTableColumns" :key="col">{{ col }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in ukTableRows" :key="idx">
              <td v-for="col in ukTableColumns" :key="col">{{ row[col] || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else>
        <span class="text-muted">Немає даних для відображення</span>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ProductSizeGuide',
  props: {
    modelValue: [String, Number],
    errors: { type: Object, default: () => ({}) }
  },
  data() {
    return {
      sizeGuides: [],
      internalSelectedId: this.modelValue || ''
    }
  },
  computed: {
    selectedGuide() {
      return this.sizeGuides.find(g => String(g.id) === String(this.internalSelectedId));
    },
    ukTableRows() {
      const guide = this.selectedGuide;
      if (!guide || !guide.sizes || !guide.sizes.uk) return [];
      if (!Array.isArray(guide.sizes.uk)) {
        // Формат: {"36-37": "23.5 см", ...}
        return Object.entries(guide.sizes.uk).map(([size, length]) => ({
          "Розмір": size,
          "Довжина": length
        }));
      }
      // Формат: масив об'єктів (одяг тощо)
      return guide.sizes.uk;
    },
    ukTableColumns() {
      if (!this.ukTableRows.length) return [];
      return Object.keys(this.ukTableRows[0]);
    }
  },
  watch: {
    modelValue(val) {
      this.internalSelectedId = val;
    },
    internalSelectedId(val) {
      this.$emit('update:modelValue', val);
    }
  },
  created() {
    axios.get('/admin/size-guides')
      .then(res => {
        this.sizeGuides = res.data;
      })
      .catch(err => {
        console.error('Error fetching size guides:', err);
      });
  }
}
</script>
