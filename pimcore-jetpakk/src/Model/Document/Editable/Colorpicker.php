<?php
namespace Krankikom\PimcoreJetpakkBundle\Model\Document\Editable;

class Colorpicker extends \Pimcore\Model\Document\Editable
{
    /**
     * Contains the data for the button
     *
     * @internal
     *
     * @var array|null
     */
    protected $data;

    /**
     * Used to get data to js file (initialize method)
     */
    public function frontend()
    {
        if (!property_exists($this, 'data')) {
            return null;
        }
        return $this->getHex();
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDataEditmode()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getHex()
    {
        if (!isset($this->data['hex'])) {
            return null;
        }
        return '#' . $this->data['hex'];
    }

    /**
     * @return string
     */
    public function getRGB()
    {
        if (!isset($this->data['r']) || !isset($this->data['g']) || !isset($this->data['b'])) {
            return null;
        }
        return 'rgb(' . $this->data['r'] . ', ' . $this->data['g'] . ', ' . $this->data['b'] . ')';
    }

    /**
     * @return string
     */
    public function getRGBA()
    {
        if (
            !isset($this->data['r']) ||
            !isset($this->data['g']) ||
            !isset($this->data['b']) ||
            !isset($this->data['a'])
        ) {
            return null;
        }
        return 'rgba(' .
            $this->data['r'] .
            ', ' .
            $this->data['g'] .
            ', ' .
            $this->data['b'] .
            ', ' .
            $this->data['a'] .
            ')';
    }

    /**
     * @return string
     */
    public function getHSL()
    {
        if (!isset($this->data['h']) || !isset($this->data['s']) || !isset($this->data['v'])) {
            return null;
        }
        function hsv2hsl($h, $s, $v)
        {
            $l = $v - ($v * $s) / 2;
            $m = min($l, 1 - $l);

            $h = round($h, 2);
            $s = round($m ? ($v - $l) / $m : 0, 2);
            $l = round($l, 2);

            return 'hsl(' . $h . 'deg ' . $s . '% ' . $l . '%)';
        }

        return hsv2hsl($this->data['h'], $this->data['s'], $this->data['v']);
    }

    /**
     * Returns type, has to be the same as in js and yaml file
     */
    public function getType()
    {
        return 'colorpicker';
    }

    /**
     * Used for getting data from editmode
     */
    public function setDataFromEditmode($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Used for getting data from database
     */
    public function setDataFromResource($data)
    {
        $this->data = \Pimcore\Tool\Serialize::unserialize($data);

        if (!is_array($this->data)) {
            $this->data = [];
        }

        return $this;
    }

    public function isEmpty()
    {
        return empty($this->data['hex']);
    }
}
