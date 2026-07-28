<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        $targetUrl = $this->resolveSafeTargetUrl($request, (string) data_get($item->data, 'url', route('notifications.index')));

        if (! $targetUrl) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Halaman tujuan notifikasi tidak tersedia atau tidak aman untuk dibuka.');
        }

        return redirect()->to($this->appendNotificationRedirectFlag($targetUrl));
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca.');
    }

    private function resolveSafeTargetUrl(Request $request, string $targetUrl): ?string
    {
        if (! Str::startsWith($targetUrl, ['http://', 'https://', '/'])) {
            return null;
        }

        $resolvedUrl = Str::startsWith($targetUrl, '/')
            ? url($targetUrl)
            : $targetUrl;

        $parsed = parse_url($resolvedUrl);
        if (! is_array($parsed)) {
            return null;
        }

        $allowedOrigins = collect([
            $this->normalizeOrigin(config('app.url')),
            $this->normalizeOrigin($request->root()),
            $this->normalizeOrigin(url('/')),
        ])->filter()->unique()->values();

        if (! $allowedOrigins->contains($this->normalizeOrigin($resolvedUrl))) {
            return null;
        }

        $path = (string) ($parsed['path'] ?? '/');
        if (
            Str::startsWith($path, ['/logout', '/push/subscriptions'])
            || $path === '/sw.js'
        ) {
            return null;
        }

        return $resolvedUrl;
    }

    private function appendNotificationRedirectFlag(string $url): string
    {
        return $url.(str_contains($url, '?') ? '&' : '?').'_notification_redirect=1';
    }

    private function normalizeOrigin(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        $parsed = parse_url($url);
        if (! is_array($parsed) || blank($parsed['scheme'] ?? null) || blank($parsed['host'] ?? null)) {
            return null;
        }

        $scheme = strtolower((string) $parsed['scheme']);
        $host = strtolower((string) $parsed['host']);
        $port = (int) ($parsed['port'] ?? $this->defaultPort($scheme));

        return sprintf('%s://%s:%d', $scheme, $host, $port);
    }

    private function defaultPort(string $scheme): int
    {
        return strtolower($scheme) === 'https' ? 443 : 80;
    }
}
