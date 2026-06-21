<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isProfileOrSettings = $request->routeIs('*profile*', '*settings*') || $request->is('pagi*');

        return [
            'id' => $this->id,
            'has_uploaded_project' => $this->when($isProfileOrSettings, $this->pagiWorks()->exists()),
            'name' => ($this->name === strtoupper($this->name)) ? ucwords(strtolower($this->name)) : $this->name,
            'email' => $this->email,
            'pagi_username' => $this->pagi_username,
            'role_title' => $this->getResolvedRoleTitle(),
            'bio' => $this->when($isProfileOrSettings, $this->bio),
            'location' => $this->when($isProfileOrSettings, $this->location),
            'website' => $this->when($isProfileOrSettings, $this->website),
            'twitter' => $this->when($isProfileOrSettings, $this->twitter),
            'linkedin' => $this->when($isProfileOrSettings, $this->linkedin),
            'github' => $this->when($isProfileOrSettings, $this->github),
            'instagram' => $this->when($isProfileOrSettings, $this->instagram),
            'metadata' => $this->when($isProfileOrSettings, $this->metadata),
            'following' => $this->when($isProfileOrSettings || ($request->user() && $this->id === $request->user()->id), fn () => $this->pagiFollowing()->pluck('following_id')->toArray()),
            'works_count' => $this->when($isProfileOrSettings, fn () => $this->pagiWorks()->count()),
            'certificates_count' => $this->when($isProfileOrSettings, fn () => count($this->metadata['certificates'] ?? []) ?: 2),
            'followers_count' => $this->when($isProfileOrSettings, fn () => $this->pagiFollowers()->count()),
            'skills' => $this->when($isProfileOrSettings, $this->metadata['skills'] ?? ['Figma', 'UI/UX Design', 'Vue.js']),
            'timezone' => $this->when($isProfileOrSettings, $this->metadata['timezone'] ?? null),
            'timezone_extended' => $this->when($isProfileOrSettings, $this->metadata['timezone_extended'] ?? null),
            'languages' => $this->when($isProfileOrSettings, $this->metadata['languages'] ?? []),
            'banner_path' => $this->banner_path,
            'tanggal_lahir' => $this->when($isProfileOrSettings, $this->tanggal_lahir ? ($this->tanggal_lahir instanceof Carbon ? $this->tanggal_lahir->format('Y-m-d') : \Illuminate\Support\Carbon::parse($this->tanggal_lahir)->format('Y-m-d')) : null),
            'no_telepon' => $this->when($isProfileOrSettings, $this->no_telepon),
            'nomor_induk' => $this->when($isProfileOrSettings, $this->nomor_induk),
            'program_studi_id' => $this->when($isProfileOrSettings, $this->program_studi_id),
            'tahun_lulus' => $this->when($isProfileOrSettings, $this->tahun_lulus),
            'user_type' => $this->user_type,
            'deletion_requested_at' => $this->deletion_requested_at ? $this->deletion_requested_at->toISOString() : null,
            'foto_path' => $this->foto_path,
            'avatar' => $this->foto_path
                ? (str_starts_with($this->foto_path, 'http') ? $this->foto_path : '/storage/'.$this->foto_path)
                : 'https://api.dicebear.com/7.x/initials/svg?seed='.urlencode($this->name).'&backgroundColor=3b82f6,6366f1,8b5cf6,ec4899,f43f5e&backgroundType=gradientLinear&bold=true',
            'unreadNotifications' => $this->when($request->routeIs('portal', '*portal*'), fn () => clone $this->unreadNotifications),
            'role' => $this->whenLoaded('role'), // just in case it's loaded
            'is_admin' => $this->isAdmin(),
            'is_super_admin' => $this->isSuperAdmin(),
        ];
    }
}
