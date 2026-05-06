<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class CertificateGenerator
{
    protected $fpdf;

    public function __construct()
    {
        // We'll initialize FPDI inside the generate method to ensure a fresh instance per call
    }

    /**
     * Generate a certificate PDF for a participant
     *
     * @param string $participantName Name to be printed on the certificate
     * @param string $filename Target filename in storage
     * @return string Path to the generated file
     */
    public function generate($participantName, $filename, $options = [])
    {
        $pdf = new Fpdi();
        
        // Path to the template
        $templatePath = public_path('certificate-template/qurbanhub-certificate.pdf');
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template certificate tidak ditemukan di: " . $templatePath);
        }

        // Add a page
        $pdf->AddPage('L'); // 'L' for Landscape, adjust if your PDF is Portrait

        // Set the source file
        $pdf->setSourceFile($templatePath);
        
        // Import page 1
        $tplIdx = $pdf->importPage(1);
        
        // Use the imported page as a template
        $pdf->useTemplate($tplIdx, 0, 0, null, null, true);

        // Opsi default
        $defaultOptions = [
            'x' => 0,
            'y' => 95,
            'font' => 'Helvetica',
            'style' => 'B',
            'size' => 38,
            'color' => [26, 77, 46], // Warna Hijau Gelap QurbanHub #1a4d2e
            'align' => 'C'
        ];
        $opts = array_merge($defaultOptions, $options);

        // --- STAMP NAME ---
        $pdf->SetFont($opts['font'], $opts['style'], $opts['size']);
        $pdf->SetTextColor($opts['color'][0], $opts['color'][1], $opts['color'][2]);
        
        // Mengatur posisi
        $pdf->SetXY($opts['x'], $opts['y']);
        $pdf->Cell($pdf->GetPageWidth(), 20, strtoupper($participantName), 0, 0, $opts['align']);

        // --- STAMP FLAG & COUNTRY ---
        if (!empty($opts['country_code'])) {
            $flagUrl = "https://flagcdn.com/w160/" . strtolower($opts['country_code']) . ".png";
            try {
                // Flag below name
                $pdf->Image($flagUrl, ($pdf->GetPageWidth() / 2) - 10, 128, 20);
                
                // Country name below flag
                $pdf->SetFont('Helvetica', '', 14);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->SetXY(0, 142);
                $pdf->Cell($pdf->GetPageWidth(), 10, strtoupper($opts['country']), 0, 0, 'C');
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        // --- SAVE FILE ---
        $storagePath = 'public/certificates/' . $filename;
        $fullPath = storage_path('app/' . $storagePath);
        
        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf->Output('F', $fullPath);

        return Storage::url($storagePath);
    }
}
