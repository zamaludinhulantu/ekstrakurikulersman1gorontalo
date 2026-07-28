<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PwaAssetController extends Controller
{
    public function manifest(Request $request): Response
    {
        $shortcuts = collect([
            ['name' => 'Ekskul Saya', 'route' => 'student.registrations.index', 'icon' => '/pwa/shortcut-ekskul.png'],
            ['name' => 'Jadwal', 'route' => 'student.schedules.index', 'icon' => '/pwa/shortcut-schedule.png'],
            ['name' => 'Presensi', 'route' => 'student.attendances.index', 'icon' => '/pwa/shortcut-attendance.png'],
            ['name' => 'Notifikasi', 'route' => 'notifications.index', 'icon' => '/pwa/shortcut-notification.png'],
        ])->filter(fn (array $shortcut) => Route::has($shortcut['route']))
            ->map(fn (array $shortcut) => [
                'name' => $shortcut['name'],
                'short_name' => $shortcut['name'],
                'url' => route($shortcut['route']),
                'icons' => [[
                    'src' => asset(ltrim($shortcut['icon'], '/')),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ]],
            ])
            ->values()
            ->all();

        $manifest = [
            'id' => '/',
            'name' => 'Sistem Informasi Ekstrakurikuler SMAN 1 Gorontalo',
            'short_name' => 'Ekskul SMAN 1',
            'description' => 'Aplikasi ekstrakurikuler SMA Negeri 1 Gorontalo untuk siswa, pembina, admin, kepala sekolah, dan superadmin.',
            'start_url' => route('login'),
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#f4f7fb',
            'theme_color' => '#1f5eff',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => asset('pwa/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('pwa/icon-maskable-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            'shortcuts' => $shortcuts,
        ];

        return response(
            json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            200,
            [
                'Content-Type' => 'application/manifest+json',
                'Cache-Control' => 'public, max-age=300',
            ]
        );
    }

    public function serviceWorker(Request $request): Response
    {
        return response()
            ->view('pwa.sw', [
                'cacheVersion' => 'pwa-v'.md5((string) File::lastModified(public_path('build/manifest.json'))),
                'offlineUrl' => route('offline'),
                'appRoot' => url('/'),
            ])
            ->header('Content-Type', 'application/javascript; charset=UTF-8')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Service-Worker-Allowed', '/');
    }

    public function offline(): View
    {
        return view('pwa.offline');
    }
}
