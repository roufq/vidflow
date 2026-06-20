<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadJob;
use App\Models\PlatformUpload;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // 1. Cek Kuota Upload Bulanan
        if ($user->upload_limit !== -1) {
            $currentMonthUploads = \App\Models\UploadJob::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
                
            if ($currentMonthUploads >= $user->upload_limit) {
                return redirect()->back()->with('error', 'Limit upload bulanan Anda telah tercapai (' . $user->upload_limit . ' video). Silakan upgrade paket berlangganan Anda ke Pro atau Bisnis.');
            }
        }

        $maxDays = $user->max_scheduling_days;
        $maxSizeKb = $user->max_file_size_mb * 1024;

        $validated = $request->validate([
            'title' => 'required|string|max:100', // YouTube STRICTLY requires max 100 chars
            'description' => 'nullable|string|max:2200', // Instagram & TikTok captions max around 2200 chars
            'tags' => 'nullable|string|max:500', // YouTube tags total length max 500 chars
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:' . $maxSizeKb,
            'selected_connections' => 'required|array|min:1',
            'selected_connections.*' => 'required|uuid|exists:platform_connections,id',
            'scheduled_at' => 'nullable|date|after:now|before_or_equal:+' . $maxDays . ' days',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // max 5MB
            'youtube_type' => 'nullable|string|in:regular,short',
        ]);

        // Store file to local VPS transit directory
        try {
            $path = $request->file('video')->store('transit_videos', 'local');
            
            if (!$path) {
                return redirect()->back()->with('error', 'Gagal mengupload video ke server lokal VPS.');
            }
            
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('transit_thumbnails', 'local');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan file secara lokal: ' . $e->getMessage());
        }
        $tagsArray = $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : [];
        
        $finalDescription = $validated['description'] ?? '';
        if (($validated['youtube_type'] ?? 'regular') === 'short') {
            if (!str_contains(strtolower($finalDescription), '#shorts')) {
                $finalDescription .= "\n\n#shorts";
            }
            if (!in_array('shorts', array_map('strtolower', $tagsArray))) {
                $tagsArray[] = 'shorts';
            }
        }

        $uploadJob = UploadJob::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => trim($finalDescription),
            'tags' => $tagsArray,
            'file_path' => $path,
            'file_size_bytes' => $request->file('video')->getSize(),
            'thumbnail_path' => $thumbnailPath,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => 'pending',
        ]);

        $connections = \App\Models\PlatformConnection::whereIn('id', $validated['selected_connections'])
            ->where('user_id', auth()->id())
            ->get();
            
        if ($connections->isEmpty()) {
            return redirect()->back()->with('error', 'Koneksi platform tidak valid atau Anda tidak memiliki akses ke akun tersebut.');
        }

        foreach ($connections as $conn) {
            $platformUpload = PlatformUpload::create([
                'job_id' => $uploadJob->id,
                'platform' => $conn->platform,
                'connection_id' => $conn->id,
                'status' => 'pending',
            ]);
            
            // Dispatch specific Job based on platform
            $delay = $uploadJob->scheduled_at ? \Carbon\Carbon::parse($uploadJob->scheduled_at) : now();
            $baseUrl = url('/');
            \App\Jobs\ProcessVideoUpload::dispatch($platformUpload, $baseUrl)->delay($delay);
        }

        return redirect()->back()->with('success', 'Video berhasil diantrikan ke ' . count($validated['selected_connections']) . ' akun!');
    }
}
