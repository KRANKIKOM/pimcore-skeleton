<?php


declare(strict_types=1);

namespace Krankikom\PimcoreJetpakkBundle\Twig;

use Pimcore\Extension\Document\Areabrick\AreabrickInterface;
use Pimcore\Extension\Document\Areabrick\AreabrickManagerInterface;
use Pimcore\Model\Document;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use App\Model\Constants;

class PimcoreJetpakkExtension extends AbstractExtension
{
    private ?array $bricksAll = null;

    private AreabrickManagerInterface $areaBrickManager;

    public function __construct(AreabrickManagerInterface $areabrickManager)
    {
        $this->areaBrickManager = $areabrickManager;
    }

    /**
     * @return TwigFilter[]
     *
     * @psalm-return list{TwigFilter}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'truncate']),
            new TwigFilter('isInstanceof', [$this, 'isInstanceof']),
        ];
    }

    /**
     * @return TwigFunction[]
     *
     * @psalm-return list{TwigFunction, TwigFunction, TwigFunction, TwigFunction, TwigFunction, TwigFunction, TwigFunction}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('icon', [$this, 'icon']),
            new TwigFunction('sharelink', [$this, 'sharelink']),
            new TwigFunction('lorem', [$this, 'lorem']),
            new TwigFunction('has', [$this, 'has']),
            new TwigFunction('classes', [$this, 'classes']),
            new TwigFunction(
                'blacklistBricks',
                [$this, 'blacklistBricks'],
                ['needs_context' => true]
            ),
            new TwigFunction('isNativeApp', [$this, 'isNativeApp']),
            new TwigFunction('filesize', [$this, 'filesize']),
        ];
    }

    public function truncate(
        string $string,
        int $your_desired_width,
        string $suffix = '[&hellip;]'
    ): string {
        /**
         * Truncates string to desired width, optional custom suffix.
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.0
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/filters/truncate
         */

        if (strlen($string) <= $your_desired_width) {
            return $string;
        }

        $parts = preg_split('/([\s\n\r]+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);
            if ($length > $your_desired_width) {
                break;
            }
        }

        return implode(array_slice($parts, 0, $last_part)) . html_entity_decode($suffix);
    }

    public function icon($name = '', $size = '24', $rotation = 0, $viewBox = ''): \Twig\Markup|null
    {
        /**
         * Generates svg-icon syntax which refers to our iconset.
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.1
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/functions/icon
         */

        if (!$name || '' === $name) {
            return null;
        }
        $attributes = [];
        if ($rotation && 0 != $rotation) {
            $attributes[] = 'data-rotate="' . $rotation . '"';
        }
        if ($viewBox && '' !== $viewBox) {
            $attributes[] = 'viewBox="' . $viewBox . '"';
            $attributes[] = 'width="' . \explode(' ', $viewBox)[2] . '"';
        }
        if ($size && '' !== $size) {
            $attributes[] = 'data-size="' . $size . '"';
        } else {
            $attributes[] = 'data-size';
        }

        $svg =
            '<svg ' .
            implode(' ', $attributes) .
            '><use xlink:href="/assets/iconset.svg#' .
            $name .
            '"></use></svg>';
        return new \Twig\Markup($svg, 'UTF-8');
    }

    public function sharelink(
        string|bool $type = false,
        string|bool $link = false
    ): \Twig\Markup|null {
        /**
         * Generates most common share links:
         * facebook, twitter, email, linkedin, whatsapp, pinterest, reddit.
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.0
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/functions/sharelink
         */

        if (!$type || !$link) {
            return null;
        }

        $sharelink = '';

        if ($type === 'facebook') {
            $sharelink = 'https://www.facebook.com/sharer/sharer.php?u=' . $link;
        }
        if ($type === 'twitter') {
            $sharelink = 'https://twitter.com/intent/tweet?url=' . $link;
        }
        if ($type === 'email') {
            $sharelink =
                'mailto:?subject=Ich möchte gerne diesen Artikel mit dir teilen.&body=Schau ihn dir mal an: ' .
                $link;
        }
        if ($type === 'linkedin') {
            $sharelink = 'https://www.linkedin.com/sharing/share-offsite/?url=' . $link;
        }
        if ($type === 'whatsapp') {
            $sharelink = 'https://api.whatsapp.com/send?text=' . $link;
        }
        if ($type === 'pinterest') {
            $sharelink = 'http://pinterest.com/pin/create/link/?url=' . $link;
        }
        if ($type === 'reddit') {
            $sharelink = 'https://reddit.com/submit?url=' . $link;
        }

        return new \Twig\Markup($sharelink, 'UTF-8');
    }

    public function lorem(int $characters = 200, bool $appendFullstop = true): string
    {
        /**
         * Generates Lorem Ipsum text so we can save on blind text in templates.
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.0
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/functions/lorem
         */

        $ipsumText = implode('', [
            'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut ',
            'labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores ',
            'et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ',
            'ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et ',
            'dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. ',
            'Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit ',
            'amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna ',
            'aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita ',
            'kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor ',
            'in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis ',
            'at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue ',
            'duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet.',
        ]);
        $ipsumArr = explode(' ', $ipsumText);
        $newIpsumArr = [];

        $index = 0;
        while ($characters > 0) {
            if ($index >= count($ipsumArr)) {
                $index = 0;
            }
            $word = $ipsumArr[$index];
            array_push($newIpsumArr, $word);
            $characters -= strlen($word);
            $index++;
        }

        $ipsumFinal = ucfirst(implode(' ', $newIpsumArr));

        if (substr($ipsumFinal, -1) === ',') {
            $ipsumFinal = substr($ipsumFinal, 0, -1);
        }

        if (substr($ipsumFinal, -1) !== '.' && $appendFullstop) {
            $ipsumFinal .= '.';
        }

        return $ipsumFinal;
    }

    public function has($data): bool
    {
        /**
         * Reduces commonly used case for checking
         * if editable has value or is in editmode.
         *
         * Return value if it is not an editable,
         * for example in case of manual template input.
         *
         * from:  {% if eyebrowline|default(false) and not eyebrowline.isEmpty() or editmode %}
         * to:    {% if eyebrowline|default(false) and has(eyebrowline) %}
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.3
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/functions/has
         */

        if (
            is_object($data) &&
            method_exists($data, 'getEditmode') &&
            method_exists($data, 'isEmpty')
        ) {
            // checks if $data actually is an editable
            return $data->getEditmode() || !$data->isEmpty();
        }

        // if it's not an editable, let it pass
        return boolval($data);
    }

    public function classes(array $classes): string
    {
        /**
         * Converts array into simple classes-string.
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.0
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/functions/classes
         */
        return implode(' ', array_filter($classes));
    }

    /**
     * @return array
     * @psalm-taint-specialize
     */
    private function getBricksAll(): array
    {
        if ($this->bricksAll !== null) {
            return $this->bricksAll;
        }

        $bricks = $this->areaBrickManager->getBricks();
        ksort($bricks);

        $this->bricksAll = [];
        foreach ($bricks as $brick) {
            $this->bricksAll[] = $brick->getId();
        }

        return $this->bricksAll;
    }

    /**
     * @return string[]
     *
     * @psalm-return array<int, string>
     */
    public function blacklistBricks(array $context, array $bricks = []): array
    {
        /**
         * Artificially creates a blacklist of blocks by subtracting
         * specific blocks from a full list of all available blocks.
         *
         * @author Thomas Franz <thomas.franz@krankikom.de>
         * @version 1.1
         * @see https://kk:insight@insight.krankikom.de/devdocs/pimcore10x/kk-custom/twig/functions/blacklistbricks
         */
        if (isset($context['editmode']) && $context['editmode'] !== true) {
            return [];
        }
        $bricksAll = $this->getBricksAll();

        $document = $context['document'] ?? null;
        if (null === $document) {
            return [];
        }

        return array_diff($bricksAll, $bricks);
    }

    /**
     * @return bool
     */
    public static function isNativeApp(): bool
    {
        if (!array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
            return false;
        }
        $ua = $_SERVER['HTTP_USER_AGENT'];
        return str_starts_with($ua, Constants::NATIVE_APP_USERAGENT_PREFIX);
    }

    public static function filesize(string $bytes, int $decimals = 1): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        $decimals = $factor < 2 ? $decimals : 0; // only add decimal point from MB on, not necessary for B and KB
        return floatval(sprintf("%.{$decimals}f", $bytes / pow(1024, $factor))) . @$size[$factor];
    }

    /**
     * @return bool
     */
    public static function isInstanceof(object|bool $obj, string $classString): bool
    {
        return $obj instanceof $classString;
    }
}
