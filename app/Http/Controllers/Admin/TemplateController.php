<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTemplateRequest;
use App\Http\Requests\Admin\UpdateTemplateRequest;
use App\Models\Template;
use App\Services\InvitationTemplateRenderer;
use App\Services\InvitationTemplateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;
        $templates = Template::query()
            ->withCount('invitations')
            ->when($search, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('admin.templates.create');
    }

    public function store(StoreTemplateRequest $request, InvitationTemplateService $templateFiles): RedirectResponse
    {
        $validated = $request->validated();
        $archive = $request->file('template_zip');
        $uploads = $this->templateUploads($request);
        unset($validated['template_zip'], $validated['index_html'], $validated['css_files'], $validated['js_files'], $validated['asset_files'], $validated['asset_paths'], $validated['asset_file']);
        $slug = $validated['slug'];
        $status = $validated['status'] ?? 'draft';

        $template = Template::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'folder_name' => $slug,
            'status' => 'draft',
        ]);

        $templateInstalled = false;
        try {
            if ($archive !== null || $uploads !== []) {
                $templateFiles->installTemplate($slug, $archive, $uploads);
                $templateInstalled = true;
            }
            if ($status === 'published' && ! $templateFiles->hasIndex($slug)) {
                throw ValidationException::withMessages(['template_zip' => 'Template harus memiliki index.html sebelum dipublikasikan.']);
            }
            $template->update(['status' => $status]);
        } catch (Throwable $exception) {
            if ($templateInstalled) {
                $templateFiles->deleteTemplate($slug);
            }
            $template->delete();
            throw $exception;
        }

        return redirect()->route('admin.templates.index')->with('success', 'Template undangan berhasil ditambahkan.');
    }

    public function edit(Template $template, InvitationTemplateService $templateFiles): View
    {
        $files = $templateFiles->files($template->folder_name);

        return view('admin.templates.edit', compact('template', 'files'));
    }

    public function update(UpdateTemplateRequest $request, Template $template, InvitationTemplateService $templateFiles): RedirectResponse
    {
        $validated = $request->validated();
        $archive = $request->file('template_zip');
        $uploads = $this->templateUploads($request);
        unset($validated['template_zip'], $validated['index_html'], $validated['css_files'], $validated['js_files'], $validated['asset_files'], $validated['asset_paths'], $validated['asset_file']);
        $newSlug = $validated['slug'];
        $validated['folder_name'] = $newSlug;

        $templateFiles->synchronize(
            $template->folder_name,
            $newSlug,
            $archive,
            $uploads,
            function () use ($template, $validated, $newSlug, $templateFiles): void {
                if ($validated['status'] === 'published' && ! $templateFiles->hasIndex($newSlug)) {
                    throw ValidationException::withMessages(['template_zip' => 'Template harus memiliki index.html sebelum dipublikasikan.']);
                }

                DB::transaction(fn () => $template->update($validated));
            },
        );

        return redirect()->route('admin.templates.index')->with('success', 'Template undangan berhasil diperbarui.');
    }

    public function destroy(Template $template, InvitationTemplateService $templateFiles): RedirectResponse
    {
        if ($template->invitations()->exists()) {
            return back()->withErrors(['template' => 'Template masih digunakan oleh undangan dan tidak dapat dihapus.']);
        }

        $templateFiles->deleteTemplate($template->folder_name);
        $template->delete();

        return redirect()->route('admin.templates.index')->with('success', 'Template undangan berhasil dihapus.');
    }

    public function togglePublication(Template $template, InvitationTemplateService $templateFiles): RedirectResponse
    {
        $status = $template->status === 'published' ? 'draft' : 'published';

        if ($status === 'published' && ! $templateFiles->hasIndex($template->folder_name)) {
            return redirect()->route('admin.templates.index')->withErrors(['status' => 'Upload template dengan index.html sebelum mempublikasikan template.']);
        }
        if ($status === 'draft' && $template->invitations()->where('status', 'published')->exists()) {
            return redirect()->route('admin.templates.index')->withErrors(['status' => 'Template masih digunakan oleh undangan yang dipublikasikan.']);
        }

        $template->update(['status' => $status]);

        return redirect()->route('admin.templates.index')->with('success', $status === 'published' ? 'Template berhasil dipublikasikan.' : 'Template dijadikan draft.');
    }

    public function preview(Template $template, InvitationTemplateRenderer $renderer): Response
    {
        try {
            $html = $renderer->renderPreview($template);
        } catch (RuntimeException) {
            abort(404);
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function destroyFile(Request $request, Template $template, InvitationTemplateService $templateFiles): RedirectResponse
    {
        $path = $request->validate(['path' => ['required', 'string', 'max:500']])['path'];

        if (! $templateFiles->deleteFile($template->folder_name, $path)) {
            return back()->withErrors(['path' => 'File tidak ditemukan atau tidak dapat dihapus.']);
        }

        return back()->with('success', 'File berhasil dihapus.');
    }

    /** @return array<int, array{file: UploadedFile, path: string}> */
    private function templateUploads(Request $request): array
    {
        $uploads = [];
        if (($indexHtml = $request->file('index_html')) !== null) {
            $uploads[] = ['file' => $indexHtml, 'path' => 'index.html'];
        }

        foreach ($request->file('css_files', []) as $file) {
            $uploads[] = ['file' => $file, 'path' => 'css/'.basename(str_replace('\\', '/', $file->getClientOriginalName()))];
        }
        foreach ($request->file('js_files', []) as $file) {
            $uploads[] = ['file' => $file, 'path' => 'js/'.basename(str_replace('\\', '/', $file->getClientOriginalName()))];
        }
        foreach ($request->file('asset_files', []) as $index => $file) {
            $path = str_replace('\\', '/', $request->input('asset_paths.'.$index, $file->getClientOriginalName()));
            $assetsPosition = strpos($path, 'assets/');
            $uploads[] = ['file' => $file, 'path' => $assetsPosition === false ? 'assets/'.$path : substr($path, $assetsPosition)];
        }
        if (($legacyAsset = $request->file('asset_file')) !== null) {
            $uploads[] = ['file' => $legacyAsset, 'path' => basename(str_replace('\\', '/', $legacyAsset->getClientOriginalName()))];
        }

        return $uploads;
    }
}
