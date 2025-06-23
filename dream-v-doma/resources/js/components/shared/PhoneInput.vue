<template>
  <div>
    <label class="form-label">Телефон <span class="text-danger">*</span></label>
    <input
      type="tel"
      class="form-control"
      v-model="displayed"
      :class="{ 'is-invalid': isInvalid }"
      @input="onInput"
      @blur="validate"
      placeholder="+380XX-XXX-XX-XX"
    />
    <div v-if="isInvalid" class="invalid-feedback">
      Введіть коректний номер телефону у форматі +380XX-XXX-XX-XX
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, watch } from 'vue'

const props = defineProps({
  modelValue: String
})

const emit = defineEmits(['update:modelValue', 'validation'])

const displayed = ref('+380-')
const isInvalid = ref(false)

watch(() => props.modelValue, (val) => {
  if (val && normalize(displayed.value) !== val) {
    displayed.value = format(val)
  }
})

function normalize(input) {
  return '+380' + input.replace(/\D/g, '').slice(0, 9)
}

function format(input) {
  const raw = input.replace(/\D/g, '').replace(/^380/, '').replace(/^0/, '').slice(0, 9)
  let result = '+380-'
  if (raw.length > 0) result += raw.slice(0, 2)
  if (raw.length > 2) result += '-' + raw.slice(2, 5)
  if (raw.length > 5) result += '-' + raw.slice(5, 7)
  if (raw.length > 7) result += '-' + raw.slice(7, 9)
  return result
}

function onInput(e) {
  let raw = e.target.value.replace(/\D/g, '')

  // Видаляємо зайві
  if (raw.startsWith('380')) raw = raw.slice(3)
  if (raw.startsWith('0')) raw = raw.slice(1)

  raw = raw.slice(0, 9)
  const formatted = format(raw)

  displayed.value = formatted
  emit('update:modelValue', '+380' + raw)
  isInvalid.value = false
  emit('validation', true)
}

function validate() {
  const digits = displayed.value.replace(/\D/g, '')
  isInvalid.value = digits.length !== 12
  emit('validation', !isInvalid.value)
}
</script>
