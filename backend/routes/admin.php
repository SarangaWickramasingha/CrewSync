<?php

require_once __DIR__ . '/../controllers/AdminController.php';

function adminGetStats() {
    (new AdminController())->getStats();
}

// Users
function adminGetAllUsers() {
    (new AdminController())->getAllUsers();
}
function adminGetUserById($id) {
    (new AdminController())->getUserById($id);
}
function adminCreateUser() {
    (new AdminController())->createUser();
}
function adminUpdateUser($id) {
    (new AdminController())->updateUser($id);
}
function adminDeleteUser($id) {
    (new AdminController())->deleteUser($id);
}

// Role-specific
function adminGetPropertyOwners() {
    (new AdminController())->getPropertyOwners();
}
function adminGetServiceProviders() {
    (new AdminController())->getServiceProviders();
}
function adminGetMaterialSuppliers() {
    (new AdminController())->getMaterialSuppliers();
}

// Reviews
function adminGetAllReviews() {
    (new AdminController())->getAllReviews();
}
function adminDeleteReview($id) {
    (new AdminController())->deleteReview($id);
}

// Feedback
function adminGetAllFeedback() {
    (new AdminController())->getAllFeedback();
}
function adminUpdateFeedback($id) {
    (new AdminController())->updateFeedback($id);
}

// Projects
function adminGetAllProjects() {
    (new AdminController())->getAllProjects();
}
function adminGetProjectWithTasks($id) {
    (new AdminController())->getProjectWithTasks($id);
}
