<x-layout title="{{ $brand->name }} | Retainer Task Board">
    <style>
        .rtb-container { display: flex; flex-direction: column; gap: 32px; }
        .rtb-table-wrap { background: var(--color-bg-primary); border-radius: 28px; border: 1px solid var(--color-border-primary); box-shadow: 0 8px 30px rgba(0,0,0,0.04); overflow: hidden; }
        .rtb-header { padding: 32px; border-bottom: 1px solid var(--color-bg-secondary); }
        .rtb-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .rtb-table th { padding: 16px 20px; text-align: left; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: var(--color-text-secondary); border-bottom: 1.5px solid var(--color-border-primary); }
        .rtb-table td { padding: 16px 20px; vertical-align: top; border-bottom: 1px solid var(--color-bg-secondary); }
        
        .col-project { width: 150px; }
        .col-date { width: 100px; }
        .col-task { width: 180px; }
        .col-type { width: 120px; }
        .col-text { width: 200px; }
        .col-ref { width: 120px; }

        .text-truncate-box { 
            font-size: 11px; 
            color: var(--color-text-primary); 
            line-height: 1.5; 
            max-height: 80px; 
            overflow-y: auto; 
            background: var(--color-bg-secondary); 
            padding: 8px 12px; 
            border-radius: 10px; 
            font-weight: 500;
            scrollbar-width: thin;
        }

        .subtask-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 8px; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; border: 1px solid; }
        
        .ref-preview { display: block; width: 100%; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--color-border-primary); transition: transform 0.2s; }
        .ref-preview:hover { transform: scale(1.1); }
        .ref-link { display: inline-flex; align-items: center; gap: 4px; font-size: 9px; font-weight: 800; color: #0055D4; text-transform: uppercase; }
        
        .user-chip { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
        .user-dot { width: 16px; height: 16px; border-radius: 50%; background: #0055D4; color: #fff; font-size: 8px; font-weight: 900; display: flex; align-items: center; justify-content: center; }
        .user-name { font-size: 10px; font-weight: 700; color: var(--color-text-secondary); }

        .rtb-row:hover { background: rgba(0,85,212,0.02); }
    </style>

    <div class="rtb-container">
        <!-- Breadcrumbs & Title -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <nav style="display: flex; gap: 10px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.2em; color: var(--color-text-secondary); margin-bottom: 12px;">
                    <a href="/brands" style="text-decoration: none; color: inherit;">Brands</a>
                    <span>/</span>
                    <a href="{{ route('brands.show', $brand) }}" style="text-decoration: none; color: inherit;">{{ $brand->name }}</a>
                </nav>
                <h1 style="font-size: 32px; font-weight: 900; color: var(--color-text-primary); letter-spacing: -0.02em;">
                    Retainer Board
                </h1>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                 @can('create-deliverable')
                 <a href="{{ route('deliverables.create') }}" style="padding: 12px 24px; background: #0055D4; border-radius: 12px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: #ffffff; text-decoration: none; box-shadow: 0 10px 25px rgba(0,85,212,0.2);">New Deliverable</a>
                 @endcan
            </div>
        </div>

        <div class="rtb-table-wrap">
            <div style="overflow-x: auto;">
                <table class="rtb-table">
                    <thead>
                        <tr>
                            <th class="col-project">Project Name</th>
                            <th class="col-date">Due Date</th>
                            <th class="col-task">Deliverable</th>
                            <th class="col-type">Post Type</th>
                            <th class="col-text">Concept</th>
                            <th class="col-text">Caption</th>
                            <th class="col-text">Post Copy</th>
                            <th class="col-ref">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliverables as $task)
                        <tr class="rtb-row">
                            <td class="col-project">
                                <div style="font-size: 12px; font-weight: 800; color: #0055D4;">{{ $task->project->name }}</div>
                                @if($task->project->job_number)
                                    <div style="font-size: 9px; font-weight: 700; color: var(--color-text-secondary); opacity: 0.6; margin-top: 2px;">[{{ $task->project->job_number }}]</div>
                                @endif
                            </td>
                            <td class="col-date">
                                <div style="font-size: 12px; font-weight: 800; color: var(--color-text-primary);">
                                    {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y') : '—' }}
                                </div>
                                <div style="font-size: 9px; font-weight: 700; color: var(--color-text-secondary); margin-top: 4px;">
                                    {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('H:i A') : '' }}
                                </div>
                            </td>
                            <td class="col-task">
                                <div style="font-size: 13px; font-weight: 800; color: var(--color-text-primary); margin-bottom: 8px;">{{ $task->title }}</div>
                                @if($task->writer)
                                    <div class="user-chip">
                                        <div class="user-dot">{{ substr($task->writer->name, 0, 1) }}</div>
                                        <span class="user-name">{{ $task->writer->name }}</span>
                                    </div>
                                @endif
                                <div style="margin-top: 8px;">
                                    <span style="font-size: 10px; font-weight: 900; background: rgba(0, 85, 212, 0.1); padding: 4px 10px; border-radius: 8px; color: #0055D4; text-transform: uppercase; letter-spacing: 0.02em;">{{ $task->approval_stage ?: 'Writer' }}</span>
                                </div>
                            </td>
                            <td class="col-type">
                                @if($task->subtask_type)
                                    @php $colors = $task->subtask_type_colors; @endphp
                                    <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }};">
                                        {{ $task->subtask_type }}
                                    </span>
                                @else
                                    <span style="color: var(--color-text-secondary); font-size: 11px;">—</span>
                                @endif
                            </td>
                            <td class="col-text">
                                <div class="text-truncate-box">
                                    {{ $task->concept ?: 'No concept provided' }}
                                </div>
                            </td>
                            <td class="col-text">
                                <div class="text-truncate-box">
                                    {{ $task->caption ?: 'No caption provided' }}
                                </div>
                            </td>
                            <td class="col-text">
                                <div class="text-truncate-box">
                                    {{ $task->post_copy ?: 'No copy provided' }}
                                </div>
                            </td>
                            <td class="col-ref">
                                @if($task->reference_file)
                                    <a href="{{ $task->reference_file }}" target="_blank">
                                        <img src="{{ $task->reference_file }}" class="ref-preview">
                                    </a>
                                @elseif($task->reference)
                                    <a href="{{ $task->reference }}" target="_blank" class="ref-link">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Link
                                    </a>
                                @else
                                    <span style="color: var(--color-text-secondary); font-size: 11px;">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" style="padding: 80px 32px; text-align: center;">
                                <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 16px;">
                                    <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-bg-secondary); border: 2px dashed var(--color-border-primary); display: flex; align-items: center; justify-content: center; color: var(--color-text-secondary);">
                                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <p style="font-size: 16px; font-weight: 800; color: var(--color-text-primary); margin: 0 0 4px;">No deliverables found</p>
                                        <p style="font-size: 13px; font-weight: 500; color: var(--color-text-secondary); margin: 0;">Try creating a deliverable for a retainer project first.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
