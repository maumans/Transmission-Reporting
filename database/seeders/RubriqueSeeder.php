<?php

namespace Database\Seeders;

use App\Models\Rubrique;
use App\Models\RubriqueApi;
use Illuminate\Database\Seeder;

class RubriqueSeeder extends Seeder
{
    public function run(): void
    {
        // Création de la rubrique BALANCE GENERALE (parent)
        $balance = Rubrique::create([
            'nom' => 'BALANCE GENERALE',
            'code' => 'BALANCE',
            'description' => 'Fichier BALANCE GENERALE',
            'fichier_modele' => 'BALANCE',
            'actif' => true
        ]);

        // Création des rubriques enfants de BALANCE
        $situ = Rubrique::create([
            'nom' => 'SITU',
            'code' => 'SITU',
            'description' => 'Fichier SITU',
            'fichier_modele' => 'SITU',
            'actif' => true,
            'parent_id' => $balance->id
        ]);

        $fins = Rubrique::create([
            'nom' => 'FINS',
            'code' => 'FINS',
            'description' => 'Fichier FINS',
            'fichier_modele' => 'FINS',
            'actif' => true,
            'parent_id' => $balance->id
        ]);

        // Création de la rubrique PRUD
        $prud = Rubrique::create([
            'nom' => 'PRUD',
            'code' => 'PRUD',
            'description' => 'Fichier PRUD',
            'fichier_modele' => 'PRUD',
            'actif' => true
        ]);

        // Création de la rubrique IGEC
        $igec = Rubrique::create([
            'nom' => 'IGEC',
            'code' => 'IGEC',
            'description' => 'Fichier IGEC',
            'fichier_modele' => 'IGEC',
            'actif' => true
        ]);

        // Création de la rubrique CEFP
        $cefp = Rubrique::create([
            'nom' => 'CEFP',
            'code' => 'CEFP',
            'description' => 'Fichier CEFP',
            'fichier_modele' => 'CEFP',
            'actif' => true
        ]);

        // Ajout des APIs pour BALANCE
        $this->createBalanceApis($balance);
        
        // Ajout des APIs pour SITU
        $this->createSituApis($situ);
        
        // Ajout des APIs pour FINS
        $this->createFinsApis($fins);
        
        // Ajout des APIs pour PRUD
        $this->createPrudApis($prud);
        
        // Ajout des APIs pour IGEC
        $this->createIgecApis($igec);
        
        // Ajout des APIs pour CEFP
        $this->createCefpApis($cefp);
    }

    private function createBalanceApis(Rubrique $rubrique): void
    {
        $apis = [
            [
                'endpoint' => '/balance/rapports',
                'methode' => 'POST',
                'description' => 'Impression des feuilles transmises pour le fichier BALANCE',
                'groupe' => 'Impression',
                'actif' => true
            ],
            [
                'endpoint' => '/api/balance',
                'methode' => 'POST',
                'description' => 'Importation de la balance',
                'groupe' => 'Importation',
                'actif' => true
            ]
        ];

        foreach ($apis as $api) {
            RubriqueApi::create(array_merge($api, ['rubrique_id' => $rubrique->id]));
        }
    }

    private function createSituApis(Rubrique $rubrique): void
    {
        $apis = [
            [
                'endpoint' => '/etats/situ/rapport',
                'methode' => 'POST',
                'description' => 'Impression des feuilles transmises pour le fichier SITU',
                'groupe' => 'Impression',
                'actif' => true
            ]
        ];

        // Ajout des APIs d'importation SITU
        for ($i = 1; $i <= 3; $i++) {
            $apis[] = [
                'endpoint' => "/api/situ/situ0{$i}",
                'methode' => 'POST',
                'description' => "Importation de la feuille SITU_0{$i}",
                'groupe' => 'Importation',
                'actif' => true
            ];
        }

        // Ajout des APIs de calcul SITU
        for ($i = 1; $i <= 3; $i++) {
            $apis[] = [
                'endpoint' => "/api/calcul/situ/situ0{$i}",
                'methode' => 'POST',
                'description' => "Calcul de la feuille SITU_0{$i}",
                'groupe' => 'Calcul',
                'actif' => true
            ];
        }

        foreach ($apis as $api) {
            RubriqueApi::create(array_merge($api, ['rubrique_id' => $rubrique->id]));
        }
    }

    private function createFinsApis(Rubrique $rubrique): void
    {
        $apis = [
            [
                'endpoint' => '/etats/fins/rapport',
                'methode' => 'POST',
                'description' => 'Impression des feuilles transmises pour le fichier FINS',
                'groupe' => 'Impression',
                'actif' => true
            ]
        ];

        // Ajout des APIs d'importation FINS
        for ($i = 1; $i <= 17; $i++) {
            $suffix = '';
            if ($i == 1 || $i == 2 || $i == 7 || $i == 9) {
                $apis[] = [
                    'endpoint' => "/api/fins/fins{$i}GNF",
                    'methode' => 'POST',
                    'description' => "Importation de la feuille FINS_{$i}_GNF",
                    'groupe' => 'Importation',
                    'actif' => true
                ];
                $apis[] = [
                    'endpoint' => "/api/fins/fins{$i}DEV",
                    'methode' => 'POST',
                    'description' => "Importation de la feuille FINS_{$i}_DEV",
                    'groupe' => 'Importation',
                    'actif' => true
                ];
            } else {
                $apis[] = [
                    'endpoint' => "/api/fins/fins{$i}",
                    'methode' => 'POST',
                    'description' => "Importation de la feuille FINS_{$i}",
                    'groupe' => 'Importation',
                    'actif' => true
                ];
            }
        }

        // Ajout des APIs de calcul FINS
        for ($i = 1; $i <= 17; $i++) {
            $suffix = '';
            if ($i == 1 || $i == 2 || $i == 7 || $i == 9) {
                $apis[] = [
                    'endpoint' => "/api/calcul/fins/fins{$i}GNF",
                    'methode' => 'POST',
                    'description' => "Calcul de la feuille FINS_{$i}_GNF",
                    'groupe' => 'Calcul',
                    'actif' => true
                ];
                $apis[] = [
                    'endpoint' => "/api/calcul/fins/fins{$i}DEV",
                    'methode' => 'POST',
                    'description' => "Calcul de la feuille FINS_{$i}_DEV",
                    'groupe' => 'Calcul',
                    'actif' => true
                ];
            } else {
                $apis[] = [
                    'endpoint' => "/api/calcul/fins/fins{$i}",
                    'methode' => 'POST',
                    'description' => "Calcul de la feuille FINS_{$i}",
                    'groupe' => 'Calcul',
                    'actif' => true
                ];
            }
        }

        foreach ($apis as $api) {
            RubriqueApi::create(array_merge($api, ['rubrique_id' => $rubrique->id]));
        }
    }

    private function createPrudApis(Rubrique $rubrique): void
    {
        $apis = [
            [
                'endpoint' => '/etats/prud/rapport',
                'methode' => 'POST',
                'description' => 'Impression des feuilles transmises pour le fichier PRUD',
                'groupe' => 'Impression',
                'actif' => true
            ],
            [
                'endpoint' => '/api/balance',
                'methode' => 'POST',
                'description' => 'Importation de la balance',
                'groupe' => 'Importation',
                'actif' => true
            ]
        ];

        // Ajout des APIs PRUD_01 à PRUD_08
        for ($i = 1; $i <= 8; $i++) {
            $apis[] = [
                'endpoint' => "/api/prud/prud_0{$i}",
                'methode' => 'POST',
                'description' => "Importation de la feuille PRUD_0{$i}",
                'groupe' => 'Importation',
                'actif' => true
            ];
        }

        foreach ($apis as $api) {
            RubriqueApi::create(array_merge($api, ['rubrique_id' => $rubrique->id]));
        }
    }

    private function createIgecApis(Rubrique $rubrique): void
    {
        $apis = [
            [
                'endpoint' => '/etats/igec/rapport',
                'methode' => 'POST',
                'description' => 'Impression des feuilles transmises pour le fichier IGEC',
                'groupe' => 'Impression',
                'actif' => true
            ]
        ];

        // Ajout des APIs IGEC_01 à IGEC_10
        for ($i = 1; $i <= 10; $i++) {
            $apis[] = [
                'endpoint' => "/api/igec/igec0{$i}",
                'methode' => 'POST',
                'description' => "Importation de la feuille IGEC_0{$i}",
                'groupe' => 'Importation',
                'actif' => true
            ];
        }

        foreach ($apis as $api) {
            RubriqueApi::create(array_merge($api, ['rubrique_id' => $rubrique->id]));
        }
    }

    private function createCefpApis(Rubrique $rubrique): void
    {
        $apis = [
            [
                'endpoint' => '/etats/cefp/rapport',
                'methode' => 'POST',
                'description' => 'Impression des feuilles transmises pour le fichier CEFP',
                'groupe' => 'Impression',
                'actif' => true
            ]
        ];

        // Ajout des APIs CEFP_01 à CEFP_17
        for ($i = 1; $i <= 17; $i++) {
            $apis[] = [
                'endpoint' => "/api/cefp/cefp_{$i}",
                'methode' => 'POST',
                'description' => "Importation de la feuille CEFP_{$i}",
                'groupe' => 'Importation',
                'actif' => true
            ];
        }

        // Ajout de l'API ADPE
        $apis[] = [
            'endpoint' => '/api/cefp/adpe',
            'methode' => 'POST',
            'description' => 'Importation de la feuille ADPE',
            'groupe' => 'Importation',
            'actif' => true
        ];

        foreach ($apis as $api) {
            RubriqueApi::create(array_merge($api, ['rubrique_id' => $rubrique->id]));
        }
    }
} 