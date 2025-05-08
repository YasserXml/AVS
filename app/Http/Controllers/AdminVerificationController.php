<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserVerifiedByAdmin;
use App\Notifications\UserVerifiedNotification;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AdminVerificationController extends Controller
{
    public function verifyUser(Request $request, User $user)
    {
        // Pastikan request memiliki tanda tangan yang valid
        if (!$request->hasValidSignature()) {
            abort(401, 'Link verifikasi tidak valid atau sudah kedaluwarsa.');
        }

        // Pastikan user yang melakukan aksi ini adalah admin
        if (!Auth::check() || !Auth::user()->hasAnyRole(['admin', 'super_admin'])) {
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'Anda harus login sebagai admin untuk melakukan verifikasi ini.');
        }

        // Verifikasi pengguna
        $user->update([
            'admin_verified' => true,
        ]);

        // Kirim email ke pengguna bahwa akunnya sudah diverifikasi
        $user->notify(new UserVerifiedByAdmin());

        // Kirim notifikasi ke admin
        Notification::make()
            ->title('Pengguna Berhasil Diverifikasi')
            ->body("Pengguna {$user->name} ({$user->email}) telah berhasil diverifikasi.")
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.users.index')
            ->with('success', 'Pengguna berhasil diverifikasi!');
    }
}
