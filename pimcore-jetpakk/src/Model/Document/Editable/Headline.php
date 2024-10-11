<?php
/**
 * Adds editable 'pimcore_headline' which enables the user to
 * customize the seo relevant headline tag aswell as its appearance.
 * @author Thomas Franz <thomas.franz@krankikom.de>
 * @version 1.1.0
 */

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Model\Document\Editable;

class Headline extends \Pimcore\Model\Document\Editable
{
    protected $data;

    /**
     * Used to get data to js file (initialize method)
     */
    public function frontend()
    {
        if (!property_exists($this, 'data')) {
            return null;
        }
        if (!isset($this->data['text'])) {
            return null;
        }
        if (!strlen($this->data['text'])) {
            return null;
        }

        $classes = [];
        $attributes = [];
        $tag = isset($this->data['headlineSeo']) ? $this->data['headlineSeo'] : null;
        $text = nl2br($this->data['text']);
        $openingTag = [$tag];

        // add classes to class collection
        if (isset($this->data['class'])) {
            if (strlen($this->data['class']) > 0) {
                array_push($classes, $this->data['class']);
            }
        }
        if (isset($this->config['headlineClass'])) {
            if (strlen($this->config['headlineClass']) > 0) {
                array_push($classes, $this->config['headlineClass']);
            }
        }
        if (isset($this->data['headlineVisual'])) {
            if (strlen($this->data['headlineVisual']) > 0) {
                array_push($classes, $this->data['headlineVisual']);
            }
        }

        // add flattened class collection to attributes collection
        if (sizeof($classes) > 0) {
            array_push($attributes, 'class="' . implode(' ', $classes) . '"');
        }

        // add attributes to attributes collection
        if (isset($this->data['attributes'])) {
            array_push($attributes, $this->data['attributes']);
        }

        // add flattened attributes collection to opening tag
        if (sizeOf($attributes) > 0) {
            array_push($openingTag, implode(' ', $attributes));
        }

        // flatten opening tag content
        $openingTag = implode(' ', $openingTag);

        return '<' . $openingTag . '>' . $text . '</' . $tag . '>';
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Returns type, has to be the same as in js and yaml file
     */
    public function getType(): string
    {
        return 'headline';
    }

    /**
     * Used for getting data from editmode
     */
    public function setDataFromEditmode($data): static
    {
        // $test = $data;
        // $test->text = html_entity_decode($test->text, ENT_HTML5);
        // $this->data = $test;
        $this->data = $data;

        return $this;
    }

    /**
     * Used for getting data from database
     */
    public function setDataFromResource($data): static
    {
        $this->data = \Pimcore\Tool\Serialize::unserialize($data);
        if (!is_array($this->data)) {
            $this->data = [];
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->data['text']);
    }
}
