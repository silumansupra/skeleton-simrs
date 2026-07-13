<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    // ── LOGIN ────────────────────────────────────────────────
    public function showLogin()
    {
        if (auth()->check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $user = User::where('username', $request->username)
            ->whereNull('deleted_at')
            ->first();

        // User tidak ditemukan
        if (! $user) {
            AuditLog::catat('login_gagal', keterangan: "Username tidak ditemukan: {$request->username}");
            return back()->withInput()->with('error', 'Username atau password salah.');
        }

        // Cek status aktif
        if (! $user->is_active) {
            return back()->withInput()->with('error', 'Akun Anda dinonaktifkan. Hubungi administrator.');
        }

        // Cek locked
        if ($user->isLocked()) {
            $until = $user->locked_until->format('H:i');
            return back()->withInput()->with('error', "Akun dikunci hingga pukul {$until}. Terlalu banyak percobaan gagal.");
        }

        // Verifikasi password
        if (! Hash::check($request->password, $user->password_hash)) {
            $attempts = $user->failed_attempts + 1;
            $updateData = ['failed_attempts' => $attempts];

            if ($attempts >= self::MAX_ATTEMPTS) {
                $updateData['locked_until'] = Carbon::now()->addMinutes(self::LOCK_MINUTES);
                $msg = "Akun dikunci selama " . self::LOCK_MINUTES . " menit karena terlalu banyak percobaan gagal.";
            } else {
                $sisa = self::MAX_ATTEMPTS - $attempts;
                $msg  = "Username atau password salah. Sisa percobaan: {$sisa}.";
            }

            $user->update($updateData);
            AuditLog::catat('login_gagal', keterangan: "Password salah untuk: {$request->username} (percobaan ke-{$attempts})");
            return back()->withInput()->with('error', $msg);
        }

        // Sukses login
        auth()->login($user, $request->boolean('remember'));

        $user->update([
            'failed_attempts' => 0,
            'locked_until'    => null,
            'last_login_at'   => now(),
            'last_login_ip'   => $request->ip(),
        ]);

        AuditLog::catat('login', keterangan: "Login berhasil: {$user->username}");

        return redirect()->intended(route('dashboard'));
    }

    // ── LOGOUT ───────────────────────────────────────────────
    public function logout(Request $request)
    {
        AuditLog::catat('logout', keterangan: 'Logout');
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    // ── FORGOT PASSWORD ──────────────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], ['email.required' => 'Email wajib diisi.']);

        $user = User::where('email', $request->email)->whereNull('deleted_at')->first();

        // Selalu tampilkan pesan sukses (security: jangan bocorkan email mana yang ada)
        if ($user) {
            $token = Str::random(64);
            $user->update([
                'reset_token'            => hash('sha256', $token),
                'reset_token_expires_at' => now()->addHour(),
            ]);

            // TODO: kirim email dengan link reset
            // Mail::to($user->email)->send(new ResetPasswordMail($token));

            AuditLog::catat('forgot_password', tabel: 'users', record_id: (string) $user->id,
                keterangan: "Reset password diminta untuk: {$user->email}");
        }

        return back()->with('success', 'Jika email terdaftar, link reset password telah dikirim.');
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $hashed = hash('sha256', $request->token);
        $user   = User::where('reset_token', $hashed)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            return back()->with('error', 'Token reset tidak valid atau sudah kadaluarsa.');
        }

        $user->update([
            'password_hash'          => Hash::make($request->password),
            'reset_token'            => null,
            'reset_token_expires_at' => null,
            'failed_attempts'        => 0,
            'locked_until'           => null,
        ]);

        AuditLog::catat('reset_password', tabel: 'users', record_id: (string) $user->id,
            keterangan: "Password direset untuk: {$user->username}");

        return redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login.');
    }
}
