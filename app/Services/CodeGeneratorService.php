<?php

namespace App\Services;

use App\Models\Person;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorSVG;
use RuntimeException;

class CodeGeneratorService
{
    public function generateForPerson(Person $person): array
    {
        $qrValue = $person->qr_code_value ?: $person->person_code;
        $barcodeValue = $person->barcode_value ?: str_replace('-', '', $person->person_code);
        $qrPath = 'qr-codes/'.$person->person_code.'.svg';
        $barcodePath = 'barcodes/'.$person->person_code.'.svg';

        $qrCode = new QrCode($qrValue);
        $qr = (new SvgWriter)->write($qrCode)->getString();

        $barcode = (new BarcodeGeneratorSVG)->getBarcode(
            $barcodeValue,
            BarcodeGeneratorSVG::TYPE_CODE_128
        );

        $disk = Storage::disk('public');

        if (! $disk->put($qrPath, $qr) || ! $disk->put($barcodePath, $barcode)) {
            throw new RuntimeException('Unable to store the generated QR code or barcode.');
        }

        return [
            'qr_code_value' => $qrValue,
            'barcode_value' => $barcodeValue,
            'qr_code_path' => $qrPath,
            'barcode_path' => $barcodePath,
        ];
    }
}
