<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Radar\RadarBlockedItem;
use App\Models\Radar\RadarDetection;
use App\Models\Radar\RadarDevice;
use App\Models\Radar\RadarProtection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RadarController extends Controller
{
    public function updateConfig(Request $request)
    {
        $request->validate([
            'protections' => ['required', 'array'],
            'protections.*.id' => ['required', 'integer'],
            'protections.*.status' => ['required', 'string', 'in:Enabled,Logging,Disabled'],
            'protections.*.auto_block' => ['boolean'],
            'protections.*.notify_admin' => ['boolean'],
            'protections.*.sensitivity_level' => ['integer', 'min:1', 'max:100'],
            'protections.*.threshold_config' => ['nullable', 'array'],
        ]);

        foreach ($request->protections as $cfg) {
            RadarProtection::query()->where('id', '=', $cfg['id'], 'and')->update([
                'status' => $cfg['status'],
                'auto_block' => $cfg['auto_block'] ?? false,
                'notify_admin' => $cfg['notify_admin'] ?? false,
                'sensitivity_level' => $cfg['sensitivity_level'] ?? 50,
                'threshold_config' => $cfg['threshold_config'] ?? null,
            ]);
        }

        return back()->with('success', 'Radar configuration berhasil disimpan.');
    }

    public function storeBlockedItem(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'in:IP,Domain,Device,Email,UserAgent'],
            'value' => ['required', 'string', 'max:255'],
            'action' => ['required', 'string', 'in:Allow,Block'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $value = trim($request->value);
        $validationError = null;

        // Security Form Validation
        if ($request->type === 'IP') {
            if (! filter_var($value, FILTER_VALIDATE_IP)) {
                $validationError = 'Format IP Address tidak valid.';
            }
        } elseif ($request->type === 'Email') {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $validationError = 'Format Email tidak valid.';
            }
        } elseif ($request->type === 'Domain') {
            if (! preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i', $value)) {
                $validationError = 'Format Domain tidak valid.';
            }
        }

        if (! $validationError) {
            // Check for duplicates
            $exists = RadarBlockedItem::query()->where('type', '=', $request->type, 'and')
                ->where('value', '=', $value, 'and')
                ->exists();

            if ($exists) {
                $validationError = 'Item ini sudah ada di daftar.';
            }
        }

        if ($validationError) {
            return response()->json(['message' => $validationError], 422);
        }

        $item = RadarBlockedItem::create([
            'type' => $request->type,
            'value' => $value,
            'action' => $request->action,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan ke daftar.',
            'item' => $item,
        ]);
    }

    public function destroyBlockedItem(int|string $id)
    {
        $item = RadarBlockedItem::findOrFail($id);
        $item->{'delete'}();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari daftar.',
        ]);
    }

    public function resetDetections()
    {
        DB::transaction(function () {
            RadarDetection::query()->{'delete'}();
            RadarDevice::query()->{'delete'}();
        });

        // Mark so that ensureRadarDataSeeded won't re-seed demo data
        cache()->put('radar_detections_cleared', true, now()->addYears(10));

        return response()->json([
            'success' => true,
            'message' => 'All detections have been cleared.',
        ]);
    }
}
