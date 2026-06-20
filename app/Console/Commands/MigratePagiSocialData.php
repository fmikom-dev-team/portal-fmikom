<?php

namespace App\Console\Commands;

use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiWorkComment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MigratePagiSocialData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagi:migrate-social-data {--rollback : Kembalikan data dari tabel normalized ke kolom JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasikan data likes dan comments PAGI ke tabel relasional ternormalisasi atau sebaliknya';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->rollbackSocialData();
        }

        return $this->migrateSocialData();
    }

    /**
     * Migrate from JSON to normalized tables.
     */
    private function migrateSocialData(): int
    {
        $this->info('Memulai migrasi data likes dan comments ke tabel normalized...');

        $works = PagiWork::all();
        $likesCount = 0;
        $commentsCount = 0;
        $repliesCount = 0;
        $commentLikesCount = 0;

        DB::transaction(function () use ($works, &$likesCount, &$commentsCount, &$repliesCount, &$commentLikesCount) {
            foreach ($works as $work) {
                // 1. Migrate Work Likes
                // First get raw likes since we overrode the getter
                $rawLikesJson = $work->getRawOriginal('likes');
                $likes = [];
                if (! empty($rawLikesJson)) {
                    $likes = is_string($rawLikesJson) ? json_decode($rawLikesJson, true) : $rawLikesJson;
                }

                if (is_array($likes)) {
                    foreach (array_unique($likes) as $userId) {
                        // Check if user exists
                        if (DB::table('users')->where('id', $userId)->exists()) {
                            DB::table('pagi_work_likes')->updateOrInsert(
                                ['work_id' => $work->id, 'user_id' => $userId],
                                ['created_at' => now(), 'updated_at' => now()]
                            );
                            $likesCount++;
                        }
                    }
                }

                // 2. Migrate Comments and Replies
                $rawCommentsJson = $work->getRawOriginal('comments');
                $comments = [];
                if (! empty($rawCommentsJson)) {
                    $comments = is_string($rawCommentsJson) ? json_decode($rawCommentsJson, true) : $rawCommentsJson;
                }

                if (is_array($comments)) {
                    foreach ($comments as $c) {
                        $cUuid = $c['id'] ?? null;
                        if (! $cUuid) {
                            continue;
                        }

                        $cUserId = $c['user_id'] ?? null;
                        // Skip if user does not exist
                        if (! $cUserId || ! DB::table('users')->where('id', $cUserId)->exists()) {
                            continue;
                        }

                        $cBody = $c['body'] ?? $c['content'] ?? '';
                        $cCreatedAt = isset($c['created_at']) ? Carbon::parse($c['created_at']) : $work->created_at;

                        $commentRecord = PagiWorkComment::updateOrCreate(
                            ['work_id' => $work->id, 'uuid' => $cUuid],
                            [
                                'user_id' => $cUserId,
                                'parent_id' => null,
                                'body' => $cBody,
                                'created_at' => $cCreatedAt,
                                'updated_at' => now(),
                            ]
                        );
                        $commentsCount++;

                        // Migrate comment likes
                        $cLikes = $c['likes'] ?? [];
                        if (is_array($cLikes)) {
                            foreach (array_unique($cLikes) as $userId) {
                                if (DB::table('users')->where('id', $userId)->exists()) {
                                    DB::table('pagi_comment_likes')->updateOrInsert(
                                        ['comment_id' => $commentRecord->id, 'user_id' => $userId],
                                        ['created_at' => now(), 'updated_at' => now()]
                                    );
                                    $commentLikesCount++;
                                }
                            }
                        }

                        // Migrate replies
                        $replies = $c['replies'] ?? [];
                        if (is_array($replies)) {
                            foreach ($replies as $r) {
                                $rUuid = $r['id'] ?? null;
                                if (! $rUuid) {
                                    continue;
                                }

                                $rUserId = $r['user_id'] ?? null;
                                if (! $rUserId || ! DB::table('users')->where('id', $rUserId)->exists()) {
                                    continue;
                                }

                                $rBody = $r['body'] ?? $r['content'] ?? '';
                                $rCreatedAt = isset($r['created_at']) ? Carbon::parse($r['created_at']) : $work->created_at;

                                $replyRecord = PagiWorkComment::updateOrCreate(
                                    ['work_id' => $work->id, 'uuid' => $rUuid],
                                    [
                                        'user_id' => $rUserId,
                                        'parent_id' => $commentRecord->id,
                                        'body' => $rBody,
                                        'created_at' => $rCreatedAt,
                                        'updated_at' => now(),
                                    ]
                                );
                                $repliesCount++;

                                // Migrate reply likes
                                $rLikes = $r['likes'] ?? [];
                                if (is_array($rLikes)) {
                                    foreach (array_unique($rLikes) as $userId) {
                                        if (DB::table('users')->where('id', $userId)->exists()) {
                                            DB::table('pagi_comment_likes')->updateOrInsert(
                                                ['comment_id' => $replyRecord->id, 'user_id' => $userId],
                                                ['created_at' => now(), 'updated_at' => now()]
                                            );
                                            $commentLikesCount++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });

        $this->info('Migrasi data selesai!');
        $this->line("- Likes dimigrasikan: {$likesCount}");
        $this->line("- Komentar dimigrasikan: {$commentsCount}");
        $this->line("- Balasan dimigrasikan: {$repliesCount}");
        $this->line("- Likes komentar dimigrasikan: {$commentLikesCount}");

        return 0;
    }

    /**
     * Rollback: Populate JSON columns from normalized tables.
     */
    private function rollbackSocialData(): int
    {
        $this->info('Memulai rollback: menyalin data dari tabel normalized kembali ke kolom JSON...');

        $works = PagiWork::all();
        $updatesCount = 0;

        DB::transaction(function () use ($works, &$updatesCount) {
            foreach ($works as $work) {
                // 1. Get likes from relation
                $likes = DB::table('pagi_work_likes')
                    ->where('work_id', $work->id)
                    ->pluck('user_id')
                    ->toArray();

                // 2. Get comments and replies
                $comments = PagiWorkComment::where('work_id', $work->id)
                    ->whereNull('parent_id')
                    ->with(['user', 'likesRelation', 'replies.user', 'replies.likesRelation'])
                    ->get()
                    ->map(function ($model) {
                        /** @var PagiWorkComment $c */
                        $c = $model;
                        $replies = $c->replies->map(function ($replyModel) {
                            /** @var PagiWorkComment $r */
                            $r = $replyModel;
                            $avatar = null;
                            if ($r->user && $r->user->foto_path) {
                                $avatar = str_starts_with($r->user->foto_path, 'http')
                                    ? $r->user->foto_path
                                    : asset('storage/'.$r->user->foto_path);
                            }

                            return [
                                'id' => $r->uuid,
                                'user_id' => $r->user_id,
                                'name' => $r->user->name ?? 'Anonymous',
                                'avatar' => $avatar,
                                'body' => $r->body,
                                'content' => $r->body,
                                'created_at' => $r->created_at->toISOString(),
                                'time' => $r->created_at->diffForHumans(),
                                'likes' => $r->likesRelation->pluck('id')->toArray(),
                            ];
                        })->toArray();

                        $avatar = null;
                        if ($c->user && $c->user->foto_path) {
                            $avatar = str_starts_with($c->user->foto_path, 'http')
                                ? $c->user->foto_path
                                : asset('storage/'.$c->user->foto_path);
                        }

                        return [
                            'id' => $c->uuid,
                            'user_id' => $c->user_id,
                            'name' => $c->user->name ?? 'Anonymous',
                            'pagi_username' => $c->user?->pagi_username,
                            'avatar' => $avatar,
                            'body' => $c->body,
                            'content' => $c->body,
                            'created_at' => $c->created_at->toISOString(),
                            'time' => $c->created_at->diffForHumans(),
                            'likes' => $c->likesRelation->pluck('id')->toArray(),
                            'replies' => $replies,
                        ];
                    })->toArray();

                // Direct update bypassing model event listeners to avoid infinite loop or re-triggering sync
                DB::table('pagi_works')
                    ->where('id', $work->id)
                    ->update([
                        'likes' => json_encode($likes),
                        'comments' => json_encode($comments),
                    ]);

                $updatesCount++;
            }
        });

        $this->info("Rollback data selesai! Menyinkronkan kembali {$updatesCount} karya.");

        return 0;
    }
}
