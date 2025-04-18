<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\OperationRubrique;
use App\Models\Rubrique;
use App\Models\RubriqueApi;
use App\Services\BanqueCentraleService;
use App\Services\BCTokenService;
use App\Services\TransmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TransmissionController extends Controller
{
    protected $transmissionService;
    protected $bcTokenService;

    public function __construct(TransmissionService $transmissionService, BCTokenService $bcTokenService)
    {
        $this->transmissionService = $transmissionService;
        $this->bcTokenService = $bcTokenService;
    }

    public function index()
    {
        $operations = Operation::with(['rubrique', 'declarant', 'valideur'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Transmission/Index', [
            'transmissions' => $operations
        ]);
    }

    public function create()
    {
        $rubriques = Rubrique::where('actif', true)->whereDoesntHave('parent')->with('apis', 'children')->get();

        return Inertia::render('Transmission/Create', [
            'rubriques' => $rubriques,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rubrique_id' => 'required|exists:rubriques,id',
            'date_arretee' => 'required|date',
            'statut' => 'required',
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:100240',
        ]);

        $file = $request->file('fichier');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('transmissions', $fileName, 'public');

        $operation = Operation::create([
            'rubrique_id' => $request->rubrique_id,
            'date_arretee' => Carbon::make($validated['date_arretee']),
            'declarant_id' => Auth::id(),
            'statut' => $validated['statut']['value'],
            'donnees' => json_encode($request->rubriques_selectionnees),
            'etat' => 'en_attente',
            'fichier' => $path
        ]);

        return redirect()->route('transmission.show', $operation)
            ->with('success', 'Opération créée avec succès.');
    }

    public function show($transmissionId)
    {
        $operation = Operation::with(['rubrique.children', 'declarant', 'valideur'])->find($transmissionId);

        return Inertia::render('Transmission/Show', [
            'transmission' => $operation
        ]);
    }

    public function executeOperation(Request $request, $transmissionId)
    {
        $request->validate([
            'api_id' => 'required|exists:rubrique_apis,id'
        ]);

        $operation = Operation::find($transmissionId);

        $api = $operation->rubrique->apis()->findOrFail($request->api_id);

        $execution = $operation->operations()->create([
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

    public function validate($transmissionId)
    {
        $operation = Operation::find($transmissionId);

        $operation->update([
            'etat' => 'validee',
            'valideur_id' => auth()->id()
        ]);

        $rubrique = Rubrique::find($operation->rubrique_id);

        OperationRubrique::create([
            'execute' => false,
            'status' => true,
            'operation_id' => $operation->id,
            'rubrique_id' => $rubrique->id
        ]);

        foreach ($rubrique->children as $rubrique) {
            OperationRubrique::create([
                'execute' => false,
                'status' => true,
                'operation_id' => $operation->id,
                'rubrique_id' => $rubrique->id
            ]);
        }

        return back()->with('success', 'Opération validée avec succès.');
    }

    public function reject($transmissionId)
    {
        $operation = Operation::find($transmissionId);

        $operation->update([
            'etat' => 'rejetee',
            'valideur_id' => auth()->id()
        ]);

        return back()->with('success', 'Opération rejetée avec succès.');
    }

    public function transmit($transmissionId)
    {

        $operation = Operation::find($transmissionId);

        try {
            DB::beginTransaction();

            // Lire le fichier de transmission
            $items = $this->transmissionService->readFile($operation->fichier);

            // Transmettre les données à l'API
            $result = $this->transmissionService->transmit($operation, $items);


            if ($result['success']) {
                $operation->update([
                    'etat' => 'transmise',
                    'valideur_id' => Auth::id(),
                    'date_transmission' => now(),
                    'reponse_api' => $result['message'],
                ]);

                $rubrique = Rubrique::find($operation->rubrique_id);

                OperationRubrique::where('operation_id', $operation->id)
                    ->where('rubrique_id', $rubrique->id)
                    ->update([
                        'execute' => true
                    ]);

                DB::commit();

                return redirect()->route('transmission.show', $transmissionId)
                    ->with('success', 'Transmission effectuée avec succès.');
            }

            DB::rollBack();
            return back()->with('error', $result['message']);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la transmission: ' . $e->getMessage());
        }
    }

    public function calculate($transmissionId, $rubrique_id)
    {

        DB::beginTransaction();

        try {
            $operation = Operation::find($transmissionId);

            $rubrique = Rubrique::find($rubrique_id);

            $bcs = new BanqueCentraleService();

            $results = collect();

            //dd("MAU",$rubrique,$rubrique->apis()->where('groupe', 'calcul')->get());

            foreach ($rubrique->apis()->where('groupe', 'calcul')->get() as $api) {
                $results->push($bcs->calculSitu($api->endpoint));
            }

        dd($results);

            OperationRubrique::where('operation_id', $operation->id)
                ->where('rubrique_id', $rubrique->id)
                ->where('groupe', 'calcul')
                ->update([
                    'execute' => true
                ]);

            if (OperationRubrique::where('operation_id', $operation->id)->whereRelation('rubrique', function ($query) use ($rubrique) {
                $query->where('groupe', 'calcul');
            })->where('execute', false)->first()) {
                DB::commit();
                return back()->with('success', 'Opération calculée avec succès.');
            } else {
                DB::commit();
                return back()->with('success', 'Opération calculée avec succès.');
            }
        } catch (\Exception $e) {

            dd($e->getMessage());

            DB::rollBack();
            return back()->with('error', 'Erreur lors du calcul de l\'opération: ' . $e->getMessage());
        }
    }
}
