<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationGuest;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class InvitationTemplateRenderer
{
    public function __construct(private Request $request) {}

    public function render(Invitation $invitation, ?InvitationGuest $guest = null): string
    {
        $invitation->loadMissing('template');

        return $this->renderHtml($invitation->template, [
            '{{guest_name}}' => $this->escape($guest?->name ?? 'Tamu Undangan'),
            '{{invitation_name}}' => $this->escape($invitation->name),
            '{{groom_name}}' => $this->escape($invitation->groom_name),
            '{{bride_name}}' => $this->escape($invitation->bride_name),
        ]);
    }

    public function renderPreview(Template $template): string
    {
        return $this->renderHtml($template, [
            '{{guest_name}}' => 'Tamu Undangan',
            '{{invitation_name}}' => 'Preview Undangan',
            '{{groom_name}}' => 'Nama Pengantin Pria',
            '{{bride_name}}' => 'Nama Pengantin Wanita',
        ]);
    }

    /** @param array<string, string> $placeholders */
    private function renderHtml(Template $template, array $placeholders): string
    {
        $path = Storage::disk('public')->path('invitations/'.$template->folder_name.'/index.html');
        $realPath = realpath($path);
        $templateRoot = realpath(Storage::disk('public')->path('invitations/'.$template->folder_name));

        if ($realPath === false || $templateRoot === false || ! str_starts_with($realPath, $templateRoot.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('File index.html template tidak ditemukan.');
        }

        $html = file_get_contents($realPath);
        if ($html === false) {
            throw new RuntimeException('File template tidak dapat dibaca.');
        }

        $assetPath = '/invitation-assets/'.Str::of($template->folder_name)->trim('/')->toString();
        $html = strtr($html, [...$placeholders, '{{asset_path}}' => $assetPath]);

        return $this->addLegacyAssetBase($html, $assetPath.'/', $this->request->getPathInfo());
    }

    private function addLegacyAssetBase(string $html, string $assetBase, string $pagePath): string
    {
        $html = preg_replace_callback(
            '/(\bhref\s*=\s*)(["\'])#([^"\']*)\2/i',
            fn (array $matches): string => $matches[1].$matches[2].e($pagePath.'#'.$matches[3]).$matches[2],
            $html,
        ) ?? $html;

        if (preg_match('/<base\s/i', $html) === 1) {
            return $html;
        }

        $base = '<base href="'.htmlspecialchars($assetBase, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'">';
        if (preg_match('/<head(?:\s[^>]*)?>/i', $html, $match, PREG_OFFSET_CAPTURE) === 1) {
            $offset = $match[0][1] + strlen($match[0][0]);

            return substr($html, 0, $offset).$base.substr($html, $offset);
        }

        return $base.$html;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
