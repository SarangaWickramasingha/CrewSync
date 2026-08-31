<?php
require_once __DIR__ . '/../controllers/ReviewController.php';

function getAssignedReviewProviders() {
    $controller = new ReviewController();
    $controller->getAssignedProviders();
}

function createReview() {
    $controller = new ReviewController();
    $controller->create();
}

function getMyReviews() {
    $controller = new ReviewController();
    $controller->getMyReviews();
}
