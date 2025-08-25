<template>
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h5 class="fw-bold mb-0">{{ title }}</h5>
    </div>
    <div class="top-actions">
      <button type="button" class="btn btn-light" @click="$emit('cancel')">Відхилити</button>
      <button type="button" class="btn btn-outline-primary" @click="$emit('draft')">Зберегти чернетку</button>
      <button type="button" class="btn btn-primary" @click="$emit('submit')">
        {{ publishLabel }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CategoryHeader',
  props: {
    title: {
      type: String,
      default: 'Додати категорію'
    },
    publishLabel: {
      type: String,
      default: 'Опублікувати категорію'
    }
  }
}
</script>

<style scoped>
.top-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
}
</style>
