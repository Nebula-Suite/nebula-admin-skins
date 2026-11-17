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
     * Fills no foreground can rescue. They quote a published palette (`#6366f1` is Tailwind's
     * `indigo-500`, `#5e81ac` is `nord10`), and editing them stops the skin being that palette.
     *
     * @var array<string, float> skin/mode => the ratio measured when it was recorded
     */
    private const AWAITING_A_NEW_FILL = [
        'flat/dark' => 4.47,
        'indigo-compact/dark' => 4.47,
        'nord/custom' => 4.40,
    ];

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

        if (isset(self::AWAITING_A_NEW_FILL[$skin . '/' . $mode])) {
            $this->assertLessThan(Contrast::AA, $ratio, $skin . '/' . $mode . ' now clears AA; delete its exception.');

            return;
        }

        $this->assertGreaterThanOrEqual(Contrast::AA, $ratio, sprintf('%s/%s primary %s', $skin, $mode, $primary));
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
