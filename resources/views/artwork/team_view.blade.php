<x-layout title="Artwork Review — {{ $deliverable->title }}">
<style>
    .ar-page { max-width: 1280px; margin: 0 auto; }
    .ar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .ar-header h1 { font-size: 20px; font-weight: 800; color: var(--color-text-primary); }
    .ar-header-sub { font-size: 12px; color: var(--color-text-secondary); font-weight: 500; margin-top: 2px; }
    .ar-back { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--color-text-secondary); text-decoration: none; padding: 6px 12px; border: 1px solid var(--color-border-primary); border-radius: 8px; transition: all 0.15s; }
    .ar-back:hover { color: var(--color-text-primary); background: var(--color-bg-secondary); }

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
    }
    .tv-pin-circle.resolved { opacity: 0.4; filter: grayscale(1); }
    .tv-pin-tail { width: 2px; height: 8px; margin-top: -1px; border-radius: 2px; }

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
    .annot-item.resolved { opacity: 0.5; }
    .annot-badge { width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800; color: #fff; flex-shrink: 0; }
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

    @if($reviews->isEmpty())
        <div class="panel">
            <div class="empty-state">
                <div class="empty-icon">🔗</div>
                <p>No review links have been generated yet.</p>
                <p style="margin-top:4px; font-size:12px;">Go back to the deliverable and click <strong>"Send to Client"</strong> to generate one.</p>
            </div>
        </div>
    @else
        <div class="ar-grid">
            {{-- Left: Artwork with pin overlay from latest review --}}
            @php
                $latestReview = $reviews->first();
                $artworkUrl   = $deliverable->final_designs ?? $deliverable->image_url ?? null;
                $pins         = $latestReview ? $latestReview->annotations->where('type', 'pin') : collect();
                $allDrawings  = $latestReview ? $latestReview->annotations->where('type', 'drawing') : collect();
            @endphp
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
            <div>
                <div class="artwork-viewer" style="padding:16px;">
                    @if($artworkUrl)
                        <div id="artworkWrapper" style="position:relative; display:inline-block; width:100%;">
                            @if(preg_match('/\.(mp4|webm|ogg|mov)/i', $artworkUrl))
                                <video src="{{ $artworkUrl }}" controls class="artwork-viewer-img"></video>
                            @else
                                <img src="{{ $artworkUrl }}" alt="Artwork" class="artwork-viewer-img" id="teamArtworkImg" onload="initTeamCanvas()" crossorigin="anonymous">
                            @endif
                            <canvas id="team-drawing-canvas" style="position:absolute; top:0; left:0; pointer-events:none; z-index:3;"></canvas>
                            <div class="pin-overlay" id="teamPinOverlay" style="z-index: 10;">
                                @foreach($pins as $pin)
                                    <div class="tv-pin"
                                         style="left:{{ $pin->x_percent }}%; top:{{ $pin->y_percent }}%;"
                                         title="{{ $pin->content }}">
                                        <div class="tv-pin-circle {{ $pin->is_resolved ? 'resolved' : '' }}"
                                             style="background:{{ $pin->color }};">
                                            {{ $pin->pin_number ?? $loop->iteration }}
                                        </div>
                                        <div class="tv-pin-tail" style="background:{{ $pin->color }};"></div>
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

            {{-- Right: Review cards + annotation list --}}
            <div>
                @foreach($reviews as $review)
                    <div class="review-card" id="review-card-{{ $review->id }}">
                        <div class="review-card-head">
                            <div class="review-card-head-left">
                                <div class="review-client">
                                    {{ $review->client_name ?? 'Client (anonymous)' }}
                                </div>
                                <div class="review-meta">
                                    Generated {{ $review->created_at->diffForHumans() }}
                                    @if($review->expires_at)
                                        · Expires {{ $review->expires_at->format('M j, Y') }}
                                    @endif
                                    @if($review->creator)
                                        · by {{ $review->creator->name }}
                                    @endif
                                </div>
                                <div style="margin-top:6px; display:flex; align-items:center; gap:8px;">
                                    <span class="status-pill {{ $review->isAccessible() ? 'active' : 'inactive' }}">
                                        {{ $review->isAccessible() ? '● Active' : '● Inactive' }}
                                    </span>
                                    <span style="font-size:11px; color:var(--color-text-secondary);">
                                        {{ $review->annotations->count() }} annotation(s)
                                        · {{ $review->annotations->where('is_resolved',false)->count() }} open
                                    </span>
                                </div>
                            </div>
                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                                {{-- Copy link button --}}
                                <button onclick="copyReviewLink('{{ route('artwork.review.show', $review->token) }}', this)"
                                        style="padding:5px 12px; background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2); border-radius:8px; font-size:10px; font-weight:700; cursor:pointer; transition:all 0.15s;">
                                    📋 Copy Link
                                </button>
                                @if($review->is_active)
                                    <button class="deactivate-btn" onclick="deactivateReview({{ $review->id }}, this)">
                                        Deactivate
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Annotation items --}}
                        @if($review->annotations->isNotEmpty())
                            <div class="annot-list">
                                @foreach($review->annotations as $ann)
                                    <div class="annot-item {{ $ann->is_resolved ? 'resolved' : '' }}" id="annot-{{ $ann->id }}">
                                        <div class="annot-badge" style="background:{{ $ann->color }};">
                                            @if($ann->type === 'pin') {{ $ann->pin_number ?? '•' }}
                                            @elseif($ann->type === 'drawing') ✏
                                            @else T
                                            @endif
                                        </div>
                                        <div class="annot-content">
                                            <div class="annot-type">{{ ucfirst($ann->type) }}</div>
                                            @if($ann->content && $ann->type !== 'drawing')
                                                <div class="annot-text">{{ $ann->content }}</div>
                                            @else
                                                <div class="annot-text" style="color:var(--color-text-secondary);">Freehand drawing</div>
                                            @endif
                                            @if($ann->is_resolved && $ann->resolvedBy)
                                                <div style="font-size:10px; color:var(--color-text-secondary); margin-top:3px;">
                                                    Resolved by {{ $ann->resolvedBy->name }}
                                                </div>
                                            @endif
                                        </div>
                                        <button
                                            class="annot-resolve-btn {{ $ann->is_resolved ? 'unresolve' : 'resolve' }}"
                                            onclick="toggleResolve({{ $ann->id }}, this)">
                                            {{ $ann->is_resolved ? '↩ Reopen' : '✓ Resolve' }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="padding:16px; text-align:center; font-size:12px; color:var(--color-text-secondary);">
                                No annotations submitted yet for this link.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
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
            if (data.is_resolved) {
                item.classList.add('resolved');
                btn.textContent = '↩ Reopen';
                btn.classList.remove('resolve'); btn.classList.add('unresolve');
            } else {
                item.classList.remove('resolved');
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
    if (!confirm('Deactivate this review link? The client will no longer be able to use it.')) return;
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
    navigator.clipboard.writeText(url).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Copied!';
        btn.style.background = 'rgba(34,197,94,0.1)';
        btn.style.color = '#22c55e';
        setTimeout(() => { btn.textContent = orig; btn.style.background = ''; btn.style.color = ''; }, 2000);
    });
}

// ──────────────────────────────────────────────────────────────────────────────
// Team canvas drawing paths renderer
// ──────────────────────────────────────────────────────────────────────────────
let teamCanvas = null;
const drawingsData = @json($allDrawings->map(fn($d) => [
    'id' => $d->id,
    'content' => $d->content,
    'color' => $d->color,
    'x_percent' => $d->x_percent,
    'y_percent' => $d->y_percent,
    'is_resolved' => (bool)$d->is_resolved
])->values());

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

    // Render each drawing
    drawingsData.forEach(d => {
        if (d.is_resolved) return;
        try {
            const pathObj = JSON.parse(d.content);
            
            // Reconstruct fabric Path
            fabric.util.enlivenObjects([pathObj], function(objects) {
                const path = objects[0];
                if (!path) return;

                path.set({
                    selectable: false,
                    evented: false,
                    stroke: d.color || '#ef4444'
                });

                // Calculate scaling factors based on original drawing percentage coordinates
                const xPos = (d.x_percent / 100) * w;
                const yPos = (d.y_percent / 100) * h;
                
                // Adjust position
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
