<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\InvitationTemplateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Mime\MimeTypes;

class PublicInvitationAssetController extends Controller
{
    public function show(
        string $templateSlug,
        string $path,
        Request $request,
        InvitationTemplateService $templateFiles,
    ): BinaryFileResponse {
        $template = Template::query()->where('slug', $templateSlug)->firstOrFail();

        abort_if($template->status !== 'published' && $request->user() === null, 404);

        $file = $templateFiles->resolveFile($templateSlug, $path);

        abort_if($file === null, 404);

        $extension = mb_strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $contentType = MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream';

        return response()->file($file, [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
