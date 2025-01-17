<?php

use App\Models\MedicalRecord;
use App\Models\Pet;
use App\Models\User;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function(){
    $this->normalUser = User::create(["first_name" => "Normal", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => false]);
    $this->staffUser = User::create(["first_name" => "Normal", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => false, "is_staff" => true]);
    $this->superUser = User::create(["first_name" => "Super", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => true]);
    $this->pet = Pet::create(["user_id" => $this->normalUser->id, "name" => "petNormal", "species" => "x", "breed" => "y", "weight" => 20.0, "gender" => "M", "agressive" => true]);
    $this->medicalRecord = MedicalRecord::create(["access_code" => "3621qw351S3452", "user_id" => $this->normalUser->id, "pet_id" => $this->pet->id, "observation" => "observation test"]);
    $this->tokenNormalUser = $this->normalUser->createToken("auth", ["*"])->plainTextToken;
    $this->tokenStaffUser = $this->staffUser->createToken("auth", ["*"])->plainTextToken;
    $this->tokenSuperUser = $this->superUser->createToken("auth", ["*"])->plainTextToken;
    $this->headers = ["Content-Type" => "application/json", "X-Requested-With" => "XMLHttpRequest"];
    $this->data = ["user_id" => $this->normalUser->id, "pet_id" => $this->pet->id, "observation" => "observation test"];
    $this->structureGet = [
        "id",
        "access_code",
        "user_id",
        "pet_id",
        "created_at",
        "updated_at"
    ];
});

// TEST GET ALL /api/v1/medicalrecords ====================================================================================
describe("GET ALL /api/v1/medicalrecords", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/medicalrecords", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Request failed not authorized', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/medicalrecords", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/medicalrecords", headers: $this->headers)
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

// TEST GET ONE /api/v1/medicalrecords{id} ====================================================================================
describe("GET ONE /api/v1/medicalrecords", function(){
    test('Request failed not authenticad', function() {
        getJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        getJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/medicalrecords/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });    

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST GET RESULT /api/v1/medicalrecords/result ====================================================================================
describe("GET RESULT /api/v1/medicalrecords/result", function(){
    test('Request failed not found', function() {
        getJson("/api/v1/medicalrecords/result?code=123", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });    

    test('Request successful', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        getJson("/api/v1/medicalrecords/result?code={$this->medicalRecord->access_code}", headers: $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST POST /api/v1/medicalrecords ====================================================================================
describe("POST /api/v1/medicalrecords", function(){
    test('Request failed not authenticad', function() {
        postJson("/api/v1/medicalrecords", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with staff user', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenStaffUser}";
        postJson("/api/v1/medicalrecords", $this->data, $this->headers)
        ->assertStatus(201)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            $this->headers["Authorization"] = "Bearer {$this->tokenStaffUser}";
            postJson("/api/v1/medicalrecords", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["user_id", "pet_id", "observation"]);
    });
});

// TEST PUT /api/v1/medicalrecords{id} ====================================================================================
describe("PUT /api/v1/medicalrecords", function(){
    test('Request failed not authenticad', function() {
        putJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        putJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });

    describe("validation", function(){
        test('Request failed field required', function($field) {
            unset($this->data[$field]);
            $this->headers["Authorization"] = "Bearer {$this->tokenStaffUser}";
            putJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", $this->data, $this->headers)
            ->assertStatus(422)
            ->assertJsonStructure(["message", "errors"])
            ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
        })->with(["user_id", "pet_id", "observation"]);
    });
});

// TEST PATCH /api/v1/medicalrecords{id} ====================================================================================
describe("PATCH /api/v1/medicalrecords", function(){
    test('Request failed not authenticad', function() {
        patchJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        patchJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", $this->data, $this->headers)
        ->assertStatus(200)
        ->assertJsonStructure(["data" => $this->structureGet]);
    });
});

// TEST DELETE /api/v1/medicalrecords{id} ====================================================================================
describe("DELETE /api/v1/medicalrecords", function(){
    test('Request failed not authenticad', function() {
        deleteJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(401)
        ->assertJsonStructure(["message"]);
    });
    
    test('Unauthorized request failed, not superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenNormalUser}";
        deleteJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(403)
        ->assertJsonStructure(["message"]);
    });

    test('Request failed not found', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/medicalrecords/100", headers: $this->headers)
        ->assertStatus(404)
        ->assertJsonStructure(["message"]);
    });

    test('Request successful with superuser', function() {
        $this->headers["Authorization"] = "Bearer {$this->tokenSuperUser}";
        deleteJson("/api/v1/medicalrecords/{$this->medicalRecord->id}", headers: $this->headers)
        ->assertStatus(204);
    });
});