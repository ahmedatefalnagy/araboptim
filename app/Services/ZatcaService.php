<?php

namespace App\Services;

class ZatcaService
{
    /**
     * Generate TLV Base64 QR Code string for ZATCA Phase 1 & 2
     *
     * @param string $sellerName
     * @param string $vatRegistrationNumber
     * @param string $timestamp
     * @param float $invoiceTotal
     * @param float $vatTotal
     * @return string
     */
    public function generateQrCodeBase64($sellerName, $vatRegistrationNumber, $timestamp, $invoiceTotal, $vatTotal)
    {
        $tlvData = $this->buildTLV(1, (string) $sellerName) .
            $this->buildTLV(2, (string) $vatRegistrationNumber) .
            $this->buildTLV(3, (string) $timestamp) .
            $this->buildTLV(4, number_format((float)$invoiceTotal, 2, '.', '')) .
            $this->buildTLV(5, number_format((float)$vatTotal, 2, '.', ''));

        return base64_encode($tlvData);
    }

    private function buildTLV($tag, $value)
    {
        return chr($tag) . chr(strlen($value)) . $value;
    }

    /**
     * Generate UBL 2.1 XML for ZATCA Phase 2
     */
    public function generateInvoiceXml($invoice)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">' . "\n";
        $xml .= '  <ID>' . $invoice->invoice_no . '</ID>' . "\n";
        $xml .= '  <IssueDate>' . $invoice->invoice_date->format('Y-m-d') . '</IssueDate>' . "\n";
        $xml .= '  <DocumentCurrencyCode>SAR</DocumentCurrencyCode>' . "\n";
        $xml .= '</Invoice>';

        return $xml;
    }

    /**
     * Generate SHA256 Hash of the XML string Base64
     */
    public function generateXmlHash($xmlString)
    {
        return base64_encode(hash('sha256', $xmlString, true));
    }
}
