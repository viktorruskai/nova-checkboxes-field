<template>
  <DefaultField
      :field="currentField"
      :errors="errors"
      :show-help-text="showHelpText"
  >
    <template #field>
      <div class="tw-w-full tw-columns-2" v-if="currentField.withGroups">
        <div
            v-for="(groupOptions, group) in currentField.options"
            :key="group"
            class="tw-mb-4"
        >
          <h3 class="tw-my-2 tw-text-lg tw-font-semibold">
            {{ group }}
          </h3>
          <div v-for="(label, value) in groupOptions" :key="value" class="tw-flex tw-mb-2">
            <input
                type="checkbox"
                :id="`${currentField.attribute}-${value}`"
                :checked="isChecked(value)"
                @change="toggleOption(value)"
                class="tw-mr-2"
            />
            <label
                :for="`${currentField.attribute}-${value}`"
                class="tw-leading-tight cursor-pointer"
                @click="toggleOption(value)"
            >
              {{ label }}
            </label>
          </div>
        </div>
      </div>
      <div class="tw-w-full tw-columns-2" v-else>
        <div
            v-for="(label, value) in currentField.options"
            :key="value"
            class="tw-flex tw-mb-2"
        >
          <input
              type="checkbox"
              :id="`${currentField.attribute}-${value}`"
              :checked="isChecked(value)"
              @change="toggleOption(value)"
              class="tw-mr-2"
          />
          <label
              :for="`${currentField.attribute}-${value}`"
              class="tw-leading-tight cursor-pointer"
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
