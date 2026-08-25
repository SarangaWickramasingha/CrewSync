<?php
require_once __DIR__ . '/../controllers/ProviderController.php';

function toggleProviderAvailability() {
    $controller = new ProviderController();
    $controller->toggleAvailability();
}
function getProviderAvailability() {
    $controller = new ProviderController();
    $controller->getAvailability();
}
function getProviderDashboardStats() {
    $controller = new ProviderController();
    $controller->getDashboardStats();
}
function getProviderCurrentWork() {
    $controller = new ProviderController();
    $controller->getCurrentWork();
}

function getProviderRecentReviews() {
    $controller = new ProviderController();
    $controller->getRecentReviews();
}
function getProviderJobRequests() {
    $controller = new ProviderController();
    $controller->getJobRequests();
}

function respondToProviderJobRequest($requestId) {
    $controller = new ProviderController();
    $controller->respondToJobRequest($requestId);
}
function getProviderTimeline() {
    $controller = new ProviderController();
    $controller->getTimeline();
}
function getProviderAllReviews() {
    $controller = new ProviderController();
    $controller->getAllReviews();
}
function uploadProviderReviewPhotos($reviewId) {
    $controller = new ProviderController();
    $controller->uploadReviewPhotos($reviewId);
}

function deleteProviderReviewPhoto($photoId) {
    $controller = new ProviderController();
    $controller->deleteReviewPhoto($photoId);
}

function getProviderProfile() {
    $controller = new ProviderController();
    $controller->getProfile();
}

function getPublicProviderProfile($providerId) {
    $controller = new ProviderController();
    $controller->getPublicProfile($providerId);
}

function updateProviderPersonalInfo() {
    $controller = new ProviderController();
    $controller->updatePersonalInfo();
}

function upsertProviderSkill() {
    $controller = new ProviderController();
    $controller->upsertSkill();
}

function removeProviderSkill($skillId) {
    $controller = new ProviderController();
    $controller->removeSkill($skillId);
}
function providerGetAllReviews() {
    getProviderAllReviews();
}
function providerUploadReviewPhotos($reviewId) {
    uploadProviderReviewPhotos($reviewId);
}
function providerDeleteReviewPhoto($photoId) {
    deleteProviderReviewPhoto($photoId);
}
function providerGetJobRequests() {
    getProviderJobRequests();
}
function providerRespondToJobRequest($requestId) {
    respondToProviderJobRequest($requestId);
}
