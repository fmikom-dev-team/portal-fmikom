<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $cv->title }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DejaVu Sans', 'Arial', sans-serif;
    font-size: {{ $customization['font_size'] ?? '10pt' }};
    line-height: {{ $customization['line_height'] ?? '1.4' }};
    color: #1f2937;
    background: #fff;
}

.page         { padding: 28px 32px; }
.page-sidebar { padding: 0; }

/* ── Shared ─────────────────────────────────────────────── */
.section  { margin-bottom: 15px; page-break-inside: avoid; }
.entry    { margin-bottom: 10px; page-break-inside: avoid; }
.e-table  { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
.e-title  { font-weight: bold; font-size: 9.5pt; text-align: left; }
.e-date   { font-size: 8.5pt; color: #6b7280; text-align: right; vertical-align: top; white-space: nowrap; width: 120px; }
.e-sub    { font-size: 9pt; color: #4b5563; font-style: italic; margin-bottom: 3px; }
.e-bold   { font-weight: bold; }
.e-desc   { font-size: 8.5pt; color: #374151; margin-top: 3px; }

/* skill tags */
.tag      { display: inline-block; padding: 2px 8px; margin: 2px 3px 2px 0; border-radius: 3px; font-size: 8pt; font-weight: 600; background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; }
.tag-p    { background: {{ ($customization['primary_color'] ?? '#1e3a8a') }}18; color: {{ $customization['primary_color'] ?? '#1e3a8a' }}; border-color: {{ ($customization['primary_color'] ?? '#1e3a8a') }}40; }

/* sidebar skill bars */
.sk-row   { margin-bottom: 7px; }
.sk-lbl   { font-size: 8.5pt; font-weight: 600; margin-bottom: 2px; }
.bar-bg   { height: 5px; background: #e5e7eb; border-radius: 3px; }
.bar-fill { height: 5px; border-radius: 3px; background: {{ $customization['primary_color'] ?? '#1e3a8a' }}; }

/* lang */
.ln-name  { font-weight: bold; font-size: 9pt; }
.ln-lvl   { font-size: 8pt; color: #6b7280; }

/* ── ATS Layout ─────────────────────────────────────────── */
.ats .hdr         { border-bottom: 2.5px solid {{ $customization['primary_color'] ?? '#1e3a8a' }}; padding-bottom: 11px; margin-bottom: 14px; }
.ats .hdr h1      { font-size: 22pt; font-weight: bold; letter-spacing: -0.3px; color: #111827; margin-bottom: 2px; }
.ats .hdr .job    { font-size: 11pt; color: {{ $customization['primary_color'] ?? '#1e3a8a' }}; font-weight: 600; margin-bottom: 6px; }
.ats .contact     { font-size: 8.5pt; color: #4b5563; }
.ats .sec-t       { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: {{ $customization['primary_color'] ?? '#1e3a8a' }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin-bottom: 9px; }

/* ── Sidebar Layout ─────────────────────────────────────── */
.sb-wrap        { width: 100%; border-collapse: collapse; }
.sb-wrap td     { vertical-align: top; }
.sb-left        { width: 33%; background: {{ ($customization['primary_color'] ?? '#1e3a8a') }}0e; border-right: 2px solid {{ ($customization['primary_color'] ?? '#1e3a8a') }}25; padding: 24px 14px 24px 16px; }
.sb-right       { width: 67%; padding: 24px 24px 24px 22px; }
.sb-name        { font-size: 14pt; font-weight: bold; color: #111827; margin-bottom: 3px; line-height: 1.2; }
.sb-job         { font-size: 9pt; color: {{ $customization['primary_color'] ?? '#1e3a8a' }}; font-weight: 700; margin-bottom: 14px; }
.sb-sec-t       { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: {{ $customization['primary_color'] ?? '#1e3a8a' }}; border-bottom: 1.5px solid {{ $customization['primary_color'] ?? '#1e3a8a' }}; padding-bottom: 2px; margin-bottom: 8px; margin-top: 14px; }
.sb-contact-row { font-size: 8pt; color: #374151; margin-bottom: 5px; word-break: break-word; }
.sb-contact-lbl { font-weight: bold; color: #111827; margin-bottom: 1px; }
.main-sec-t     { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.7px; color: {{ $customization['primary_color'] ?? '#1e3a8a' }}; border-bottom: 1.5px solid {{ $customization['primary_color'] ?? '#1e3a8a' }}; padding-bottom: 3px; margin-bottom: 9px; }

/* ── Executive Layout ───────────────────────────────────── */
.exec .hdr      { text-align: center; border-bottom: 1px solid #d1d5db; padding-bottom: 12px; margin-bottom: 16px; }
.exec .hdr h1   { font-size: 24pt; font-weight: normal; font-family: 'DejaVu Serif', Georgia, serif; letter-spacing: 2px; color: #111827; margin-bottom: 4px; }
.exec .hdr .job { font-size: 11pt; font-style: italic; color: #4b5563; margin-bottom: 8px; }
.exec .contact  { font-size: 8.5pt; color: #4b5563; border-top: 0.5px solid #d1d5db; padding-top: 6px; }
.exec .sec-t    { font-size: 10pt; font-weight: bold; font-family: 'DejaVu Serif', Georgia, serif; letter-spacing: 1px; border-bottom: 1px solid {{ $customization['primary_color'] ?? '#1e3a8a' }}; padding-bottom: 3px; margin-bottom: 10px; color: #111827; }

/* ── Creative Layout ────────────────────────────────────── */
.crea .hdr      { margin-bottom: 20px; padding-left: 4px; }
.crea .hdr h1   { font-size: 26pt; font-weight: 800; line-height: 1.05; color: #111827; margin-bottom: 4px; }
.crea .hdr .job { font-size: 13pt; font-weight: 300; color: {{ $customization['primary_color'] ?? '#6b21a8' }}; margin-bottom: 8px; }
.crea .contact  { font-size: 8.5pt; color: #6b7280; }
.crea .sec-t    { font-size: 10pt; font-weight: bold; margin-bottom: 3px; color: #111827; }
.crea .accent   { display: block; width: 28px; height: 3px; background: {{ $customization['primary_color'] ?? '#6b21a8' }}; margin-bottom: 9px; border-radius: 2px; }

/* ── Student Layout ─────────────────────────────────────── */
.stu .hdr       { background: #f9fafb; border-left: 5px solid {{ $customization['primary_color'] ?? '#10b981' }}; padding: 14px 16px; margin-bottom: 16px; }
.stu .hdr h1    { font-size: 20pt; font-weight: bold; color: #111827; margin-bottom: 2px; }
.stu .hdr .job  { font-size: 10.5pt; color: #4b5563; margin-bottom: 5px; }
.stu .contact   { font-size: 8.5pt; color: #4b5563; }
.stu .sec-t     { font-size: 9.5pt; font-weight: bold; color: {{ $customization['primary_color'] ?? '#10b981' }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin-bottom: 9px; }

/* contact separators */
.sep { color: #9ca3af; margin: 0 5px; }
</style>
</head>
<body>
@php
    $T      = $cv->template_id;
    $vis    = $customization['sections_visibility'] ?? [];
    $ord    = $customization['section_order'] ?? ['summary','experience','education','organizations','skills','certifications','trainings','achievements','languages','references'];
    $pri    = $customization['primary_color'] ?? '#1e3a8a';

    // sanitize skills
    $skills = array_values(array_filter(array_map(function($s) {
        if (!is_array($s)) return ['name'=>strval($s),'level'=>80];
        if (is_array($s['name'] ?? null)) { $s['level'] = $s['name']['percentage'] ?? $s['level'] ?? 80; $s['name'] = $s['name']['name'] ?? ''; }
        $s['name'] = strval($s['name'] ?? ''); $s['level'] = (int)($s['level'] ?? 80);
        return empty($s['name']) ? null : $s;
    }, $skills ?? [])));

    // sanitize languages
    $languages = array_values(array_filter(array_map(function($l) {
        if (!is_array($l)) return ['name'=>strval($l),'proficiency'=>''];
        if (is_array($l['name'] ?? null)) { $n=$l['name']; $l['proficiency']=$n['proficiency']??$l['proficiency']??''; $l['name']=$n['language']??$n['name']??''; }
        $l['name']=strval($l['name']??''); $l['proficiency']=strval($l['proficiency']??'');
        return empty($l['name']) ? null : $l;
    }, $languages ?? [])));

    // photo
    $photoSrc = '';
    $fp = is_array($personalInfo['foto_path'] ?? null) ? '' : ($personalInfo['foto_path'] ?? '');
    if ($fp) {
        if (str_starts_with($fp,'http')) { $photoSrc = $fp; }
        else { $fpath = public_path('storage/'.ltrim($fp,'/')); if(file_exists($fpath)){ $ext=strtolower(pathinfo($fpath,PATHINFO_EXTENSION)); $photoSrc='data:image/'.($ext==='jpg'?'jpeg':$ext).';base64,'.base64_encode(file_get_contents($fpath)); } }
    }

    // contact list (non-empty)
    $ctc = array_values(array_filter([
        $personalInfo['email']    ?? '',
        $personalInfo['phone']    ?? '',
        $personalInfo['location'] ?? '',
        $personalInfo['website']  ?? '',
        $personalInfo['linkedin'] ?? '',
        $personalInfo['github']   ?? '',
    ]));

    // whitelist of known tools matching JS side
    $KNOWN_TOOLS = [
        "figma" => true, "photoshop" => true, "illustrator" => true, "premiere" => true, "visual-studio-code" => true, "visual-studio" => true,
        "vue" => true, "react" => true, "tailwind-css" => true, "laravel" => true, "php" => true, "javascript" => true, "html5" => true, "css" => true,
        "git" => true, "github" => true, "docker" => true, "postman" => true, "canva" => true, "trello" => true, "jira" => true, "sass" => true, "nodedotjs" => true,
        "typescript" => true, "python" => true, "mysql" => true, "postgresql" => true, "mongodb" => true, "firebase" => true, "flutter" => true,
        "kotlin" => true, "swift" => true, "adobe-xd" => true, "adobe-indesign" => true, "adobe-after-effects" => true, "bootstrap" => true,
        "wordpress" => true, "jquery" => true, "npm" => true, "yarn" => true, "vite" => true, "webpack" => true, "aws" => true, "kubernetes" => true, "redis" => true,
        "google-cloud" => true, "azure" => true, "linux" => true, "ubuntu" => true, "android" => true, "ios" => true, "java" => true, "c" => true, "cpp" => true, "csharp" => true, "go" => true,
        "cplusplus" => true
    ];

    $getToolSlug = function($toolName) {
        $name = strtolower(trim($toolName));
        if ($name === "figma") return "figma";
        if ($name === "photoshop" || $name === "adobe photoshop" || $name === "ps") return "photoshop";
        if ($name === "illustrator" || $name === "adobe illustrator" || $name === "ai") return "illustrator";
        if ($name === "premiere" || $name === "premiere pro" || $name === "pr" || $name === "premierepro") return "premiere";
        if ($name === "vs code" || $name === "vscode" || $name === "visual studio code" || $name === "visual-studio-code") return "visual-studio-code";
        if ($name === "visual studio" || $name === "vs") return "visual-studio";
        if ($name === "vue" || $name === "vue.js" || $name === "vuejs" || $name === "vuedotjs") return "vue";
        if ($name === "react" || $name === "reactjs" || $name === "react.js") return "react";
        if ($name === "tailwind" || $name === "tailwindcss" || $name === "tailwind css" || $name === "tailwind-css") return "tailwind-css";
        if ($name === "laravel") return "laravel";
        if ($name === "php") return "php";
        if ($name === "javascript" || $name === "js") return "javascript";
        if ($name === "html" || $name === "html5") return "html5";
        if ($name === "css" || $name === "css3") return "css";
        if ($name === "git") return "git";
        if ($name === "github") return "github";
        if ($name === "docker") return "docker";
        if ($name === "postman") return "postman";
        if ($name === "canva") return "canva";
        if ($name === "trello") return "trello";
        if ($name === "jira") return "jira";
        if ($name === "sass" || $name === "scss") return "sass";
        if ($name === "nodejs" || $name === "node" || $name === "node.js") return "nodedotjs";
        if ($name === "typescript" || $name === "ts") return "typescript";
        if ($name === "python") return "python";
        if ($name === "mysql") return "mysql";
        if ($name === "postgresql" || $name === "postgres") return "postgresql";
        if ($name === "mongodb" || $name === "mongo") return "mongodb";
        if ($name === "firebase") return "firebase";
        if ($name === "flutter") return "flutter";
        if ($name === "kotlin") return "kotlin";
        if ($name === "swift") return "swift";
        if ($name === "xd" || $name === "adobe xd") return "adobe-xd";
        if ($name === "indesign" || $name === "adobe indesign") return "adobe-indesign";
        if ($name === "after effects" || $name === "ae" || $name === "adobe after effects") return "adobe-after-effects";
        if ($name === "bootstrap") return "bootstrap";
        if ($name === "wordpress") return "wordpress";
        if ($name === "jquery") return "jquery";
        if ($name === "npm") return "npm";
        if ($name === "yarn") return "yarn";
        if ($name === "vite") return "vite";
        if ($name === "webpack") return "webpack";
        if ($name === "aws") return "aws";
        if ($name === "kubernetes") return "kubernetes";
        if ($name === "redis") return "redis";
        if ($name === "google cloud" || $name === "google-cloud" || $name === "gcp") return "google-cloud";
        if ($name === "azure") return "azure";
        if ($name === "linux") return "linux";
        if ($name === "ubuntu") return "ubuntu";
        if ($name === "android") return "android";
        if ($name === "ios") return "ios";
        if ($name === "java") return "java";
        if ($name === "c") return "c";
        if ($name === "c++" || $name === "cpp") return "cplusplus";
        if ($name === "c#" || $name === "csharp") return "csharp";
        if ($name === "go" || $name === "golang") return "go";

        $slug = str_replace('.js', 'dotjs', $name);
        $slug = str_replace('.net', 'dotnet', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    };

    $hasLogo = function($toolName) use ($getToolSlug, $KNOWN_TOOLS) {
        if (!$toolName) return false;
        $slug = $getToolSlug($toolName);
        return isset($KNOWN_TOOLS[$slug]);
    };

    $getLogoBase64 = function($toolName) use ($getToolSlug, $KNOWN_TOOLS) {
        $slug = $getToolSlug($toolName);
        if (!isset($KNOWN_TOOLS[$slug])) return '';

        return \Illuminate\Support\Facades\Cache::remember('thesvg_b64_'.$slug, 86400 * 7, function() use ($slug) {
            $url = "https://cdn.jsdelivr.net/gh/glincker/thesvg@main/public/icons/{$slug}/default.svg";
            try {
                $ctx = stream_context_create(['http' => ['timeout' => 3]]);
                $content = file_get_contents($url, false, $ctx);
                if ($content) {
                    return 'data:image/svg+xml;base64,' . base64_encode($content);
                }
            } catch (\Exception $e) {
                // Ignore or log
            }
            return '';
        });
    };

    // helpers
    $dateRange = fn($s,$e,$def='Sekarang') => ($s ? $s.' — ' : '') . ($e ?: $def);
    $visible   = fn($sec) => $vis[$sec] ?? true;

    $showCheckbox   = !empty($customization['skills_show_checkbox']);
    $showLogo       = !empty($customization['skills_show_logo']);
    $showPercentage = !empty($customization['skills_show_percentage']);
@endphp

{{-- ═══════════════════════════════════════════════════
     ATS PROFESSIONAL / CUSTOM
═══════════════════════════════════════════════════ --}}
@if($T === 'ats-professional' || $T === 'custom')
<div class="page ats">
    <div class="hdr">
        <h1>{{ $personalInfo['name'] ?? '' }}</h1>
        @if(!empty($personalInfo['job_title']))<div class="job">{{ $personalInfo['job_title'] }}</div>@endif
        <div class="contact">
            @foreach($ctc as $i => $c){{ $i > 0 ? ' • ' : '' }}{{ $c }}@endforeach
        </div>
    </div>

    @foreach($ord as $sec)
    @if($visible($sec))

    @if($sec==='summary' && !empty($personalInfo['summary']))
    <div class="section"><div class="sec-t">Ringkasan Profesional</div>
        <div style="font-size:9pt;color:#374151;text-align:justify;">{!! nl2br(e($personalInfo['summary'])) !!}</div>
    </div>
    @endif

    @if($sec==='experience' && count($experience) > 0)
    <div class="section"><div class="sec-t">Pengalaman Kerja</div>
        @foreach($experience as $exp)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $exp['position'] ?? '' }} <span style="font-weight:normal;color:#4b5563;">di {{ $exp['company'] ?? '' }}</span></td>
                <td class="e-date">{{ $dateRange($exp['start_date'] ?? '', $exp['end_date'] ?? '') }}</td>
            </tr></table>
            @if(!empty($exp['description']))<div class="e-desc">{!! nl2br(e($exp['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='education' && count($education) > 0)
    <div class="section"><div class="sec-t">Pendidikan</div>
        @foreach($education as $edu)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $edu['degree'] ?? '' }}{{ !empty($edu['field']) ? ' — '.$edu['field'] : '' }}</td>
                <td class="e-date">{{ $dateRange($edu['start_date'] ?? '', $edu['end_date'] ?? '', '') }}</td>
            </tr></table>
            <div class="e-sub">{{ $edu['school'] ?? '' }}</div>
            @if(!empty($edu['description']))<div class="e-desc">{!! nl2br(e($edu['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='organizations' && count($organizations) > 0)
    <div class="section"><div class="sec-t">Organisasi</div>
        @foreach($organizations as $org)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $org['role'] ?? '' }} <span style="font-weight:normal;color:#4b5563;">di {{ $org['name'] ?? '' }}</span></td>
                <td class="e-date">{{ $dateRange($org['start_date'] ?? '', $org['end_date'] ?? '', '') }}</td>
            </tr></table>
            @if(!empty($org['description']))<div class="e-desc">{!! nl2br(e($org['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='skills' && count($skills) > 0)
    <div class="section"><div class="sec-t">Keahlian</div>
        @if($showCheckbox || $showLogo || $showPercentage)
        <table style="width:100%;border-collapse:collapse;margin-top:4px;">
            @foreach(array_chunk($skills, 2) as $row)
            <tr>
                @foreach($row as $sk)
                <td style="width:50%;padding-right:15px;padding-bottom:8px;vertical-align:top;">
                    <div style="font-size:8.5pt;font-weight:600;margin-bottom:3.5px;line-height:1.2;">
                        @if($showCheckbox)
                            <span style="color:{{ $pri }};font-weight:bold;margin-right:4px;vertical-align:middle;">✓</span>
                        @endif
                        @if($showLogo && $hasLogo($sk['name']))
                            @php $logoB64 = $getLogoBase64($sk['name']); @endphp
                            @if($logoB64)
                                <img src="{{ $logoB64 }}" style="width:12px;height:12px;vertical-align:middle;margin-right:4px;" />
                            @endif
                        @endif
                        <span style="vertical-align:middle;">{{ $sk['name'] }}</span>
                        @if($showPercentage)
                            <span style="color:#9ca3af;font-weight:normal;font-size:7.5pt;float:right;margin-top:1px;">{{ $sk['level'] }}%</span>
                        @endif
                    </div>
                    @if($showPercentage)
                    <div style="height:5px;background:#e5e7eb;border-radius:3px;margin-top:2px;">
                        <div style="height:5px;border-radius:3px;background:{{ $pri }};width:{{ min(100,$sk['level']) }}%;"></div>
                    </div>
                    @endif
                </td>
                @endforeach
                @if(count($row) === 1)
                <td style="width:50%;"></td>
                @endif
            </tr>
            @endforeach
        </table>
        @else
        <div style="margin-top:2px;">
            @foreach($skills as $sk)
            <span class="tag" style="padding: 3px 8px; line-height: 1.2; display: inline-block; vertical-align: middle;">
                @if($showLogo && $hasLogo($sk['name']))
                    @php $logoB64 = $getLogoBase64($sk['name']); @endphp
                    @if($logoB64)
                        <img src="{{ $logoB64 }}" style="width:11px;height:11px;vertical-align:middle;margin-right:3px;margin-top:-1px;" />
                    @endif
                @endif
                <span style="vertical-align:middle;">{{ $sk['name'] }}</span>
                <span style="font-weight:normal;color:#6b7280;vertical-align:middle;margin-left:2px;">({{ $sk['level'] }}%)</span>
            </span>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    @if($sec==='certifications' && count($certifications) > 0)
    <div class="section"><div class="sec-t">Sertifikasi</div>
        @foreach($certifications as $cert)
        @if(!empty($cert['name']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $cert['name'] }}</td>
                <td class="e-date">{{ $cert['date'] ?? '' }}</td>
            </tr></table>
            <div class="e-sub">{{ $cert['issuer'] ?? '' }}{{ !empty($cert['credential_id']) ? ' · ID: '.$cert['credential_id'] : '' }}</div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='trainings' && count($trainings) > 0)
    <div class="section"><div class="sec-t">Pelatihan &amp; Kursus</div>
        @foreach($trainings as $trn)
        @if(!empty($trn['name']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $trn['name'] }}</td>
                <td class="e-date">{{ $trn['date'] ?? '' }}</td>
            </tr></table>
            <div class="e-sub">{{ $trn['provider'] ?? '' }}</div>
            @if(!empty($trn['description']))<div class="e-desc">{{ $trn['description'] }}</div>@endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='achievements' && count($achievements) > 0)
    <div class="section"><div class="sec-t">Prestasi &amp; Penghargaan</div>
        @foreach($achievements as $ach)
        @if(!empty($ach['title']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $ach['title'] }}</td>
                <td class="e-date">{{ $ach['date'] ?? '' }}</td>
            </tr></table>
            @if(!empty($ach['description']))<div class="e-desc">{{ $ach['description'] }}</div>@endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='languages' && count($languages) > 0)
    <div class="section"><div class="sec-t">Bahasa</div>
        <table style="width:100%;border-collapse:collapse;">
        @foreach($languages as $idx => $ln)
        @if($idx % 3 === 0)
        @if($idx > 0)</tr>@endif
        <tr>
        @endif
        <td style="width:33%;padding-right:10px;padding-bottom:5px;vertical-align:top;">
            <div class="ln-name">{{ $ln['name'] }}</div>
            @if(!empty($ln['proficiency']))<div class="ln-lvl">{{ $ln['proficiency'] }}</div>@endif
        </td>
        @endforeach
        </tr>
        </table>
    </div>
    @endif

    @if($sec==='references' && count($references) > 0)
    <div class="section"><div class="sec-t">Referensi</div>
        <table style="width:100%;border-collapse:collapse;">
        @foreach($references as $idx => $ref)
        @if($idx % 2 === 0)
        @if($idx > 0)</tr>@endif
        <tr>
        @endif
        <td style="width:50%;padding-right:14px;padding-bottom:8px;vertical-align:top;">
            <div style="font-weight:bold;font-size:9pt;">{{ $ref['name'] ?? '' }}</div>
            <div style="font-size:8.5pt;color:#4b5563;">{{ ($ref['position'] ?? '') . (!empty($ref['company']) ? ' di '.$ref['company'] : '') }}</div>
            @if(!empty($ref['contact']))<div style="font-size:8pt;color:#6b7280;">{{ $ref['contact'] }}</div>@endif
        </td>
        @endforeach
        </tr>
        </table>
    </div>
    @endif

    @endif
    @endforeach
</div>

{{-- ═══════════════════════════════════════════════════
     MODERN SIDEBAR
═══════════════════════════════════════════════════ --}}
@elseif($T === 'modern-sidebar')
<div class="page-sidebar">
<table class="sb-wrap"><tr>

<td class="sb-left">
    @if(!empty($photoSrc))
    <div style="width:72px;height:72px;overflow:hidden;border-radius:50%;margin:0 auto 12px auto;border:2.5px solid {{ $pri }}40;">
        <img src="{{ $photoSrc }}" style="width:100%;height:100%;display:block;" />
    </div>
    @endif
    <div class="sb-name">{{ $personalInfo['name'] ?? '' }}</div>
    @if(!empty($personalInfo['job_title']))<div class="sb-job">{{ $personalInfo['job_title'] }}</div>@endif

    <div class="sb-sec-t" style="margin-top:0;">Kontak</div>
    @if(!empty($personalInfo['email']))<div class="sb-contact-row"><div class="sb-contact-lbl">Email</div>{{ $personalInfo['email'] }}</div>@endif
    @if(!empty($personalInfo['phone']))<div class="sb-contact-row"><div class="sb-contact-lbl">Telepon</div>{{ $personalInfo['phone'] }}</div>@endif
    @if(!empty($personalInfo['location']))<div class="sb-contact-row"><div class="sb-contact-lbl">Lokasi</div>{{ $personalInfo['location'] }}</div>@endif
    @if(!empty($personalInfo['linkedin']))<div class="sb-contact-row"><div class="sb-contact-lbl">LinkedIn</div>{{ $personalInfo['linkedin'] }}</div>@endif
    @if(!empty($personalInfo['github']))<div class="sb-contact-row"><div class="sb-contact-lbl">GitHub</div>{{ $personalInfo['github'] }}</div>@endif
    @if(!empty($personalInfo['website']))<div class="sb-contact-row"><div class="sb-contact-lbl">Website</div>{{ $personalInfo['website'] }}</div>@endif

    @foreach($ord as $sec)
    @if($visible($sec))
    @if($sec==='skills' && count($skills) > 0)
    <div class="sb-sec-t">Keahlian</div>
    @foreach($skills as $sk)
    <div class="sk-row">
        <div class="sk-lbl">{{ $sk['name'] }}</div>
        <div class="bar-bg"><div class="bar-fill" style="width:{{ min(100,$sk['level']) }}%;"></div></div>
    </div>
    @endforeach
    @endif
    @if($sec==='languages' && count($languages) > 0)
    <div class="sb-sec-t">Bahasa</div>
    @foreach($languages as $ln)
    <div style="margin-bottom:5px;">
        <div class="ln-name">{{ $ln['name'] }}</div>
        @if(!empty($ln['proficiency']))<div class="ln-lvl">{{ $ln['proficiency'] }}</div>@endif
    </div>
    @endforeach
    @endif
    @endif
    @endforeach
</td>

<td class="sb-right">
    @foreach($ord as $sec)
    @if($visible($sec))

    @if($sec==='summary' && !empty($personalInfo['summary']))
    <div class="section"><div class="main-sec-t">Tentang Saya</div>
        <div style="font-size:9pt;color:#374151;text-align:justify;">{!! nl2br(e($personalInfo['summary'])) !!}</div>
    </div>
    @endif

    @if($sec==='experience' && count($experience) > 0)
    <div class="section"><div class="main-sec-t">Pengalaman Kerja</div>
        @foreach($experience as $exp)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title" style="color:{{ $pri }};">{{ $exp['position'] ?? '' }}</td>
                <td class="e-date">{{ $dateRange($exp['start_date'] ?? '', $exp['end_date'] ?? '') }}</td>
            </tr></table>
            <div class="e-sub e-bold">{{ $exp['company'] ?? '' }}</div>
            @if(!empty($exp['description']))<div class="e-desc">{!! nl2br(e($exp['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='education' && count($education) > 0)
    <div class="section"><div class="main-sec-t">Pendidikan</div>
        @foreach($education as $edu)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $edu['degree'] ?? '' }}{{ !empty($edu['field']) ? ' — '.$edu['field'] : '' }}</td>
                <td class="e-date">{{ $dateRange($edu['start_date'] ?? '', $edu['end_date'] ?? '', '') }}</td>
            </tr></table>
            <div class="e-sub">{{ $edu['school'] ?? '' }}</div>
            @if(!empty($edu['description']))<div class="e-desc">{!! nl2br(e($edu['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='organizations' && count($organizations) > 0)
    <div class="section"><div class="main-sec-t">Organisasi</div>
        @foreach($organizations as $org)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $org['role'] ?? '' }} <span style="font-weight:normal;color:#4b5563;">di {{ $org['name'] ?? '' }}</span></td>
                <td class="e-date">{{ $dateRange($org['start_date'] ?? '', $org['end_date'] ?? '', '') }}</td>
            </tr></table>
            @if(!empty($org['description']))<div class="e-desc">{!! nl2br(e($org['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='certifications' && count($certifications) > 0)
    <div class="section"><div class="main-sec-t">Sertifikasi</div>
        @foreach($certifications as $cert)
        @if(!empty($cert['name']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $cert['name'] }}</td>
                <td class="e-date">{{ $cert['date'] ?? '' }}</td>
            </tr></table>
            <div class="e-sub">{{ $cert['issuer'] ?? '' }}{{ !empty($cert['credential_id']) ? ' · ID: '.$cert['credential_id'] : '' }}</div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='trainings' && count($trainings) > 0)
    <div class="section"><div class="main-sec-t">Pelatihan &amp; Kursus</div>
        @foreach($trainings as $trn)
        @if(!empty($trn['name']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $trn['name'] }}</td>
                <td class="e-date">{{ $trn['date'] ?? '' }}</td>
            </tr></table>
            <div class="e-sub">{{ $trn['provider'] ?? '' }}</div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='achievements' && count($achievements) > 0)
    <div class="section"><div class="main-sec-t">Prestasi</div>
        @foreach($achievements as $ach)
        @if(!empty($ach['title']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $ach['title'] }}</td>
                <td class="e-date">{{ $ach['date'] ?? '' }}</td>
            </tr></table>
            @if(!empty($ach['description']))<div class="e-desc">{{ $ach['description'] }}</div>@endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='references' && count($references) > 0)
    <div class="section"><div class="main-sec-t">Referensi</div>
        <table style="width:100%;border-collapse:collapse;">
        @foreach($references as $idx => $ref)
        @if($idx % 2 === 0)
        @if($idx > 0)</tr>@endif
        <tr>
        @endif
        <td style="width:50%;padding-right:12px;padding-bottom:8px;vertical-align:top;">
            <div style="font-weight:bold;font-size:9pt;">{{ $ref['name'] ?? '' }}</div>
            <div style="font-size:8.5pt;color:#4b5563;">{{ ($ref['position'] ?? '') . (!empty($ref['company']) ? ' di '.$ref['company'] : '') }}</div>
            @if(!empty($ref['contact']))<div style="font-size:8pt;color:#6b7280;">{{ $ref['contact'] }}</div>@endif
        </td>
        @endforeach
        </tr>
        </table>
    </div>
    @endif

    @endif
    @endforeach
</td>

</tr></table>
</div>

{{-- ═══════════════════════════════════════════════════
     EXECUTIVE / CREATIVE / STUDENT  (shared structure)
═══════════════════════════════════════════════════ --}}
@else
@php
    $cls = $T === 'executive' ? 'exec' : ($T === 'creative-minimal' ? 'crea' : 'stu');
    $isCrea = $cls === 'crea';
    $isExec = $cls === 'exec';
    $isStu  = $cls === 'stu';
@endphp
<div class="page {{ $cls }}">

    {{-- Header --}}
    <div class="hdr">
        @if(!empty($photoSrc) && $isStu)
        <div style="float:left;width:72px;height:72px;overflow:hidden;border-radius:8px;margin-right:14px;">
            <img src="{{ $photoSrc }}" style="width:100%;height:100%;display:block;" />
        </div>
        @endif
        <h1>{{ $personalInfo['name'] ?? '' }}</h1>
        @if(!empty($personalInfo['job_title']))<div class="job">{{ $personalInfo['job_title'] }}</div>@endif
        <div class="contact">
            @foreach($ctc as $i => $c){{ $i > 0 ? ($isExec ? '   |   ' : ' • ') : '' }}{{ $c }}@endforeach
        </div>
        <div style="clear:both;"></div>
    </div>

    @foreach($ord as $sec)
    @if($visible($sec))

    @if($sec==='summary' && !empty($personalInfo['summary']))
    <div class="section">
        <div class="sec-t">Ringkasan</div>
        @if($isCrea)<span class="accent"></span>@endif
        <div style="font-size:9pt;color:#374151;text-align:justify;">{!! nl2br(e($personalInfo['summary'])) !!}</div>
    </div>
    @endif

    @if($sec==='experience' && count($experience) > 0)
    <div class="section">
        <div class="sec-t">Pengalaman Profesional</div>
        @if($isCrea)<span class="accent"></span>@endif
        @foreach($experience as $exp)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title" style="color:{{ $isExec ? '#111827' : $pri }};">{{ $exp['position'] ?? '' }}</td>
                <td class="e-date">{{ $dateRange($exp['start_date'] ?? '', $exp['end_date'] ?? '') }}</td>
            </tr></table>
            <div class="e-sub e-bold">{{ $exp['company'] ?? '' }}</div>
            @if(!empty($exp['description']))<div class="e-desc">{!! nl2br(e($exp['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='education' && count($education) > 0)
    <div class="section">
        <div class="sec-t">Pendidikan</div>
        @if($isCrea)<span class="accent"></span>@endif
        @foreach($education as $edu)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $edu['degree'] ?? '' }}{{ !empty($edu['field']) ? ' — '.$edu['field'] : '' }}</td>
                <td class="e-date">{{ $dateRange($edu['start_date'] ?? '', $edu['end_date'] ?? '', '') }}</td>
            </tr></table>
            <div class="e-sub">{{ $edu['school'] ?? '' }}</div>
            @if(!empty($edu['description']))<div class="e-desc">{!! nl2br(e($edu['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='organizations' && count($organizations) > 0)
    <div class="section">
        <div class="sec-t">Organisasi</div>
        @if($isCrea)<span class="accent"></span>@endif
        @foreach($organizations as $org)
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $org['role'] ?? '' }} <span style="font-weight:normal;color:#4b5563;">di {{ $org['name'] ?? '' }}</span></td>
                <td class="e-date">{{ $dateRange($org['start_date'] ?? '', $org['end_date'] ?? '', '') }}</td>
            </tr></table>
            @if(!empty($org['description']))<div class="e-desc">{!! nl2br(e($org['description'])) !!}</div>@endif
        </div>
        @endforeach
    </div>
    @endif

    @if($sec==='skills' && count($skills) > 0)
    <div class="section">
        <div class="sec-t">Keahlian</div>
        @if($isCrea)<span class="accent"></span>@endif
        
        @if($showCheckbox || $showLogo || $showPercentage)
        <table style="width:100%;border-collapse:collapse;margin-top:4px;">
            @foreach(array_chunk($skills, 2) as $row)
            <tr>
                @foreach($row as $sk)
                <td style="width:50%;padding-right:15px;padding-bottom:8px;vertical-align:top;">
                    <div style="font-size:8.5pt;font-weight:600;margin-bottom:3.5px;line-height:1.2;">
                        @if($showCheckbox)
                            <span style="color:{{ $pri }};font-weight:bold;margin-right:4px;vertical-align:middle;">✓</span>
                        @endif
                        @if($showLogo && $hasLogo($sk['name']))
                            @php $logoB64 = $getLogoBase64($sk['name']); @endphp
                            @if($logoB64)
                                <img src="{{ $logoB64 }}" style="width:12px;height:12px;vertical-align:middle;margin-right:4px;" />
                            @endif
                        @endif
                        <span style="vertical-align:middle;">{{ $sk['name'] }}</span>
                        @if($showPercentage)
                            <span style="color:#9ca3af;font-weight:normal;font-size:7.5pt;float:right;margin-top:1px;">{{ $sk['level'] }}%</span>
                        @endif
                    </div>
                    @if($showPercentage)
                    <div style="height:5px;background:#e5e7eb;border-radius:3px;margin-top:2px;">
                        <div style="height:5px;border-radius:3px;background:{{ $pri }};width:{{ min(100,$sk['level']) }}%;"></div>
                    </div>
                    @endif
                </td>
                @endforeach
                @if(count($row) === 1)
                <td style="width:50%;"></td>
                @endif
            </tr>
            @endforeach
        </table>
        @else
        <div style="margin-top:2px;">
            @foreach($skills as $sk)
            <span class="tag tag-p" style="padding: 3px 8px; line-height: 1.2; display: inline-block; vertical-align: middle;">
                @if($showLogo && $hasLogo($sk['name']))
                    @php $logoB64 = $getLogoBase64($sk['name']); @endphp
                    @if($logoB64)
                        <img src="{{ $logoB64 }}" style="width:11px;height:11px;vertical-align:middle;margin-right:3px;margin-top:-1px;" />
                    @endif
                @endif
                <span style="vertical-align:middle;">{{ $sk['name'] }}</span>
                <span style="font-weight:normal;color:#6b7280;vertical-align:middle;margin-left:2px;">({{ $sk['level'] }}%)</span>
            </span>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    @if($sec==='certifications' && count($certifications) > 0)
    <div class="section">
        <div class="sec-t">Sertifikasi</div>
        @if($isCrea)<span class="accent"></span>@endif
        @foreach($certifications as $cert)
        @if(!empty($cert['name']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $cert['name'] }}</td>
                <td class="e-date">{{ $cert['date'] ?? '' }}</td>
            </tr></table>
            <div class="e-sub">{{ $cert['issuer'] ?? '' }}{{ !empty($cert['credential_id']) ? ' · ID: '.$cert['credential_id'] : '' }}</div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='trainings' && count($trainings) > 0)
    <div class="section">
        <div class="sec-t">Pelatihan &amp; Kursus</div>
        @if($isCrea)<span class="accent"></span>@endif
        @foreach($trainings as $trn)
        @if(!empty($trn['name']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $trn['name'] }}</td>
                <td class="e-date">{{ $trn['date'] ?? '' }}</td>
            </tr></table>
            <div class="e-sub">{{ $trn['provider'] ?? '' }}</div>
            @if(!empty($trn['description']))<div class="e-desc">{{ $trn['description'] }}</div>@endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='achievements' && count($achievements) > 0)
    <div class="section">
        <div class="sec-t">Prestasi &amp; Penghargaan</div>
        @if($isCrea)<span class="accent"></span>@endif
        @foreach($achievements as $ach)
        @if(!empty($ach['title']))
        <div class="entry">
            <table class="e-table"><tr>
                <td class="e-title">{{ $ach['title'] }}</td>
                <td class="e-date">{{ $ach['date'] ?? '' }}</td>
            </tr></table>
            @if(!empty($ach['description']))<div class="e-desc">{{ $ach['description'] }}</div>@endif
        </div>
        @endif
        @endforeach
    </div>
    @endif

    @if($sec==='languages' && count($languages) > 0)
    <div class="section">
        <div class="sec-t">Bahasa</div>
        @if($isCrea)<span class="accent"></span>@endif
        <table style="width:100%;border-collapse:collapse;">
        @foreach($languages as $idx => $ln)
        @if($idx % 3 === 0)
        @if($idx > 0)</tr>@endif
        <tr>
        @endif
        <td style="width:33%;padding-right:10px;padding-bottom:5px;vertical-align:top;">
            <div class="ln-name">{{ $ln['name'] }}</div>
            @if(!empty($ln['proficiency']))<div class="ln-lvl">{{ $ln['proficiency'] }}</div>@endif
        </td>
        @endforeach
        </tr>
        </table>
    </div>
    @endif

    @if($sec==='references' && count($references) > 0)
    <div class="section">
        <div class="sec-t">Referensi</div>
        @if($isCrea)<span class="accent"></span>@endif
        <table style="width:100%;border-collapse:collapse;">
        @foreach($references as $idx => $ref)
        @if($idx % 2 === 0)
        @if($idx > 0)</tr>@endif
        <tr>
        @endif
        <td style="width:50%;padding-right:14px;padding-bottom:8px;vertical-align:top;">
            <div style="font-weight:bold;font-size:9pt;">{{ $ref['name'] ?? '' }}</div>
            <div style="font-size:8.5pt;color:#4b5563;">{{ ($ref['position'] ?? '') . (!empty($ref['company']) ? ' di '.$ref['company'] : '') }}</div>
            @if(!empty($ref['contact']))<div style="font-size:8pt;color:#6b7280;">{{ $ref['contact'] }}</div>@endif
        </td>
        @endforeach
        </tr>
        </table>
    </div>
    @endif

    @endif
    @endforeach
</div>
@endif

</body>
</html>
