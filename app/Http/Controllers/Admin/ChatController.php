<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:waiting,active,closed'],
        ]);

        $conversations = Conversation::query()
            ->withCount(['messages as unread_count' => fn ($query) => $query->where('sender_type', 'guest')->where('is_read', false)])
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where(fn ($query) => $query->where('guest_name', 'like', "%{$search}%")->orWhere('ticket_number', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'active' => Conversation::active()->count(),
            'waiting' => Conversation::where('status', 'waiting')->count(),
            'unread' => Conversation::whereHas('messages', fn ($query) => $query->where('sender_type', 'guest')->where('is_read', false))->count(),
        ];

        return view('admin.chats.index', compact('conversations', 'counts'));
    }

    public function show(Conversation $conversation): View
    {
        $conversation->messages()->where('sender_type', 'guest')->where('is_read', false)->update(['is_read' => true]);

        if ($conversation->status === 'waiting') {
            $conversation->update(['status' => 'active']);
        }

        return view('admin.chats.show', ['conversation' => $conversation->fresh('messages')]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:1000']]);

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'message' => trim($data['message']),
        ]);
        $conversation->update(['status' => 'active', 'last_message_at' => now()]);

        return back()->with('success', 'Balasan terkirim.');
    }

    public function updateStatus(Request $request, Conversation $conversation): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:waiting,active,closed']]);
        $conversation->update(['status' => $data['status']]);

        if ($data['status'] === 'closed') {
            $nextConversation = Conversation::active()
                ->whereKeyNot($conversation->id)
                ->orderByRaw("case when status = 'waiting' then 0 else 1 end")
                ->orderByDesc('last_message_at')
                ->first();

            if ($nextConversation) {
                return redirect()->route('admin.chats.show', $nextConversation)
                    ->with('success', 'Chat ditutup. Anda dialihkan ke tiket berikutnya.');
            }

            return redirect()->route('admin.chats.index')->with('success', 'Chat ditutup. Tidak ada antrian aktif saat ini.');
        }

        return back()->with('success', 'Status percakapan diperbarui.');
    }
}
