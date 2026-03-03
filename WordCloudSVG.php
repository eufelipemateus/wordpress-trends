<?php
namespace Eufelipemateus\Trends;


class WordCloudSVG
{
    private array $words = [];
    private int $width;
    private int $height;

    public function __construct(int $width = 800, int $height = 400)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function setWords(array $words): void
    {
        arsort($words);
        $this->words = $words;
    }

    public function generate(): string
    {
        if (empty($this->words)) return "";

        $maxFreq = max($this->words);
        $minFreq = min($this->words);
        $diffFreq = max(1, $maxFreq - $minFreq);

        $svg = "<svg width='{$this->width}' height='{$this->height}' viewBox='0 0 {$this->width} {$this->height}' xmlns='http://www.w3.org/2000/svg'>";
        $svg .= "<rect width='100%' height='100%' fill='#ffffff' />";

        $placedRects = [];

        foreach ($this->words as $word => $count) {
            $percent = ($count - $minFreq) / $diffFreq;
            $fontSize = 12 + ($percent * 70); // De 12px a 82px

            $hue = (int)(240 - ($percent * 240));
            $color = "hsl($hue, 80%, 50%)";

            $placed = false;
            $angle = 0;
            $radius = 0;

            $w = strlen($word) * ($fontSize * 0.6);
            $h = $fontSize;

            while ($radius < max($this->width, $this->height) / 2) {
                $x = ($this->width / 2) + ($radius * cos($angle)) - ($w / 2);
                $y = ($this->height / 2) + ($radius * sin($angle));

                $currentRect = ['x1' => $x, 'y1' => $y - $h, 'x2' => $x + $w, 'y2' => $y];

                if (!$this->checkCollision($currentRect, $placedRects) && $this->isInside($currentRect)) {
                    $placedRects[] = $currentRect;
                    $svg .= "<text x='{$x}' y='{$y}' font-size='{$fontSize}px' font-weight='bold' fill='{$color}' style='font-family:sans-serif;'>" . htmlspecialchars($word) . "</text>";
                    $placed = true;
                    break;
                }

                $angle += 0.4; // Passo do ângulo
                $radius += 0.2; // Passo do raio
            }
        }

        $svg .= "</svg>";
        return $svg;
    }

    private function checkCollision($rect, $others): bool
    {
        foreach ($others as $other) {
            if (!($rect['x2'] < $other['x1'] || $rect['x1'] > $other['x2'] ||
                $rect['y2'] < $other['y1'] || $rect['y1'] > $other['y2'])) {
                return true;
            }
        }
        return false;
    }

    private function isInside($rect): bool
    {
        return ($rect['x1'] > 0 && $rect['x2'] < $this->width &&
            $rect['y1'] > 0 && $rect['y2'] < $this->height);
    }
}
