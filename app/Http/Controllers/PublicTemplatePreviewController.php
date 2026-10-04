<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\InvitationTemplateRenderer;
use Illuminate\Http\Response;
use RuntimeException;

class PublicTemplatePreviewController extends Controller
{
    public function __invoke(Template $template, InvitationTemplateRenderer $renderer): Response
    {
        abort_unless($template->status === 'published', 404);

        try {
            $html = $renderer->renderPreview($template);
        } catch (RuntimeException) {
            abort(404);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
