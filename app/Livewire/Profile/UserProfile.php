<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserProfile extends Component
{
    public string $name     = '';
    public string $email    = '';
    public string $phone    = '';
    public string $current_password  = '';
    public string $password          = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $user        = Auth::user();
        $this->name  = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:30',
        ]);

        Auth::user()->update([
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
        ]);

        session()->flash('success', 'Profile updated.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        Auth::user()->update(['password' => Hash::make($this->password)]);
        $this->current_password = $this->password = $this->password_confirmation = '';
        session()->flash('success', 'Password changed successfully.');
    }

    public function render()
    {
        return view('livewire.profile.user-profile')
            ->layout('layouts.app', ['title' => 'My Profile']);
    }
}
