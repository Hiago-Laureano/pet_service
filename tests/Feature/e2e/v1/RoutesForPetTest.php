<?php

use App\Models\Pet;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function(){
    $this->normalUser = User::create(["first_name" => "Normal", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => false]);
    $this->superUser = User::create(["first_name" => "Super", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => true]);
    $this->petNormalUser = Pet::create(["user_id" => $this->normalUser->id, "name" => "petNormal", "species" => "x", "breed" => "y", "weight" => 20.0, "gender" => "M", "agressive" => true]);
    $this->petSuperUser = Pet::create(["user_id" => $this->superUser->id, "name" => "petSuper", "species" => "x", "breed" => "y", "weight" => 20.0, "gender" => "M", "agressive" => true]);
    $this->tokenNormalUser = $this->normalUser->createToken("auth", ["*"])->plainTextToken;
    $this->tokenSuperUser = $this->superUser->createToken("auth", ["*"])->plainTextToken;
    $this->headers = ["Content-Type" => "application/json", "X-Requested-With" => "XMLHttpRequest"];
    $this->data = ["name" => "pet1", "species" => "xxx", "breed" => "yyy", "weight" => 25.0, "gender" => "F", "agressive" => false];
    $this->structureGet = [
        "id",
        "user_id",
        "species",
        "breed",
        "weight",
        "age",
        "gender",
        "agressive",
        "created_at",
        "updated_at"
    ];
});

// TEST GET ALL /api/v1/pets ====================================================================================
describe("GET ALL /api/v1/pets", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/pets", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/pets", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/pets", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure([
            "data" => [
                "*" => $this->structureGet
            ],
            "links" => [
                "first",
                "last",
                "prev",
                "next"
            ]
        ]);
    });
});

// TEST GET ONE /api/v1/pets{id} ====================================================================================
describe("GET ONE /api/v1/pets", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/pets/{$this->petNormalUser->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/pets/{$this->petSuperUser->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/pets/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });    

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/pets/{$this->petNormalUser->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/pets/{$this->petNormalUser->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST POST /api/v1/pets ====================================================================================
describe("POST /api/v1/pets", function(){
    test('Request failed not authenticad', function() {
        postJson("/api/v1/pets", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with normal user and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        postJson("/api/v1/pets", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with normal user and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        $this->data["user_id"] = $this->normalUser->id;
        postJson("/api/v1/pets", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        postJson("/api/v1/pets", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->superUser->id]]);
    });

    test('Request successful with superuser and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        $this->data["user_id"] = $this->normalUser->id;
        postJson("/api/v1/pets", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
            postJson("/api/v1/pets", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["name", "species", "breed", "weight", "gender", "agressive"]);
    });
});

// TEST PUT /api/v1/pets{id} ====================================================================================
describe("PUT /api/v1/pets", function(){
    test('Request failed not authenticad', function() {
        putJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/pets/{$this->petSuperUser->id}", $this->data, $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with owner user and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with owner user and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        $this->data["user_id"] = $this->normalUser->id;
        putJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->superUser->id]]);
    });

    test('Request successful with superuser and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        $this->data["user_id"] = $this->normalUser->id;
        putJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/pets/100", $this->data, $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });
    describe("validation", function(){
        test('Request failed field required', function($field) {
            $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
            unset($this->data[$field]);
            putJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["name", "species", "breed", "weight", "gender", "agressive"]);
    });
});

// TEST PATCH /api/v1/pets{id} ====================================================================================
describe("PATCH /api/v1/pets", function(){
    test('Request failed not authenticad', function() {
        patchJson("/api/v1/pets/{$this->petNormalUser->id}", ["name" => "newname"], $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/pets/{$this->petSuperUser->id}", ["name" => "newname"], $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/pets/100", ["name" => "newname"], $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with owner user and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with owner user and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        $this->data["user_id"] = $this->normalUser->id;
        patchJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        $this->data["user_id"] = $this->normalUser->id;
        patchJson("/api/v1/pets/{$this->petNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });
});

// TEST DELETE /api/v1/pets{id} ====================================================================================
describe("DELETE /api/v1/pets", function(){
    test('Request failed not authenticad', function() {
        deleteJson("/api/v1/pets/{$this->petNormalUser->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/pets/{$this->petSuperUser->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/pets/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/pets/{$this->petNormalUser->id}", headers: $this->headers)
        ->assertStatus(204);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/pets/{$this->petNormalUser->id}", headers: $this->headers)
        ->assertStatus(204);
    });
});