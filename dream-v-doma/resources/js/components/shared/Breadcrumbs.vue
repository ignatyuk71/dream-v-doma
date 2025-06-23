<template>
    <nav class="container pt-2 pt-xxl-3 my-3 my-md-4" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li
          v-for="(item, index) in items"
          :key="index"
          class="breadcrumb-item"
          :class="{ active: item.active }"
          :aria-current="item.active ? 'page' : null"
        >
          <template v-if="item.active">
            {{ item.text }}
          </template>
          <template v-else>
            <a :href="item.href">{{ item.text }}</a>
          </template>
        </li>
      </ol>
    </nav>
  </template>
  
  <script setup>
  defineProps({
    items: {
      type: Array,
      required: true,
      default: () => [],
    },
  })
  </script>
  
  <style scoped>
  .breadcrumb {
    background-color: transparent;
    margin-bottom: 0;
    padding-left: 0;
  }
  </style>
  