<!-- ProductHeader.vue -->
<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h5 class="fw-bold mb-0">Додати новий товар</h5>
      </div>
      <div class="top-actions">
        <button type="button" class="btn btn-light">Відхилити</button>
        <button type="button" class="btn btn-outline-primary">Зберегти чернетку</button>
        <button type="button" @click="$emit('submit')" class="btn btn-primary">
            Опублікувати товар
        </button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'ProductHeader'
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
  