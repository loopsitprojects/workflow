<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $parent->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.55;
            background: #fff;
        }

        /* ── Page layout ── */
        .page {
            padding: 32px 40px 40px;
            max-width: 780px;
            margin: 0 auto;
        }
        .page-break { page-break-after: always; }

        /* ── Cover page ── */
        .cover {
            padding: 60px 40px 40px;
            min-height: 720px;
            display: block;
        }
        .cover-brand {
            font-size: 13px;
            font-weight: 900;
            color: #0055D4;
            letter-spacing: -0.02em;
            margin-bottom: 2px;
        }
        .cover-brand span { color: #94a3b8; font-weight: 400; }
        .cover-rule {
            height: 3px;
            background: #0055D4;
            margin: 18px 0 32px;
            border-radius: 2px;
            width: 50px;
        }
        .cover-context {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .cover-title {
            font-size: 28px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .cover-meta {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .cover-meta strong { color: #334155; }
        .cover-footer {
            position: absolute;
            bottom: 40px;
            left: 40px;
            right: 40px;
        }
        .cover-footer-rule { height: 1px; background: #e2e8f0; margin-bottom: 10px; }
        .cover-footer-text {
            font-size: 8.5px;
            color: #cbd5e1;
            display: table;
            width: 100%;
        }
        .cover-footer-left  { display: table-cell; }
        .cover-footer-right { display: table-cell; text-align: right; }

        /* ── Document header (repeated per page) ── */
        .doc-header {
            display: table;
            width: 100%;
            margin-bottom: 18px;
        }
        .doc-header-left  { display: table-cell; vertical-align: middle; }
        .doc-header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .brand-wordmark {
            font-size: 13px;
            font-weight: 900;
            color: #0055D4;
            letter-spacing: -0.02em;
        }
        .brand-wordmark span { color: #64748b; font-weight: 400; }
        .doc-meta {
            font-size: 8.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 1px;
        }
        .post-counter {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: 700;
        }

        /* ── Rules ── */
        .rule      { height: 2px; background: #0055D4; margin-bottom: 18px; border-radius: 2px; }
        .rule-thin { height: 1px; background: #e2e8f0; margin: 14px 0; }

        /* ── Title block ── */
        .title-block { margin-bottom: 12px; }
        .deliverable-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 7px;
            line-height: 1.2;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-right: 5px;
        }
        .badge-type   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-stage  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-closed { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .deadline { display: inline-block; font-size: 9px; color: #64748b; margin-left: 3px; }

        /* ── Alert ── */
        .alert {
            background: #fff1f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #ef4444;
            border-radius: 6px;
            padding: 9px 13px;
            margin-bottom: 14px;
        }
        .alert-label {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #ef4444;
            margin-bottom: 2px;
        }
        .alert-body { font-size: 10.5px; color: #7f1d1d; line-height: 1.5; }

        /* ── Section heading ── */
        .section-label {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        /* ── Content block ── */
        .content-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 10px 12px;
            font-size: 10.5px;
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.6;
            word-break: break-word;
        }
        .section { margin-bottom: 14px; }

        /* ── 2-col grid ── */
        .two-col { display: table; width: 100%; border-spacing: 10px 0; margin-bottom: 14px; }
        .two-col-cell { display: table-cell; width: 50%; vertical-align: top; }

        /* ── Images ── */
        .img-container {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
        }
        .img-container img { max-width: 100%; max-height: 200px; border-radius: 3px; display: block; margin: 0 auto; }
        .img-link  { font-size: 9.5px; color: #0055D4; word-break: break-all; }
        .img-none  { font-size: 9.5px; color: #cbd5e1; font-style: italic; }
        .img-full  {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
            margin-bottom: 14px;
        }
        .img-full img { max-width: 100%; max-height: 280px; border-radius: 3px; display: block; margin: 0 auto; }

        /* ── Team ── */
        .team-grid  { display: table; width: 100%; }
        .team-cell  { display: table-cell; width: 25%; vertical-align: top; padding-right: 8px; }
        .team-role-label {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 1px;
        }
        .team-name-val { font-size: 10.5px; font-weight: 600; color: #1e293b; }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: table;
            width: 100%;
        }
        .doc-footer-left  { display: table-cell; font-size: 8px; color: #cbd5e1; }
        .doc-footer-right { display: table-cell; text-align: right; font-size: 8px; color: #cbd5e1; }
    </style>
</head>
<body>

@php
    /* Convert storage URLs to base64 data URIs for reliable DomPDF rendering.
       Extracts the relative path after /storage/ so it works regardless of domain/port. */
    if (!function_exists('batchPdfImgSrc')) {
        function batchPdfImgSrc($url) {
            if (!$url) return null;
            if (!preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $url)) return null;
            $toDataUri = function($abs) {
                $ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
                $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                         'gif'=>'image/gif','webp'=>'image/webp'][$ext] ?? 'image/jpeg';
                return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($abs));
            };
            // New storage: /references/..., /artwork/..., /brand_logos/..., /briefs/...
            if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
                $abs = public_path(ltrim($url, '/'));
                if (file_exists($abs)) return $toDataUri($abs);
            }
            // Legacy storage: /storage/...
            if (preg_match('#/storage/(.+)$#i', $url, $m)) {
                $abs = storage_path('app/public/' . $m[1]);
                if (file_exists($abs)) return $toDataUri($abs);
            }
            // Absolute filesystem path fallback
            if (file_exists($url)) return $toDataUri($url);
            return null;
        }
    }

    $brand      = $parent->project->brand->name ?? '';
    $project    = $parent->project->name ?? '';
    $exportedAt = \Carbon\Carbon::now()->format('d M Y');
    $totalPosts = count($deliverables);
@endphp

{{-- ══ COVER PAGE ══ --}}
<div class="cover" style="position:relative;">
    <div class="cover-brand">Loops <span>Work</span></div>

    @if($brand || $project)
    <div class="cover-rule"></div>
    <div class="cover-context">{{ implode(' / ', array_filter([$brand, $project])) }}</div>
    @else
    <div class="cover-rule"></div>
    @endif

    <div class="cover-title">{{ $parent->title }}</div>

    <div class="cover-meta">
        <strong>{{ $totalPosts }}</strong> {{ $totalPosts === 1 ? 'post' : 'posts' }} in this batch
    </div>
    @if($parent->deadline)
    <div class="cover-meta">
        Due <strong>{{ \Carbon\Carbon::parse($parent->deadline)->format('d M Y') }}</strong>
    </div>
    @endif
    <div class="cover-meta" style="margin-top:6px;">Exported {{ $exportedAt }}</div>

    <div class="cover-footer">
        <div class="cover-footer-rule"></div>
        <div class="cover-footer-text">
            <div class="cover-footer-left">{{ $brand ? $brand . ' · ' : '' }}Deliverable Batch</div>
            <div class="cover-footer-right">loops.work · Confidential</div>
        </div>
    </div>
</div>

{{-- ══ PER-DELIVERABLE PAGES ══ --}}
@foreach($deliverables as $index => $deliverable)
<div class="page-break"></div>
<div class="page">

    @php
        $refImgSrc  = batchPdfImgSrc($deliverable->reference_file);
        $artImgSrc  = batchPdfImgSrc($deliverable->final_designs);
        $hasRefImg  = (bool) $refImgSrc;
        $hasArtImg  = (bool) $artImgSrc;
        $deadline   = $deliverable->deadline ? \Carbon\Carbon::parse($deliverable->deadline)->format('d M Y') : null;
        $stageName  = $deliverable->approval_stage ?? 'N/A';
        $isClosed   = strtolower($stageName) === 'closed';
    @endphp

    {{-- Page header --}}
    <div class="doc-header">
        <div class="doc-header-left">
            <div class="brand-wordmark">Loops <span>Work</span></div>
            @if($brand || $project)
            <div class="doc-meta">{{ implode(' / ', array_filter([$brand, $project])) }}</div>
            @endif
        </div>
        <div class="doc-header-right">
            <span class="post-counter">Post {{ $index + 1 }} of {{ $totalPosts }}</span>
        </div>
    </div>

    <div class="rule"></div>

    {{-- Title block --}}
    <div class="title-block">
        <div class="deliverable-title">{{ $deliverable->title }}</div>
        <div>
            @if($deliverable->post_type)
                <span class="badge badge-type">{{ $deliverable->post_type }}</span>
            @endif
            <span class="badge {{ $isClosed ? 'badge-closed' : 'badge-stage' }}">{{ $stageName }}</span>
            @if($deadline)
                <span class="deadline">Due {{ $deadline }}</span>
            @endif
        </div>
    </div>

    {{-- Revision alert --}}
    @if($deliverable->revision_instructions)
    <div class="alert">
        <div class="alert-label">Revision Requested</div>
        <div class="alert-body">{{ $deliverable->revision_instructions }}</div>
    </div>
    @endif

    <div class="rule-thin"></div>

    {{-- Content sections --}}
    @if($deliverable->concept)
    <div class="section">
        <div class="section-label">Concept</div>
        <div class="content-block">{{ $deliverable->concept }}</div>
    </div>
    @endif

    @if($deliverable->caption)
    <div class="section">
        <div class="section-label">Caption</div>
        <div class="content-block">{{ $deliverable->caption }}</div>
    </div>
    @endif

    @if($deliverable->post_copy)
    <div class="section">
        <div class="section-label">Post Copy</div>
        <div class="content-block">{{ $deliverable->post_copy }}</div>
    </div>
    @endif

    @if($deliverable->notes)
    <div class="section">
        <div class="section-label">Notes</div>
        <div class="content-block">{{ $deliverable->notes }}</div>
    </div>
    @endif

    {{-- Media --}}
    @if($hasRefImg || $hasArtImg || $deliverable->reference || $deliverable->final_designs_link || $deliverable->reference_file || $deliverable->final_designs)
    <div class="rule-thin"></div>

    @if($hasRefImg && $hasArtImg)
        <div class="two-col">
            <div class="two-col-cell">
                <div class="section-label">Reference</div>
                <div class="img-container"><img src="{{ $refImgSrc }}" alt="Reference"></div>
            </div>
            <div class="two-col-cell">
                <div class="section-label">Artwork</div>
                <div class="img-container"><img src="{{ $artImgSrc }}" alt="Artwork"></div>
            </div>
        </div>
    @elseif($hasRefImg)
        <div class="section">
            <div class="section-label">Reference</div>
            <div class="img-full"><img src="{{ $refImgSrc }}" alt="Reference"></div>
        </div>
    @elseif($hasArtImg)
        <div class="section">
            <div class="section-label">Artwork</div>
            <div class="img-full"><img src="{{ $artImgSrc }}" alt="Artwork"></div>
        </div>
    @else
        <div class="two-col">
            <div class="two-col-cell">
                <div class="section-label">Reference</div>
                <div class="img-container">
                    @if($deliverable->reference)
                        <div class="img-link">{{ $deliverable->reference }}</div>
                    @elseif($deliverable->reference_file)
                        <div class="img-link">{{ $deliverable->reference_file }}</div>
                    @else
                        <div class="img-none">None provided</div>
                    @endif
                </div>
            </div>
            <div class="two-col-cell">
                <div class="section-label">Artwork</div>
                <div class="img-container">
                    @if($deliverable->final_designs_link)
                        <div class="img-link">{{ $deliverable->final_designs_link }}</div>
                    @elseif($deliverable->final_designs)
                        <div class="img-link">{{ $deliverable->final_designs }}</div>
                    @else
                        <div class="img-none">Pending</div>
                    @endif
                </div>
            </div>
        </div>
    @endif
    @endif

    {{-- Team --}}
    <div class="rule-thin"></div>
    <div class="section">
        <div class="section-label" style="margin-bottom:7px;">Team</div>
        <div class="team-grid">
            <div class="team-cell">
                <div class="team-role-label">Writer</div>
                <div class="team-name-val">{{ $deliverable->writer->name ?? '—' }}</div>
            </div>
            <div class="team-cell">
                <div class="team-role-label">Approver</div>
                <div class="team-name-val">{{ $deliverable->approver->name ?? '—' }}</div>
            </div>
            <div class="team-cell">
                <div class="team-role-label">Brand Manager</div>
                <div class="team-name-val">{{ $deliverable->brandManager->name ?? '—' }}</div>
            </div>
            <div class="team-cell">
                <div class="team-role-label">Designer</div>
                <div class="team-name-val">{{ $deliverable->designer->name ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="doc-footer">
        <div class="doc-footer-left">{{ $brand ? $brand . ' · ' : '' }}{{ $deliverable->title }}</div>
        <div class="doc-footer-right">Post {{ $index + 1 }} of {{ $totalPosts }} · loops.work</div>
    </div>

</div>
@endforeach

</body>
</html>
