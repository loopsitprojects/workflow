<x-layout title="Edit User">
<style>
.f-wrap{max-width:640px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;}
.f-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);}
.f-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:7px;}
.f-label.blue{color:#3b82f6;}
.f-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s;-webkit-appearance:none;appearance:none;}
.f-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
.f-input::placeholder{color:var(--color-text-secondary);opacity:0.45;}
.f-title{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.f-footer{background:var(--color-bg-secondary);padding:14px 24px;display:flex;justify-content:flex-end;gap:8px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-cancel{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.12s;}
.btn-cancel:hover{background:var(--color-bg-secondary);color:var(--color-text-primary);}
.btn-submit{padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,85,212,0.25);transition:all 0.12s;}
.btn-submit:hover{background:#0044aa;}
.f-err{color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;}
.f-hint{font-size:11px;color:var(--color-text-secondary);margin-bottom:7px;font-weight:500;}
</style>

<nav style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin:0 auto 12px;max-width:640px;">
    <a href="{{ route('admin.settings') }}" style="text-decoration:none;color:inherit;">Admin</a>
    <span style="opacity:0.4;">/</span>
    <a href="{{ route('users.index') }}" style="text-decoration:none;color:inherit;">Users</a>
    <span style="opacity:0.4;">/</span>
    <span style="color:var(--color-text-primary);">{{ $user->username }}</span>
</nav>
<div class="f-wrap">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="f-section">
            <div>
                <label class="f-label blue">Username</label>
                <input type="text" name="username" required class="f-input"
                       value="{{ old('username', $user->username) }}" autocomplete="username" pattern="[a-zA-Z0-9_\-]+" maxlength="30">
                <p style="font-size:11px;color:var(--color-text-secondary);margin-top:6px;font-weight:500;">Letters, numbers, underscores, and hyphens only.</p>
                @error('username')<p class="f-err">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Email Address</label>
                    <input type="email" name="email" required class="f-input"
                           value="{{ old('email', $user->email) }}">
                    @error('email')<p class="f-err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="f-label">Workspace Role</label>
                    <select name="role" required class="f-input">
                        @foreach(['Writer','Designer','Coordinator','Approver','Brand Manager','Admin'] as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="f-err">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="f-section">
            <p class="f-hint">Leave password fields blank to keep the current password.</p>
            <div class="f-grid">
                <div>
                    <label class="f-label">New Password</label>
                    <input type="password" name="password" placeholder="••••••••" class="f-input">
                    @error('password')<p class="f-err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="f-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••" class="f-input">
                </div>
            </div>
        </div>

        <div class="f-footer">
            <a href="{{ route('users.index') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-submit">Save Changes</button>
        </div>
    </form>
</div>
</x-layout>
