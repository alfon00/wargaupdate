@php
    use App\Support\LetterPreviewHtml;

    $letterStyles = LetterPreviewHtml::extractStyles($fullHtml);
    $letterBody = LetterPreviewHtml::extractFragment($fullHtml);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pratinjau surat — {{ $application->application_number }}</title>
    @if($letterStyles !== '')
        <style>{!! $letterStyles !!}</style>
    @endif
    <style>
        html, body { margin: 0; padding: 0; background: #f1f5f9; color: #111; }
        .lw-letter-tab-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .75rem 1rem;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
        }
        .lw-letter-tab-toolbar-text { margin: 0; font-size: .8125rem; color: #64748b; line-height: 1.4; }
        .lw-letter-tab-toolbar-text strong { color: #0f172a; font-weight: 600; }
        .lw-letter-tab-toolbar-actions { display: flex; gap: .5rem; flex-shrink: 0; flex-wrap: wrap; }
        .lw-letter-tab-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .4rem .85rem;
            font-size: .8125rem;
            font-weight: 500;
            line-height: 1.25;
            color: #334155;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            text-decoration: none;
        }
        .lw-letter-tab-back:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .lw-letter-tab-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .4rem .85rem;
            font-size: .8125rem;
            font-weight: 600;
            line-height: 1.25;
            color: #fff;
            background: #2563eb;
            border: 1px solid #1d4ed8;
            border-radius: .5rem;
            cursor: pointer;
        }
        .lw-letter-tab-print:hover { background: #1d4ed8; }
        .lw-letter-tab-document {
            max-width: 210mm;
            margin: 1rem auto 2rem;
            padding: 12mm 15mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(15, 23, 42, .08);
        }
        @media print {
            html, body { background: #fff; }
            .lw-letter-tab-toolbar { display: none !important; }
            .lw-letter-tab-document {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            body { margin: 36px 48px; }
        }
    </style>
</head>
<body>
    <header class="lw-letter-tab-toolbar" aria-label="Alat pratinjau surat">
        <p class="lw-letter-tab-toolbar-text">
            <strong>Pratinjau surat</strong> — {{ $application->application_number }}
            <span aria-hidden="true">·</span> Bukan PDF resmi
        </p>
        <div class="lw-letter-tab-toolbar-actions">
            <a href="{{ route('rt.applications.letter.compose', $application) }}" class="lw-letter-tab-back">
                ← Kembali ke susun surat
            </a>
            <button type="button" class="lw-letter-tab-print" onclick="window.print()">Cetak</button>
        </div>
    </header>
    <main class="lw-letter-tab-document" role="document" aria-label="Pratinjau surat pengantar">
        {!! $letterBody !!}
    </main>
</body>
</html>
