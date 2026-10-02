<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class GuestChatController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'guest_token' => ['required', 'uuid'],
            'guest_name' => ['required', 'string', 'min:2', 'max:120'],
            'guest_phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+()\-\s]+$/'],
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $this->ensureNotRateLimited($request, 'start', 4, 60);

        $conversation = Conversation::firstOrNew(['guest_token' => $data['guest_token']]);
        $isNewConversation = ! $conversation->exists;

        if ($isNewConversation) {
            $conversation->ticket_number = $this->nextTicketNumber();
            $conversation->status = 'waiting';
        }

        $conversation->fill([
            'guest_name' => $data['guest_name'],
            'guest_phone' => $data['guest_phone'] ?? null,
            'last_message_at' => now(),
        ])->save();

        $conversation->messages()->create([
            'sender_type' => 'guest',
            'message' => trim($data['message']),
        ]);

        $queuePosition = $this->queuePosition($conversation);

        if ($isNewConversation && $queuePosition > 1) {
            $conversation->messages()->create([
                'sender_type' => 'admin',
                'message' => "Saat ini terdapat antrean. Anda berada di urutan ke-{$queuePosition}. Mohon menunggu, kami akan segera membantu.",
            ]);
        }

        return response()->json($this->payload($conversation->fresh('messages')), 201);
    }

    public function show(string $guestToken): JsonResponse
    {
        abort_unless(Str::isUuid($guestToken), 404);

        $conversation = Conversation::where('guest_token', $guestToken)->with('messages')->firstOrFail();
        $conversation->messages()->where('sender_type', 'admin')->where('is_read', false)->update(['is_read' => true]);

        return response()->json($this->payload($conversation->fresh('messages')));
    }

    public function message(Request $request, string $guestToken): JsonResponse
    {
        abort_unless(Str::isUuid($guestToken), 404);
        $data = $request->validate(['message' => ['required', 'string', 'min:1', 'max:1000']]);
        $this->ensureNotRateLimited($request, 'message', 12, 60);

        $conversation = Conversation::where('guest_token', $guestToken)->firstOrFail();
        abort_if($conversation->status === 'closed', 422, 'Percakapan ini telah ditutup.');

        $conversation->messages()->create([
            'sender_type' => 'guest',
            'message' => trim($data['message']),
        ]);
        $conversation->update(['status' => 'waiting', 'last_message_at' => now()]);

        return response()->json($this->payload($conversation->fresh('messages')));
    }

    public function close(string $guestToken): JsonResponse
    {
        abort_unless(Str::isUuid($guestToken), 404);

        $conversation = Conversation::where('guest_token', $guestToken)->firstOrFail();
        $conversation->update(['status' => 'closed']);

        return response()->json($this->payload($conversation->fresh('messages')));
    }

    private function ensureNotRateLimited(Request $request, string $action, int $maxAttempts, int $decay): void
    {
        $key = 'guest-chat:'.$action.':'.$request->ip();

        abort_if(RateLimiter::tooManyAttempts($key, $maxAttempts), 429, 'Terlalu banyak percobaan. Silakan coba kembali sebentar lagi.');
        RateLimiter::hit($key, $decay);
    }

    private function nextTicketNumber(): string
    {
        $number = (int) Conversation::max('id') + 1;

        do {
            $ticket = 'CH-'.str_pad((string) $number++, 4, '0', STR_PAD_LEFT);
        } while (Conversation::where('ticket_number', $ticket)->exists());

        return $ticket;
    }

    /** @return array<string, mixed> */
    private function payload(Conversation $conversation): array
    {
        return [
            'ticket_number' => $conversation->ticket_number,
            'guest_name' => $conversation->guest_name,
            'status' => $conversation->status,
            'queue_position' => $this->queuePosition($conversation),
            'messages' => $conversation->messages->map(fn ($message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'message' => $message->message,
                'created_at' => $message->created_at->toIso8601String(),
            ])->values(),
        ];
    }

    private function queuePosition(Conversation $conversation): int
    {
        if ($conversation->status === 'closed') {
            return 0;
        }

        return Conversation::active()->where('id', '<=', $conversation->id)->count();
    }
}
