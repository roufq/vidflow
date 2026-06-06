<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadJob;
use App\Models\PlatformUpload;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100', // YouTube STRICTLY requires max 100 chars
            'description' => 'nullable|string|max:2200', // Instagram & TikTok captions max around 2200 chars
            'tags' => 'nullable|string|max:500', // YouTube tags total length max 500 chars
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/webm|max:1048576', // max 1GB (1048576 KB) to be safe for TikTok/IG API
            'selected_connections' => 'required|array|min:1',
            'selected_connections.*' => 'required|uuid|exists:platform_connections,id',
            'scheduled_at' => 'nullable|date|after:now',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // max 5MB
        ]);

        // Store file to Google Drive (5TB)
        try {
            $path = $request->file('video')->store('transit_videos', env('FILESYSTEM_DISK', 'public'));
            
            if (!$path) {
                return redirect()->back()->with('error', 'Gagal mengupload video ke Google Drive. Kemungkinan token Google Drive Anda sudah Expired atau koneksi server terputus.');
            }
            
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('transit_thumbnails', env('FILESYSTEM_DISK', 'public'));
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Google Drive Error: ' . $e->getMessage() . '. Jika Unauthorized, berarti Token Google Drive Anda sudah Expired (Hangus 7 hari).');
        }
        $tagsArray = $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : [];

        $uploadJob = UploadJob::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'tags' => $tagsArray,
            'file_path' => $path,
            'file_size_bytes' => $request->file('video')->getSize(),
            'thumbnail_path' => $thumbnailPath,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => 'pending',
        ]);

        $connections = \App\Models\PlatformConnection::whereIn('id', $validated['selected_connections'])->get();

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
