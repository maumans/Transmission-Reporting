<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Services\BalanceService;
use App\Services\BCTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BalanceController extends Controller
{
    protected $balanceService;
    protected $bcTokenService;

    public function __construct(BalanceService $balanceService, BCTokenService $bcTokenService)
    {
        $this->balanceService = $balanceService;
        $this->bcTokenService = $bcTokenService;
    }

    public function index()
    {
        $balances = Balance::with(['declarant', 'valideur'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Balance/Index', [
            'balances' => $balances
        ]);
    }

    public function create()
    {
        return Inertia::render('Balance/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_arretee' => 'required|date',
            "statut" => 'required',
            'fichier_balance' => 'required|file|mimes:xlsx,xls,csv|max:100240',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('fichier_balance');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('balances', $fileName, 'public');

            $balance = Balance::create([
                'date_arretee' => Carbon::make($request->date_arretee),
                'statut' => $request->statut['value'],
                'etat' => 'en_attente',
                'declarant_id' => auth()->id(),
                'fichier_balance' => $path,
            ]);

            DB::commit();

            return redirect()->route('balance.show', $balance->id)
                ->with('success', 'La balance a été créée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->with('error', 'Une erreur est survenue lors de la création de la balance.');
        }
    }

    public function show(Balance $balance)
    {
        $balance->load([
            'declarant',
            'valideur',
        ]);

        return Inertia::render('Balance/Show', [
            'balance' => $balance,
        ]);
    }

    public function executeOperation(Request $request, Balance $balance)
    {
        $request->validate([
            'api_id' => 'required|exists:rubrique_apis,id'
        ]);

        $api = $balance->rubrique->apis()->findOrFail($request->api_id);

        $execution = $balance->executions()->create([
            'rubrique_api_id' => $api->id,
            'statut' => 'en_cours'
        ]);

        try {
            // Récupérer les données du fichier
            $data = Storage::get($balance->fichier_balance);
            
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

    public function validate(Balance $balance)
    {

        $balance->update([
            'etat' => 'validee',
            'valideur_id' => auth()->id()
        ]);

        return back()->with('success', 'Balance validée avec succès.');
    }

    public function reject(Balance $balance)
    {

        $balance->update([
            'etat' => 'rejetee',
            'valideur_id' => auth()->id()
        ]);

        return back()->with('success', 'Balance rejetée avec succès.');
    }

    public function transmission(Balance $balance)
    {

        try {
            DB::beginTransaction();

            // Lire le fichier de balance
            $items = $this->balanceService->readBalanceFile($balance->fichier_balance);

            // Transmettre les données à l'API
            $result = $this->balanceService->transmitBalance($balance, $items);


            if ($result['success']) {
                $balance->update([
                    'etat' => 'transmise',
                    'valideur_id' => Auth::id(),
                    'date_transmission' => now(),
                    'reponse_api' => $result['message'],
                ]);

                DB::commit();
                
                return back()->with('success', 'Balance transmise avec succès.');
            }
            

            DB::rollBack();
            return back()->with('error', $result['message']);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la transmission: ' . $e->getMessage());
        }
    }
}
