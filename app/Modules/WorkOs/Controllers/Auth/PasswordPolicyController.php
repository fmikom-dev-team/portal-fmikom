<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PasswordPolicyController extends Controller
{
    public function index() { return response()->json(['policies' => []]); }
    public function updateGlobal(Request $request) { return response()->json(['message' => 'Updated']); }
    public function updateForOrganization(Request $request, $organization) { return response()->json(['message' => 'Updated']); }
}
