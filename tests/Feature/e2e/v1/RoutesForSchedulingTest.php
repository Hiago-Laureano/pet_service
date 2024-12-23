<?php

use App\Models\Pet;
use App\Models\Scheduling;
use App\Models\Service;
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
    $this->service = Service::create(["name" => "service-x", "price" => 30.99]);
    $this->schedulingNormalUser = Scheduling::create(["user_id" => $this->normalUser->id, "pet_id" => $this->petNormalUser->id, "service_id" => $this->service->id, "date" => "2025-02-20 13:20:00", "finished" => false]);
    $this->schedulingSuperUser = Scheduling::create(["user_id" => $this->superUser->id, "pet_id" => $this->petSuperUser->id, "service_id" => $this->service->id, "date" => "2025-02-20 13:20:00", "finished" => false]);
    $this->tokenNormalUser = $this->normalUser->createToken("auth", ["*"])->plainTextToken;
    $this->tokenSuperUser = $this->superUser->createToken("auth", ["*"])->plainTextToken;
    $this->headers = ["Content-Type" => "application/json", "X-Requested-With" => "XMLHttpRequest"];
    $this->data = ["pet_id" => $this->petNormalUser->id, "service_id" => $this->service->id, "date" => "2025-02-20 13:20:00"];
    $this->structureGet = [
        "id",
        "user_id",
        "pet_id",
        "service_id",
        "date",
        "finished",
        "created_at",
        "updated_at"
    ];
});

// TEST GET ALL /api/v1/schedulings ====================================================================================
describe("GET ALL /api/v1/schedulings", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/schedulings", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/schedulings", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/schedulings", headers: $this->headers)
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

// TEST GET ONE /api/v1/schedulings{id} ====================================================================================
describe("GET ONE /api/v1/schedulings", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/schedulings/{$this->schedulingSuperUser->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/schedulings/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });    

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST POST /api/v1/schedulings ====================================================================================
describe("POST /api/v1/schedulings", function(){
    test('Request failed not authenticad', function() {
        postJson("/api/v1/schedulings", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with normal user and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        postJson("/api/v1/schedulings", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with normal user and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        $this->data["user_id"] = $this->normalUser->id;
        postJson("/api/v1/schedulings", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        postJson("/api/v1/schedulings", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->superUser->id]]);
    });

    test('Request successful with superuser and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        $this->data["user_id"] = $this->normalUser->id;
        postJson("/api/v1/schedulings", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
            postJson("/api/v1/schedulings", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["pet_id", "service_id", "date"]);
    });
});

// TEST PUT /api/v1/schedulings{id} ====================================================================================
describe("PUT /api/v1/schedulings", function(){
    test('Request failed not authenticad', function() {
        putJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/schedulings/{$this->schedulingSuperUser->id}", $this->data, $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with owner user and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        putJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with owner user and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        $this->data["user_id"] = $this->normalUser->id;
        putJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/schedulings/{$this->schedulingSuperUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->superUser->id]]);
    });

    test('Request successful with superuser and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        $this->data["user_id"] = $this->normalUser->id;
        putJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/schedulings/100", $this->data, $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });
    describe("validation", function(){
        test('Request failed field required', function($field) {
            $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
            unset($this->data[$field]);
            putJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["pet_id", "service_id", "date"]);
    });
});

// TEST PATCH /api/v1/schedulings{id} ====================================================================================
describe("PATCH /api/v1/schedulings", function(){
    test('Request failed not authenticad', function() {
        patchJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", ["date" => "2025-03-12 13:20:00"], $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/schedulings/{$this->schedulingSuperUser->id}", ["date" => "2025-03-12 13:20:00"], $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/schedulings/100", ["date" => "2025-03-12 13:20:00"], $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with owner user and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        patchJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with owner user and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        $this->data["user_id"] = $this->normalUser->id;
        patchJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and not defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });

    test('Request successful with superuser and defined user_id', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        $this->data["user_id"] = $this->normalUser->id;
        patchJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet])
        ->assertJson(["data" => ["user_id" => $this->normalUser->id]]);
    });
});

// TEST DELETE /api/v1/schedulings{id} ====================================================================================
describe("DELETE /api/v1/schedulings", function(){
    test('Request failed not authenticad', function() {
        deleteJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not owner or superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/schedulings/{$this->schedulingSuperUser->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/schedulings/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", headers: $this->headers)
        ->assertStatus(204);
    });

    test('Request successful with user owner', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/schedulings/{$this->schedulingNormalUser->id}", headers: $this->headers)
        ->assertStatus(204);
    });
});