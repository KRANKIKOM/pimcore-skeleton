<?php

declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Model\Document\Editable;

use Pimcore\Logger;
use Pimcore\Model;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;

class Button extends \Pimcore\Model\Document\Editable
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
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'button';
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): mixed
    {
        // update path if internal button
        $this->updatePathFromInternal(true);

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataEditmode(): static
    {
        /** : mixed */
        // update path if internal button
        $this->updatePathFromInternal(true, true);

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditmodeElementClasses($options = []): array
    {
        // we don't want the class attribute being applied to the editable container element (<div>, only to the <a> tag inside
        // the default behavior of the parent method is to include the "class" attribute
        $classes = ['pimcore_editable', 'pimcore_editable_' . $this->getType()];

        return $classes;
    }

    /**
     * {@inheritdoc}
     */
    public function frontend()
    {
        $url = $this->getHref();

        if (strlen($url) > 0) {
            if (!is_array($this->config)) {
                $this->config = [];
            }

            $prefix = '';
            $suffix = '';
            $noText = false;

            if (array_key_exists('textPrefix', $this->config)) {
                $prefix = $this->config['textPrefix'];
                unset($this->config['textPrefix']);
            }

            if (array_key_exists('textSuffix', $this->config)) {
                $suffix = $this->config['textSuffix'];
                unset($this->config['textSuffix']);
            }

            if (isset($this->config['noText']) && $this->config['noText'] == true) {
                $noText = true;
                unset($this->config['noText']);
            }

            // add attributes to button
            $allowedAttributes = [
                'charset',
                'coords',
                'hreflang',
                'name',
                'rel',
                'rev',
                'shape',
                'target',
                'accesskey',
                'class',
                'dir',
                'draggable',
                'dropzone',
                'contextmenu',
                'id',
                'lang',
                'style',
                'tabindex',
                'title',
                'media',
                'download',
                'ping',
                'type',
                'referrerpolicy',
                'xml:lang',
                'onblur',
                'onclick',
                'ondblclick',
                'onfocus',
                'onmousedown',
                'onmousemove',
                'onmouseout',
                'onmouseover',
                'onmouseup',
                'onkeydown',
                'onkeypress',
                'onkeyup',
            ];
            $defaultAttributes = [];

            if (!is_array($this->data)) {
                $this->data = [];
            }

            $availableAttribs = array_merge($defaultAttributes, $this->data, $this->config);

            // add attributes to button
            $attribs = [];
            foreach ($availableAttribs as $key => $value) {
                if (
                    (is_string($value) || is_numeric($value)) &&
                    (strpos($key, 'data-') === 0 ||
                        strpos($key, 'aria-') === 0 ||
                        in_array($key, $allowedAttributes))
                ) {
                    if (!empty($this->data[$key]) && !empty($this->config[$key])) {
                        $attribs[] =
                            $key . '="' . $this->data[$key] . ' ' . $this->config[$key] . '"';
                    } elseif (!empty($value)) {
                        $attribs[] = $key . '="' . $value . '"';
                    }
                }
            }

            $attribs = array_unique($attribs);

            if (array_key_exists('attributes', $this->data) && !empty($this->data['attributes'])) {
                $attribs[] = $this->data['attributes'];
            }

            $classes = ['btn'];
            $icon = '';
            $iconRight = '';
            $iconLeft = '';

            $btnClassChain = '';
            // add classes to class collection
            if (isset($this->data['btnType'])) {
                if (strlen($this->data['btnType']) > 0 && $this->data['btnType'] !== 'fill') {
                    $btnClassChain .= '-' . $this->data['btnType'];
                }
            }
            if (isset($this->data['btnStyle'])) {
                if (strlen($this->data['btnStyle']) > 0) {
                    $btnClassChain .= '-' . $this->data['btnStyle'];
                }
            }
            if (strlen($btnClassChain) > 0) {
                array_push($classes, 'btn' . $btnClassChain);
            }

            if (isset($this->data['btnIcon'])) {
                if (strlen($this->data['btnIcon']) > 0) {
                    $icon = explode('/', $this->data['btnIcon']);
                    $name = array_key_exists(0, $icon) ? $icon[0] : null;
                    $rotation =
                        array_key_exists(1, $icon) && $icon[1] !== '0'
                            ? ' data-rotate="' . $icon[1] . '"'
                            : null;
                    $size = array_key_exists(2, $icon) ? $icon[2] : 24;
                    $icon =
                        '<div class="icon"><svg data-size="' .
                        $size .
                        '"' .
                        $rotation .
                        '><use xlink:href="/assets/iconset.svg#' .
                        $name .
                        '"></use></svg></div>';
                    array_push($classes, 'btn--has-icon');
                }
            }
            if (isset($this->data['btnIconPos'])) {
                if (strlen($this->data['btnIconPos']) > 0) {
                    $pos = $this->data['btnIconPos'];
                    if ($pos === 'left') {
                        $iconLeft = $icon;
                    } else {
                        $iconRight = $icon;
                    }
                }
            }

            $text = '';
            if (!$noText and isset($this->data['text'])) {
                $text = htmlspecialchars($this->data['text']);
            }

            $content = $prefix . $text . $suffix;
            if (strlen($content) > 0) {
                $content = '<span>' . $content . '</span>';
            }

            return '<a class="' .
                implode(' ', $classes) .
                '" href="' .
                $url .
                '" ' .
                implode(' ', $attribs) .
                '>' .
                $iconLeft .
                $content .
                $iconRight .
                '</a>';
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function checkValidity(): bool
    {
        $sane = true;
        if (is_array($this->data) && isset($this->data['internal']) && $this->data['internal']) {
            if ($this->data['internalType'] == 'document') {
                $doc = Document::getById($this->data['internalId']);
                if (!$doc) {
                    $sane = false;
                    Logger::notice(
                        'Detected insane relation, removing reference to non existent document with id [' .
                            $this->getDocumentId() .
                            ']'
                    );
                    $this->data = null;
                }
            } elseif ($this->data['internalType'] == 'asset') {
                $asset = Asset::getById($this->data['internalId']);
                if (!$asset) {
                    $sane = false;
                    Logger::notice(
                        'Detected insane relation, removing reference to non existent asset with id [' .
                            $this->getDocumentId() .
                            ']'
                    );
                    $this->data = null;
                }
            } elseif ($this->data['internalType'] == 'object') {
                $object = Model\DataObject\Concrete::getById($this->data['internalId']);
                if (!$object) {
                    $sane = false;
                    Logger::notice(
                        'Detected insane relation, removing reference to non existent object with id [' .
                            $this->getDocumentId() .
                            ']'
                    );
                    $this->data = null;
                }
            }
        }

        return $sane;
    }

    /**
     * @return string
     */
    public function getHref()
    {
        $this->updatePathFromInternal();

        $url = $this->data['path'] ?? '';

        if (strlen($this->data['parameters'] ?? '') > 0) {
            $url .=
                (strpos($url, '?') !== false ? '&' : '?') .
                str_replace('?', '', $this->getParameters());
        }

        if (strlen($this->data['anchor'] ?? '') > 0) {
            $anchor = $this->getAnchor();
            $anchor = str_replace('"', urlencode('"'), $anchor);
            $url .= '#' . str_replace('#', '', $anchor);
        }

        return $url;
    }

    /**
     * @param bool $realPath
     * @param bool $editmode
     */
    private function updatePathFromInternal($realPath = false, $editmode = false)
    {
        $method = 'getFullPath';
        if ($realPath) {
            $method = 'getRealFullPath';
        }

        if (isset($this->data['internal']) && $this->data['internal']) {
            if ($this->data['internalType'] == 'document') {
                if ($doc = Document::getById($this->data['internalId'])) {
                    if ($editmode || (!Document::doHideUnpublished() || $doc->isPublished())) {
                        $this->data['path'] = $doc->$method();
                    } else {
                        $this->data['path'] = '';
                    }
                }
            } elseif ($this->data['internalType'] == 'asset') {
                if ($asset = Asset::getById($this->data['internalId'])) {
                    $this->data['path'] = $asset->$method();
                }
            } elseif ($this->data['internalType'] == 'object') {
                if ($object = Model\DataObject::getById($this->data['internalId'])) {
                    if ($editmode) {
                        $this->data['path'] = $object->getFullPath();
                    } else {
                        if ($object instanceof Model\DataObject\Concrete) {
                            if ($linkGenerator = $object->getClass()->getLinkGenerator()) {
                                if ($realPath) {
                                    $this->data['path'] = $object->getFullPath();
                                } else {
                                    $this->data['path'] = $linkGenerator->generate($object, [
                                        'document' => $this->getDocument(),
                                        'context' => $this,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        // deletes unnecessary attribute, which was set by mistake in earlier versions, see also
        // https://github.com/pimcore/pimcore/issues/7394
        if (isset($this->data['type'])) {
            unset($this->data['type']);
        }
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->data['text'] ?? '';
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->data['text'] = $text;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->data['target'] ?? '';
    }

    /**
     * @return string
     */
    public function getParameters()
    {
        return $this->data['parameters'] ?? '';
    }

    /**
     * @return string
     */
    public function getAnchor()
    {
        return $this->data['anchor'] ?? '';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->data['title'] ?? '';
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->data['rel'] ?? '';
    }

    /**
     * @return string
     */
    public function getTabindex()
    {
        return $this->data['tabindex'] ?? '';
    }

    /**
     * @return string
     */
    public function getAccesskey()
    {
        return $this->data['accesskey'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->data['class'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->data['attributes'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getBtnStyle()
    {
        return $this->data['btnStyle'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getBtnType()
    {
        return $this->data['btnType'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getBtnIcon()
    {
        return $this->data['btnIcon'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getBtnIconPos()
    {
        return $this->data['btnIconPos'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFromResource($data): static
    {
        $this->data = \Pimcore\Tool\Serialize::unserialize($data);

        if (!is_array($this->data)) {
            $this->data = [];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDataFromEditmode($data): static
    {
        if (!is_array($data)) {
            $data = [];
        }

        $path = $data['path'] ?? null;

        if (!empty($path)) {
            $target = null;

            if ($data['linktype'] == 'internal' && $data['internalType']) {
                $target = Model\Element\Service::getElementByPath($data['internalType'], $path);
                if ($target) {
                    $data['internal'] = true;
                    $data['internalId'] = $target->getId();
                }
            }

            if (!$target) {
                if ($target = Document::getByPath($path)) {
                    $data['internal'] = true;
                    $data['internalId'] = $target->getId();
                    $data['internalType'] = 'document';
                } elseif ($target = Asset::getByPath($path)) {
                    $data['internal'] = true;
                    $data['internalId'] = $target->getId();
                    $data['internalType'] = 'asset';
                } elseif ($target = Model\DataObject\Concrete::getByPath($path)) {
                    $data['internal'] = true;
                    $data['internalId'] = $target->getId();
                    $data['internalType'] = 'object';
                } else {
                    $data['internal'] = false;
                    $data['internalId'] = null;
                    $data['internalType'] = null;
                    $data['linktype'] = 'direct';
                }

                if ($target) {
                    $data['linktype'] = 'internal';
                }
            }
        }

        $this->data = $data;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty(): bool
    {
        return strlen($this->getHref()) < 1;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveDependencies(): array
    {
        $dependencies = [];
        $isInternal = $this->data['internal'] ?? false;

        if (is_array($this->data) && $isInternal) {
            if ((int) $this->data['internalId'] > 0) {
                if ($this->data['internalType'] == 'document') {
                    if ($doc = Document::getById($this->data['internalId'])) {
                        $key = 'document_' . $doc->getId();

                        $dependencies[$key] = [
                            'id' => $doc->getId(),
                            'type' => 'document',
                        ];
                    }
                } elseif ($this->data['internalType'] == 'asset') {
                    if ($asset = Asset::getById($this->data['internalId'])) {
                        $key = 'asset_' . $asset->getId();

                        $dependencies[$key] = [
                            'id' => $asset->getId(),
                            'type' => 'asset',
                        ];
                    }
                }
            }
        }

        return $dependencies;
    }

    /**
     * { @inheritdoc }
     */
    public function rewriteIds($idMapping)
    {
        /** : void */ if (isset($this->data['internal']) && $this->data['internal']) {
            $type = $this->data['internalType'];
            $id = (int) $this->data['internalId'];

            if (array_key_exists($type, $idMapping)) {
                if (array_key_exists($id, $idMapping[$type])) {
                    $this->data['internalId'] = $idMapping[$type][$id];
                    $this->getHref();
                }
            }
        }
    }
}
