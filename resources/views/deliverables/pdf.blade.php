<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $deliverable->title }}</title>
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

        /* ── Document header ── */
        .doc-header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .doc-header-left {
            display: table-cell;
            vertical-align: middle;
        }
        .doc-header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .brand-wordmark {
            font-size: 15px;
            font-weight: 900;
            color: #0055D4;
            letter-spacing: -0.03em;
        }
        .brand-wordmark span {
            color: #64748b;
            font-weight: 400;
        }
        .doc-meta {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 2px;
        }

        /* ── Blue rule ── */
        .rule {
            height: 2px;
            background: #0055D4;
            margin-bottom: 20px;
            border-radius: 2px;
        }
        .rule-thin {
            height: 1px;
            background: #e2e8f0;
            margin: 16px 0;
        }

        /* ── Breadcrumb context ── */
        .context {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 6px;
        }

        /* ── Deliverable title block ── */
        .title-block {
            margin-bottom: 14px;
        }
        .deliverable-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .badges {
            display: table;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-right: 6px;
        }
        .badge-type   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .badge-stage  { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-closed { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .deadline {
            display: inline-block;
            font-size: 9px;
            color: #64748b;
            margin-left: 4px;
        }

        /* ── Alert (revision) ── */
        .alert {
            background: #fff1f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #ef4444;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .alert-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #ef4444;
            margin-bottom: 3px;
        }
        .alert-body {
            font-size: 11px;
            color: #7f1d1d;
            line-height: 1.5;
        }

        /* ── Section heading ── */
        .section-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        /* ── Content block ── */
        .content-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 11px 13px;
            font-size: 11px;
            color: #334155;
            white-space: pre-wrap;
            line-height: 1.65;
            word-break: break-word;
        }

        /* ── Section ── */
        .section { margin-bottom: 16px; }

        /* ── 2-col grid (table-based for DomPDF) ── */
        .two-col { display: table; width: 100%; border-spacing: 12px 0; margin-bottom: 16px; }
        .two-col-cell { display: table-cell; width: 50%; vertical-align: top; }

        /* ── Images ── */
        .img-container {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .img-container img {
            max-width: 100%;
            max-height: 220px;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }
        .img-link {
            font-size: 10px;
            color: #0055D4;
            word-break: break-all;
        }
        .img-none {
            font-size: 10px;
            color: #cbd5e1;
            font-style: italic;
        }

        /* ── Full-width image ── */
        .img-full {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            margin-bottom: 16px;
        }
        .img-full img {
            max-width: 100%;
            max-height: 320px;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }

        /* ── Team grid ── */
        .team-grid { display: table; width: 100%; }
        .team-cell { display: table-cell; width: 25%; vertical-align: top; padding-right: 10px; }
        .team-role-label {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 2px;
        }
        .team-name-val {
            font-size: 11px;
            font-weight: 600;
            color: #1e293b;
        }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: table;
            width: 100%;
        }
        .doc-footer-left {
            display: table-cell;
            font-size: 8.5px;
            color: #cbd5e1;
        }
        .doc-footer-right {
            display: table-cell;
            text-align: right;
            font-size: 8.5px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
<div class="page">

    @php
        /* Convert storage URLs to base64 data URIs for reliable DomPDF rendering.
           Extracts the relative path after /storage/ so it works regardless of domain/port. */
        $imgSrc = function($url) {
            if (!$url) return null;
            if (!preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $url)) return null;

            // Extract relative path from any URL containing /storage/
            if (preg_match('#/storage/(.+)$#i', $url, $m)) {
                $abs = storage_path('app/public/' . $m[1]);
                if (file_exists($abs)) {
                    $ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
                    $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                             'gif'=>'image/gif','webp'=>'image/webp'][$ext] ?? 'image/jpeg';
                    return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($abs));
                }
            }
            // Absolute filesystem path fallback (already a path, not URL)
            if (file_exists($url)) {
                $ext  = strtolower(pathinfo($url, PATHINFO_EXTENSION));
                $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                         'gif'=>'image/gif','webp'=>'image/webp'][$ext] ?? 'image/jpeg';
                return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($url));
            }
            return null; // don't return the raw URL — DomPDF won't fetch it without remote enabled
        };

        $refImgSrc  = $imgSrc($deliverable->reference_file);
        $artImgSrc  = $imgSrc($deliverable->final_designs);
        $hasRefImg  = (bool) $refImgSrc;
        $hasArtImg  = (bool) $artImgSrc;
        $brand      = $deliverable->project->brand->name  ?? '';
        $project    = $deliverable->project->name         ?? '';
        $deadline   = $deliverable->deadline ? \Carbon\Carbon::parse($deliverable->deadline)->format('d M Y') : null;
        $exportedAt = \Carbon\Carbon::now()->format('d M Y');
        $stageName  = $deliverable->approval_stage ?? 'N/A';
        $isClosed   = strtolower($stageName) === 'closed';
    @endphp

    {{-- Document Header --}}
    <div class="doc-header">
        <div class="doc-header-left">
            <div class="brand-wordmark">Loops <span>Work</span></div>
            @if($brand || $project)
            <div class="doc-meta">{{ implode(' / ', array_filter([$brand, $project])) }}</div>
            @endif
        </div>
        <div class="doc-header-right">
            <div class="doc-meta">Deliverable Brief</div>
            <div class="doc-meta" style="margin-top:2px;">Exported {{ $exportedAt }}</div>
        </div>
    </div>

    <div class="rule"></div>

    {{-- Title Block --}}
    <div class="title-block">
        <div class="deliverable-title">{{ $deliverable->title }}</div>
        <div class="badges">
            @if($deliverable->post_type)
                <span class="badge badge-type">{{ $deliverable->post_type }}</span>
            @endif
            <span class="badge {{ $isClosed ? 'badge-closed' : 'badge-stage' }}">{{ $stageName }}</span>
            @if($deadline)
                <span class="deadline">Due {{ $deadline }}</span>
            @endif
        </div>
    </div>

    {{-- Revision Alert --}}
    @if($deliverable->revision_instructions)
    <div class="alert">
        <div class="alert-label">Revision Requested</div>
        <div class="alert-body">{{ $deliverable->revision_instructions }}</div>
    </div>
    @endif

    <div class="rule-thin"></div>

    {{-- Content Sections --}}
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
    @if($hasRefImg || $hasArtImg || $deliverable->reference || $deliverable->final_designs_link)
    <div class="rule-thin"></div>

    @if($hasRefImg && $hasArtImg)
        {{-- Both images — side by side --}}
        <div class="two-col">
            <div class="two-col-cell">
                <div class="section-label">Reference</div>
                <div class="img-container">
                    <img src="{{ $refImgSrc }}" alt="Reference">
                </div>
            </div>
            <div class="two-col-cell">
                <div class="section-label">Artwork</div>
                <div class="img-container">
                    <img src="{{ $artImgSrc }}" alt="Artwork">
                </div>
            </div>
        </div>
    @elseif($hasRefImg)
        <div class="section">
            <div class="section-label">Reference</div>
            <div class="img-full">
                <img src="{{ $refImgSrc }}" alt="Reference">
            </div>
        </div>
    @elseif($hasArtImg)
        <div class="section">
            <div class="section-label">Artwork</div>
            <div class="img-full">
                <img src="{{ $artImgSrc }}" alt="Artwork">
            </div>
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
        <div class="section-label" style="margin-bottom:8px;">Team</div>
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
        <div class="doc-footer-right">loops.work · Confidential</div>
    </div>

</div>
</body>
</html>
