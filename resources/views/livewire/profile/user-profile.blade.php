<div>
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Profile Info --}}
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Profile Information</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Full Name *</label>
                    <input wire:model="name" type="text" class="input-field w-full" />
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input wire:model="email" type="email" class="input-field w-full" />
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input wire:model="phone" type="text" class="input-field w-full" />
                </div>
                <button wire:click="updateProfile" class="btn-primary">Update Profile</button>
            </div>
        </div>

        {{-- Change Password --}}
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Change Password</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Current Password *</label>
                    <input wire:model="current_password" type="password" class="input-field w-full" />
                    @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">New Password *</label>
                    <input wire:model="password" type="password" class="input-field w-full" />
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Confirm New Password *</label>
                    <input wire:model="password_confirmation" type="password" class="input-field w-full" />
                </div>
                <button wire:click="updatePassword" class="btn-primary">Change Password</button>
            </div>
        </div>
    </div>
</div>
