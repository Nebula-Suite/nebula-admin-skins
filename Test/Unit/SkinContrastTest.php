<?php

declare(strict_types=1);

namespace Nebula\AdminSkins\Test\Unit;

use Nebula\Skin\Model\Contrast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The checks Nebula_Skin runs on its own skins, run on these thirteen.
 *
 * Nebula's tests read only Nebula_Skin's skin directory, so without this a skin here is never measured.
 */
class SkinContrastTest extends TestCase
{
    private const PRIMARY_VAR = '--nebula-button-primary-bg';

    /**
     * @param array<string, string> $variables
     */
    #[DataProvider('skinModes')]
    public function testRowsAndBooleanLabelsRemainReadable(array $variables): void
    {
        $contrast = new Contrast();
        $this->assertNotSame($variables['--nebula-bg-primary'], $variables['--nebula-grid-row-alt-bg']);
        foreach ([
            ['--nebula-bg-primary', '--nebula-text-secondary'],
            ['--nebula-grid-row-alt-bg', '--nebula-text-secondary'],
            ['--nebula-boolean-yes-bg', '--nebula-boolean-yes-color'],
            ['--nebula-boolean-no-bg', '--nebula-boolean-no-color'],
        ] as [$background, $foreground]) {
            $this->assertGreaterThanOrEqual(
                Contrast::AA,
                $contrast->ratio($variables[$background], $variables[$foreground]),
                $foreground . ' on ' . $background
            );
        }
    }

    #[DataProvider('skinPrimaries')]
    public function testTheComputedForegroundClearsAa(string $skin, string $mode, string $primary): void
    {
        $contrast = new Contrast();
        $ratio = $contrast->ratio($primary, $contrast->foregroundFor($primary));

        $this->assertGreaterThanOrEqual(Contrast::AA, $ratio, sprintf('%s/%s primary %s', $skin, $mode, $primary));
    }

    /**
     * The loader computes a foreground only for a flat fill, so a gradient keeps the stylesheet's
     * white label and every stop has to carry it.
     */
    #[DataProvider('gradientStops')]
    public function testEveryGradientStopCarriesTheDefaultForeground(string $skin, string $mode, string $stop): void
    {
        $this->assertGreaterThanOrEqual(
            Contrast::AA,
            (new Contrast())->ratio($stop, Contrast::ON_DARK),
            sprintf('%s/%s gradient stop %s under a white label', $skin, $mode, $stop)
        );
    }

    public function testEverySkinIsCovered(): void
    {
        $this->assertCount(26, self::skinModes());
        // Nostalgia fills with a gradient in both modes, which the hex check skips.
        $this->assertGreaterThanOrEqual(24, count(self::skinPrimaries()));
    }

    /**
     * @return array<string, array{array<string, string>}>
     */
    public static function skinModes(): array
    {
        $cases = [];
        foreach (self::skins() as $skin) {
            $cases[$skin['id'] . '/light'] = [$skin['custom']];
            $cases[$skin['id'] . '/dark'] = [array_merge($skin['custom'], $skin['dark'])];
        }

        return $cases;
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function skinPrimaries(): array
    {
        $cases = [];
        foreach (self::skins() as $skin) {
            foreach (['custom', 'dark'] as $mode) {
                $primary = $skin[$mode][self::PRIMARY_VAR] ?? null;
                if (is_string($primary) && preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $primary)) {
                    $cases[$skin['id'] . '/' . $mode] = [$skin['id'], $mode, $primary];
                }
            }
        }

        return $cases;
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function gradientStops(): array
    {
        $cases = [];
        foreach (self::skins() as $skin) {
            foreach (['custom', 'dark'] as $mode) {
                $primary = $skin[$mode][self::PRIMARY_VAR] ?? null;
                if (!is_string($primary) || !str_contains($primary, 'gradient(')) {
                    continue;
                }
                preg_match_all('/#(?:[0-9a-f]{6}|[0-9a-f]{3})\b/i', $primary, $stops);
                foreach ($stops[0] as $stop) {
                    $cases[$skin['id'] . '/' . $mode . '/' . $stop] = [$skin['id'], $mode, $stop];
                }
            }
        }

        return $cases;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function skins(): array
    {
        $skins = [];
        foreach (glob(dirname(__DIR__, 2) . '/view/adminhtml/nebula/skin/*.json') ?: [] as $file) {
            $skins[] = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        }

        return $skins;
    }
}
