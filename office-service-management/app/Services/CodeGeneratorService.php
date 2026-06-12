<?php

namespace App\Services;

use App\Models\Person;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorSVG;

class CodeGeneratorService
{
    public function generateForPerson(Person $person): array
    {
        $qrPath = 'qr-codes/'.$person->person_code.'.svg';
        $barcodePath = 'barcodes/'.$person->person_code.'.svg';

        $qrCode = new QrCode($person->qr_code_value);
        $qr = (new SvgWriter())->write($qrCode)->getString();

        $barcode = (new BarcodeGeneratorSVG())->getBarcode(
            $person->barcode_value,
            BarcodeGeneratorSVG::TYPE_CODE_128
        );

        Storage::disk('public')->put($qrPath, $qr);
        Storage::disk('public')->put($barcodePath, $barcode);

        return [
            'qr_code_path' => $qrPath,
            'barcode_path' => $barcodePath,
        ];
    }
}
