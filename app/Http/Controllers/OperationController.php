<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Rubrique;
use App\Models\OperationExecution;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OperationController extends Controller
{
    public function index()
    {
        $operations = Operation::with(['rubrique', 'declarant', 'valideur'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Operation/Index', [
            'operations' => $operations
        ]);
    }

    public function create()
    {
        $rubriques = Rubrique::where('actif', true)->get();
        return Inertia::render('Operation/Create', [
            'rubriques' => $rubriques,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'date_arretee' => 'required|date',
            "statut"=>'required',
            'fichier_balance' => 'required|file|mimes:xlsx,xls,csv|max:100240',
            'rubriques_selectionnees' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Stockage du fichier
            $file = $request->file('fichier_balance');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('balances', $fileName, 'public');

            // Création de l'opération
            $operation = Operation::create([
                'date_arretee' => Carbon::make($request->date_arretee),
                'statut' => 'en_attente',
                'declarant_id' => auth()->id(),
                'fichier_balance' => $path,
                'donnees' => [
                    'rubriques_selectionnees' => $request->rubriques_selectionnees,
                ],
            ]);

            // Création des exécutions pour chaque rubrique sélectionnée
            foreach ($request->rubriques_selectionnees as $rubriqueId) {
                $rubrique = Rubrique::where('id', $rubriqueId)->first();

                $rubriqueApis = $rubrique->apis()->where('actif', true)->get();

                foreach ($rubriqueApis as $rubriqueApi) {
                    OperationExecution::create([
                        'operation_id' => $operation->id,
                        'rubrique_api_id' => $rubriqueApi->id,
                        'statut' => 'en_attente',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('operation.show', $operation->id)
                ->with('success', 'L\'opération a été créée avec succès.');

        } catch (\Exception $e) {

            dd($e->getMessage());
            DB::rollBack();
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->with('error', 'Une erreur est survenue lors de la création de l\'opération.');
        }
    }

    public function show(Operation $operation)
    {
        $operation->load([
            'rubrique',
            'declarant',
            'valideur',
            'executions.rubriqueApi',
        ]);

        return Inertia::render('Operation/Show', [
            'operation' => $operation,
        ]);
    }

    public function executeOperation(Request $request, Operation $operation)
    {
        $request->validate([
            'api_id' => 'required|exists:rubrique_apis,id'
        ]);

        $api = $operation->rubrique->apis()->findOrFail($request->api_id);

        $execution = $operation->executions()->create([
            'rubrique_api_id' => $api->id,
            'statut' => 'en_cours'
        ]);

        try {
            // Récupérer les données du fichier
            $data = Storage::get($operation->fichier_balance);
            
            // Appeler l'API
            $response = $api->execute($data);
            
            // Mettre à jour l'exécution
            $execution->update([
                'statut' => 'reussie',
                'date_execution' => now()
            ]);

            return back()->with('success', 'Opération exécutée avec succès.');
        } catch (\Exception $e) {
            $execution->update([
                'statut' => 'echouee',
                'date_execution' => now(),
                'erreur' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de l\'exécution de l\'opération.');
        }
    }

    public function validate(Operation $operation)
    {
        if (auth()->user()->cannot('validate', $operation)) {
            abort(403);
        }

        $operation->update([
            'statut' => 'validee',
            'valideur_id' => auth()->id()
        ]);

        return back()->with('success', 'Opération validée avec succès.');
    }

    public function reject(Operation $operation)
    {
        if (auth()->user()->cannot('validate', $operation)) {
            abort(403);
        }

        $operation->update([
            'statut' => 'rejetee',
            'valideur_id' => auth()->id()
        ]);

        return back()->with('success', 'Opération rejetée avec succès.');
    }
} 