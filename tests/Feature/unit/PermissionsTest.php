<?php

use App\Models\Pet;
use App\Models\User;
use App\Services\Permissions;

beforeEach(function(){
    $this->normalUser = User::create(["first_name" => "Normal", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => false]);
    $this->superUser =User::create(["first_name" => "Super", "last_name" => "User", "phone" => 5500000000000, "email" => fake()->unique()->email, "password" => "12345", "is_superuser" => true]);
    $this->petNormalUser = Pet::create(["user_id" => $this->normalUser->id, "name" => "petNormal", "species" => "x", "breed" => "y", "weight" => 20.0, "gender" => "M", "agressive" => true]);
    $this->petSuperUser = Pet::create(["user_id" => $this->superUser->id, "name" => "petSuper", "species" => "x", "breed" => "y", "weight" => 20.0, "gender" => "M", "agressive" => true]);
});

test("Function IsSuperuser return true with superuser", function(){
    $result = Permissions::IsSuperuser($this->superUser);
    expect($result)->toBe(true);
});

test("Function IsSuperuser return false without superuser", function(){
    $result = Permissions::IsSuperuser($this->normalUser);
    expect($result)->toBe(false);
});
#----------------------------------------------------------------------
test("Function IsSuperuserOrMe return true with superuser", function(){
    $result = Permissions::IsSuperuserOrMe($this->superUser, $this->normalUser->id);
    expect($result)->toBe(true);
});

test("Function IsSuperuserOrMe return true with owner user", function(){
    $result = Permissions::IsSuperuserOrMe($this->normalUser, $this->normalUser->id);
    expect($result)->toBe(true);
});

test("Function IsSuperuserOrMe return false not superuser and owner", function(){
    $result = Permissions::IsSuperuserOrMe($this->normalUser, $this->superUser->id);
    expect($result)->toBe(false);
});
#----------------------------------------------------------------------
test("Function IsSuperuserOrMyPet return true with superuser", function(){
    $result = Permissions::IsSuperuserOrMyPet($this->superUser, $this->normalUser->id);
    expect($result)->toBe(true);
});

test("Function IsSuperuserOrMyPet return true with owner user", function(){
    $result = Permissions::IsSuperuserOrMyPet($this->normalUser, $this->petNormalUser->id);
    expect($result)->toBe(true);
});

test("Function IsSuperuserOrMyPet return false not superuser and owner", function(){
    $result = Permissions::IsSuperuserOrMyPet($this->normalUser, $this->petSuperUser->id);
    expect($result)->toBe(false);
});