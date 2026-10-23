<?php

namespace App\Support;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Génération de QR codes pour les documents PDF (certificats, mandats, ordres de
 * paiement). Format SVG (aucune extension PHP requise — compatible DomPDF), à
 * embarquer inline dans les vues. Le contenu encode un sceau de vérification :
 * module | type | numéro | empreinte d'intégrité.
 */
class DocumentQrCode
{
    /**
     * Retourne le balisage SVG d'un QR code prêt à être embarqué dans un PDF.
     */
    public static function svg(string $data, int $size = 110): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(0)
            ->errorCorrection('M')
            ->generate($data);
    }

    /**
     * Balise <img> avec data-URI SVG — rendu fiable par DomPDF (aucune extension
     * requise). À insérer dans les vues PDF via {!! ... !!}.
     */
    public static function imgTag(string $data, int $size = 110): string
    {
        $uri = 'data:image/svg+xml;base64,'.base64_encode(self::svg($data, $size));

        return '<img src="'.$uri.'" width="'.$size.'" height="'.$size.'" alt="QR" />';
    }

    /**
     * Construit la charge utile de vérification standardisée d'un document.
     */
    public static function payload(string $type, string $numero, ?string $hash): string
    {
        return implode('|', ['CEEAC', $type, $numero, $hash ?? '']);
    }
}
