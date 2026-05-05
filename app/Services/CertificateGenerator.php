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
    public function generate($participantName, $filename)
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

        // --- STAMP NAME ---
        // Penyesuaian font: Helvetica Bold, Ukuran 38
        $pdf->SetFont('Helvetica', 'B', 38);
        $pdf->SetTextColor(26, 77, 46); // Warna Hijau Gelap QurbanHub #1a4d2e
        
        // Menggunakan Cell untuk Center Aligment otomatis secara horizontal
        // Y = 95 adalah perkiraan posisi baris nama pada sertifikat landscape
        $pdf->SetXY(0, 95);
        $pdf->Cell($pdf->GetPageWidth(), 20, strtoupper($participantName), 0, 0, 'C');

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
