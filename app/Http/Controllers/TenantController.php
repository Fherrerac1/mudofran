<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    // List all tenants
    public function index()
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }
        $tenants = Tenant::all();
        return Inertia::render('Cruds/Tenants/Index', [
            'user' => $user,
            'tenants' => $tenants,
        ]);
    }
    // Show form to create a new tenant
    public function create()
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }
        $user = Auth::user();
        return Inertia::render('Cruds/Tenants/Create', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }
        // ✅ Validate input
        $validated = $request->validate([
            'slug' => 'required|unique:tenants,slug',
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|max:255|unique:users,email',
            'user.password' => ['required', 'confirmed', Rules\Password::defaults()],
            'configuration.style_color' => 'nullable|string|max:50',
            'configuration.text_color' => 'nullable|string|max:50',
            'configuration.unique_color' => 'nullable|string|max:50',
            'configuration.serial_num' => 'nullable|string|max:100',
            'configuration.url_app' => 'nullable|string|max:255',
            'configuration.business_name' => 'nullable|string|max:255',
            'configuration.address' => 'nullable|string|max:255',
            'configuration.postal_code' => 'nullable|string|max:20',
            'configuration.phone' => 'nullable|string|max:50',
            'configuration.town' => 'nullable|string|max:100',
            'configuration.province' => 'nullable|string|max:100',
            'configuration.email' => 'nullable|email|max:255',
            'configuration.tax_id' => 'nullable|string|max:50',
            'configuration.business_type' => 'nullable|string|max:100',
            'configuration.pusher_app_id' => 'nullable|string|max:255',
            'configuration.pusher_app_key' => 'nullable|string|max:255',
            'configuration.pusher_app_secret' => 'nullable|string|max:255',
            'configuration.pusher_app_cluster' => 'nullable|string|max:255',
            'main_logo' => 'nullable|image|mimes:png|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated) {
            // ✅ Create Tenant
            $tenant = Tenant::create([
                'name' => $validated['user']['name'],
                'slug' => Str::slug($validated['slug']),
            ]);

            // ✅ Create admin User linked to Tenant
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
                'password' => Hash::make($validated['user']['password']),
                'rol' => 'admin',
            ]);

            // ✅ Create Configuration linked to Tenant
            $configuration = Configuration::create([
                'tenant_id' => $tenant->id,
                'style_color' => $request->input('configuration.style_color', '#000000'),
                'text_color' => $request->input('configuration.text_color', '#FFFFFF'),
                'unique_color' => $request->input('configuration.unique_color', '#FF0000'),
                'serial_num' => $request->input('configuration.serial_num', 'SN-' . Str::random(8)),
                'url_app' => $request->input('configuration.url_app', 'https://elayudante.com'),
                'business_name' => $request->input('configuration.business_name', 'Default Business'),
                'address' => $request->input('configuration.address', '123 Main St'),
                'postal_code' => $request->input('configuration.postal_code', '12345'),
                'phone' => $request->input('configuration.phone', '555-555-5555'),
                'town' => $request->input('configuration.town', 'Default Town'),
                'province' => $request->input('configuration.province', 'Default Province'),
                'email' => $request->input('configuration.email', 'info@elayudante.es'),
                'tax_id' => $request->input('configuration.tax_id', '123456789'),
                'business_type' => $request->input('configuration.business_type', 'Default Type'),
                'pusher_app_id' => $request->input('configuration.pusher_app_id'),
                'pusher_app_key' => $request->input('configuration.pusher_app_key'),
                'pusher_app_secret' => $request->input('configuration.pusher_app_secret'),
                'pusher_app_cluster' => $request->input('configuration.pusher_app_cluster', 'eu'),
            ]);

            // ✅ Handle logo upload (stored in storage/app/public/tenants/{slug}/images)
            if ($request->hasFile('main_logo')) {
                $path = $request->file('main_logo')->storeAs(
                    "tenants/{$tenant->slug}/images",
                    'main_logo.png',
                    'public' // make sure disk 'public' is configured
                );

                $configuration->update([
                    'main_logo' => $path, // will be accessible via asset('storage/'.$path)
                ]);
            }
        });

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant, admin user, and configuration created successfully!');
    }
    // Show form to edit a tenant
    public function edit(Tenant $tenant)
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }
        // Load tenant domains
        $adminUser = User::where('rol', 'admin')->where('tenant_id', $tenant->id)->first();
        $configuration = $tenant->configuration;

        return Inertia::render('Cruds/Tenants/Update', [
            'user' => $user,
            'tenant' => $tenant,
            'adminUser' => $adminUser,
            'configuration' => $configuration,
        ]);
    }
    public function update(Request $request, Tenant $tenant)
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }

        $adminUser = $tenant->users()->where('rol', 'admin')->firstOrFail();
        $validated = $request->validate([
            'user.name' => 'required|string|max:255',
            'user.email' => 'required|email|max:255|unique:users,email,' . $adminUser->id,
            'user.password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'configuration.style_color' => 'nullable|string|max:50',
            'configuration.text_color' => 'nullable|string|max:50',
            'configuration.unique_color' => 'nullable|string|max:50',
            'configuration.serial_num' => 'nullable|string|max:100',
            'configuration.url_app' => 'nullable|string|max:255',
            'configuration.business_name' => 'nullable|string|max:255',
            'configuration.address' => 'nullable|string|max:255',
            'configuration.postal_code' => 'nullable|string|max:20',
            'configuration.phone' => 'nullable|string|max:50',
            'configuration.town' => 'nullable|string|max:100',
            'configuration.province' => 'nullable|string|max:100',
            'configuration.email' => 'nullable|email|max:255',
            'configuration.tax_id' => 'nullable|string|max:50',
            'configuration.business_type' => 'nullable|string|max:100',
            'configuration.pusher_app_id' => 'nullable|string|max:255',
            'configuration.pusher_app_key' => 'nullable|string|max:255',
            'configuration.pusher_app_secret' => 'nullable|string|max:255',
            'configuration.pusher_app_cluster' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($tenant, $adminUser, $request, $validated) {

            if ($tenant->api_token == null) {
                $tenant->update([
                    'api_token' => Str::random(60),
                ]);
            }


            // ✅ Update Admin User
            $userData = [
                'name' => $validated['user']['name'],
                'email' => $validated['user']['email'],
            ];

            // Only update password if provided
            if (!empty($validated['user']['password'])) {
                $userData['password'] = Hash::make($validated['user']['password']);
            }

            $adminUser->update($userData);

            // ✅ Update Configuration
            $configuration = $tenant->configuration;
            $configuration->update([
                'style_color' => $request->input('configuration.style_color', $configuration->style_color),
                'text_color' => $request->input('configuration.text_color', $configuration->text_color),
                'unique_color' => $request->input('configuration.unique_color', $configuration->unique_color),
                'serial_num' => $request->input('configuration.serial_num', $configuration->serial_num),
                'url_app' => $request->input('configuration.url_app', $configuration->url_app),
                'business_name' => $request->input('configuration.business_name', $configuration->business_name),
                'address' => $request->input('configuration.address', $configuration->address),
                'postal_code' => $request->input('configuration.postal_code', $configuration->postal_code),
                'phone' => $request->input('configuration.phone', $configuration->phone),
                'town' => $request->input('configuration.town', $configuration->town),
                'province' => $request->input('configuration.province', $configuration->province),
                'email' => $request->input('configuration.email', $configuration->email),
                'tax_id' => $request->input('configuration.tax_id', $configuration->tax_id),
                'business_type' => $request->input('configuration.business_type', $configuration->business_type),
                'pusher_app_id' => $request->input('configuration.pusher_app_id', $configuration->pusher_app_id),
                'pusher_app_key' => $request->input('configuration.pusher_app_key', $configuration->pusher_app_key),
                'pusher_app_secret' => $request->input('configuration.pusher_app_secret', $configuration->pusher_app_secret),
                'pusher_app_cluster' => $request->input('configuration.pusher_app_cluster', $configuration->pusher_app_cluster),
            ]);

            // ✅ Update Logo if new one uploaded
            if ($request->hasFile('main_logo')) {
                if ($configuration->main_logo && Storage::disk('public')->exists($configuration->main_logo)) {
                    Storage::disk('public')->delete($configuration->main_logo);
                }

                $path = $request->file('main_logo')->storeAs(
                    "tenants/{$tenant->slug}/images",
                    'main_logo.png',
                    'public'
                );

                $configuration->update(['main_logo' => $path]);
            }
        });

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant, admin user, and configuration updated successfully!');
    }

    // Optional: delete tenant
    public function destroy(Tenant $tenant)
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }
        $tenant->delete();
        return redirect()->route('tenants.index')
            ->with('success', 'Tenant deleted successfully!');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if ($user->rol !== 'super_admin') {
            abort(403);
        }
        $user = Auth::user();
        $tenants = Tenant::all()->load('users', 'facturas', 'presupuestos', 'activities');
        return Inertia::render('Cruds/Tenants/Super_Dashboard', [
            'user' => $user,
            'tenants' => $tenants,
        ]);
    }
}
