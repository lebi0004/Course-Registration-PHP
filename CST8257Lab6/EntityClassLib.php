<?php
class User {
    private $userId;
    private $name;
    private $phone;
    private $password; // Add this property to store the hashed password

    // Constructor to initialize the User object
    public function __construct($userId, $name, $phone, $password) {
        $this->userId = $userId;
        $this->name = $name;
        $this->phone = $phone;
        $this->password = $password; // Store the hashed password
    }

    // Getter methods to retrieve user properties
    public function getUserId() {
        return $this->userId;
    }

    public function getName() {
        return $this->name;
    }

    public function getPhone() {
        return $this->phone;
    }

    // Getter for the hashed password
    public function getPassword() {
        return $this->password;
    }
}
