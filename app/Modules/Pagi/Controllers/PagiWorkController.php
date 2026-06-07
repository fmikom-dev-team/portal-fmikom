<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;
use App\Models\PagiCustomWork;
use App\Models\PagiWork;
use App\Models\Module;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\DB;

class PagiWorkController extends Controller
{
    protected const ERROR_DISABLED = 'Fitur Works sementara dinonaktifkan.';

    /**
     * Display the template selection page for custom portfolios/works.
     */
    public function index(Request $request)
    {
        return redirect()->route('module.pagi.dashboard')
            ->with('error', self::ERROR_DISABLED);
    }

    /**
     * Preview a specific template with user profile data.
     */
    public function previewTheme(Request $request, string $theme)
    {
        return redirect()->route('module.pagi.dashboard')
            ->with('error', self::ERROR_DISABLED);
    }

    /**
     * Open the customizer editor screen for a theme.
     */
    public function editPortfolio(Request $request, string $theme)
    {
        return redirect()->route('module.pagi.dashboard')
            ->with('error', self::ERROR_DISABLED);
    }

    /**
     * Save the custom portfolio/work customization settings.
     */
    public function savePortfolio(Request $request)
    {
        return redirect()->route('module.pagi.dashboard')
            ->with('error', self::ERROR_DISABLED);
    }

    /**
     * View a user's standalone public custom portfolio/work.
     */
    public function viewPublicPortfolio(Request $request, User $user)
    {
        return redirect()->route('module.pagi.dashboard')
            ->with('error', self::ERROR_DISABLED);
    }

    /**
     * Format user profiles helper.
     */
    protected function formatUserProfile(User $user): array
    {
        $pagiModuleId = 1;
        $pagiRoles = DB::table('user_module_roles')
            ->join('roles', 'user_module_roles.role_id', '=', 'roles.id')
            ->where('user_module_roles.user_id', $user->id)
            ->where('user_module_roles.module_id', $pagiModuleId)
            ->where('user_module_roles.is_active', true)
            ->select('roles.nama', 'roles.slug')
            ->get();

        $pagiRoleLabel = null;
        if ($pagiRoles->isNotEmpty()) {
            $nonAdmin = $pagiRoles->first(fn($r) => !in_array($r->slug, ['super-admin', 'admin']));
            $chosen = $nonAdmin ?? $pagiRoles->first();
            $pagiRoleLabel = $chosen?->nama;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'pagi_username' => $user->pagi_username,
            'role_title' => $user->role_title,
            'pagi_role' => $pagiRoleLabel,
            'user_type' => $user->user_type,
            'bio' => $user->bio,
            'location' => $user->location,
            'website' => $user->website,
            'twitter' => $user->twitter,
            'linkedin' => $user->linkedin,
            'github' => $user->github,
            'instagram' => $user->instagram,
            'foto_path' => $user->foto_path,
            'banner_path' => $user->banner_path,
            'tanggal_lahir' => $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : null,
            'skills' => $user->metadata['skills'] ?? ['Figma', 'UI/UX Design', 'Vue.js'],
            'timezone' => $user->metadata['timezone'] ?? null,
            'timezone_extended' => $user->metadata['timezone_extended'] ?? null,
            'languages' => $user->metadata['languages'] ?? [],
            'followers_count' => count($user->metadata['followers'] ?? []),
            'following_count' => count($user->metadata['following'] ?? []),
            'certificates' => $this->resolveCertificateLogos(
                array_key_exists('certificates', $user->metadata ?? [])
                    ? $user->metadata['certificates']
                    : [
                        ['id' => 1, 'title' => 'Google UX Design Professional Certificate', 'issuer' => 'Coursera', 'date' => 'Januari 2026', 'credentialId' => 'G-18A8B2C3'],
                        ['id' => 2, 'title' => 'Figma UI/UX Advanced Design Course', 'issuer' => 'FMIKOM Academy', 'date' => 'Desember 2025', 'credentialId' => 'FM-882143']
                    ]
            ),
        ];
    }

    /**
     * Get user projects/works list helper.
     */
    private function getUserProjects(User $user): array
    {
        $isOwner = auth()->check() && auth()->id() === $user->id;
        $projectsQuery = PagiWork::with(['tags', 'user'])
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('content', 'like', '%' . $user->name . '%');
            });

        if (!$isOwner) {
            $projectsQuery->where('is_published', true)
                          ->where(function($q) {
                              $q->whereNull('visibility')
                                ->orWhere('visibility', 'Everyone');
                          });
        }

        $projectOrder = $user->metadata['pagi_project_order'] ?? null;
        $projectsCollection = $projectsQuery->get();
        if (is_array($projectOrder)) {
            $orderMap = array_flip($projectOrder);
            $projectsCollection = $projectsCollection->sortBy(function($p) use ($orderMap) {
                return $orderMap[$p->id] ?? (-$p->id);
            })->values();
        } else {
            $projectsCollection = $projectsCollection->sortByDesc('created_at')->values();
        }

        return $projectsCollection->map(function($p) use ($user) {
            $creator = $p->user ?? $user;
            return [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'title' => $p->title ?? 'Untitled Project',
                'image' => $p->cover_image ? (str_starts_with($p->cover_image, 'http') ? $p->cover_image : asset('storage/' . $p->cover_image)) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=2564&auto=format&fit=crop',
                'content' => $this->formatPortfolioContent($p->content),
                'created_at' => $p->created_at->format('F jS Y'),
                'likes' => count($p->likes ?? []),
                'liked' => auth()->check() ? in_array(auth()->id(), $p->likes ?? []) : false,
                'comments' => $this->formatComments($p->comments ?? []),
                'views' => $p->views_count ?? 0,
                'is_published' => (bool)$p->is_published,
                'tools_used' => $p->tools_used,
                'description' => $p->description,
                'category' => $p->category,
                'tags' => $p->tags->map(fn($t) => $t->name)->toArray(),
                'resolved_collaborators' => $this->resolveCollaborators($p),
                'user' => [
                    'id' => $creator->id,
                    'name' => $creator->name,
                    'pagi_username' => $creator->pagi_username,
                    'avatar' => $creator->foto_path ? (str_starts_with($creator->foto_path, 'http') ? $creator->foto_path : asset('storage/' . $creator->foto_path)) : null,
                    'location' => $creator->location ?? 'Banyumas, Indonesia',
                ],
            ];
        })->toArray();
    }

    private function resolveCertificateLogos(?array $certificates): array
    {
        if (empty($certificates)) {
            return [];
        }
        
        $storageDir = storage_path('app/public/org-logos');
        $extensions = ['svg', 'png', 'jpg', 'jpeg', 'webp', 'gif'];
        
        return array_map(function($cert) use ($storageDir, $extensions) {
            if (!empty($cert['issuer'])) {
                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cert['issuer']), '-'));
                foreach ($extensions as $ext) {
                    if (file_exists("{$storageDir}/{$slug}.{$ext}")) {
                        $cert['logo_url'] = asset("storage/org-logos/{$slug}.{$ext}");
                        break;
                    }
                }
            }
            return $cert;
        }, $certificates);
    }

    private function resolveCollaborators($portfolio)
    {
        $details = collect($portfolio->content)->firstWhere('type', 'featured_details') ?: [];
        $collaboratorsData = $details['collaborators'] ?? [];
        $resolvedCollaborators = [];

        if (is_array($collaboratorsData) && count($collaboratorsData) > 0) {
            $names = [];
            $statusMap = [];
            foreach ($collaboratorsData as $c) {
                if (is_array($c)) {
                    $names[] = $c['name'];
                    $statusMap[$c['name']] = $c['status'] ?? 'pending';
                } else {
                    $names[] = $c;
                    $statusMap[$c] = 'accepted';
                }
            }

            $collabUsers = User::whereIn('name', $names)->get();
            foreach ($collabUsers as $cu) {
                $resolvedCollaborators[] = [
                    'id' => $cu->id,
                    'name' => $cu->name,
                    'pagi_username' => $cu->pagi_username,
                    'avatar' => $cu->foto_path ? (str_starts_with($cu->foto_path, 'http') ? $cu->foto_path : asset('storage/' . $cu->foto_path)) : null,
                    'status' => $statusMap[$cu->name] ?? 'pending',
                ];
            }
        }
        return $resolvedCollaborators;
    }

    private function formatPortfolioContent($content)
    {
        if (is_string($content)) {
            $content = json_decode($content, true);
        }
        if (is_array($content)) {
            foreach ($content as &$block) {
                if (isset($block['file_path']) && !empty($block['file_path'])) {
                    $block['preview'] = str_starts_with($block['file_path'], 'http')
                        ? $block['file_path']
                        : asset('storage/' . $block['file_path']);
                }
                if (isset($block['file_paths']) && is_array($block['file_paths'])) {
                    $block['previews'] = array_map(function($path) {
                        return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
                    }, $block['file_paths']);
                }
            }
        }
        return $content;
    }

    private function formatComments(?array $comments): array
    {
        if (empty($comments)) {
            return [];
        }

        $userIds = array_unique(array_filter(array_column($comments, 'user_id')));
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return array_map(function($c) use ($users) {
            $u = $users[$c['user_id']] ?? null;
            return [
                'id' => $c['id'] ?? null,
                'user_id' => $c['user_id'] ?? null,
                'name' => $u ? $u->name : ($c['name'] ?? 'Anonymous'),
                'avatar' => $u 
                    ? ($u->foto_path ? (str_starts_with($u->foto_path, 'http') ? $u->foto_path : asset('storage/' . $u->foto_path)) : null)
                    : null,
                'pagi_username' => $u ? $u->pagi_username : null,
                'comment' => $c['comment'] ?? '',
                'created_at' => isset($c['created_at']) ? \Illuminate\Support\Carbon::parse($c['created_at'])->diffForHumans() : '',
            ];
        }, $comments);
    }
}
