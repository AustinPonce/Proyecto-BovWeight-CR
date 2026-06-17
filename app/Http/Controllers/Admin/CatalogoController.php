<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use App\Models\Medicamento;
use App\Models\Raza;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function index(): View
    {
        return view('admin.catalogos.index', [
            'razas'        => Raza::orderBy('raza')->get(),
            'estados'      => Estado::orderBy('estado')->get(),
            'medicamentos' => Medicamento::orderBy('nombre')->get(),
        ]);
    }

    // --- Medicamentos ---

    public function storeMedicamento(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'       => ['required', 'string', 'max:150'],
            'unidad'       => ['required', 'in:ml,mg,g,UI'],
            'dosis_por_kg' => ['required', 'numeric', 'min:0.0001', 'max:999'],
            'descripcion'  => ['nullable', 'string', 'max:500'],
        ]);

        Medicamento::create($request->only(['nombre', 'unidad', 'dosis_por_kg', 'descripcion']));

        return redirect()->route('admin.catalogos.index', ['tab' => 'medicamentos'])
            ->with('exito', 'Medicamento agregado.');
    }

    public function updateMedicamento(Request $request, Medicamento $medicamento): RedirectResponse
    {
        $request->validate([
            'nombre'       => ['required', 'string', 'max:150'],
            'unidad'       => ['required', 'in:ml,mg,g,UI'],
            'dosis_por_kg' => ['required', 'numeric', 'min:0.0001', 'max:999'],
            'descripcion'  => ['nullable', 'string', 'max:500'],
        ]);

        $medicamento->update($request->only(['nombre', 'unidad', 'dosis_por_kg', 'descripcion']));

        return redirect()->route('admin.catalogos.index', ['tab' => 'medicamentos'])
            ->with('exito', 'Medicamento actualizado.');
    }

    public function destroyMedicamento(Medicamento $medicamento): RedirectResponse
    {
        $medicamento->delete();
        return redirect()->route('admin.catalogos.index', ['tab' => 'medicamentos'])
            ->with('exito', 'Medicamento eliminado.');
    }

    // --- Razas ---

    public function storeRaza(Request $request): RedirectResponse
    {
        $request->validate(['raza' => ['required', 'string', 'max:100', 'unique:Raza,raza']]);
        Raza::create(['raza' => $request->input('raza')]);
        return redirect()->route('admin.catalogos.index', ['tab' => 'razas'])
            ->with('exito', 'Raza agregada.');
    }

    public function updateRaza(Request $request, Raza $raza): RedirectResponse
    {
        $request->validate(['raza' => ['required', 'string', 'max:100']]);
        $raza->update(['raza' => $request->input('raza')]);
        return redirect()->route('admin.catalogos.index', ['tab' => 'razas'])
            ->with('exito', 'Raza actualizada.');
    }

    public function destroyRaza(Raza $raza): RedirectResponse
    {
        $raza->delete();
        return redirect()->route('admin.catalogos.index', ['tab' => 'razas'])
            ->with('exito', 'Raza eliminada.');
    }

    // --- Estados ---

    public function storeEstado(Request $request): RedirectResponse
    {
        $request->validate(['estado' => ['required', 'string', 'max:100', 'unique:Estado,estado']]);
        Estado::create(['estado' => $request->input('estado')]);
        return redirect()->route('admin.catalogos.index', ['tab' => 'estados'])
            ->with('exito', 'Estado agregado.');
    }

    public function destroyEstado(Estado $estado): RedirectResponse
    {
        $estado->delete();
        return redirect()->route('admin.catalogos.index', ['tab' => 'estados'])
            ->with('exito', 'Estado eliminado.');
    }
}
