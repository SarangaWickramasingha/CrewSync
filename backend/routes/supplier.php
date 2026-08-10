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