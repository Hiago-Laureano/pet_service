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
    $this->data = ["first_name" => "User", "last_name" => "Test", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345678"];
    $this->structureGet = [
        "id",
        "first_name",
        "last_name",
        "phone",
        "email",
        "email_verified_at",
        "is_staff",
        "is_superuser",
        "pets" => [
            "*" => [
                "id",
                "user_id",
                "name",
                "species",
                "breed",
                "weight",
                "age",
                "gender",
                "agressive",
                "created_at",
                "updated_at"
            ]
        ],
        "created_at",
        "updated_at"
    ];
});

// TEST GET ALL /api/v1/users ====================================================================================
describe("GET ALL /api/v1/users", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/users", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/users", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/users", headers: $this->headers)
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

// TEST GET ONE /api/v1/users{id} ====================================================================================
describe("GET ONE /api/v1/users", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/users/{$this->normalUser->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/users/{$this->superUser->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/users/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });    

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/users/{$this->normalUser->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/users/{$this->normalUser->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST POST /api/v1/users ====================================================================================
describe("POST /api/v1/users", function(){
    test('Request successful', function() {
        postJson("/api/v1/users", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            postJson("/api/v1/users", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["first_name", "last_name", "phone", "email", "password"]);
    });
});

// TEST PUT /api/v1/users{id} ====================================================================================
describe("PUT /api/v1/users", function(){
    test('Request failed not authenticad', function() {
        putJson("/api/v1/users/{$this->normalUser->id}", $this->data, $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/users/{$this->superUser->id}", $this->data, $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/users/{$this->normalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/users/{$this->normalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/users/100", $this->data, $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });
    describe("validation", function(){
        test('Request failed field required', function($field) {
            $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
            unset($this->data[$field]);
            putJson("/api/v1/users/{$this->normalUser->id}", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["first_name", "last_name", "phone", "email", "password"]);
    });
});

// TEST PATCH /api/v1/users{id} ====================================================================================
describe("PATCH /api/v1/users", function(){
    test('Request failed not authenticad', function() {
        patchJson("/api/v1/users/{$this->normalUser->id}", ["email" => fake()->unique->email()], $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/users/{$this->superUser->id}", ["email" => fake()->unique->email()], $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/users/100", $this->data, $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/users/{$this->normalUser->id}", ["email" => fake()->unique->email()], $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/users/{$this->normalUser->id}", ["email" => fake()->unique->email()], $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST DELETE /api/v1/users{id} ====================================================================================
describe("DELETE /api/v1/users", function(){
    test('Request failed not authenticad', function() {
        deleteJson("/api/v1/users/{$this->normalUser->id}", headers:  $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/users/{$this->superUser->id}", headers:  $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/users/100", headers:  $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/users/{$this->normalUser->id}", headers:  $this->headers)
        ->assertStatus(204);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/users/{$this->normalUser->id}", headers:  $this->headers)
        ->assertStatus(204);
    });
});