<x-layout title="Add New User">
<style>
.f-wrap{max-width:560px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:16px;overflow:hidden;font-family:'Inter',sans-serif;}
.f-section{padding:24px 28px;border-bottom:1px solid var(--color-border-primary);}
.f-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:8px;}
.f-label.blue{color:#3b82f6;}
.f-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:10px;padding:10px 14px;font-size:14px;font-weight:500;color:var(--color-text-primary);outline:none;transition:all 0.15s;-webkit-appearance:none;appearance:none;}
.f-input:focus{border-color:#3b82f6;background:var(--color-bg-primary);box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
.f-input::placeholder{color:var(--color-text-secondary);opacity:0.5;}
.f-title{width:100%;background:transparent;border:none;outline:none;font-size:22px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
.f-title::placeholder{color:var(--color-border-primary);}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.f-footer{background:var(--color-bg-secondary);padding:16px 28px;display:flex;justify-content:flex-end;gap:10px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-cancel{padding:9px 20px;border-radius:9px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.15s;}
.btn-cancel:hover{background:var(--color-bg-secondary);}
.btn-submit{padding:9px 24px;border-radius:9px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(0,85,212,0.25);transition:all 0.15s;}
.btn-submit:hover{background:#0044aa;}
.f-err{color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;}
</style>

<nav style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin:0 auto 12px;max-width:560px;">
    <a href="{{ route('admin.settings') }}" style="text-decoration:none;color:inherit;">Admin</a>
    <span style="opacity:0.4;">/</span>
    <a href="{{ route('users.index') }}" style="text-decoration:none;color:inherit;">Users</a>
    <span style="opacity:0.4;">/</span>
    <span style="color:var(--color-text-primary);">New User</span>
</nav>
<div class="f-wrap">
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        <div class="f-section">
            <label class="f-label blue">Full Name</label>
            <input type="text" name="name" required placeholder="Enter full name…" class="f-title"
                   value="{{ old('name') }}">
            @error('name')<p class="f-err">{{ $message }}</p>@enderror
        </div>

        <div class="f-section">
            <div>
                <label class="f-label">Username</label>
                <input type="text" name="username" required placeholder="e.g. jsmith" class="f-input"
                       value="{{ old('username') }}" autocomplete="username" pattern="[a-zA-Z0-9_\-]+" maxlength="30">
                <p style="font-size:11px;color:var(--color-text-secondary);margin-top:6px;font-weight:500;">Letters, numbers, underscores, and hyphens only.</p>
                @error('username')<p class="f-err">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Email Address</label>
                    <input type="email" name="email" required placeholder="name@example.com" class="f-input"
                           value="{{ old('email') }}">
                    @error('email')<p class="f-err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="f-label">Workspace Role</label>
                    <select name="role" required class="f-input">
                        @foreach(['Writer','Designer','Coordinator','Traffic Coordinator','Approver','Brand Manager','Admin'] as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="f-err">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="f-input">
                    @error('password')<p class="f-err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="f-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="f-input">
                </div>
            </div>
        </div>

        <div class="f-footer">
            <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Create Account</button>
        </div>
    </form>
</div>
</x-layout>
