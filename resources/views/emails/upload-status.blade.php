<!DOCTYPE html>
<html>
<head>
    <title>Status Upload Video</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Halo,</h2>
    <p>Berikut adalah status terbaru dari unggahan video Anda di VidFlow.</p>
    
    <table style="border-collapse: collapse; width: 100%; max-width: 600px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Judul Video</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{ $platformUpload->uploadJob->title }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Platform</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-transform: capitalize;">{{ $platformUpload->platform }}</td>
        </tr>
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Status Akhir</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-transform: capitalize;">
                <span style="color: {{ $platformUpload->status === 'done' ? 'green' : 'red' }}">
                    {{ $platformUpload->status }}
                </span>
            </td>
        </tr>
        @if($platformUpload->status === 'failed')
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Pesan Error</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; color: red;">{{ $platformUpload->error_message }}</td>
        </tr>
        @endif
        @if($platformUpload->status === 'done')
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;">Link Video</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                <a href="{{ $platformUpload->platform_url }}" target="_blank">Lihat Video</a>
            </td>
        </tr>
        @endif
    </table>

    <p>Terima kasih telah menggunakan VidFlow!</p>
</body>
</html>
