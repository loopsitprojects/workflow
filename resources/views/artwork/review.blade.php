<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Artwork Review — {{ $review->deliverable->title ?? 'Review' }}</title>
    <meta name="description" content="Review and annotate the final artwork sent to you.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Fabric.js for canvas annotations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0d1117;
            --surface:  #161b22;
            --border:   rgba(255,255,255,0.08);
            --text:     #e6edf3;
            --muted:    #8b949e;
            --accent:   #238636;
            --red:      #ef4444;
            --blue:     #3b82f6;
            --purple:   #8b5cf6;
            --yellow:   #f59e0b;
            --radius:   14px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top Bar ─────────────────────────────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 60px;
            background: rgba(22, 27, 34, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 1000;
            gap: 16px;
        }

        .topbar-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 240px;
        }

        .topbar-title span {
            color: var(--muted);
            font-weight: 400;
            margin-right: 6px;
        }

        /* ── Toolbar ─────────────────────────────────────────────────────── */
        .toolbar {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 6px 10px;
        }

        .tool-btn {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            border: 1.5px solid transparent;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            font-size: 15px;
        }

        .tool-btn:hover { background: rgba(255,255,255,0.07); color: var(--text); }
        .tool-btn.active { background: rgba(59,130,246,0.15); border-color: rgba(59,130,246,0.4); color: #3b82f6; }
        .tool-separator { width: 1px; height: 22px; background: var(--border); margin: 0 2px; }

        .color-swatch {
            width: 20px; height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform 0.1s;
        }
        .color-swatch.selected { border-color: #fff; transform: scale(1.2); }

        /* ── Submit button ───────────────────────────────────────────────── */
        .btn-submit {
            padding: 9px 22px;
            background: linear-gradient(135deg, #238636, #2ea043);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(35,134,54,0.4);
        }
        .btn-submit:hover { background: linear-gradient(135deg, #2ea043, #3fb950); transform: translateY(-1px); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* ── Multi-artwork Carousel & Switcher ─────────────────────────── */
        .slide-nav-group {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 3px 8px;
        }
        .slide-nav-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        .slide-nav-btn:hover { background: rgba(255,255,255,0.22); color: #38bdf8; }
        .slide-nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .artwork-carousel-bar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(18, 24, 38, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 16px;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            box-shadow: 0 12px 32px rgba(0,0,0,0.7);
            max-width: 90vw;
            overflow-x: auto;
        }
        .slide-thumb-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
            user-select: none;
            flex-shrink: 0;
        }
        .slide-thumb-card:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .slide-thumb-card.active {
            background: rgba(59, 130, 246, 0.22);
            border-color: #3b82f6;
            box-shadow: 0 0 16px rgba(59, 130, 246, 0.4);
        }
        .slide-thumb-img {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            object-fit: cover;
            background: #000;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .slide-thumb-badge {
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 999px;
        }

        /* ── Canvas Area ─────────────────────────────────────────────────── */
        .canvas-area {
            margin-top: 60px;
            margin-bottom: 90px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 24px;
            overflow: auto;
        }

        .artwork-wrapper {
            position: relative;
            display: inline-block;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06);
            max-width: 100%;
        }

        #artwork-img {
            display: block;
            max-width: 100%;
            height: auto;
            max-height: calc(100vh - 180px);
            object-fit: contain;
            border-radius: 16px;
            /* Prevent the browser from allowing the image to be dragged */
            user-select: none;
            -webkit-user-drag: none;
            pointer-events: none;      /* canvas sits on top; image should not capture events */
            draggable: false;
        }

        #annotation-canvas {
            position: absolute;
            top: 0; left: 0;
            border-radius: 16px;
            z-index: 2;               /* must be above the image */
            cursor: crosshair;
        }

        /* ── Pins (DOM-based for text bubbles) ───────────────────────────── */
        .pin-marker {
            position: absolute;
            transform: translate(-50%, -100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: default;
            z-index: 10;
            pointer-events: auto;
        }

        .pin-circle {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            color: #fff;
            border: 2.5px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            cursor: pointer;
            transition: transform 0.15s;
        }

        .pin-circle:hover { transform: scale(1.15); }

        .pin-tail {
            width: 2px; height: 10px;
            margin-top: -1px;
            border-radius: 2px;
        }

        .pin-comment {
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #1c2128;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            color: var(--text);
            white-space: nowrap;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            z-index: 20;
            pointer-events: none;
        }

        .pin-marker:hover .pin-comment { display: block; }

        .pin-delete {
            position: absolute;
            top: -6px; right: -6px;
            width: 16px; height: 16px;
            border-radius: 50%;
            background: #ef4444;
            border: none;
            color: #fff;
            font-size: 8px;
            font-weight: 900;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            line-height: 1;
        }

        .pin-marker:hover .pin-delete { display: flex; }

        /* ── Side Panel — comment list ───────────────────────────────────── */
        .side-panel {
            position: fixed;
            top: 60px;
            right: 0;
            width: 300px;
            height: calc(100vh - 60px);
            background: var(--surface);
            border-left: 1px solid var(--border);
            overflow-y: auto;
            z-index: 900;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .side-panel.open { transform: translateX(0); }

        .panel-header {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .panel-header h3 { font-size: 13px; font-weight: 700; }

        .panel-toggle {
            position: fixed;
            top: 70px;
            right: 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 8px 10px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            z-index: 950;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .panel-toggle:hover { color: var(--text); }

        .annotation-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .annotation-num {
            width: 24px; height: 24px;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #fff;
        }

        .annotation-text { font-size: 12px; color: var(--text); line-height: 1.5; }

        /* ── Modals ──────────────────────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s;
        }

        .modal-overlay.active { opacity: 1; pointer-events: all; }

        .modal-box {
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 28px;
            width: 360px;
            max-width: 95vw;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
            transform: scale(0.95);
            transition: transform 0.25s;
        }

        .modal-overlay.active .modal-box { transform: scale(1); }

        .modal-title {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .modal-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 12px 14px;
            font-family: inherit;
            font-size: 13px;
            color: var(--text);
            resize: vertical;
            margin-bottom: 14px;
            outline: none;
            transition: border-color 0.15s;
        }

        .modal-input:focus { border-color: rgba(59,130,246,0.4); }

        .modal-actions { display: flex; gap: 10px; }

        .btn-primary {
            flex: 1;
            padding: 11px;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-primary:hover { background: #2563eb; }

        .btn-ghost {
            padding: 11px 16px;
            background: transparent;
            color: var(--muted);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-ghost:hover { color: var(--text); background: rgba(255,255,255,0.05); }

        /* ── Name capture modal ──────────────────────────────────────────── */
        #nameModal { z-index: 3000; }
        #nameModal .modal-box {
            text-align: center;
        }

        .brand-logo {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #238636, #3b82f6);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            font-size: 20px;
        }

        /* ── Success toast ───────────────────────────────────────────────── */
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: #238636;
            color: #fff;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            box-shadow: 0 8px 30px rgba(35,134,54,0.4);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 5000;
        }

        .toast.show { transform: translateX(-50%) translateY(0); }

        /* ── Undo hint ───────────────────────────────────────────────────── */
        .hint {
            position: fixed;
            bottom: 24px;
            left: 24px;
            font-size: 11px;
            color: var(--muted);
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px 12px;
            z-index: 50;
        }

        /* Mobile */
        @media (max-width: 600px) {
            .topbar-title { max-width: 120px; }
            .canvas-area { padding: 12px; margin-bottom: 90px; }
        }
    </style>
</head>
<body>

{{-- ── Top Bar ──────────────────────────────────────────────────────────── --}}
<header class="topbar">
    <div style="display:flex; align-items:center; gap:10px;">
        <div class="topbar-title">
            <span>Proofing:</span> {{ $review->deliverable->title ?? 'Artwork' }}
        </div>
        <span style="font-size:11px; font-weight:800; background:rgba(59,130,246,0.15); color:#38bdf8; border:1px solid rgba(59,130,246,0.3); padding:3px 10px; border-radius:8px;">
            {{ $review->deliverable->brand->name ?? 'Live' }}
        </span>

        @if($review->deliverable?->updated_at)
            <span style="font-size:10px; font-weight:700; color:var(--text-muted); background:rgba(255,255,255,0.06); border:1px solid var(--border); padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;" title="Last updated: {{ $review->deliverable->updated_at->format('M d, Y h:i A') }}">
                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Artwork updated {{ $review->deliverable->updated_at->diffForHumans() }}
            </span>
        @endif

        @php
            $artworksList = !empty($artworks) ? $artworks : [];
            if (empty($artworksList) && $review->deliverable) {
                $artworksList = $review->deliverable->getAllArtworkFiles();
            }
            $totalArtworks = count($artworksList);
        @endphp

        @if($totalArtworks > 1)
            <div class="slide-nav-group">
                <button class="slide-nav-btn" onclick="prevSlide()" title="Previous Slide (←)">◀</button>
                <span id="topSlideIndicator" style="font-size:11px; font-weight:800; color:var(--text); white-space:nowrap; padding:0 4px;">
                    Slide 1 of {{ $totalArtworks }}
                </span>
                <button class="slide-nav-btn" onclick="nextSlide()" title="Next Slide (→)">▶</button>
            </div>
        @endif
    </div>

    <div class="toolbar">
        {{-- Pin --}}
        <button class="tool-btn active" id="btnPin" title="Pin Comment (P)" onclick="setMode('pin')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </button>

        {{-- Draw --}}
        <button class="tool-btn" id="btnDraw" title="Draw (D)" onclick="setMode('draw')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </button>

        {{-- Text --}}
        <button class="tool-btn" id="btnText" title="Text (T)" onclick="setMode('text')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </button>

        <div class="tool-separator"></div>

        {{-- Undo --}}
        <button class="tool-btn" title="Undo (Ctrl+Z)" onclick="undo()">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6"/>
            </svg>
        </button>

        {{-- Clear --}}
        <button class="tool-btn" title="Clear this slide" onclick="clearAll()" style="color:var(--red)">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>

        <div class="tool-separator"></div>

        {{-- Color swatches --}}
        @foreach(['#ef4444','#f59e0b','#22c55e','#3b82f6','#8b5cf6','#ec4899'] as $c)
        <div class="color-swatch {{ $loop->first ? 'selected' : '' }}"
             style="background:{{ $c }}"
             data-color="{{ $c }}"
             onclick="setColor('{{ $c }}', this)"
             title="{{ $c }}"></div>
        @endforeach
    </div>

    <div style="display:flex; align-items:center; gap:8px;">
        <button type="button" onclick="approveArtwork()" style="padding:7px 14px; background:#10b981; color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.15s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            Approve Artwork
        </button>

        <button class="btn-submit" id="btnSubmit" onclick="submitReview()" style="padding:7px 16px; font-size:12px;">
            Submit Feedback ✓
        </button>
    </div>
</header>

{{-- ── Canvas Area ─────────────────────────────────────────────────────── --}}
<div class="canvas-area">
    <div class="artwork-wrapper" id="artworkWrapper">
        @php
            $initialArtworkUrl = $totalArtworks > 0 ? $artworksList[0] : null;
        @endphp

        @if($initialArtworkUrl)
            <img id="artwork-img"
                 src="{{ $initialArtworkUrl }}"
                 alt="Artwork"
                 draggable="false"
                 ondragstart="return false"
                 onload="initCanvas()">
        @else
            <div style="width:600px; height:400px; display:flex; align-items:center; justify-content:center; color:var(--muted); font-weight:600; font-size:15px; border-radius:16px; border:1px dashed rgba(255,255,255,0.1);">
                No artwork image available
            </div>
        @endif

        <canvas id="annotation-canvas"></canvas>

        {{-- DOM pins container (overlaid on image) --}}
        <div id="pins-layer" style="position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none;"></div>
    </div>
</div>

{{-- ── Multi-artwork Dockable Carousel Bar ─────────────────────────────── --}}
@if($totalArtworks > 1)
<div class="artwork-carousel-bar" id="artworkCarouselBar">
    <span style="font-size:11px; font-weight:800; color:var(--muted); text-transform:uppercase; letter-spacing:0.06em; margin-right:4px;">
        Artworks ({{ $totalArtworks }}):
    </span>
    @foreach($artworksList as $idx => $artUrl)
        <div class="slide-thumb-card {{ $idx === 0 ? 'active' : '' }}" id="slideThumb-{{ $idx }}" onclick="switchSlide({{ $idx }})">
            @if(preg_match('/\.(mp4|webm|ogg|mov)/i', $artUrl))
                <div class="slide-thumb-img" style="display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px;">🎬</div>
            @else
                <img src="{{ $artUrl }}" class="slide-thumb-img" alt="Slide {{ $idx + 1 }}">
            @endif
            <div style="display:flex; flex-direction:column; gap:1px;">
                <span style="font-size:11px; font-weight:800; color:var(--text);">Slide {{ $idx + 1 }}</span>
                <span id="slideNoteCount-{{ $idx }}" style="font-size:9px; font-weight:600; color:var(--muted);">0 notes</span>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- ── Side Panel (comment list) ──────────────────────────────────────── --}}
<button class="panel-toggle" id="panelToggle" onclick="togglePanel()">
    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
    </svg>
    Comments <span id="annotCountBadge" style="background:#3b82f6; color:#fff; border-radius:999px; padding:1px 7px; font-size:10px; font-weight:900;">0</span>
</button>

<aside class="side-panel" id="sidePanel">
    <div class="panel-header">
        <h3>Your Annotations</h3>
        <button onclick="togglePanel()" style="background:none; border:none; color:var(--muted); cursor:pointer; font-size:18px;">✕</button>
    </div>
    <div id="annotList" style="flex:1; overflow-y:auto;"></div>
</aside>

{{-- ── Pin Comment Modal ──────────────────────────────────────────────── --}}
<div class="modal-overlay" id="pinModal">
    <div class="modal-box">
        <div class="modal-title">📍 Add a comment to this pin</div>
        <textarea class="modal-input" id="pinCommentInput" rows="3"
                  placeholder="What would you like to note here?"></textarea>
        <div class="modal-actions">
            <button class="btn-ghost" onclick="cancelPin()">Cancel</button>
            <button class="btn-primary" onclick="confirmPin()">Add Pin</button>
        </div>
    </div>
</div>

{{-- ── Name Capture Modal (shown first) ──────────────────────────────── --}}
<div class="modal-overlay active" id="nameModal">
    <div class="modal-box">
        <div class="brand-logo">🎨</div>
        <div class="modal-title" style="font-size:18px; margin-bottom:8px;">Welcome!</div>
        <p style="font-size:13px; color:var(--muted); margin-bottom:20px; line-height:1.6;">
            You've been invited to review this artwork. Leave your name so the team knows who gave the feedback.
        </p>
        <input class="modal-input" type="text" id="clientNameInput"
               placeholder="Your name (e.g. Sara Hassan)"
               style="margin-bottom:16px; padding:13px 14px;">
        <div class="modal-actions" style="margin-top:16px;">
            <button class="btn-primary" onclick="saveName()" style="width:100%;">Start Reviewing</button>
        </div>
    </div>
</div>

{{-- ── Text placement Modal ────────────────────────────────────────────── --}}
<div class="modal-overlay" id="textModal">
    <div class="modal-box">
        <div class="modal-title">💬 Add a text note</div>
        <textarea class="modal-input" id="textCommentInput" rows="3"
                  placeholder="Type your note…"></textarea>
        <div class="modal-actions">
            <button class="btn-ghost" onclick="cancelText()">Cancel</button>
            <button class="btn-primary" onclick="confirmText()">Place Note</button>
        </div>
    </div>
</div>

{{-- ── Toast ────────────────────────────────────────────────────────────── --}}
<div class="toast" id="toast"></div>

{{-- ── Keyboard hint ─────────────────────────────────────────────────────── --}}
<div class="hint">P = Pin &nbsp;|&nbsp; D = Draw &nbsp;|&nbsp; T = Text &nbsp;|&nbsp; ← / → = Slides &nbsp;|&nbsp; Ctrl+Z = Undo</div>

<script>
// ──────────────────────────────────────────────────────────────────────────────
// State
// ──────────────────────────────────────────────────────────────────────────────
let currentMode       = 'pin';
let currentColor      = '#ef4444';
let clientName        = '';
let canvas            = null;
let pendingPin        = null;   // {xPct, yPct} waiting for comment
let pendingText       = null;   // {xPct, yPct}

const ALL_ARTWORKS        = @json($artworksList);
const TOKEN               = @json($review->token);
const INITIAL_ANNOTATIONS = @json($review->annotations ?? []);
const SERVER_CLIENT_NAME  = @json($review->client_name ?? '');

let currentSlideIndex = 0;
let hideResolvedPins = false;
let activeStatusFilter = 'all'; // 'all', 'open', 'resolved'

// Initialize artworkState for each slide
let artworkState = (ALL_ARTWORKS && ALL_ARTWORKS.length > 0 ? ALL_ARTWORKS : ['']).map(() => ({
    pins: [],
    drawings: [],
    textNotes: [],
    undoStack: [],
    pinCounter: 0,
}));

// Populate initial annotations by artwork_index
if (INITIAL_ANNOTATIONS && INITIAL_ANNOTATIONS.length > 0) {
    INITIAL_ANNOTATIONS.forEach(ann => {
        const idx = (typeof ann.artwork_index !== 'undefined' && ann.artwork_index !== null) ? parseInt(ann.artwork_index) : 0;
        if (!artworkState[idx]) {
            artworkState[idx] = { pins: [], drawings: [], textNotes: [], undoStack: [], pinCounter: 0 };
        }
        const st = artworkState[idx];
        const isResolved = Boolean(ann.is_resolved);
        const resolvedBy = ann.resolved_by ? (ann.resolved_by.name || 'Team') : null;

        if (ann.type === 'pin') {
            st.pinCounter = Math.max(st.pinCounter, ann.pin_number || 0);
            st.pins.push({
                id:          ann.id || Date.now() + Math.random(),
                xPct:        parseFloat(ann.x_percent),
                yPct:        parseFloat(ann.y_percent),
                color:       ann.color || '#ef4444',
                comment:     ann.content || `Pin #${ann.pin_number}`,
                pinNumber:   ann.pin_number || st.pinCounter,
                responseText:ann.response_text || null,
                comments:    ann.comments || [],
                isResolved:  isResolved,
                resolvedBy:  resolvedBy,
                isSubmitted: true,
            });
        } else if (ann.type === 'drawing') {
            st.drawings.push({
                id:          ann.id || Date.now() + Math.random(),
                type:        'drawing',
                content:     ann.content,
                color:       ann.color || '#ef4444',
                x_percent:   ann.x_percent,
                y_percent:   ann.y_percent,
                isResolved:  isResolved,
                resolvedBy:  resolvedBy,
                isSubmitted: true,
            });
        } else if (ann.type === 'text') {
            st.textNotes.push({
                id:          ann.id || Date.now() + Math.random(),
                xPct:        parseFloat(ann.x_percent),
                yPct:        parseFloat(ann.y_percent),
                color:       ann.color || '#ef4444',
                content:     ann.content,
                isResolved:  isResolved,
                resolvedBy:  resolvedBy,
                isSubmitted: true,
            });
        }
    });
}

// ──────────────────────────────────────────────────────────────────────────────
// Initialise Fabric canvas & render active slide
// ──────────────────────────────────────────────────────────────────────────────
function initCanvas() {
    const img = document.getElementById('artwork-img');
    if (!img) return;

    requestAnimationFrame(() => {
        const w = img.offsetWidth;
        const h = img.offsetHeight;

        if (!w || !h) {
            setTimeout(initCanvas, 50);
            return;
        }

        const wrapper = document.getElementById('artworkWrapper');
        wrapper.style.width  = w + 'px';
        wrapper.style.height = h + 'px';

        const el = document.getElementById('annotation-canvas');

        if (!canvas) {
            el.width  = w;
            el.height = h;
            el.style.width  = w + 'px';
            el.style.height = h + 'px';

            canvas = new fabric.Canvas('annotation-canvas', {
                isDrawingMode: currentMode === 'draw',
                selection: false,
                width: w,
                height: h,
            });

            const fabricContainer = wrapper.querySelector('.canvas-container');
            if (fabricContainer) {
                fabricContainer.style.position = 'absolute';
                fabricContainer.style.top      = '0';
                fabricContainer.style.left     = '0';
                fabricContainer.style.zIndex   = '5';
                fabricContainer.style.width    = w + 'px';
                fabricContainer.style.height   = h + 'px';
            }

            const upperCanvas = wrapper.querySelector('.upper-canvas');
            if (upperCanvas) {
                upperCanvas.style.cursor = 'crosshair';
                upperCanvas.style.zIndex = '6';
            }

            canvas.freeDrawingBrush.width = 3;
            canvas.freeDrawingBrush.color = currentColor;

            // Save each completed freehand path into active slide
            canvas.on('path:created', function(opt) {
                const path = opt.path;
                const drawId = Date.now();
                path.drawId = drawId;
                const st = artworkState[currentSlideIndex];
                const dObj = {
                    id:        drawId,
                    type:      'drawing',
                    content:   JSON.stringify(path.toObject()),
                    color:     currentColor,
                    x_percent: (path.left / canvas.width)  * 100,
                    y_percent: (path.top  / canvas.height) * 100,
                    isSubmitted: false,
                };
                st.drawings.push(dObj);
                st.undoStack.push({ type: 'drawing', data: path });
                updateSidePanel();
            });

            // Capture click event for pins and text
            canvas.on('mouse:down', function(opt) {
                const p = opt.absolutePointer || opt.pointer || { x: 0, y: 0 };
                const xPct = (p.x / canvas.width)  * 100;
                const yPct = (p.y / canvas.height) * 100;

                if (currentMode === 'pin') {
                    pendingPin = { xPct, yPct };
                    openModal('pinModal');
                } else if (currentMode === 'text') {
                    pendingText = { xPct, yPct };
                    openModal('textModal');
                }
            });
        } else {
            canvas.setWidth(w);
            canvas.setHeight(h);
            canvas.calcOffset();

            const fabricContainer = wrapper.querySelector('.canvas-container');
            if (fabricContainer) {
                fabricContainer.style.width  = w + 'px';
                fabricContainer.style.height = h + 'px';
            }
        }

        renderCurrentSlideObjects();
        updateSidePanel();
    });
}

function renderCurrentSlideObjects() {
    if (!canvas) return;

    canvas.clear();

    const layer = document.getElementById('pins-layer');
    if (layer) layer.innerHTML = '';

    const st = artworkState[currentSlideIndex] || { pins: [], drawings: [], textNotes: [] };

    // Render pins
    st.pins.forEach(pin => renderPin(pin));

    // Render drawings on fabric
    if (st.drawings && st.drawings.length > 0) {
        st.drawings.forEach(d => {
            try {
                const objData = typeof d.content === 'string' ? JSON.parse(d.content) : d.content;
                fabric.util.enlivenObjects([objData], function(objects) {
                    objects.forEach(function(o) {
                        o.drawId = d.id;
                        canvas.add(o);
                    });
                });
            } catch(e) {}
        });
    }

    // Render text notes on fabric
    if (st.textNotes && st.textNotes.length > 0) {
        st.textNotes.forEach(note => {
            const itext = new fabric.IText(note.content, {
                left:      (note.xPct / 100) * canvas.width,
                top:       (note.yPct / 100) * canvas.height,
                fill:      note.color || '#ef4444',
                fontSize:  16,
                fontWeight:'700',
                fontFamily:'Inter, sans-serif',
                selectable:false,
                evented:   false,
            });
            itext.noteId = note.id;
            canvas.add(itext);
        });
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// Slide switching
// ──────────────────────────────────────────────────────────────────────────────
function switchSlide(idx) {
    if (idx < 0 || idx >= ALL_ARTWORKS.length) return;

    currentSlideIndex = idx;

    // Update top indicator
    const topInd = document.getElementById('topSlideIndicator');
    if (topInd) topInd.textContent = `Slide ${idx + 1} of ${ALL_ARTWORKS.length}`;

    // Update bottom carousel active thumbnail
    document.querySelectorAll('.slide-thumb-card').forEach((el, i) => {
        el.classList.toggle('active', i === idx);
    });

    // Update main image source
    const img = document.getElementById('artwork-img');
    if (img) {
        img.onload = () => {
            initCanvas();
        };
        img.src = ALL_ARTWORKS[idx];
        if (img.complete && img.naturalWidth > 0) {
            initCanvas();
        }
    }
}

function prevSlide() {
    if (currentSlideIndex > 0) {
        switchSlide(currentSlideIndex - 1);
    }
}

function nextSlide() {
    if (currentSlideIndex < ALL_ARTWORKS.length - 1) {
        switchSlide(currentSlideIndex + 1);
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// Mode switching
// ──────────────────────────────────────────────────────────────────────────────
function setMode(mode) {
    currentMode = mode;
    document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));

    if (mode === 'draw') {
        document.getElementById('btnDraw').classList.add('active');
        if (canvas) {
            canvas.isDrawingMode = true;
            canvas.selection = false;
            canvas.freeDrawingCursor = 'crosshair';
        }
    } else if (mode === 'text') {
        document.getElementById('btnText').classList.add('active');
        if (canvas) {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.defaultCursor = 'crosshair';
        }
    } else {
        document.getElementById('btnPin').classList.add('active');
        if (canvas) {
            canvas.isDrawingMode = false;
            canvas.selection = false;
            canvas.defaultCursor = 'crosshair';
        }
    }

    const upperCanvas = document.querySelector('#artworkWrapper .upper-canvas');
    if (upperCanvas) upperCanvas.style.cursor = 'crosshair';
}

// ──────────────────────────────────────────────────────────────────────────────
// Color
// ──────────────────────────────────────────────────────────────────────────────
function setColor(color, el) {
    currentColor = color;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    if (canvas) { canvas.freeDrawingBrush.color = color; }
}

// ──────────────────────────────────────────────────────────────────────────────
// Pin & Text handling
// ──────────────────────────────────────────────────────────────────────────────
function openModal(id) {
    document.getElementById(id).classList.add('active');
    const inp = document.getElementById(id === 'pinModal' ? 'pinCommentInput' : 'textCommentInput');
    if (inp) { inp.value = ''; setTimeout(() => inp.focus(), 200); }
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function cancelPin() {
    pendingPin = null;
    closeModal('pinModal');
}

function confirmPin() {
    if (!pendingPin) return;
    const comment = document.getElementById('pinCommentInput').value.trim();
    const st = artworkState[currentSlideIndex];
    st.pinCounter++;
    const pin = {
        id:        Date.now(),
        xPct:      pendingPin.xPct,
        yPct:      pendingPin.yPct,
        color:     currentColor,
        comment:   comment || `Pin #${st.pinCounter}`,
        pinNumber: st.pinCounter,
        isSubmitted: false,
    };
    st.pins.push(pin);
    st.undoStack.push({ type: 'pin', data: pin });
    renderPin(pin);
    updateSidePanel();
    pendingPin = null;
    closeModal('pinModal');
}

function renderPin(pin) {
    const layer = document.getElementById('pins-layer');
    if (!layer) return;

    const marker = document.createElement('div');
    marker.className = `pin-marker ${pin.isResolved ? 'resolved' : ''}`;
    marker.id = `pin-${pin.id}`;
    marker.style.left = `${pin.xPct}%`;
    marker.style.top  = `${pin.yPct}%`;
    marker.style.pointerEvents = 'auto';

    if (hideResolvedPins && pin.isResolved) {
        marker.style.display = 'none';
    }

    const pinBg = pin.isResolved ? '#10b981' : pin.color;
    const pinBadge = pin.isResolved ? `✓ ${pin.pinNumber}` : pin.pinNumber;

    marker.innerHTML = `
        <div class="pin-comment">
            ${pin.isResolved ? '<span style="color:#10b981; font-weight:800;">[✓ Resolved]</span> ' : ''}${escHtml(pin.comment)}
        </div>
        <div class="pin-circle" style="background:${pinBg}; border-color:rgba(255,255,255,0.4);">
            ${pinBadge}
        </div>
        <div class="pin-tail" style="background:${pinBg};"></div>
        ${!pin.isSubmitted ? `<button class="pin-delete" onclick="removePin(${pin.id})">✕</button>` : ''}
    `;

    layer.appendChild(marker);
}

function removePin(id) {
    const st = artworkState[currentSlideIndex];
    st.pins = st.pins.filter(p => p.id !== id);
    const el = document.getElementById(`pin-${id}`);
    if (el) el.remove();
    updateSidePanel();
}

function cancelText() {
    pendingText = null;
    closeModal('textModal');
}

function confirmText() {
    if (!pendingText) return;
    const text = document.getElementById('textCommentInput').value.trim();
    if (!text) { cancelText(); return; }

    const st = artworkState[currentSlideIndex];
    const noteId = Date.now();
    const note = {
        id:          noteId,
        xPct:        pendingText.xPct,
        yPct:        pendingText.yPct,
        color:       currentColor,
        content:     text,
        isSubmitted: false,
    };
    st.textNotes.push(note);

    if (canvas) {
        const itext = new fabric.IText(text, {
            left:      (pendingText.xPct / 100) * canvas.width,
            top:       (pendingText.yPct / 100) * canvas.height,
            fill:      currentColor,
            fontSize:  16,
            fontWeight:'700',
            fontFamily:'Inter, sans-serif',
            selectable:false,
            evented:   false,
        });
        itext.noteId = noteId;
        canvas.add(itext);
        st.undoStack.push({ type: 'text', data: note, fabricObj: itext });
    }

    updateSidePanel();
    pendingText = null;
    closeModal('textModal');
}

function removeTextNote(id) {
    const st = artworkState[currentSlideIndex];
    st.textNotes = st.textNotes.filter(t => t.id !== id);
    if (canvas) {
        const obj = canvas.getObjects().find(o => o.noteId === id);
        if (obj) canvas.remove(obj);
    }
    updateSidePanel();
}

function removeDrawing(id) {
    const st = artworkState[currentSlideIndex];
    st.drawings = st.drawings.filter(d => d.id !== id);
    if (canvas) {
        const obj = canvas.getObjects().find(o => o.drawId === id);
        if (obj) canvas.remove(obj);
    }
    updateSidePanel();
}

function undo() {
    const st = artworkState[currentSlideIndex];
    if (st.undoStack.length === 0) return;
    const last = st.undoStack.pop();
    if (last.type === 'pin') {
        removePin(last.data.id);
    } else if (last.type === 'drawing') {
        removeDrawing(last.data.id || last.data.drawId);
    } else if (last.type === 'text') {
        removeTextNote(last.data.id);
    }
    updateSidePanel();
}

async function clearAll() {
    if (!await window.customConfirm({ title: 'Clear Slide Annotations?', message: `Clear all annotations on Slide ${currentSlideIndex + 1}? This action cannot be undone.`, isDanger: true })) return;
    const st = artworkState[currentSlideIndex];
    st.pins = []; st.drawings = []; st.textNotes = []; st.undoStack = []; st.pinCounter = 0;
    const layer = document.getElementById('pins-layer');
    if (layer) layer.innerHTML = '';
    if (canvas) canvas.clear();
    updateSidePanel();
}

let activePanelTab = 'current'; // 'current', 0, 1, 2... or 'all'

function setPanelTab(tab) {
    activePanelTab = tab;
    if (typeof tab === 'number') {
        switchSlide(tab);
    }
    updateSidePanel();
}

function toggleHideResolvedPins() {
    hideResolvedPins = !hideResolvedPins;
    const btn = document.getElementById('toggleResolvedPinsBtn');
    if (btn) {
        btn.textContent = hideResolvedPins ? '👁 Show Resolved Pins' : '👁 Hide Resolved Pins';
        btn.style.color = hideResolvedPins ? '#f59e0b' : 'var(--muted)';
    }
    renderCurrentSlideObjects();
}

function setStatusFilter(filter) {
    activeStatusFilter = filter;
    updateSidePanel();
}

// ──────────────────────────────────────────────────────────────────────────────
// Side panel & Thumbnail Badges
// ──────────────────────────────────────────────────────────────────────────────
function updateSidePanel() {
    let grandTotal = 0;
    let grandOpen = 0;
    let grandResolved = 0;

    // Update per-slide note counters on carousel thumbnails
    artworkState.forEach((st, idx) => {
        const slideTotal = st.pins.length + st.drawings.length + st.textNotes.length;
        const slideOpen = st.pins.filter(p => !p.isResolved).length + st.drawings.filter(d => !d.isResolved).length + st.textNotes.filter(t => !t.isResolved).length;
        const slideResolved = slideTotal - slideOpen;

        grandTotal += slideTotal;
        grandOpen += slideOpen;
        grandResolved += slideResolved;

        const countEl = document.getElementById(`slideNoteCount-${idx}`);
        if (countEl) {
            countEl.textContent = `${slideTotal} note${slideTotal === 1 ? '' : 's'}`;
            countEl.style.color = slideOpen > 0 ? '#f59e0b' : (slideResolved > 0 ? '#10b981' : 'var(--muted)');
            countEl.style.fontWeight = slideTotal > 0 ? '800' : '600';
        }
    });

    document.getElementById('annotCountBadge').textContent = grandTotal;

    const list = document.getElementById('annotList');
    list.innerHTML = '';

    // Status Filter & Visibility Bar inside Side Panel
    let filterBarHtml = `
        <div style="padding:10px 14px; background:rgba(255,255,255,0.04); border-bottom:1px solid var(--border); display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <button onclick="setStatusFilter('all')" style="padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid ${activeStatusFilter === 'all' ? '#38bdf8' : 'var(--border)'}; background:${activeStatusFilter === 'all' ? 'rgba(56,189,248,0.2)' : 'transparent'}; color:${activeStatusFilter === 'all' ? '#38bdf8' : 'var(--muted)'};">
                        All (${grandTotal})
                    </button>
                    <button onclick="setStatusFilter('open')" style="padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid ${activeStatusFilter === 'open' ? '#f59e0b' : 'var(--border)'}; background:${activeStatusFilter === 'open' ? 'rgba(245,158,11,0.2)' : 'transparent'}; color:${activeStatusFilter === 'open' ? '#f59e0b' : 'var(--muted)'};">
                        🟡 Open (${grandOpen})
                    </button>
                    <button onclick="setStatusFilter('resolved')" style="padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid ${activeStatusFilter === 'resolved' ? '#10b981' : 'var(--border)'}; background:${activeStatusFilter === 'resolved' ? 'rgba(16,185,129,0.2)' : 'transparent'}; color:${activeStatusFilter === 'resolved' ? '#10b981' : 'var(--muted)'};">
                        🟢 Resolved (${grandResolved})
                    </button>
                </div>
            </div>
            ${grandResolved > 0 ? `
                <button id="toggleResolvedPinsBtn" onclick="toggleHideResolvedPins()" style="background:none; border:none; color:var(--muted); font-size:10px; font-weight:700; cursor:pointer; text-align:left; padding:0; display:inline-flex; align-items:center; gap:4px;">
                    ${hideResolvedPins ? '👁 Show Resolved Pins' : '👁 Hide Resolved Pins'}
                </button>
            ` : ''}
        </div>
    `;
    list.innerHTML += filterBarHtml;

    // Render Tab Selector inside Side Panel if multiple artworks exist
    if (artworkState.length > 1) {
        let tabsHtml = `
            <div style="display:flex; align-items:center; gap:6px; padding:10px 14px; background:rgba(255,255,255,0.02); border-bottom:1px solid var(--border); overflow-x:auto;">
                <button onclick="setPanelTab('current')" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid ${activePanelTab === 'current' || activePanelTab === currentSlideIndex ? '#3b82f6' : 'var(--border)'}; background:${activePanelTab === 'current' || activePanelTab === currentSlideIndex ? 'rgba(59,130,246,0.2)' : 'transparent'}; color:${activePanelTab === 'current' || activePanelTab === currentSlideIndex ? '#38bdf8' : 'var(--muted)'}; white-space:nowrap;">
                    🖼 Slide ${currentSlideIndex + 1}
                </button>`;
        
        artworkState.forEach((st, idx) => {
            if (idx === currentSlideIndex) return;
            tabsHtml += `
                <button onclick="setPanelTab(${idx})" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid ${activePanelTab === idx ? '#3b82f6' : 'var(--border)'}; background:${activePanelTab === idx ? 'rgba(59,130,246,0.2)' : 'transparent'}; color:${activePanelTab === idx ? '#38bdf8' : 'var(--muted)'}; white-space:nowrap;">
                    Slide ${idx + 1}
                </button>`;
        });

        tabsHtml += `
                <button onclick="setPanelTab('all')" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid ${activePanelTab === 'all' ? '#3b82f6' : 'var(--border)'}; background:${activePanelTab === 'all' ? 'rgba(59,130,246,0.2)' : 'transparent'}; color:${activePanelTab === 'all' ? '#38bdf8' : 'var(--muted)'}; white-space:nowrap;">
                    All Slides
                </button>
            </div>`;
        list.innerHTML += tabsHtml;
    }

    if (grandTotal === 0) {
        list.innerHTML += '<p style="padding:24px 16px; color:var(--muted); font-size:12px; text-align:center;">No feedback on this deliverable yet.<br>Click on the artwork to drop a comment.</p>';
        return;
    }

    const slidesToRender = (activePanelTab === 'all')
        ? artworkState.map((_, i) => i)
        : (typeof activePanelTab === 'number' ? [activePanelTab] : [currentSlideIndex]);

    slidesToRender.forEach(slideIdx => {
        const st = artworkState[slideIdx];
        if (!st) return;

        let pins = st.pins;
        let drawings = st.drawings;
        let textNotes = st.textNotes;

        if (activeStatusFilter === 'open') {
            pins = pins.filter(p => !p.isResolved);
            drawings = drawings.filter(d => !d.isResolved);
            textNotes = textNotes.filter(t => !t.isResolved);
        } else if (activeStatusFilter === 'resolved') {
            pins = pins.filter(p => p.isResolved);
            drawings = drawings.filter(d => d.isResolved);
            textNotes = textNotes.filter(t => t.isResolved);
        }

        const totalInFilter = pins.length + drawings.length + textNotes.length;

        let slideHtml = '';
        slideHtml += `
            <div style="margin:14px 14px 8px; padding:8px 12px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:10px; display:flex; justify-content:space-between; align-items:center; cursor:pointer;" onclick="switchSlide(${slideIdx})">
                <span style="font-size:12px; font-weight:800; color:${slideIdx === currentSlideIndex ? '#38bdf8' : 'var(--text)'};">
                    🖼 Slide ${slideIdx + 1} Feedback ${slideIdx === currentSlideIndex ? '★ (Viewing)' : ''}
                </span>
                <span style="font-size:10px; font-weight:800; color:#fff; background:rgba(59,130,246,0.3); border-radius:999px; padding:2px 8px;">${totalInFilter} note${totalInFilter === 1 ? '' : 's'}</span>
            </div>`;

        if (totalInFilter === 0) {
            slideHtml += `<div style="padding:10px 16px; color:var(--muted); font-size:11px; font-style:italic;">No ${activeStatusFilter !== 'all' ? activeStatusFilter : ''} notes on Slide ${slideIdx + 1}.</div>`;
        }

        pins.forEach(pin => {
            slideHtml += `
                <div class="annotation-item" style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px; cursor:pointer; border-left:3px solid ${pin.isResolved ? '#10b981' : '#f59e0b'};" onclick="switchSlide(${slideIdx})">
                    <div style="display:flex; align-items:flex-start; gap:8px; flex:1; min-width:0;">
                        <div class="annotation-num" style="background:${pin.isResolved ? '#10b981' : pin.color}; flex-shrink:0;">${pin.isResolved ? '✓' : pin.pinNumber}</div>
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px;">
                                <span style="font-size:10px; font-weight:800; color:${pin.isResolved ? '#10b981' : '#f59e0b'};">${pin.isResolved ? '✓ Resolved' : '🟡 Open Note'}</span>
                                ${pin.resolvedBy ? `<span style="font-size:10px; color:var(--muted);">by ${escHtml(pin.resolvedBy)}</span>` : ''}
                            </div>
                            <div class="annotation-text" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><strong>Pin:</strong> ${escHtml(pin.comment)}</div>
                            ${pin.comments && pin.comments.length > 0 ? `
                                <div style="margin-top:6px; display:flex; flex-direction:column; gap:4px;">
                                    ${pin.comments.map(c => `
                                        <div style="padding:4px 8px; background:rgba(59,130,246,0.15); border:1px solid rgba(59,130,246,0.3); border-radius:6px; font-size:11px; color:#fff; white-space:normal;">
                                            <span style="font-weight:800; color:#60a5fa;">💬 ${escHtml(c.user ? c.user.name : 'Team')}:</span> ${escHtml(c.comment)}
                                        </div>
                                    `).join('')}
                                </div>
                            ` : (pin.responseText ? `
                                <div style="margin-top:4px; padding:4px 8px; background:rgba(59,130,246,0.15); border:1px solid rgba(59,130,246,0.3); border-radius:6px; font-size:11px; color:#fff; white-space:normal;">
                                    <span style="font-weight:800; color:#60a5fa;">💬 Team Reply:</span> ${escHtml(pin.responseText)}
                                </div>
                            ` : '')}
                        </div>
                    </div>
                    ${pin.isSubmitted ? `
                        <span style="font-size:10px; font-weight:800; color:${pin.isResolved ? '#10b981' : '#f59e0b'}; background:${pin.isResolved ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)'}; border:1px solid ${pin.isResolved ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)'}; padding:2px 7px; border-radius:6px; flex-shrink:0;">${pin.isResolved ? '✓ Resolved' : 'Submitted'}</span>
                    ` : `
                        <button onclick="event.stopPropagation(); removePin(${pin.id})" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; font-weight:700; padding:2px 6px; border-radius:4px; opacity:0.7; transition:all 0.15s; flex-shrink:0;" onmouseover="this.style.opacity='1'; this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.opacity='0.7'; this.style.background='none'" title="Remove Pin">✕</button>
                    `}
                </div>`;
        });

        drawings.forEach((d, i) => {
            slideHtml += `
                <div class="annotation-item" style="display:flex; align-items:center; justify-content:space-between; gap:8px; cursor:pointer;" onclick="switchSlide(${slideIdx})">
                    <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                        <div class="annotation-num" style="background:${d.isResolved ? '#10b981' : d.color}; font-size:12px;">✏</div>
                        <div class="annotation-text" style="color:var(--muted); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">Freehand drawing #${i+1}</div>
                    </div>
                    ${d.isSubmitted ? `
                        <span style="font-size:10px; font-weight:800; color:${d.isResolved ? '#10b981' : '#f59e0b'}; background:${d.isResolved ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)'}; border:1px solid ${d.isResolved ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)'}; padding:2px 7px; border-radius:6px; flex-shrink:0;">${d.isResolved ? '✓ Resolved' : 'Submitted'}</span>
                    ` : `
                        <button onclick="event.stopPropagation(); removeDrawing(${d.id})" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; font-weight:700; padding:2px 6px; border-radius:4px; opacity:0.7; transition:all 0.15s; flex-shrink:0;" onmouseover="this.style.opacity='1'; this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.opacity='0.7'; this.style.background='none'" title="Remove Drawing">✕</button>
                    `}
                </div>`;
        });

        textNotes.forEach(t => {
            slideHtml += `
                <div class="annotation-item" style="display:flex; align-items:center; justify-content:space-between; gap:8px; cursor:pointer;" onclick="switchSlide(${slideIdx})">
                    <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:0;">
                        <div class="annotation-num" style="background:${t.isResolved ? '#10b981' : t.color}; font-size:12px;">T</div>
                        <div class="annotation-text" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escHtml(t.content)}</div>
                    </div>
                    ${t.isSubmitted ? `
                        <span style="font-size:10px; font-weight:800; color:${t.isResolved ? '#10b981' : '#f59e0b'}; background:${t.isResolved ? 'rgba(16,185,129,0.1)' : 'rgba(245,158,11,0.1)'}; border:1px solid ${t.isResolved ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)'}; padding:2px 7px; border-radius:6px; flex-shrink:0;">${t.isResolved ? '✓ Resolved' : 'Submitted'}</span>
                    ` : `
                        <button onclick="event.stopPropagation(); removeTextNote(${t.id})" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; font-weight:700; padding:2px 6px; border-radius:4px; opacity:0.7; transition:all 0.15s; flex-shrink:0;" onmouseover="this.style.opacity='1'; this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.opacity='0.7'; this.style.background='none'" title="Remove Text">✕</button>
                    `}
                </div>`;
        });

        list.innerHTML += slideHtml;
    });
}

async function approveArtwork() {
    if (!await window.customConfirm({
        title: 'Approve Artwork?',
        message: 'Are you sure you want to approve this artwork deliverable? This will sign off on the designs and notify the creative team.',
        isDanger: false
    })) return;

    try {
        const resp = await fetch(`/artwork-review/${TOKEN}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const data = await resp.json();
        if (data.success) {
            showToast('🎉 Artwork Approved! Thank you.', '#10b981');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showToast(data.error || 'Failed to approve artwork.', '#ef4444');
        }
    } catch(e) {
        console.error(e);
        showToast('Network error while approving artwork.', '#ef4444');
    }
}

function togglePanel() {
    document.getElementById('sidePanel').classList.toggle('open');
}

// ──────────────────────────────────────────────────────────────────────────────
// Name modal
// ──────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const savedName = localStorage.getItem('client_name_' + TOKEN) || SERVER_CLIENT_NAME;
    if (savedName) {
        clientName = savedName;
        const inp = document.getElementById('clientNameInput');
        if (inp) inp.value = savedName;
        closeModal('nameModal');
        showToast(`Welcome back, ${savedName}!`, '#0055D4');
    }
});

function saveName() {
    const input = document.getElementById('clientNameInput');
    const val = input.value.trim();
    if (!val) {
        input.style.borderColor = '#ef4444';
        input.focus();
        return;
    }
    clientName = val;
    localStorage.setItem('client_name_' + TOKEN, val);
    closeModal('nameModal');
}

// ──────────────────────────────────────────────────────────────────────────────
// Submit
// ──────────────────────────────────────────────────────────────────────────────
async function submitReview() {
    const annotations = [];
    let totalUnsubmitted = 0;

    artworkState.forEach((st, slideIdx) => {
        const slideUrl = ALL_ARTWORKS[slideIdx] || '';

        st.pins.filter(p => !p.isSubmitted).forEach(pin => {
            totalUnsubmitted++;
            annotations.push({
                artwork_index: slideIdx,
                image_url:     slideUrl,
                type:          'pin',
                x_percent:     pin.xPct,
                y_percent:     pin.yPct,
                content:       pin.comment,
                color:         pin.color,
                pin_number:    pin.pinNumber,
            });
        });

        st.drawings.filter(d => !d.isSubmitted).forEach(d => {
            totalUnsubmitted++;
            annotations.push({
                artwork_index: slideIdx,
                image_url:     slideUrl,
                type:          'drawing',
                x_percent:     d.x_percent,
                y_percent:     d.y_percent,
                content:       d.content,
                color:         d.color,
            });
        });

        st.textNotes.filter(t => !t.isSubmitted).forEach(t => {
            totalUnsubmitted++;
            annotations.push({
                artwork_index: slideIdx,
                image_url:     slideUrl,
                type:          'text',
                x_percent:     t.xPct,
                y_percent:     t.yPct,
                content:       t.content,
                color:         t.color,
            });
        });
    });

    if (totalUnsubmitted === 0) {
        showToast('No new annotations to submit. Add feedback on any slide before submitting.', '#f59e0b');
        return;
    }

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    try {
        const resp = await fetch(`/artwork-review/${TOKEN}/annotate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ client_name: clientName, annotations }),
        });

        const result = await resp.json();
        if (resp.ok && result.success) {
            artworkState.forEach(st => {
                st.pins.forEach(p => p.isSubmitted = true);
                st.drawings.forEach(d => d.isSubmitted = true);
                st.textNotes.forEach(t => t.isSubmitted = true);
            });

            updateSidePanel();

            showToast('✓ Feedback submitted successfully across all slides! Thank you.');
            btn.textContent = '✓ Submitted';
            setTimeout(() => { btn.textContent = 'Submit Feedback ✓'; btn.disabled = false; }, 2500);
        } else {
            showToast(result.error || 'Submission failed. Please try again.', '#ef4444');
            btn.disabled = false;
            btn.textContent = 'Submit Feedback ✓';
        }
    } catch (e) {
        showToast('Network error. Please check your connection.', '#ef4444');
        btn.disabled = false;
        btn.textContent = 'Submit Feedback ✓';
    }
}

// ──────────────────────────────────────────────────────────────────────────────
// Utilities
// ──────────────────────────────────────────────────────────────────────────────
function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, bg = '#238636') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.background = bg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 4000);
}

// Keyboard shortcuts
document.addEventListener('keydown', e => {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    if (e.key === 'p' || e.key === 'P') setMode('pin');
    if (e.key === 'd' || e.key === 'D') setMode('draw');
    if (e.key === 't' || e.key === 'T') setMode('text');
    if (e.key === 'ArrowLeft') prevSlide();
    if (e.key === 'ArrowRight') nextSlide();
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
});

// Resize canvas when window resizes
window.addEventListener('resize', () => {
    const img = document.getElementById('artwork-img');
    if (!img || !canvas) return;
    const w = img.offsetWidth;
    const h = img.offsetHeight;
    canvas.setWidth(w);
    canvas.setHeight(h);
    canvas.calcOffset();
});

// Init panel
updateSidePanel();
</script>
</body>
</html>
