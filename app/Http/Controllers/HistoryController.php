<?php

namespace App\Http\Controllers;

use App\Models\UploadJob;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index()
    {
        $jobs = UploadJob::where('user_id', auth()->id())
            ->with('platformUploads')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return inertia('History', ['jobs' => $jobs]);
    }

    public function retry($id)
    {
        $platformUpload = \App\Models\PlatformUpload::whereHas('uploadJob', function($q) {
            $q->where('user_id', auth()->id());
        })->findOrFail($id);

        if ($platformUpload->status !== 'failed') {
            return back()->with('error', 'Hanya video yang gagal yang bisa dicoba ulang.');
        }

        $platformUpload->update([
            'status' => 'pending',
            'error_message' => null,
            'retry_count' => 0
        ]);

        \App\Jobs\ProcessVideoUpload::dispatch($platformUpload);

        return back()->with('success', 'Video berhasil dimasukkan kembali ke antrean upload.');
    }
}
