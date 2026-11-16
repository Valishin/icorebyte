<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttachmentController extends Controller
{

    /**
     * Sirve un adjunto almacenado en disco privado.
     * La URL debe estar firmada con URL::temporarySignedRoute().
     */
    public function show(Request $request, string $filename): Response
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Enlace caducado o inválido.');
        }

        // Evitar path traversal
        if (Str::contains($filename, ['/', '\\', '..', "\0"])) {
            abort(404);
        }

        $path = 'chat/attachments/' . $filename;

        if (! Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return response()->file(
            Storage::disk('private')->path($path),
            [
                'Content-Type'  => 'image/jpeg',
                'Cache-Control' => 'private, max-age=3600',
            ]
        );
    }

}
