<?php
require_once __DIR__ . '/../controllers/SupplierController.php';

function getSupplierProducts() {
    $controller = new SupplierController();
    $controller->getProducts();
}

function upsertSupplierProduct() {
    $controller = new SupplierController();
    $controller->upsertProduct();
}

function removeSupplierProduct($id) {
    $controller = new SupplierController();
    $controller->removeProduct($id);
}

function getSupplierOrders() {
    $controller = new SupplierController();
    $controller->getOrders();
}

function createSupplierOrder() {
    $controller = new SupplierController();
    $controller->createOrder();
}

function updateSupplierOrderStatus($orderId) {
    $controller = new SupplierController();
    $controller->updateOrderStatus($orderId);
}

function getSupplierProfile() {
    $controller = new SupplierController();
    $controller->getProfile();
}

function updateSupplierProfile() {
    $controller = new SupplierController();
    $controller->updateProfile();
}