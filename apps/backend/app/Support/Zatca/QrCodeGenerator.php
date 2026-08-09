<?php

namespace App\Support\Zatca;

/**
 * مولّد QR مبسّط بصيغة ZATCA Phase 1 (Base64-encoded TLV).
 *
 * الحقول الخمسة الإلزامية: اسم البائع، الرقم الضريبي، الطابع الزمني،
 * إجمالي الفاتورة شامل الضريبة، إجمالي الضريبة.
 *
 * ملاحظة: هذا تنفيذ Phase 1 (بدون الختم المشفّر / التوقيع الرقمي CSID
 * المطلوب في Phase 2 - Integration). ربط Phase 2 الفعلي مع بوابة فاتورة
 * يحتاج شهادة CSID من ZATCA وتوقيع XML/UBL منفصل، ويُضاف لاحقاً في
 * integrations/zatca.
 */
class QrCodeGenerator
{
    public static function generate(
        string $sellerName,
        string $vatNumber,
        string $timestamp,
        string $totalWithVat,
        string $vatTotal
    ): string {
        $tlv = self::tlv(1, $sellerName)
            .self::tlv(2, $vatNumber)
            .self::tlv(3, $timestamp)
            .self::tlv(4, $totalWithVat)
            .self::tlv(5, $vatTotal);

        return base64_encode($tlv);
    }

    private static function tlv(int $tag, string $value): string
    {
        return chr($tag).chr(strlen($value)).$value;
    }
}
