<?php

namespace Idez\NovaCheckboxesField;

use JsonException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class Checkboxes extends Field
{
    use SupportsDependentFields;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-checkboxes-field';

    /**
     * The callback to be used to hydrate the model attribute after get only checked options.
     *
     * @var (callable(object, string, array):mixed)|null
     */
    public $setValueCallback;

    /**
     * Enable groups of checkboxes.
     * 
     * @return self
     */
    public function withGroups()
    {
        return $this->withMeta(['withGroups' => true]);
    }

    /**
     * Specify the available options
     *
     * @param  array  $options
     * @return self
     */
    public function options(array $options): Checkboxes
    {
        return $this->withMeta(['options' => $options]);
    }

    /**
     * Specify a callback that should be used to hydrate the model attribute for the field.
     *
     * @param  callable(object, string, array):mixed  $setValueCallback
     * @return self
     */
    public function setValueUsing(callable $setValueCallback): static
    {
        $this->setValueCallback = $setValueCallback;

        return $this;
    }

    /**
     * Override default method to avoid errors. Use setValueUsing to customize the model atribute hydratation.
     *
     * @param $fillCallback
     * @return Checkboxes
     */
    public function fillUsing($fillCallback): Checkboxes
    {
        return $this;
    }

    /**
     * @throws JsonException
     */
    public function fillAttributeFromRequest(NovaRequest $request, string $requestAttribute, object $model, string $attribute): void
    {
        $value = $request[$requestAttribute] ?? [];

        // Normalize incoming payload
        if (is_string($value)) {
            $decoded = null;
            try {
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $decoded = null;
            }

            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                $value = $decoded;
            } else {
                // Fallbacks: comma separated or single scalar
                $trimmed = trim($value);
                if ($trimmed === '') {
                    $value = [];
                } elseif (str_contains($trimmed, ',')) {
                    $value = array_map('trim', explode(',', $trimmed));
                } else {
                    $value = [$trimmed];
                }
            }
        }

        if (!is_array($value)) {
            $value = $value === null ? [] : (array)$value;
        }

        // If associative map of key => bool, keep only truthy keys
        $isAssoc = array_keys($value) !== range(0, count($value) - 1);
        if ($isAssoc) {
            $value = array_keys(array_filter($value, static fn($v) => (bool)$v));
        }

        // Clean values: cast to string, remove empties, unique, reindex
        $value = array_values(array_unique(array_filter(array_map(static fn($v) => is_bool($v) ? ($v ? '1' : '0') : (string)$v, $value), static fn($v) => $v !== '')));

        // Allow customization via callback
        if (is_callable($this->setValueCallback)) {
            $model->{$attribute} = call_user_func($this->setValueCallback, $model, $attribute, $value);
            return;
        }

        $model->{$attribute} = $value;
    }

    /**
     * @throws JsonException
     */
    public function fillModelWithData(object $model, mixed $value, string $attribute): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (!is_array($value)) {
            $value = $value === null ? [] : (array)$value;
        }

        data_set($model, $attribute, $value);
    }
}
