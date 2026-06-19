# PHP_Laravel12_Remote_Models

## Introduction

PHP_Laravel12_Remote_Models is a Laravel 12 based project that demonstrates how remote data can be synchronized and accessed using custom Eloquent models and API communication.

The project simulates a Remote Models architecture where one Laravel server acts as a Remote/Host Server and another acts as a Local Consumer Server. The remote server exposes celebrity records through a secured API endpoint, while the local server fetches the data using Laravel HTTP Client, stores it in a local cache table, and allows developers to work with the synchronized records using standard Eloquent queries.

This implementation showcases API communication, API key authentication, data synchronization, local caching, custom traits, and Eloquent model integration, providing a practical example of consuming remote data within Laravel applications.

---

## Features

- Laravel 12 Framework
- Remote Model Implementation
- API Based Data Synchronization
- API Key Authentication
- Custom Trait Implementation
- Eloquent Model Usage
- Local Cache Storage
- Database Synchronization
- Remote Data Fetching

---

## Technologies Used

- PHP 8+
- Laravel 12
- SQLite
- Laravel HTTP Client
- Eloquent ORM
- REST API

---

# Installation

## Step 1: Create Laravel 12 Project:

```bash
composer create-project laravel/laravel PHP_Laravel12_Remote_Models "12.*"

cd PHP_Laravel12_Remote_Models
```

---

## Step 2: Environment Configuration

Update:

```.env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

REMOTE_DOMAIN=http://127.0.0.1:8001
REMOTE_MODELS_API_KEY=your-api-key-here
```

### Generate Api Key

Run:

```bash
php artisan tinker
```

Then:

```bash
Str::random(32);
```

Example output:

```bash
a8K9sL2mPq7XvY3nB5rT0wZ6cD1eF4gH
```
Copy it.

Then .env:

```bash
REMOTE_MODELS_API_KEY=a8K9sL2mPq7XvY3nB5rT0wZ6cD1eF4gH
```

Run migration:

```bash
php artisan migrate
```

---

## Step 3: Migrations Table 

### celebrities table

Create migration:

```bash
php artisan make:migration create_celebrities_table
```

File: database/migrations/xxxx_xx_xx_create_celebrities_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('celebrities', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->date('birthday')->nullable();

            $table->string('profession')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('celebrities');
    }
};
```

### celebrities_cache table

Create migration:

```bash
php artisan make:migration create_celebrities_cache_table
```

File: database/migrations/xxxx_xx_xx_create_celebrities_cache_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{


    public function up(): void
    {


        Schema::create('celebrities_cache', function (Blueprint $table) {


            $table->id();


            $table->unsignedBigInteger('remote_id')
            ->nullable();


            $table->string('name');


            $table->date('birthday')
            ->nullable();


            $table->string('profession')
            ->nullable();


            $table->timestamps();


        });


    }




    public function down(): void
    {

        Schema::dropIfExists('celebrities_cache');

    }


};
```

Run migration:

```bash
php artisan migrate
```

---

## Step 4: Create Models

Create models:

```bash
php artisan make:model Celebrity

php artisan make:model HostCelebrity
```

### HostCelebrity Model

File: app/Models/HostCelebrity.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class HostCelebrity extends Model
{

    protected $table = 'celebrities';


    protected $fillable = [

        'name',
        'birthday',
        'profession'

    ];

}
```

### Celebrity Remote Model

File: app/Models/Celebrity.php

```php
<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRemoteData;



class Celebrity extends Model
{


    use HasRemoteData;



    protected $table =
    "celebrities_cache";



    protected $fillable = [

        'remote_id',
        'name',
        'birthday',
        'profession'

    ];
}
```

---

## Step 5: Create Remote Trait

Create:

```bash
php artisan make:trait HasRemoteData
```

File: app/Traits/HasRemoteData.php

```php
<?php

namespace App\Traits;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;



trait HasRemoteData
{


    public static function syncRemote()
    {


        $response = Http::withHeaders([

            'X-API-KEY'=>config('remote-models.api-key')

        ])
        ->get(
            config('remote-models.domain')
            .config('remote-models.api-path')
        );



        if($response->failed())
        {
            throw new \Exception(
                'Remote API Error'
            );
        }



        $records = $response->json('data');



        if(!Schema::hasTable('celebrities_cache'))
        {

            Schema::create(
                'celebrities_cache',
                function(Blueprint $table)
                {

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



        foreach($records as $record)
        {


            DB::table('celebrities_cache')
            ->insert([

                'remote_id'=>$record['id'],

                'name'=>$record['name'],

                'birthday'=>$record['birthday'] ?? null,

                'profession'=>$record['profession'] ?? null,

                'created_at'=>now(),

                'updated_at'=>now()

            ]);

        }



        return true;


    }




    public static function remote()
    {

        self::syncRemote();


        return self::query();

    }


}
```

---

## Step 6: Remote Models Config

File: config/remote-models.php


```php
<?php


return [


    'domain' =>
    env(
        'REMOTE_DOMAIN',
        'http://127.0.0.1:8001'
    ),



    'api-path' =>
    '/api/remote/models',



    'api-key' =>
    env(
        'REMOTE_MODELS_API_KEY',
        'your-api-key-here'
    ),



];
```

---

## Step 7: Controller

Create:

```bash
php artisan make:controller CelebrityController
```

File: app/Http/Controllers/CelebrityController.php


```php
<?php

namespace App\Http\Controllers;


use App\Models\Celebrity;
use App\Models\HostCelebrity;
use Illuminate\Http\Request;



class CelebrityController extends Controller
{


    /*
    Host API
    */

    public function remoteSync(Request $request)
    {


        if(
            $request->header('X-API-KEY')
            != config('remote-models.api-key')
        )
        {

            return response()->json([
                'message'=>'Unauthorized'
            ],401);

        }



        return response()->json([

            'data'=>HostCelebrity::all()

        ]);


    }


    /*
    Local Remote Model Test
    */


    public function index()
    {


        $data =
        Celebrity::remote()
        ->get();



        return response()->json($data);


    }



}
```

---

## Step 8: Celebrity Seeder

Create:

```bash
php artisan make:seeder CelebritySeeder
```
File: database/seeders/CelebritySeeder.php

Insert data:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HostCelebrity;

class CelebritySeeder extends Seeder
{
    public function run(): void
    {
        HostCelebrity::insert([

            [
                'name' => 'Dwayne Johnson',
                'birthday' => '1972-05-02',
                'profession' => 'Actor',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Robert Downey Jr',
                'birthday' => '1965-04-04',
                'profession' => 'Actor',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'name' => 'Cristiano Ronaldo',
                'birthday' => '1985-02-05',
                'profession' => 'Football Player',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
```

### Update DatabaseSeeder.php

File: File: database/seeders/DatabaseSeeder.php

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;


    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([

            CelebritySeeder::class

        ]);

    }
}
```

Run:

```bash
php artisan db:seed
```

---

## Step 9: Routes

File: routes/api.php


```php
<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CelebrityController;



Route::get('/remote/models', [CelebrityController::class, 'remoteSync']);

Route::get('/celebrities', [CelebrityController::class, 'index']);
```

---

### Update bootstrap/app.php

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## Step 10: Running the Application

This project simulates a Remote Models architecture where:

- Port **8001** acts as the Remote/Host API server.
- Port **8000** acts as the Local Consumer server.

Both servers use the same codebase for demonstration purposes.

### Start Remote Server

Open Terminal 1 and run:

```bash
php artisan serve --port=8001
```

Remote API Endpoint:

```text
http://127.0.0.1:8001/api/remote/models
```

This endpoint exposes data from the `celebrities` table.

---

### Start Local Server

Open Terminal 2 and run:

```bash
php artisan serve --port=8000
```

Local API Endpoint:

```text
http://127.0.0.1:8000/api/celebrities
```

---

## Postman Testing

### 1. Remote Host API

This endpoint exposes celebrity data from the `celebrities` table and acts as the remote data provider.

**Method:** GET

**URL:**

```bash
http://127.0.0.1:8001/api/remote/models
```

**Headers:**

```bash
X-API-KEY : your-generated-api-key
```

**Response Example:**

```json
{
    "data": [
        {
            "id": 1,
            "name": "Dwayne Johnson",
            "birthday": "1972-05-02",
            "profession": "Actor",
            "created_at": "2026-06-18T10:00:00.000000Z",
            "updated_at": "2026-06-18T10:00:00.000000Z"
        }
    ]
}
```

---

### 2. Local Remote Model API

This endpoint fetches data from the remote server, synchronizes it into the `celebrities_cache` table, and returns the cached records.

**Method:** GET

**URL:**

```bash
http://127.0.0.1:8000/api/celebrities
```

**Response Example:**

```json
[
    {
        "id": 1,
        "remote_id": 1,
        "name": "Dwayne Johnson",
        "birthday": "1972-05-02",
        "profession": "Actor",
        "created_at": "2026-06-18T10:05:00.000000Z",
        "updated_at": "2026-06-18T10:05:00.000000Z"
    }
]
```

---

## Tinker Testing

Start Tinker:

```bash
php artisan tinker
```

### Check Source Data (celebrities table)

```php
App\Models\HostCelebrity::all();
```

Output:

```text
Dwayne Johnson
Robert Downey Jr
Cristiano Ronaldo
```

---

### Check Cached Data (celebrities_cache table)

First call:

```bash
http://127.0.0.1:8000/api/celebrities
```

Then run:

```php
App\Models\Celebrity::all();
```

Output:

```text
Dwayne Johnson
Robert Downey Jr
Cristiano Ronaldo
```

---

## SQLite Database

This project uses SQLite as the database engine.

### Tables

```text
celebrities
celebrities_cache
```

### Purpose

| Table             | Description                                                              |
| ----------------- | ------------------------------------------------------------------------ |
| celebrities       | Stores the original celebrity records and acts as the remote data source |
| celebrities_cache | Stores synchronized records fetched from the remote API                  |

The `celebrities_cache` table is automatically refreshed whenever the `/api/celebrities` endpoint is accessed.

---

## Screenshots

### Remote API Response With Key

<img width="1363" height="996" alt="Screenshot 2026-06-19 102004" src="https://github.com/user-attachments/assets/458f31bf-45cc-4ce2-a6b0-02fc4ea88f86" />

### Remote API Response Without Key

<img width="1378" height="996" alt="Screenshot 2026-06-19 101916" src="https://github.com/user-attachments/assets/362fece6-0eff-493a-bc34-f52ea08d27e0" />

### Local Synchronization Response

<img width="1372" height="1000" alt="Screenshot 2026-06-19 102043" src="https://github.com/user-attachments/assets/ff50f698-1ab9-4cba-8be4-bf22fc3f1def" />

---

## Project Structure

```text
PHP_Laravel12_Remote_Models
├── app
│   ├── Http
│   │   └── Controllers
│   │       └── CelebrityController.php
│   ├── Models
│   │   ├── Celebrity.php
│   │   └── HostCelebrity.php
│   └── Traits
│       └── HasRemoteData.php
├── bootstrap
├── config
│   └── remote-models.php
├── database
│   ├── factories
│   ├── migrations
│   │   ├── xxxx_xx_xx_create_celebrities_table.php
│   │   └── xxxx_xx_xx_create_celebrities_cache_table.php
│   └── seeders
│       ├── CelebritySeeder.php
│       └── DatabaseSeeder.php
├── public
├── resources
├── routes
│   └── api.php
├── storage
├── tests
├── .env
├── .env.example
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
└── README.md
```

---

## Conclusion

PHP_Laravel12_Remote_Models demonstrates how Laravel applications can securely fetch, synchronize, cache, and manage remote data through API communication while maintaining a familiar Eloquent-based development workflow.                                                                                                       

