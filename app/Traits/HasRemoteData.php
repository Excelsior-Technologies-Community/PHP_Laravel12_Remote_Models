<?php

namespace App\Traits;

use App\Models\SyncHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;



trait HasRemoteData
{


    public static function syncRemote()
    {


        $response = Http::withHeaders([

            'X-API-KEY' => config('remote-models.api-key')

        ])
            ->get(
                config('remote-models.domain')
                . config('remote-models.api-path')
            );



        if ($response->failed()) {
            throw new \Exception(
                'Remote API Error'
            );
        }



        $records = $response->json('data');



        if (!Schema::hasTable('celebrities_cache')) {

            Schema::create(
                'celebrities_cache',
                function (Blueprint $table) {

                    $table->id();

                    $table->unsignedBigInteger('remote_id');

                    $table->string('name');

                    $table->date('birthday')
                        ->nullable();

                    $table->string('profession')
                        ->nullable();

                    $table->timestamps();

                }
            );

        }



        DB::table('celebrities_cache')
            ->truncate();



        foreach ($records as $record) {
            DB::table('celebrities_cache')
                ->insert([

                    'remote_id' => $record['id'],

                    'name' => $record['name'],

                    'birthday' => $record['birthday'] ?? null,

                    'profession' => $record['profession'] ?? null,

                    'created_at' => now(),

                    'updated_at' => now()

                ]);
        }

        SyncHistory::create([

            'records_count' => count($records),

            'synced_at' => now()

        ]);

        return true;



        return true;


    }




    public static function remote()
    {

        self::syncRemote();


        return self::query();

    }


}