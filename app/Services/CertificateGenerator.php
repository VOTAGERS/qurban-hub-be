<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class CertificateGenerator
{
    public function generate(string $participantName, string $filename, array $options = []): string
    {
        $this->defineFontPath();

        $pdf = new Fpdi();
        $pdf->AddPage('L'); // Landscape

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
        $pdf->useTemplate($tplIdx, 0, 0, null, null, true);
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
            'x'            => 0,
            'y'            => 95,        
            'font'         => 'TheSeasons',
            'style'        => '',
            'size'         => 45,
            'color'        => [122, 27, 46], 
            'align'        => 'C',
            'country_code' => null,
            'country'      => '',
        ];
    }

    private function stampName(Fpdi $pdf, string $name, array $opts): void
    {
        $name= strtoupper($name);
        $pageWidth = $pdf->GetPageWidth();
        $maxWidth  = $pageWidth * 0.80;
        $fontSize  = $opts['size'];   
        $minSize   = 20;            
        do {
            $pdf->SetFont($opts['font'], $opts['style'], $fontSize);
            $textWidth = $pdf->GetStringWidth($name);
            if ($textWidth <= $maxWidth || $fontSize <= $minSize) break;
            $fontSize -= 1;
        } while (true);

        $pdf->SetTextColor(...$opts['color']);
        $pdf->SetXY($opts['x'], $opts['y']);
        $pdf->Cell($pageWidth, 20, $name, 0, 0, $opts['align']);
    }

    private function stampFlag(Fpdi $pdf, array $opts): void
    {
        $flagUrl = 'https://flagcdn.com/w160/' . strtolower($opts['country_code']) . '.png';

        try {
            $pageCenter = $pdf->GetPageWidth() / 2;

            $pdf->Image($flagUrl, $pageCenter - 8, 120, 16);

            // Nama negara di bawah bendera
            $pdf->SetFont('TheSeasons', '', 14);
            $pdf->SetTextColor(122, 27, 46);
            $pdf->SetXY(0, 138);
            $pdf->Cell($pdf->GetPageWidth(), 10, strtoupper($opts['country']), 0, 0, 'C');
        } catch (\Exception $e) {
            // Ignore errors
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
