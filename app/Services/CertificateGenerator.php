<?php
namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class CertificateGenerator
{
    private const PAGE_W = 297.0;
    private const PAGE_H = 210.0;

    public function generate(string $participantName, string $filename, array $options = []): string
    {
        $this->defineFontPath();

        $pdf = new Fpdi();
        $pdf->AddPage('L', [self::PAGE_W, self::PAGE_H]);

        $this->loadTemplate($pdf);
        $this->registerFonts($pdf);

        $opts = array_merge($this->defaultOptions(), $options);

        $this->stampName($pdf, $participantName, $opts);

        if (!empty($opts['country_code'])) {
            $this->stampFlag($pdf, $opts);
        }

        return $this->saveFile($pdf, $filename);
    }


    private function defineFontPath(): void
    {
        if (!defined('FPDF_FONTPATH')) {
            define('FPDF_FONTPATH', public_path('fonts/The.Seasons/'));
        }
    }

    private function loadTemplate(Fpdi $pdf): void
    {
        $templatePath = public_path('certificate-template/qurbanhub-certificate.pdf');

        if (!file_exists($templatePath)) {
            throw new \Exception("Template tidak ditemukan: {$templatePath}");
        }

        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, self::PAGE_W, self::PAGE_H, true);
    }

    private function registerFonts(Fpdi $pdf): void
    {
        $pdf->AddFont('TheSeasons', '',  'TheSeasonsRegular.php');
        $pdf->AddFont('TheSeasons', 'B', 'TheSeasonsBold.php');
        $pdf->AddFont('TheSeasons', 'I', 'TheSeasonsItalic.php');
    }

    private function defaultOptions(): array
    {
        return [
            'font'         => 'TheSeasons',
            'style'        => '',
            'size'         => 42,
            'color'        => [122, 27, 46],
            'align'        => 'C',
            'country_code' => null,
            'country'      => '',
        ];
    }
   

    private function stampName(Fpdi $pdf, string $name, array $opts): void
    {
        $name     = $this->simplifyName($name);
        $maxWidth = self::PAGE_W * 0.75;
        $fontSize = $opts['size'];
        $minSize  = 18;

        do {
            $pdf->SetFont($opts['font'], $opts['style'], $fontSize);
            $textWidth = $pdf->GetStringWidth($name);
            if ($textWidth <= $maxWidth || $fontSize <= $minSize) break;
            $fontSize -= 1;
        } while (true);

        $pdf->SetTextColor(...$opts['color']);

        $pdf->SetXY(0, 95);
        $pdf->Cell(self::PAGE_W, 12, $name, 0, 0, 'C');
    }


    private function simplifyName(string $name): string
    {
        $name  = trim($name);
        $words = explode(' ', $name);

        if (count($words) <= 3) {
            return strtoupper($name);
        }

        $connectors = ['bin', 'binte', 'binti', 'd/o', 's/o', 'al', 'bt', 'b'];
        $filtered   = array_filter($words, fn($w) => !in_array(strtolower($w), $connectors));
        $filtered   = array_values($filtered);
        if (count($filtered) > 3) {
            $simplified = $filtered[0] . ' ' . end($filtered);
        } else {
            $simplified = implode(' ', $filtered);
        }

        return strtoupper($simplified);
    }

   
    private function stampFlag(Fpdi $pdf, array $opts): void
    {
        $flagUrl = 'https://flagcdn.com/w160/' . strtolower($opts['country_code']) . '.png';

        try {
            $centerX   = self::PAGE_W / 2;
            $flagW     = 18;
            $flagH     = 12;
            $flagX     = $centerX - ($flagW / 2);
            $flagY     = 110; 

            $pdf->Image($flagUrl, $flagX, $flagY, $flagW, $flagH);

            $pdf->SetFont('TheSeasons', '', 10);
            $pdf->SetTextColor(122, 27, 46);
            $pdf->SetXY(0, $flagY + $flagH + 2);
            $pdf->Cell(self::PAGE_W, 6, strtoupper($opts['country']), 0, 0, 'C');

        } catch (\Exception $e) {
        }
    }


    private function saveFile(Fpdi $pdf, string $filename): string
    {
        $storagePath = 'public/certificates/' . $filename;
        $fullPath    = storage_path('app/' . $storagePath);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf->Output('F', $fullPath);

        return Storage::url($storagePath);
    }
}