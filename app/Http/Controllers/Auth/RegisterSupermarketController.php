<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterSupermarketRequest;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterSupermarketController extends Controller
{
    public function create()
    {
        return view('auth.register-supermarket');
    }

    public function store(RegisterSupermarketRequest $request, Workspace $workspace): RedirectResponse
    {
        $validated = $request->validated();

        [$user, $organization] = DB::transaction(function () use ($validated): array {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'status' => 'active',
            ]);

            $organization = Organization::query()->create([
                'name' => $validated['supermarket_name'],
                'slug' => $this->uniqueSlug($validated['supermarket_name']),
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'active',
            ]);

            OrganizationMembership::query()->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
            ]);

            return [$user, $organization];
        });

        Auth::login($user);
        $request->session()->regenerate();
        $workspace->select($organization);
        session(['onboarding_step' => 'business']);

        return redirect()->route('onboarding.index');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'supermarket';
        $slug = $base;
        $suffix = 2;

        while (Organization::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
