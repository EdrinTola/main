<?php
require_once 'Recipe.php';
session_start();

if (!isset($_SESSION['name']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login.php");
    exit;
}

$recipeModel = new Recipe();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $recipeModel->createRecipe($_POST['title'], $_POST['description'], $_POST['image_url'], $_SESSION['name']);
    } elseif (isset($_POST['update'])) {
        $recipeModel->updateRecipe($_POST['id'], $_POST['title'], $_POST['description'], $_POST['image_url']);
    } elseif (isset($_POST['delete'])) {
        $recipeModel->deleteRecipe($_POST['id']);
    }
    header("Location: admin_recipes.php"); 
    exit;
}

$recipes = $recipeModel->getAllRecipes();
?>
