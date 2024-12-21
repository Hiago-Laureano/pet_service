<?php

// TEST GET ALL /api/v1/services ====================================================================================

use App\Models\Service;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function(){
    Service::factory(3)->create();
    $this->service = Service::find(1);
    $this->normalUser = User::create(["first_name" => "Normal", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => false]);
    $this->superUser = User::create(["first_name" => "Super", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => true]);
    $this->tokenNormalUser = $this->normalUser->createToken("auth", ["*"])->plainTextToken;
    $this->tokenSuperUser = $this->superUser->createToken("auth", ["*"])->plainTextToken;
    $this->headers = ["Content-Type" => "application/json", "X-Requested-With" => "XMLHttpRequest"];
    $this->data = ["name" => "service-x", "price" => 99.99];
    $this->structureGet = [
        "id",
        "name",
        "price",
        "created_at",
        "updated_at"
    ];
});

// TEST GET ALL /api/v1/services ====================================================================================
describe("GET ALL /api/v1/services", function(){
    test("Request failed unauthenticated", function(){
        getJson("/api/v1/services", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test("Request failed unauthorized not superuser", function(){
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/services", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test("Request success with superuser", function(){
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/services", headers: $this->headers)
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

// TEST GET ONE /api/v1/services/{id} ====================================================================================
describe("GET ONE /api/v1/services", function(){
    test("Request failed unauthenticated", function(){
        getJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test("Request failed unauthorized not superuser", function(){
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test("Request success with superuser", function(){
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST POST /api/v1/services ====================================================================================
describe("POST /api/v1/services", function(){
    test('Request failed not authenticad', function() {
        postJson("/api/v1/services", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        postJson("/api/v1/services", $this->data, $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        postJson("/api/v1/services", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
            postJson("/api/v1/services", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["name", "price"]);
    });
});

// TEST PUT /api/v1/services ====================================================================================
describe("PUT /api/v1/services", function(){
    test('Request failed not authenticad', function() {
        putJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/services/{$this->service->id}", $this->data, $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/services/{$this->service->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
            putJson("/api/v1/services/{$this->service->id}", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["name", "price"]);
    });
});

// TEST PATCH /api/v1/services ====================================================================================
describe("PATCH /api/v1/services", function(){
    test('Request failed not authenticad', function() {
        patchJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/services/{$this->service->id}", ["name" => "service-x"], $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/services/{$this->service->id}", ["name" => "service-x"], $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST DELETE /api/v1/services{id} ====================================================================================
describe("DELETE /api/v1/services", function(){
    test('Request failed not authenticad', function() {
        deleteJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/services/{$this->service->id}", headers: $this->headers)
        ->assertStatus(204);
    });
});