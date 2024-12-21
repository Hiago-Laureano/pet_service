<?php

use App\Models\User;

use function Pest\Laravel\postJson;

beforeEach(function(){
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken("auth", ["*"])->plainTextToken;
    $this->headers = ["Content-Type" => "application/json", "X-Requested-With" => "XMLHttpRequest"];
});

// TEST /api/v1/auth ====================================================================================
test("login is successful", function(){
    $data = ["email" => $this->user->email, "password" => "12345"];
    postJson("/api/v1/auth", $data, $this->headers)
    ->assertStatus(200)
    ->assertJsonStructure(["token", "name", "email"]);
});

test("login failed incorrect password", function(){
    $data = ["email" => $this->user->email, "password" => "123p"];
    postJson("/api/v1/auth", $data, $this->headers)
    ->assertStatus(403)
    ->assertJsonStructure(["message"]);
});

test("login failed incorrect email", function(){
    $data = ["email" => "test@test.t", "password" => "12345"];
    postJson("/api/v1/auth", $data, $this->headers)
    ->assertStatus(403)
    ->assertJsonStructure(["message"]);
});

describe("validation", function(){
    test("required field", function(string $field){
        $data = ["email" => $this->user->email, "password" => "12345"];
        unset($data[$field]);
        postJson("/api/v1/auth", $data, $this->headers)
        ->assertStatus(422)
        ->assertJsonStructure(["message", "errors"])
        ->assertJsonValidationErrors([$field => trans("validation.required", ["attribute" => $field])]);
    })->with(["email", "password"]);

    test("email field not in email format", function(){
        $data = ["email" => "test", "password" => "12345"];
        postJson("/api/v1/auth", $data, $this->headers)
        ->assertStatus(422)
        ->assertJsonStructure(["message", "errors"])
        ->assertJsonValidationErrors(["email" => trans("validation.email", ["attribute" => "email"])]);
    });
});

// TEST /api/v1/logout ====================================================================================
test("logout is successful", function(){
    $this->headers["Authorization"] = "Bearer {$this->token}";
    postJson("/api/v1/logout", headers: $this->headers)
    ->assertStatus(204);
});

test("logout failed not authenticated", function(){
    postJson("/api/v1/logout", headers: $this->headers)
    ->assertStatus(401)
    ->assertJsonStructure(["message"]);
});