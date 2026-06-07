<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
     * Select a template.
     */
    public function selectTemplate(Request $request)
    {
        return redirect()->route('module.pagi.dashboard')
            ->with('error', self::ERROR_DISABLED);
    }
}
