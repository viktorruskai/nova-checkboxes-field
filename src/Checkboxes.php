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
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param NovaRequest $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        if ($request->exists($requestAttribute)) {
            /**
             * Split checked options and remove unchecked options.
             */
            if (!is_array($checkedOptions = $request[$requestAttribute])) {
                $checkedOptions = collect(explode(',', $checkedOptions))
                    ->reject(fn ($name) => empty($name))
                    ->all();
            }

            if (isset($this->setValueCallback)) {
                return call_user_func($this->setValueCallback, $model, $attribute, $checkedOptions);
            }

            $model->{$attribute} = $checkedOptions;
        }
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
