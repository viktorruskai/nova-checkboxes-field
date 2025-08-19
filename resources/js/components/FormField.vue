<template>
  <DefaultField
      :field="currentField"
      :errors="errors"
      :show-help-text="showHelpText"
  >
    <template #field>
      <div v-if="currentField.withGroups">
        <div
            v-for="(groupOptions, group) in currentField.options"
            :key="group"
        >
          <h3>{{ group }}</h3>
          <div v-for="(label, value) in groupOptions" :key="value">
            <input
                type="checkbox"
                :id="`${currentField.attribute}-${value}`"
                :checked="isChecked(value)"
                @change="toggleOption(value)"
            />
            <label
                :for="`${currentField.attribute}-${value}`"
                @click="toggleOption(value)"
            >
              {{ label }}
            </label>
          </div>
        </div>
      </div>
      <div v-else>
        <div
            v-for="(label, value) in currentField.options"
            :key="value"
        >
          <input
              type="checkbox"
              :id="`${currentField.attribute}-${value}`"
              :checked="isChecked(value)"
              @change="toggleOption(value)"
          />
          <label
              :for="`${currentField.attribute}-${value}`"
              @click="toggleOption(value)"
          >
            {{ label }}
          </label>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import {DependentFormField, HandlesValidationErrors} from "laravel-nova";

export default {
  mixins: [DependentFormField, HandlesValidationErrors],

  methods: {
    isChecked(option) {
      return Array.isArray(this.value) && this.value.includes(option);
    },
    toggleOption(option) {
      let updated = Array.isArray(this.value) ? [...this.value] : [];
      updated = updated.includes(option)
          ? updated.filter(v => v !== option)
          : [...updated, option];
      this.$emit("input", updated);
    },
    setInitialValue() {
      this.value = Array.isArray(this.currentField.value)
          ? this.currentField.value
          : [];
    },
    fill(formData) {
      this.fillIfVisible(
          formData,
          this.fieldAttribute,
          JSON.stringify(this.value || [])
      );
    },
  },
};
</script>
