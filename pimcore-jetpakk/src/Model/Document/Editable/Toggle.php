<?php
/**
 * Adds editable 'pimcore_toggle' which enables the user to
 * toggle between values with a pleasant appearance in backend
 * @author Thomas Franz <thomas.franz@krankikom.de>
 * @version 1.0.0
 */

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Model\Document\Editable;

class Toggle extends \Pimcore\Model\Document\Editable
{
    protected $data;
    protected $value;

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'toggle';
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): mixed
    {
        $value = null;

        if (property_exists($this, 'config')) {
            if (isset($this->config['choices'])) {
                if (count($this->config['choices']) > 0) {
                    if (isset($this->config['choices'][0]['value'])) {
                        $value = $this->config['choices'][0]['value'];
                    }
                }
            }
            if (isset($this->config['defaultValue'])) {
                $value = $this->config['defaultValue'];
            }
        }
        if (property_exists($this, 'data')) {
            if (isset($this->data['value'])) {
                $value = $this->data['value'];
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function frontend()
    {
        return $this->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFromResource($data): static
    {
        $isUnserializable = @unserialize($data);
        if ($isUnserializable !== false) {
            $this->data = \Pimcore\Tool\Serialize::unserialize($data);
        }

        $this->value = $this->getValue();

        if (!is_array($this->data)) {
            $this->data = ['value' => $this->data];
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFromEditmode($data): static
    {
        $this->data = $data;
        $this->value = $this->getValue();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return !$this->value;
    }

    /**
     * @return string
     */
    public function isChecked()
    {
        return $this->value;
    }
}
