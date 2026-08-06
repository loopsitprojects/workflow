<x-layout title="Add New User">
<style>
.f-wrap{max-width:640px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;}
.f-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);}
.f-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:7px;}
.f-label.blue{color:#3b82f6;}
.f-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s;-webkit-appearance:none;appearance:none;}
.f-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
.f-input::placeholder{color:var(--color-text-secondary);opacity:0.45;}
.f-title{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
.f-title::placeholder{opacity:0.25;color:var(--color-text-primary);}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.f-footer{background:var(--color-bg-secondary);padding:14px 24px;display:flex;justify-content:flex-end;gap:8px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-cancel{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.12s;}
.btn-cancel:hover{background:var(--color-bg-secondary);color:var(--color-text-primary);}
.btn-submit{padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,85,212,0.25);transition:all 0.12s;}
.btn-submit:hover{background:#0044aa;}
.f-err{color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;}
</style>

<nav style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin:0 auto 12px;max-width:640px;">
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
            <div>
                <label class="f-label blue">Username</label>
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
                        @foreach(['Operations Manager','Writer','Designer','Coordinator','Approver','Approver Coordinator','Brand Manager','Admin'] as $role)
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
                    <div style="position:relative;">
                        <input type="password" id="password_input" name="password" required placeholder="••••••••" class="f-input" style="padding-right: 40px;">
                        <button type="button" onclick="togglePassword('password_input', 'eye_icon_1')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--color-text-secondary); cursor:pointer; padding:0; display:flex; align-items:center;">
                            <svg id="eye_icon_1" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="f-err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="f-label">Confirm Password</label>
                    <div style="position:relative;">
                        <input type="password" id="password_confirm_input" name="password_confirmation" required placeholder="••••••••" class="f-input" style="padding-right: 40px;">
                        <button type="button" onclick="togglePassword('password_confirm_input', 'eye_icon_2')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--color-text-secondary); cursor:pointer; padding:0; display:flex; align-items:center;">
                            <svg id="eye_icon_2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="f-footer">
            <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Create Account</button>
        </div>
    </form>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }
    }
</script>
</x-layout>
