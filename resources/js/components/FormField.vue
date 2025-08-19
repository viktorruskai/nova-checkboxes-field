<template>
  <DefaultField
      :field="currentField"
      :errors="errors"
      :show-help-text="showHelpText"
  >
    <template #field>
      <!-- Grouped options -->
      <div class="tw-w-full tw-columns-2" v-if="currentField.withGroups">
        <div
            v-for="(groupOptions, group) in currentField.options"
            :key="group"
            class="tw-mb-4"
        >
          <h3 class="tw-my-2 tw-text-lg tw-font-semibold">
            {{ group }}
          </h3>

          <div
              v-for="(label, value) in groupOptions"
              :key="value"
              class="tw-flex tw-mb-2"
          >
            <checkbox
                :id="`${currentField.attribute}-${value}`"
                :value="value"
                :checked="isChecked(value)"
                @input="toggleOption(value)"
                class="tw-mr-2"
            />
            <label
                :for="`${currentField.attribute}-${value}`"
                v-text="label"
                class="tw-leading-tight tw-w-full tw-ml-2 cursor-pointer"
                @click="toggleOption(value)"
            ></label>
          </div>
        </div>
      </div>

      <!-- Flat options -->
      <div class="tw-w-full tw-columns-2" v-else>
        <div
            v-for="(label, value) in currentField.options"
            :key="value"
            class="tw-flex tw-mb-2"
        >
          <checkbox
              :id="`${currentField.attribute}-${value}`"
              :value="value"
              :checked="isChecked(value)"
              @input="toggleOption(value)"
              class="tw-mr-2"
          />
          <label
              :for="`${currentField.attribute}-${value}`"
              v-text="label"
              class="tw-leading-tight cursor-pointer"
              @click="toggleOption(value)"
          ></label>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import {DependentFormField, HandlesValidationErrors} from "laravel-nova";

export default {
  mixins: [DependentFormField, HandlesValidationErrors],
  props: ["resourceName", "resourceId", "field"],

  methods: {
    isChecked(option) {
      return Array.isArray(this.value) && this.value.includes(option);
    },

    toggleOption(option) {
      let updated = Array.isArray(this.value) ? [...this.value] : [];
      updated = updated.includes(option)
          ? updated.filter((v) => v !== option)
          : [...updated, option];

      this.$emit("input", updated);
    },

    // Nova calls this to initialize the field's value
    setInitialValue() {
      this.value = Array.isArray(this.field.value) ? this.field.value : [];
    },

    // Use the helper so the field doesn't submit when hidden
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
