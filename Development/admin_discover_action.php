<?php
// admin_discover_action.php
include 'db_connect.php';

$action = $_POST['action'] ?? '';

if ($action == 'create') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $order = $_POST['display_order'];
    $image = $_POST['image_url']; // In a real app, handle $_FILES here

    $stmt = $conn->prepare("INSERT INTO explore_section (title, content, image_url, display_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $content, $image, $order);
    
    if ($stmt->execute()) echo json_encode(["success" => true]);
    else echo json_encode(["success" => false, "error" => $conn->error]);

} elseif ($action == 'update') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $order = $_POST['display_order'];
    $image = $_POST['image_url'];

    $stmt = $conn->prepare("UPDATE explore_section SET title=?, content=?, image_url=?, display_order=? WHERE explore_id=?");
    $stmt->bind_param("sssii", $title, $content, $image, $order, $id);

    if ($stmt->execute()) echo json_encode(["success" => true]);
    else echo json_encode(["success" => false, "error" => $conn->error]);

} elseif ($action == 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM explore_section WHERE explore_id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) echo json_encode(["success" => true]);
    else echo json_encode(["success" => false, "error" => $conn->error]);
}
?>