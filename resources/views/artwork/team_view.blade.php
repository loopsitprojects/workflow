<x-layout title="Artwork Review — {{ $deliverable->title }}">
<style>
    .ar-page { max-width: 1280px; margin: 0 auto; }
    .ar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .ar-header h1 { font-size: 20px; font-weight: 800; color: var(--color-text-primary); }
    .ar-header-sub { font-size: 12px; color: var(--color-text-secondary); font-weight: 500; margin-top: 2px; }
    .ar-back { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #0f172a; background: #ffffff; text-decoration: none; padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); transition: all 0.15s ease; }
    .dark .ar-back { background: #1e293b; color: #f8fafc; border-color: #334155; box-shadow: none; }
    .ar-back:hover { border-color: #94a3b8; background: #f8fafc; color: #0284c7; transform: translateY(-1px); }
    .dark .ar-back:hover { background: #334155; border-color: #475569; color: #38bdf8; }

    .ar-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start; }
    @media (max-width: 900px) { .ar-grid { grid-template-columns: 1fr; } }

    /* Artwork viewer */
    .artwork-viewer { background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 20px; overflow: hidden; position: relative; }
    .artwork-viewer-img { width: 100%; display: block; border-radius: 16px; }
    .pin-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; }
    .tv-pin {
        position: absolute;
        transform: translate(-50%, -100%);
        display: flex; flex-direction: column; align-items: center;
        z-index: 5; cursor: pointer; pointer-events: auto;
    }
    .tv-pin-circle {
        width: 26px; height: 26px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 800; color: #fff;
        border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 3px 10px rgba(0,0,0,0.3);
        transition: background-color 0.2s;
    }
    .tv-pin-circle.resolved { background: #10b981 !important; border-color: rgba(255,255,255,0.6); }
    .tv-pin-tail { width: 2px; height: 8px; margin-top: -1px; border-radius: 2px; transition: background-color 0.2s; }
    .tv-pin-tail.resolved { background: #10b981 !important; }

    /* Reviews & annotations list */
    .panel { background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 20px; overflow: hidden; }
    .panel-head { padding: 16px 20px; border-bottom: 1px solid var(--color-border-primary); display: flex; justify-content: space-between; align-items: center; }
    .panel-head h2 { font-size: 13px; font-weight: 800; color: var(--color-text-primary); text-transform: uppercase; letter-spacing: 0.05em; }
    .panel-body { padding: 16px; }

    .review-card { border: 1px solid var(--color-border-primary); border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
    .review-card-head { padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; background: var(--color-bg-secondary); }
    .review-card-head-left { }
    .review-client { font-size: 13px; font-weight: 700; color: var(--color-text-primary); }
    .review-meta { font-size: 11px; color: var(--color-text-secondary); margin-top: 1px; }
    .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
    .status-pill.active { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
    .status-pill.inactive { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.15); }

    .annot-list { padding: 12px 16px; display: flex; flex-direction: column; gap: 8px; }
    .annot-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 10px; border: 1px solid var(--color-border-primary); background: var(--color-bg-secondary); transition: all 0.15s; }
    .annot-item.resolved { background: rgba(16,185,129,0.04); border-color: rgba(16,185,129,0.2); }
    .annot-badge { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #fff; flex-shrink: 0; transition: background-color 0.2s; }
    .annot-item.resolved .annot-badge { background: #10b981 !important; }
    .annot-content { flex: 1; }
    .annot-type { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-secondary); margin-bottom: 2px; }
    .annot-text { font-size: 12px; font-weight: 500; color: var(--color-text-primary); line-height: 1.4; }
    .annot-resolve-btn { padding: 4px 10px; border-radius: 7px; font-size: 10px; font-weight: 700; cursor: pointer; border: none; transition: all 0.15s; }
    .annot-resolve-btn.resolve { background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); }
    .annot-resolve-btn.resolve:hover { background: rgba(34,197,94,0.2); }
    .annot-resolve-btn.unresolve { background: rgba(100,116,139,0.1); color: var(--color-text-secondary); border: 1px solid var(--color-border-primary); }
    .annot-resolve-btn.unresolve:hover { background: rgba(100,116,139,0.15); }

    .deactivate-btn { padding: 4px 10px; border-radius: 7px; font-size: 10px; font-weight: 700; cursor: pointer; background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.15); transition: all 0.15s; }
    .deactivate-btn:hover { background: rgba(239,68,68,0.2); }

    .empty-state { text-align: center; padding: 32px 16px; color: var(--color-text-secondary); font-size: 13px; }
    .empty-icon { font-size: 36px; margin-bottom: 8px; }
</style>

<div class="ar-page">
    {{-- Header --}}
    <div class="ar-header">
        <div>
            <a href="{{ route('deliverables.show', $deliverable) }}" class="ar-back">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Deliverable
            </a>
            <h1 style="margin-top:12px;">Client Artwork Reviews</h1>
            <div class="ar-header-sub">{{ $deliverable->title }} · {{ $deliverable->project->title ?? '' }}</div>
        </div>
    </div>

    @if(!\App\Services\FeatureManager::isClientReviewEnabled())
        <div style="margin-bottom:20px; padding:14px 20px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); border-radius:14px; display:flex; align-items:center; gap:12px; color:#ef4444; font-size:13px; font-weight:700;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>The "Send to Client" review portal is currently <strong>DISABLED</strong> in Admin Settings. External clients cannot access this proof.</span>
        </div>
    @endif

    @php
        $activeReview = isset($review) ? $review : (isset($reviews) ? $reviews->first() : null);
    @endphp

    @if(!$activeReview)
        <div class="panel">
            <div class="empty-state">
                <div class="empty-icon">🔗</div>
                <p>No review link has been generated yet.</p>
                <p style="margin-top:4px; font-size:12px;">Go back to the deliverable and click <strong>"Send to Client"</strong> to generate one.</p>
            </div>
        </div>
    @else
        <div class="ar-grid">
            {{-- Left: Artwork with pin overlay from latest review --}}
            @php
                $artworksList = !empty($artworks) ? $artworks : [];
                if (empty($artworksList)) {
                    $artworksList = $deliverable->getAllArtworkFiles();
                }
                $totalArtworks = count($artworksList);
                $initialArtworkUrl = $totalArtworks > 0 ? $artworksList[0] : null;

                $allAnnotations = $activeReview->annotations;
                $pins           = $allAnnotations->where('type', 'pin');
                $allDrawings    = $allAnnotations->where('type', 'drawing');
                $openCount      = $allAnnotations->where('is_resolved', false)->count();
                $resolvedCount  = $allAnnotations->where('is_resolved', true)->count();
            @endphp
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
            <div>
                @if($totalArtworks > 1)
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:14px; padding:8px 14px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <button onclick="teamPrevSlide()" style="background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); color:var(--color-text-primary); border-radius:8px; padding:4px 10px; font-size:11px; font-weight:700; cursor:pointer;">◀</button>
                            <span id="teamSlideIndicator" style="font-size:12px; font-weight:800; color:var(--color-text-primary);">Slide 1 of {{ $totalArtworks }}</span>
                            <button onclick="teamNextSlide()" style="background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); color:var(--color-text-primary); border-radius:8px; padding:4px 10px; font-size:11px; font-weight:700; cursor:pointer;">▶</button>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px; overflow-x:auto;">
                            @foreach($artworksList as $tIdx => $tArt)
                                <div class="team-thumb-card {{ $tIdx === 0 ? 'active' : '' }}" id="teamThumb-{{ $tIdx }}" onclick="teamSwitchSlide({{ $tIdx }})" style="cursor:pointer; border-radius:8px; padding:2px; border:2px solid {{ $tIdx === 0 ? '#0055D4' : 'transparent' }};">
                                    <img src="{{ $tArt }}" style="width:28px; height:28px; border-radius:6px; object-fit:cover;" title="Slide {{ $tIdx + 1 }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="artwork-viewer" style="padding:16px;">
                    @if($initialArtworkUrl)
                        <div id="artworkWrapper" style="position:relative; display:inline-block; width:100%;">
                            @if(preg_match('/\.(mp4|webm|ogg|mov)/i', $initialArtworkUrl))
                                <video src="{{ $initialArtworkUrl }}" controls class="artwork-viewer-img" id="teamArtworkVideo"></video>
                            @else
                                <img src="{{ $initialArtworkUrl }}" alt="Artwork" class="artwork-viewer-img" id="teamArtworkImg" onload="initTeamCanvas()">
                            @endif
                            <canvas id="team-drawing-canvas" style="position:absolute; top:0; left:0; pointer-events:none; z-index:3;"></canvas>
                            <div class="pin-overlay" id="teamPinOverlay" style="z-index: 10;">
                                @foreach($pins as $pin)
                                    <div class="tv-pin team-pin-item"
                                         id="tv-pin-{{ $pin->id }}"
                                         data-artwork-index="{{ $pin->artwork_index ?? 0 }}"
                                         data-review-id="{{ $pin->artwork_review_id }}"
                                         style="left:{{ $pin->x_percent }}%; top:{{ $pin->y_percent }}%; display:none;"
                                         title="{{ $pin->content }}">
                                        <div class="tv-pin-circle {{ $pin->is_resolved ? 'resolved' : '' }}"
                                             style="background:{{ $pin->is_resolved ? '#10b981' : $pin->color }};">
                                            {{ $pin->is_resolved ? '✓' : ($pin->pin_number ?? $loop->iteration) }}
                                        </div>
                                        <div class="tv-pin-tail {{ $pin->is_resolved ? 'resolved' : '' }}" style="background:{{ $pin->is_resolved ? '#10b981' : $pin->color }};"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div style="padding:32px; text-align:center; color:var(--color-text-secondary);">No artwork image available.</div>
                    @endif
                </div>

                @if($allDrawings->count() > 0)
                    <p style="font-size:11px; color:var(--color-text-secondary); margin-top:8px; text-align:center;">
                        ✏️ {{ $allDrawings->count() }} freehand drawing(s) loaded.
                    </p>
                @endif
            </div>

            {{-- Right: Permanent Review Card + Living Annotation Checklist --}}
            <div>
                <div class="review-card" id="review-card-{{ $activeReview->id }}">
                    <div class="review-card-head">
                        <div class="review-card-head-left">
                            <div class="review-client" style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:12px; font-weight:800; background:rgba(16,185,129,0.15); color:#10b981; border:1px solid rgba(16,185,129,0.3); padding:2px 8px; border-radius:6px;">
                                    Live Review
                                </span>
                                <span>{{ $activeReview->client_name ?? 'Client Review Session' }}</span>
                            </div>
                            <div class="review-meta" style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span>Active link for {{ $deliverable->title }}</span>
                                <span>·</span>
                                <span style="color:#0055D4; font-weight:700;">🔄 Artwork updated {{ $deliverable->updated_at?->diffForHumans() }}</span>
                            </div>
                            <div style="margin-top:6px; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span class="status-pill active">
                                    ● Active
                                </span>
                                <span style="font-size:11px; font-weight:700; color:var(--color-text-primary);">
                                    {{ $allAnnotations->count() }} total notes
                                </span>
                                <span style="font-size:11px; font-weight:800; color:#f59e0b;">
                                    · 🟡 {{ $openCount }} open
                                </span>
                                <span style="font-size:11px; font-weight:800; color:#10b981;">
                                    · 🟢 {{ $resolvedCount }} resolved
                                </span>
                            </div>
                        </div>
                        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                            {{-- Copy link button --}}
                            <button type="button" onclick="copyReviewLink('{{ route('artwork.review.show', $activeReview->token) }}', this)"
                                    style="padding:6px 12px; background:rgba(59,130,246,0.12); color:#38bdf8; border:1px solid rgba(59,130,246,0.3); border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.15s;"
                                    onmouseover="this.style.background='rgba(59,130,246,0.25)'; this.style.color='#fff';"
                                    onmouseout="this.style.background='rgba(59,130,246,0.12)'; this.style.color='#38bdf8';">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <span>Copy Link</span>
                            </button>
                            <a href="{{ route('artwork.review.show', $activeReview->token) }}" target="_blank"
                               style="font-size:10px; font-weight:700; color:var(--color-text-secondary); text-decoration:none; display:inline-flex; align-items:center; gap:3px;">
                                <span>View as Client</span> ↗
                            </a>
                        </div>
                    </div>

                    {{-- Status and Slide Filter Bar --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; padding:10px 14px; background:var(--color-bg-primary); border-bottom:1px solid var(--color-border-primary); flex-wrap:wrap;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button type="button" onclick="teamFilterStatus('all')" id="team-status-btn-all" class="team-status-btn" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid #0055D4; background:rgba(0,85,212,0.1); color:#0055D4;">
                                All ({{ $allAnnotations->count() }})
                            </button>
                            <button type="button" onclick="teamFilterStatus('open')" id="team-status-btn-open" class="team-status-btn" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid var(--color-border-primary); background:transparent; color:var(--color-text-secondary);">
                                🟡 Open ({{ $openCount }})
                            </button>
                            <button type="button" onclick="teamFilterStatus('resolved')" id="team-status-btn-resolved" class="team-status-btn" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid var(--color-border-primary); background:transparent; color:var(--color-text-secondary);">
                                🟢 Resolved ({{ $resolvedCount }})
                            </button>
                        </div>
                    </div>

                    {{-- Annotation items --}}
                    @if($activeReview->annotations->isNotEmpty())
                            @php
                                $groupedAnn = $activeReview->annotations->groupBy(fn($a) => (int)($a->artwork_index ?? 0));
                            @endphp

                            @if($totalArtworks > 1)
                                <div style="display:flex; align-items:center; gap:6px; padding:8px 14px; background:var(--color-bg-primary); border-bottom:1px solid var(--color-border-primary); overflow-x:auto;">
                                    <button type="button" onclick="teamFilterAnnotTab('all')" id="team-tab-btn-all" class="team-tab-btn" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid var(--color-border-primary); background:transparent; color:var(--color-text-secondary); white-space:nowrap;">
                                        All Slides ({{ $activeReview->annotations->count() }})
                                    </button>
                                    @for($s = 0; $s < $totalArtworks; $s++)
                                        @php $sCount = isset($groupedAnn[$s]) ? $groupedAnn[$s]->count() : 0; @endphp
                                        <button type="button" onclick="teamFilterAnnotTab({{ $s }})" id="team-tab-btn-{{ $s }}" class="team-tab-btn" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:800; cursor:pointer; border:1px solid {{ $s === 0 ? '#0055D4' : 'var(--color-border-primary)' }}; background:{{ $s === 0 ? 'rgba(0,85,212,0.1)' : 'transparent' }}; color:{{ $s === 0 ? '#0055D4' : 'var(--color-text-secondary)' }}; white-space:nowrap;">
                                            Slide {{ $s + 1 }} ({{ $sCount }})
                                        </button>
                                    @endfor
                                </div>
                            @endif

                            <div class="annot-list">
                                @foreach($groupedAnn as $slideIdx => $slideAnnotations)
                                    <div class="team-slide-annot-group" id="team-slide-group-{{ $slideIdx }}" data-slide-index="{{ $slideIdx }}" style="display:{{ $totalArtworks > 1 && $slideIdx !== 0 ? 'none' : 'block' }};">
                                        @if($totalArtworks > 1)
                                            <div style="padding:8px 12px; margin:8px 0 6px; background:var(--color-bg-primary); border-radius:8px; display:flex; justify-content:space-between; align-items:center; border-left:3px solid #0055D4; cursor:pointer;" onclick="teamSwitchSlide({{ $slideIdx }})">
                                                <span style="font-size:11px; font-weight:800; color:var(--color-text-primary);">
                                                    🖼️ Slide {{ $slideIdx + 1 }} Feedback
                                                </span>
                                                <span style="font-size:10px; font-weight:700; color:var(--color-text-secondary);">
                                                    {{ $slideAnnotations->count() }} item(s)
                                                </span>
                                            </div>
                                        @endif

                                        @foreach($slideAnnotations as $ann)
                                            <div class="annot-item team-note-card {{ $ann->is_resolved ? 'resolved' : 'open' }}" id="annot-{{ $ann->id }}" data-resolved="{{ $ann->is_resolved ? '1' : '0' }}" data-color="{{ $ann->color }}" onclick="teamSwitchSlide({{ $ann->artwork_index ?? 0 }}); flashTeamPin({{ $ann->id }});" style="cursor:pointer; margin-bottom:8px; border-left:3px solid {{ $ann->is_resolved ? '#10b981' : '#f59e0b' }};">
                                                <div class="annot-badge" style="background:{{ $ann->is_resolved ? '#10b981' : $ann->color }};">
                                                    @if($ann->type === 'pin') {{ $ann->is_resolved ? '✓' : ($ann->pin_number ?? '•') }}
                                                    @elseif($ann->type === 'drawing') ✏
                                                    @else T
                                                    @endif
                                                </div>
                                                <div class="annot-content">
                                                    <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                                                        <div style="display:flex; align-items:center; gap:6px;">
                                                            <span class="annot-type">{{ ucfirst($ann->type) }}</span>
                                                            <span style="font-size:10px; font-weight:800; color:{{ $ann->is_resolved ? '#10b981' : '#f59e0b' }};">
                                                                {{ $ann->is_resolved ? '✓ Resolved' : '🟡 Open' }}
                                                            </span>
                                                        </div>
                                                        @if($totalArtworks > 1)
                                                            <span style="font-size:9px; font-weight:800; color:#0055D4; background:rgba(0,85,212,0.08); padding:1px 6px; border-radius:4px;">
                                                                Slide {{ ($ann->artwork_index ?? 0) + 1 }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($ann->content && $ann->type !== 'drawing')
                                                        <div class="annot-text">{{ $ann->content }}</div>
                                                    @else
                                                        <div class="annot-text" style="color:var(--color-text-secondary);">Freehand drawing</div>
                                                    @endif
                                                    
                                                    {{-- Multi-comment Discussion Thread --}}
                                                    <div id="annot-comments-container-{{ $ann->id }}" style="margin-top:8px; display:flex; flex-direction:column; gap:6px;">
                                                        <div id="annot-comments-list-{{ $ann->id }}" style="display:flex; flex-direction:column; gap:6px;">
                                                            @foreach($ann->comments as $comment)
                                                                <div id="annot-comment-item-{{ $comment->id }}" style="padding:8px 10px; border-radius:8px; background:rgba(0,85,212,0.06); border:1px solid rgba(0,85,212,0.18); font-size:11px;">
                                                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                                                                        <div style="display:flex; align-items:center; gap:6px;">
                                                                            <span style="font-weight:800; color:#0055D4; font-size:10px;">
                                                                                {{ $comment->user->name ?? 'Team Member' }}
                                                                            </span>
                                                                            <span style="color:var(--color-text-secondary); font-size:9px;">
                                                                                {{ $comment->created_at->diffForHumans() }}
                                                                            </span>
                                                                        </div>
                                                                        @if($comment->user_id === auth()->id() || auth()->user()?->isAdmin())
                                                                            <button type="button" onclick="deleteAnnotationComment({{ $comment->id }})" title="Delete Comment" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#ef4444; cursor:pointer; font-size:10px; font-weight:700; padding:2px 7px; border-radius:5px; display:inline-flex; align-items:center; gap:3px; transition:all 0.15s;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff';" onmouseout="this.style.background='rgba(239,68,68,0.15)'; this.style.color='#ef4444';">
                                                                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                                                <span>Delete</span>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                    <div style="color:var(--color-text-primary); font-size:11px; white-space:pre-wrap; word-break:break-word;">{{ $comment->comment }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        {{-- Inline Add Reply Box --}}
                                                        <div id="reply-form-{{ $ann->id }}" style="display:none; margin-top:6px;">
                                                            <textarea id="reply-input-{{ $ann->id }}" rows="2" placeholder="Write a comment / reply..." style="width:100%; padding:6px 8px; font-size:11px; border:1px solid var(--color-border-primary); border-radius:6px; background:var(--color-bg-primary); color:var(--color-text-primary); outline:none; resize:none;"></textarea>
                                                            <div style="display:flex; justify-content:flex-end; gap:6px; margin-top:4px;">
                                                                <button type="button" onclick="toggleReplyForm({{ $ann->id }})" style="padding:4px 10px; border-radius:6px; font-size:10px; font-weight:700; border:1px solid var(--color-border-primary); background:transparent; color:var(--color-text-secondary); cursor:pointer;">Cancel</button>
                                                                <button type="button" onclick="submitAnnotationResponse({{ $ann->id }})" style="padding:4px 12px; border-radius:6px; font-size:10px; font-weight:700; border:none; background:#0055D4; color:#fff; cursor:pointer;">Post Comment</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if($ann->is_resolved && $ann->resolvedBy)
                                                        <div style="font-size:10px; color:var(--color-text-secondary); margin-top:4px;">
                                                            Resolved by {{ $ann->resolvedBy->name }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
                                                    <button type="button" onclick="toggleReplyForm({{ $ann->id }})" class="annot-resolve-btn" style="background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.2);">
                                                        💬 Reply
                                                    </button>
                                                    <button type="button" class="annot-resolve-btn {{ $ann->is_resolved ? 'unresolve' : 'resolve' }}" onclick="toggleResolve({{ $ann->id }}, this)">
                                                        {{ $ann->is_resolved ? '↩ Reopen' : '✓ Resolve' }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding:16px; text-align:center; font-size:12px; color:var(--color-text-secondary);">
                                No annotations submitted yet on this deliverable.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function toggleReplyForm(id) {
    const form = document.getElementById('reply-form-' + id);
    if (!form) return;
    if (form.style.display === 'none' || !form.style.display) {
        form.style.display = 'block';
        const input = document.getElementById('reply-input-' + id);
        if (input) { input.value = ''; input.focus(); }
    } else {
        form.style.display = 'none';
    }
}

function submitAnnotationResponse(id) {
    const input = document.getElementById('reply-input-' + id);
    if (!input) return;
    const text = input.value.trim();
    if (!text) {
        input.style.borderColor = '#ef4444';
        return;
    }

    fetch(`/artwork-annotations/${id}/respond`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ comment: text }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.comment) {
            const list = document.getElementById('annot-comments-list-' + id);
            if (list) {
                const c = data.comment;
                const commentHtml = `
                    <div id="annot-comment-item-${c.id}" style="padding:8px 10px; border-radius:8px; background:rgba(0,85,212,0.06); border:1px solid rgba(0,85,212,0.18); font-size:11px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="font-weight:800; color:#0055D4; font-size:10px;">${escHtml(c.user_name)}</span>
                                <span style="color:var(--color-text-secondary); font-size:9px;">${escHtml(c.created_at_human)}</span>
                            </div>
                            <button type="button" onclick="deleteAnnotationComment(${c.id})" title="Delete Comment" style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#ef4444; cursor:pointer; font-size:10px; font-weight:700; padding:2px 7px; border-radius:5px; display:inline-flex; align-items:center; gap:3px; transition:all 0.15s;" onmouseover="this.style.background='#ef4444'; this.style.color='#fff';" onmouseout="this.style.background='rgba(239,68,68,0.15)'; this.style.color='#ef4444';">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span>Delete</span>
                            </button>
                        </div>
                        <div style="color:var(--color-text-primary); font-size:11px; white-space:pre-wrap; word-break:break-word;">${escHtml(c.comment)}</div>
                    </div>`;
                list.insertAdjacentHTML('beforeend', commentHtml);
            }
            toggleReplyForm(id);
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(err => console.error(err));
}

async function deleteAnnotationComment(commentId) {
    if (!await window.customConfirm({ title: 'Delete Comment?', message: 'Are you sure you want to delete this comment?', isDanger: true })) return;

    fetch(`/artwork-annotation-comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById('annot-comment-item-' + commentId);
            if (el) el.remove();
        } else {
            alert(data.error || 'Failed to delete comment.');
        }
    })
    .catch(err => console.error(err));
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function toggleResolve(annotId, btn) {
    btn.disabled = true;
    try {
        const resp = await fetch(`/artwork-annotations/${annotId}/resolve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        const data = await resp.json();
        if (data.success) {
            const item = document.getElementById(`annot-${annotId}`);
            const origColor = item ? (item.dataset.color || '#ef4444') : '#ef4444';
            const badge = item ? item.querySelector('.annot-badge') : null;
            const pinEl = document.getElementById(`tv-pin-${annotId}`);
            const circle = pinEl ? pinEl.querySelector('.tv-pin-circle') : null;
            const tail = pinEl ? pinEl.querySelector('.tv-pin-tail') : null;

            if (data.is_resolved) {
                if (item) { item.classList.add('resolved'); }
                if (badge) { badge.style.background = '#10b981'; }
                if (circle) { circle.classList.add('resolved'); circle.style.background = '#10b981'; }
                if (tail) { tail.classList.add('resolved'); tail.style.background = '#10b981'; }
                btn.textContent = '↩ Reopen';
                btn.classList.remove('resolve'); btn.classList.add('unresolve');
            } else {
                if (item) { item.classList.remove('resolved'); }
                if (badge) { badge.style.background = origColor; }
                if (circle) { circle.classList.remove('resolved'); circle.style.background = origColor; }
                if (tail) { tail.classList.remove('resolved'); tail.style.background = origColor; }
                btn.textContent = '✓ Resolve';
                btn.classList.remove('unresolve'); btn.classList.add('resolve');
            }

            // Sync with local drawings data array and redraw
            const localDrawing = drawingsData.find(d => d.id === annotId);
            if (localDrawing) {
                localDrawing.is_resolved = data.is_resolved;
                initTeamCanvas();
            }
        }
    } catch(e) { console.error(e); }
    btn.disabled = false;
}

async function deactivateReview(reviewId, btn) {
    if (!await window.customConfirm({ title: 'Deactivate Link?', message: 'Deactivate this review link? The client will no longer be able to use it.', isDanger: true })) return;
    btn.disabled = true;
    try {
        const resp = await fetch(`/artwork-reviews/${reviewId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        const data = await resp.json();
        if (data.success) {
            btn.remove();
            const card = document.getElementById(`review-card-${reviewId}`);
            if (card) {
                const pill = card.querySelector('.status-pill');
                if (pill) { pill.textContent = '● Inactive'; pill.className = 'status-pill inactive'; }
            }
        }
    } catch(e) { console.error(e); }
    btn.disabled = false;
}

function copyReviewLink(url, btn) {
    if (!url) return;

    function setSuccess() {
        if (!btn) return;
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg> <span>Copied!</span>';
        btn.style.background = 'rgba(34,197,94,0.18)';
        btn.style.borderColor = 'rgba(34,197,94,0.4)';
        btn.style.color = '#22c55e';
        setTimeout(() => {
            btn.innerHTML = origHtml;
            btn.style.background = '';
            btn.style.borderColor = '';
            btn.style.color = '';
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url)
            .then(setSuccess)
            .catch(() => fallbackCopyText(url, setSuccess));
    } else {
        fallbackCopyText(url, setSuccess);
    }
}

function fallbackCopyText(text, onSuccess) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        const successful = document.execCommand('copy');
        if (successful && onSuccess) {
            onSuccess();
        } else if (!successful) {
            prompt("Copy this review link:", text);
        }
    } catch (err) {
        prompt("Copy this review link:", text);
    }
    document.body.removeChild(textArea);
}

// ──────────────────────────────────────────────────────────────────────────────
// Team canvas drawing paths renderer & Multi-artwork Slide Switcher
// ──────────────────────────────────────────────────────────────────────────────
let teamCanvas = null;
let currentTeamSlideIndex = 0;
let currentTeamStatusFilter = 'all';
const TEAM_ARTWORKS = @json($artworksList ?? []);

@php
$drawingsList = $allDrawings ?? collect();
$drawingsMapped = $drawingsList->map(function($d) {
    return [
        'id'            => $d->id,
        'artwork_index' => (int)($d->artwork_index ?? 0),
        'content'       => $d->content,
        'color'         => $d->color,
        'x_percent'     => $d->x_percent,
        'y_percent'     => $d->y_percent,
        'is_resolved'   => (bool)$d->is_resolved
    ];
})->values();
@endphp
const drawingsData = @json($drawingsMapped);

function teamFilterStatus(status) {
    currentTeamStatusFilter = status;
    document.querySelectorAll('.team-status-btn').forEach(b => {
        b.style.borderColor = 'var(--color-border-primary)';
        b.style.background = 'transparent';
        b.style.color = 'var(--color-text-secondary)';
    });
    const clicked = document.getElementById('team-status-btn-' + status);
    if (clicked) {
        clicked.style.borderColor = '#0055D4';
        clicked.style.background = 'rgba(0,85,212,0.1)';
        clicked.style.color = '#0055D4';
    }

    document.querySelectorAll('.team-note-card').forEach(card => {
        const isResolved = card.getAttribute('data-resolved') === '1';
        if (status === 'all') {
            card.style.display = 'flex';
        } else if (status === 'open') {
            card.style.display = !isResolved ? 'flex' : 'none';
        } else if (status === 'resolved') {
            card.style.display = isResolved ? 'flex' : 'none';
        }
    });
}

function teamSwitchSlide(idx) {
    if (idx < 0 || idx >= TEAM_ARTWORKS.length) return;
    currentTeamSlideIndex = idx;

    // Update slide indicator
    const ind = document.getElementById('teamSlideIndicator');
    if (ind) ind.textContent = `Slide ${idx + 1} of ${TEAM_ARTWORKS.length}`;

    // Update thumbnail highlights
    document.querySelectorAll('.team-thumb-card').forEach((el, i) => {
        el.style.borderColor = (i === idx) ? '#0055D4' : 'transparent';
    });

    // Update main image/video
    const img = document.getElementById('teamArtworkImg');
    if (img) {
        img.src = TEAM_ARTWORKS[idx];
    }

    // Toggle pins visibility for this slide
    document.querySelectorAll('.team-pin-item').forEach(pin => {
        const pinSlide = parseInt(pin.getAttribute('data-artwork-index') || '0');
        pin.style.display = (pinSlide === idx) ? 'flex' : 'none';
    });

    // Highlight right-panel tab button for this slide
    document.querySelectorAll('.team-tab-btn').forEach(btn => {
        btn.style.borderColor = 'var(--color-border-primary)';
        btn.style.background = 'transparent';
        btn.style.color = 'var(--color-text-secondary)';
    });
    const activeTabBtn = document.getElementById(`team-tab-btn-${idx}`);
    if (activeTabBtn) {
        activeTabBtn.style.borderColor = '#0055D4';
        activeTabBtn.style.background = 'rgba(0,85,212,0.1)';
        activeTabBtn.style.color = '#0055D4';
    }

    // Show only this slide's annotations in the right panel
    document.querySelectorAll('.team-slide-annot-group').forEach(grp => {
        const grpIdx = parseInt(grp.getAttribute('data-slide-index') || '0');
        grp.style.display = (grpIdx === idx) ? 'block' : 'none';
    });

    initTeamCanvas();
}

function teamFilterAnnotTab(filter) {
    document.querySelectorAll('.team-tab-btn').forEach(btn => {
        btn.style.borderColor = 'var(--color-border-primary)';
        btn.style.background = 'transparent';
        btn.style.color = 'var(--color-text-secondary)';
    });

    const clickedBtn = document.getElementById(`team-tab-btn-${filter}`);
    if (clickedBtn) {
        clickedBtn.style.borderColor = '#0055D4';
        clickedBtn.style.background = 'rgba(0,85,212,0.1)';
        clickedBtn.style.color = '#0055D4';
    }

    if (filter === 'all') {
        document.querySelectorAll('.team-slide-annot-group').forEach(g => g.style.display = 'block');
    } else {
        document.querySelectorAll('.team-slide-annot-group').forEach(g => {
            const gIdx = parseInt(g.getAttribute('data-slide-index') || '0');
            g.style.display = (gIdx === filter) ? 'block' : 'none';
        });
        teamSwitchSlide(filter);
    }
}

function teamPrevSlide() {
    if (currentTeamSlideIndex > 0) {
        teamSwitchSlide(currentTeamSlideIndex - 1);
    }
}

function teamNextSlide() {
    if (currentTeamSlideIndex < TEAM_ARTWORKS.length - 1) {
        teamSwitchSlide(currentTeamSlideIndex + 1);
    }
}

function flashTeamPin(id) {
    const pin = document.getElementById(`tv-pin-${id}`);
    if (pin) {
        pin.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        pin.style.transform = 'translate(-50%, -100%) scale(1.5)';
        pin.style.transition = 'transform 0.2s ease';
        pin.style.zIndex = '999';
        setTimeout(() => {
            pin.style.transform = 'translate(-50%, -100%) scale(1)';
            setTimeout(() => { pin.style.zIndex = '10'; }, 300);
        }, 600);
    }
}

function initTeamCanvas() {
    const img = document.getElementById('teamArtworkImg');
    if (!img) return;
    
    const w = img.offsetWidth;
    const h = img.offsetHeight;
    if (!w || !h) {
        setTimeout(initTeamCanvas, 50);
        return;
    }

    const wrapper = document.getElementById('artworkWrapper');
    if (wrapper) {
        wrapper.style.width  = w + 'px';
        wrapper.style.height = h + 'px';
    }

    const el = document.getElementById('team-drawing-canvas');
    if (!el) return;

    el.width  = w;
    el.height = h;
    el.style.width  = w + 'px';
    el.style.height = h + 'px';

    if (teamCanvas) {
        teamCanvas.setWidth(w);
        teamCanvas.setHeight(h);
        teamCanvas.clear();
    } else {
        teamCanvas = new fabric.StaticCanvas('team-drawing-canvas', {
            width: w,
            height: h
        });
    }

    // Render drawings only for current slide and active round
    const slideDrawings = drawingsData.filter(d => 
        (d.artwork_index || 0) === currentTeamSlideIndex &&
        (currentTeamRoundId === 'all' || d.review_id == currentTeamRoundId)
    );

    slideDrawings.forEach(d => {
        try {
            const pathObj = JSON.parse(d.content);
            
            fabric.util.enlivenObjects([pathObj], function(objects) {
                const path = objects[0];
                if (!path) return;

                path.set({
                    selectable: false,
                    evented: false,
                    stroke: d.is_resolved ? '#10b981' : (d.color || '#ef4444'),
                    opacity: d.is_resolved ? 0.75 : 1.0
                });

                const xPos = (d.x_percent / 100) * w;
                const yPos = (d.y_percent / 100) * h;
                
                path.set({
                    left: xPos,
                    top: yPos
                });

                teamCanvas.add(path);
            });
        } catch (e) {
            console.error('Failed to render drawing', e);
        }
    });

    teamCanvas.renderAll();
}

// Re-init canvas on window resize
window.addEventListener('resize', () => {
    initTeamCanvas();
});

// Update resolve status dynamically on canvas
window.addEventListener('load', () => {
    setTimeout(initTeamCanvas, 200);
});

</script>
</x-layout>
