<?php 
session_start(); 
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "bookstore_db";
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("gagal koneksi". $conn->connect_error);
}