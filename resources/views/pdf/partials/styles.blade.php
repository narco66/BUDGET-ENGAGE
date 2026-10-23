<style>
    * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
    body { color: #1E293B; font-size: 11px; margin: 0; padding: 0 12mm; }
    .pdf-entete { text-align: center; border-bottom: 2px solid #1E3A8A; padding-bottom: 10px; margin-bottom: 14px; }
    .pdf-entete h1 { color: #1E3A8A; font-size: 17px; margin: 0 0 4px; }
    .pdf-entete .org { color: #64748B; font-size: 10px; margin: 0; }
    .pdf-entete .meta-line { color: #94A3B8; font-size: 9px; margin-top: 6px; }
    .pdf-meta-bar {
        display: table; width: 100%; margin-bottom: 12px; font-size: 9px; color: #64748B;
        border: 1px solid #E2E8F0; border-radius: 4px; padding: 6px 8px;
    }
    .pdf-meta-bar span { margin-right: 16px; }
    h2.pdf-section {
        color: #1E3A8A; font-size: 12px; margin: 14px 0 6px;
        border-bottom: 1px solid #E2E8F0; padding-bottom: 3px;
    }
    table.pdf-meta { width: 100%; border-collapse: collapse; }
    table.pdf-meta td { padding: 4px 6px; vertical-align: top; }
    table.pdf-meta td.label { color: #64748B; width: 24%; }
    table.pdf-data { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.pdf-data th {
        background: #1E3A8A; color: #fff; text-align: left;
        padding: 5px 6px; font-size: 9px;
    }
    table.pdf-data td { padding: 4px 6px; border-bottom: 1px solid #E2E8F0; font-size: 9px; }
    table.pdf-data tr.total td { font-weight: bold; background: #EFF6FF; }
    .pdf-right { text-align: right; }
    .pdf-montant {
        margin-top: 14px; padding: 10px; background: #1E3A8A; color: #fff;
        text-align: center; font-size: 13px; font-weight: bold; border-radius: 6px;
    }
    .pdf-sceau {
        margin-top: 16px; padding: 10px; border: 1px dashed #1E3A8A; border-radius: 6px;
        font-size: 9px; color: #334155; width: 100%; border-collapse: collapse;
    }
    .pdf-sceau .num { font-family: monospace; font-weight: bold; }
    .pdf-signatures { margin-top: 28px; width: 100%; border-collapse: collapse; }
    .pdf-signatures td {
        width: 33%; text-align: center; font-size: 10px; color: #64748B;
        padding-top: 36px; border-top: 1px solid #94A3B8; vertical-align: top;
    }
    .pdf-pied {
        position: fixed; bottom: 8mm; left: 12mm; right: 12mm;
        font-size: 8px; color: #94A3B8; border-top: 1px solid #E2E8F0; padding-top: 4px;
    }
    .pdf-pied-left { float: left; }
    .pdf-pied-right { float: right; text-align: right; }
    .pdf-qr { float: right; margin-left: 10px; }
    .pdf-clear { clear: both; }

    /* En-tête institutionnel CEEAC réutilisable (documents auto-portants) */
    .ceeac-head { width: 100%; border-collapse: collapse; border-bottom: 2px solid #1E3A8A; padding-bottom: 6px; margin-bottom: 12px; }
    .ceeac-head td { vertical-align: top; padding: 0 6px; }
    .ceeac-head .logo { width: 66px; }
    .ceeac-head .logo img { width: 58px; height: 58px; }
    .ceeac-org { font-size: 8.5px; color: #1E3A8A; font-weight: bold; line-height: 1.25; }
    .ceeac-org small { display: block; color: #64748B; font-weight: normal; font-size: 7.5px; margin-top: 2px; }
    .ceeac-center { text-align: center; }
    .ceeac-center .app { color: #1E3A8A; font-size: 15px; font-weight: bold; letter-spacing: .5px; }
    .ceeac-center .sys { color: #64748B; font-size: 7.5px; margin: 1px 0 5px; }
    .ceeac-doctitle { display: inline-block; background: #1E3A8A; color: #fff; font-size: 10.5px; font-weight: bold; padding: 4px 12px; border-radius: 5px; }
    .ceeac-metabox { width: 195px; border: 1px solid #E2E8F0; border-radius: 5px; border-collapse: separate; font-size: 7.5px; }
    .ceeac-metabox td { padding: 3px 6px; border-bottom: 1px solid #EEF2F7; }
    .ceeac-metabox td.k { color: #64748B; }
    .ceeac-metabox td.v { text-align: right; font-weight: bold; color: #1E293B; }
    .ceeac-badge { display: inline-block; color: #fff; font-size: 7px; font-weight: bold; padding: 1px 7px; border-radius: 9px; }
    .ceeac-pied { position: fixed; bottom: 6mm; left: 12mm; right: 12mm; font-size: 7px; color: #94A3B8; border-top: 1px solid #E2E8F0; padding-top: 3px; }
    .ceeac-pied .l { float: left; }
    .ceeac-pied .r { float: right; }
    .ceeac-pied .r:after { content: "Page " counter(page) " sur " counter(pages); }

    @page { margin: 15mm 12mm 20mm 12mm; }
</style>
