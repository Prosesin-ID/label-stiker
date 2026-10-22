<?php

namespace App\Http\Controllers;

use App\Models\OperationalCategory;
use App\Models\OperationalItem;
use App\Models\OperationalLocation;
use App\Models\OperationalNeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationalMasterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MASTER DATA OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        return view('operational-masters.index', [
            'needs' => OperationalNeed::query()
                ->orderBy('name')
                ->get(),

            'categories' => OperationalCategory::query()
                ->orderBy('name')
                ->get(),

            'locations' => OperationalLocation::query()
                ->orderBy('code')
                ->get(),

            'items' => OperationalItem::query()
                ->with('category')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN KEBUTUHAN
    |--------------------------------------------------------------------------
    */

    public function storeNeed(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('operational_needs', 'name'),
            ],
        ]);

        OperationalNeed::create([
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return $this->success('Kebutuhan operasional berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE KEBUTUHAN
    |--------------------------------------------------------------------------
    */

    public function updateNeed(
        Request $request,
        OperationalNeed $need
    ): RedirectResponse {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('operational_needs', 'name')
                    ->ignore($need->id),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $need->update([
            'name' => trim($data['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->success('Kebutuhan operasional berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN KATEGORI
    |--------------------------------------------------------------------------
    */

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('operational_categories', 'name'),
            ],
        ]);

        OperationalCategory::create([
            'name' => trim($data['name']),
            'is_active' => true,
        ]);

        return $this->success('Kategori operasional berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE KATEGORI
    |--------------------------------------------------------------------------
    */

    public function updateCategory(
        Request $request,
        OperationalCategory $category
    ): RedirectResponse {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('operational_categories', 'name')
                    ->ignore($category->id),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $category->update([
            'name' => trim($data['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->success('Kategori operasional berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN LOKASI
    |--------------------------------------------------------------------------
    */

    public function storeLocation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('operational_locations', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        OperationalLocation::create([
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'address' => $data['address'] ?? null,
            'is_active' => true,
        ]);

        return $this->success('Lokasi operasional berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE LOKASI
    |--------------------------------------------------------------------------
    */

    public function updateLocation(
        Request $request,
        OperationalLocation $location
    ): RedirectResponse {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('operational_locations', 'code')
                    ->ignore($location->id),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $location->update([
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'address' => $data['address'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->success('Lokasi operasional berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN ITEM OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function storeItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('operational_items', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'max:200',
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('operational_categories', 'id')
                    ->where('is_active', true),
            ],
        ]);

        OperationalItem::create([
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'category_id' => $data['category_id'] ?? null,
            'is_active' => true,
        ]);

        return $this->success('Item operasional berhasil ditambahkan.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ITEM OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function updateItem(
        Request $request,
        OperationalItem $item
    ): RedirectResponse {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('operational_items', 'code')
                    ->ignore($item->id),
            ],
            'name' => [
                'required',
                'string',
                'max:200',
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('operational_categories', 'id'),
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $item->update([
            'code' => strtoupper(trim($data['code'])),
            'name' => trim($data['name']),
            'category_id' => $data['category_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->success('Item operasional berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE SUKSES
    |--------------------------------------------------------------------------
    */

    private function success(string $message): RedirectResponse
    {
        return redirect()
            ->route('operational-masters.index')
            ->with('success', $message);
    }
}
